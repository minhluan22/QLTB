<?php

namespace App\Http\Controllers;

use App\Models\BorrowRequest;
use App\Models\BorrowDetail;
use App\Models\DeviceReturn;
use App\Models\Damage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ReturnController - Xử lý trả thiết bị
 *
 * Luồng khi trả có báo hỏng/mất:
 *   1. Cộng lại available_qty (thiết bị về kho)
 *   2. Nếu hỏng → damaged_qty += qty, available_qty -= qty
 *   3. Nếu mất  → lost_qty   += qty, available_qty -= qty
 *   4. updateStatus()
 */
class ReturnController extends Controller
{
    /**
     * Danh sách tất cả lần trả thiết bị (Admin)
     */
    public function index(Request $request)
    {
        $returns = DeviceReturn::with([
            'borrowRequest.user',
            'borrowRequest.borrowDetails.device',
            'returnedBy',
        ])->latest()->paginate(15);

        return view('returns.index', compact('returns'));
    }

    /**
     * Form trả thiết bị
     */
    public function create(Request $request)
    {
        $borrowRequestId = $request->get('borrow_request_id');

        $borrowRequest = BorrowRequest::with('borrowDetails.device')
            ->whereIn('status', ['borrowing', 'overdue'])
            ->findOrFail($borrowRequestId);

        return view('returns.create', compact('borrowRequest'));
    }

    /**
     * Xử lý trả thiết bị
     *
     * Bước 1: Cộng lại available_qty cho từng thiết bị đã trả
     * Bước 2: Nếu có báo hỏng/mất → trừ available_qty & tăng damaged_qty / lost_qty
     * Bước 3: updateStatus() cho mỗi thiết bị bị ảnh hưởng
     * Bước 4: Đánh dấu yêu cầu = 'returned'
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrow_request_id'           => ['required', 'exists:borrow_requests,id'],
            'return_date'                 => ['required', 'date'],
            'note'                        => ['nullable', 'string'],
            // Mảng báo hỏng/mất khi trả (optional)
            'damages'                     => ['nullable', 'array'],
            'damages.*.borrow_detail_id'  => ['required', 'exists:borrow_details,id'],
            'damages.*.damage_type'       => ['required', 'in:hỏng,mất'],
            'damages.*.quantity'          => ['required', 'integer', 'min:1'],
            'damages.*.detected_date'     => ['nullable', 'date'],
            'damages.*.description'       => ['required', 'string'],
            'damages.*.cause'             => ['nullable', 'string'],
            'damages.*.severity'          => ['nullable', 'in:minor,moderate,severe'],
            'damages.*.resolution'        => ['nullable', 'string'],
        ]);

        $borrowRequest = BorrowRequest::with('borrowDetails.device')
            ->findOrFail($validated['borrow_request_id']);

        if (!in_array($borrowRequest->status, ['borrowing', 'overdue'])) {
            return back()->with('error', 'Yêu cầu này chưa được mượn hoặc đã được trả rồi.');
        }

        DB::transaction(function () use ($validated, $borrowRequest) {
            // ── Bước 1: Cộng lại available_qty (thiết bị về kho) ────────────
            foreach ($borrowRequest->borrowDetails as $detail) {
                $device = $detail->device;
                $device->available_qty += $detail->quantity;
                $device->save(); // lưu tạm, updateStatus() sẽ gọi sau
            }

            // ── Bước 2: Xử lý báo hỏng / mất kèm theo khi trả ──────────────
            if (!empty($validated['damages'])) {
                foreach ($validated['damages'] as $dmg) {
                    $detail = BorrowDetail::with('device')->find($dmg['borrow_detail_id']);
                    $device = $detail->device;
                    $qty    = $dmg['quantity'];

                    // Ghi nhận bản ghi damage
                    Damage::create([
                        'borrow_detail_id' => $dmg['borrow_detail_id'],
                        'device_id'        => $detail->device_id,
                        'damage_type'      => $dmg['damage_type'],
                        'quantity'         => $qty,
                        'detected_date'    => $dmg['detected_date'] ?? $validated['return_date'],
                        'description'      => $dmg['description'],
                        'cause'            => $dmg['cause'] ?? null,
                        'severity'         => $dmg['severity'] ?? 'minor',
                        'resolution'       => $dmg['resolution'] ?? null,
                        'reported_by'      => auth()->id(),
                    ]);

                    // Cập nhật thiết bị theo loại
                    if ($dmg['damage_type'] === 'hỏng') {
                        $device->damaged_qty  += $qty;
                    } else {
                        $device->lost_qty += $qty;
                    }
                    $device->available_qty  = max(0, $device->available_qty - $qty);
                    $device->save();
                }
            }

            // ── Bước 3: Cập nhật trạng thái tất cả thiết bị liên quan ───────
            foreach ($borrowRequest->borrowDetails as $detail) {
                $detail->device->refresh()->updateStatus();
            }

            // ── Bước 4: Ghi nhận trả và đổi trạng thái yêu cầu ─────────────
            DeviceReturn::create([
                'borrow_request_id' => $borrowRequest->id,
                'returned_by'       => auth()->id(),
                'return_date'       => $validated['return_date'],
                'note'              => $validated['note'] ?? null,
            ]);

            $borrowRequest->update(['status' => 'returned']);
        });

        return redirect()->route('borrow-requests.index')
            ->with('success', 'Đã trả thiết bị thành công!');
    }
}
