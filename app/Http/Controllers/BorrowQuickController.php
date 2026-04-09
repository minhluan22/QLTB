<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\BorrowRequest;
use App\Models\BorrowDetail;
use App\Models\DeviceReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowQuickController extends Controller
{
    /**
     * Danh sách thiết bị để mượn/trả nhanh
     */
    public function index(Request $request)
    {
        // 1. Lấy query đã được lọc
        $devices = $this->getFilteredDevicesQuery($request)->get()->unique('id');

        // 2. Chuyển đổi sang danh sách phẳng (Flat List) tùy vai trò
        $flatList = $this->transformToFlatList($devices);

        // 3. Phân trang thủ công 10 item / trang
        $perPage   = 10;
        $page      = $request->input('page', 1);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $flatList->forPage($page, $perPage)->values(),
            $flatList->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('borrow-quick.index', [
            'devices'  => $devices,
            'flatList' => $paginator,
        ]);
    }

    /**
     * Mượn thiết bị nhanh
     */
    public function borrow(Request $request, Device $device)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $device->available_qty,
            'expected_return_date' => 'required|date|after_or_equal:today',
        ], [
            'quantity.max' => 'Số lượng mượn không thể vượt quá số lượng còn lại trong kho (' . $device->available_qty . ').',
        ]);

        DB::transaction(function () use ($request, $device) {
            $borrowReq = BorrowRequest::create([
                'user_id' => auth()->id(),
                'status' => 'borrowing',
                'purpose' => 'Mượn thiết bị nhanh',
                'class_name' => '',
                'borrow_date' => now(),
                'expected_return_date' => \Carbon\Carbon::parse($request->expected_return_date)->endOfDay(),
            ]);

            BorrowDetail::create([
                'borrow_request_id' => $borrowReq->id,
                'device_id' => $device->id,
                'quantity' => $request->quantity,
            ]);

            $device->available_qty -= $request->quantity;
            $device->updateStatus();
        });

        return back()->with('success', 'Mượn thiết bị thành công!');
    }

    /**
     * Trả thiết bị nhanh
     */
    public function returnEquipment(Request $request, BorrowRequest $borrowRequest)
    {
        if (!in_array($borrowRequest->status, ['borrowing', 'overdue'])) {
            return back()->with('error', 'Phiếu này không ở trạng thái cần trả.');
        }

        $detail = $borrowRequest->borrowDetails->first();
        if (!$detail) {
            return back()->with('error', 'Lỗi: Không tìm thấy chi tiết thiết bị.');
        }

        $request->validate([
            'normal_qty' => 'nullable|integer|min:0',
            'damaged_qty' => 'nullable|integer|min:0',
            'lost_qty' => 'nullable|integer|min:0',
            'normal_note' => 'nullable|string',
            'damaged_note' => 'nullable|string',
            'damaged_detected_date' => 'nullable|date',
            'damaged_severity' => 'nullable|string',
            'damaged_cause' => 'nullable|string',
            'damaged_resolution' => 'nullable|string',
            'lost_note' => 'nullable|string',
            'lost_detected_date' => 'nullable|date',
            'lost_cause' => 'nullable|string',
            'lost_resolution' => 'nullable|string',
        ]);

        $nQty = intval($request->input('normal_qty', 0));
        $dQty = intval($request->input('damaged_qty', 0));
        $lQty = intval($request->input('lost_qty', 0));
        $returnQty = $nQty + $dQty + $lQty;

        if ($returnQty <= 0 || $returnQty > $detail->quantity) {
            return back()->with('error', 'Số lượng trả không hợp lệ!');
        }

        $device = $detail->device;

        DB::transaction(function () use ($borrowRequest, $detail, $device, $returnQty, $nQty, $dQty, $lQty, $request) {
            if ($returnQty < $detail->quantity) {
                $detail->quantity -= $returnQty;
                $detail->save();

                $returnedRequest = $borrowRequest->replicate();
                $returnedRequest->status = 'returned';
                $returnedRequest->save();

                BorrowDetail::create([
                    'borrow_request_id' => $returnedRequest->id,
                    'device_id' => $device->id,
                    'quantity' => $returnQty,
                ]);

                $activeRequest = $returnedRequest;
            } else {
                $borrowRequest->update(['status' => 'returned']);
                $activeRequest = $borrowRequest;
            }

            if ($nQty > 0) $device->available_qty += $nQty;
            if ($dQty > 0) {
                $device->damaged_qty += $dQty;
                \App\Models\Damage::create([
                    'borrow_detail_id' => $activeRequest->borrowDetails->first()->id,
                    'device_id' => $device->id,
                    'damage_type' => 'hỏng',
                    'quantity' => $dQty,
                    'detected_date' => $request->damaged_detected_date ?? now(),
                    'severity' => $request->damaged_severity,
                    'cause' => $request->damaged_cause,
                    'resolution' => $request->damaged_resolution,
                    'description' => $request->damaged_note ?? 'Báo hỏng khi trả thiết bị nhanh',
                    'reported_by' => auth()->id()
                ]);
            }
            if ($lQty > 0) {
                $device->lost_qty += $lQty;
                \App\Models\Damage::create([
                    'borrow_detail_id' => $activeRequest->borrowDetails->first()->id,
                    'device_id' => $device->id,
                    'damage_type' => 'mất',
                    'quantity' => $lQty,
                    'detected_date' => $request->lost_detected_date ?? now(),
                    'cause' => $request->lost_cause,
                    'resolution' => $request->lost_resolution,
                    'description' => $request->lost_note ?? 'Báo mất khi trả thiết bị nhanh',
                    'reported_by' => auth()->id()
                ]);
            }

            $device->updateStatus();

            DeviceReturn::create([
                'borrow_request_id' => $activeRequest->id,
                'returned_by' => auth()->id(),
                'return_date' => now(),
                'note' => $request->normal_note
            ]);
        });

        return back()->with('success', 'Đã trả '. $returnQty .' thiết bị thành công!');
    }

    // ================= HELPER METHODS =================

    /**
     * Xây dựng Query thiết bị dựa trên bộ lọc
     */
    private function getFilteredDevicesQuery(Request $request)
    {
        $search = $request->input('search');
        $subjectGroup = $request->input('subject_group');
        $borrower = auth()->user()->isAdmin() ? $request->input('borrower') : null;
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Device::with(['borrowDetails' => function($q) use ($borrower, $dateFrom, $dateTo) {
            $q->whereHas('borrowRequest', function($sub) use ($borrower, $dateFrom, $dateTo) {
                $sub->whereIn('status', ['borrowing', 'overdue']);
                if (!empty($borrower)) {
                    $sub->whereHas('user', fn($u) => $u->where('name', 'LIKE', "%{$borrower}%"));
                }
                if (!empty($dateFrom)) $sub->whereDate('borrow_date', '>=', $dateFrom);
                if (!empty($dateTo))   $sub->whereDate('expected_return_date', '<=', $dateTo);
            })->with('borrowRequest.user');
        }]);

        if (!empty($search)) {
            $query->where(fn($q) => $q->where('name', 'LIKE', "%{$search}%")->orWhere('code', 'LIKE', "%{$search}%"));
        }
        if (!empty($subjectGroup)) $query->where('subject_group', $subjectGroup);

        if (!empty($borrower) || !empty($dateFrom) || !empty($dateTo)) {
            $query->whereHas('borrowDetails.borrowRequest', function($q) use ($borrower, $dateFrom, $dateTo) {
                $q->whereIn('status', ['borrowing', 'overdue']);
                if (!empty($borrower)) {
                    $q->whereHas('user', fn($u) => $u->where('name', 'LIKE', "%{$borrower}%"));
                }
                if (!empty($dateFrom)) $q->whereDate('borrow_date', '>=', $dateFrom);
                if (!empty($dateTo))   $q->whereDate('expected_return_date', '<=', $dateTo);
            });
        }

        return $query->latest();
    }

    /**
     * Chuyển đổi sang Flat List theo vai trò người dùng
     */
    private function transformToFlatList($devices)
    {
        if (auth()->user()->isAdmin()) {
            return $this->transformAdminList($devices);
        }
        return $this->transformTeacherList($devices);
    }

    /**
     * Danh sách Admin: Mỗi detail là một dòng
     */
    private function transformAdminList($devices)
    {
        $flatList = collect();
        foreach ($devices as $device) {
            if ($device->borrowDetails->isEmpty()) {
                $flatList->push((object)[
                    'device' => $device, 'is_borrowed' => false, 'borrower_name' => '', 'borrower_id' => null,
                    'borrow_date' => null, 'return_date' => null, 'quantity' => 0, 'borrow_request_id' => null,
                ]);
            } else {
                foreach ($device->borrowDetails as $detail) {
                    $req = $detail->borrowRequest;
                    $flatList->push((object)[
                        'device' => $device, 'is_borrowed' => true, 'borrower_name' => $req->user->name ?? 'N/A', 'borrower_id' => $req->user_id,
                        'borrow_date' => $req->borrow_date, 'return_date' => $req->expected_return_date, 'quantity' => $detail->quantity, 'borrow_request_id' => $req->id,
                    ]);
                }
            }
        }
        return $flatList->sortByDesc('is_borrowed')->values();
    }

    /**
     * Danh sách Giáo viên: Mỗi thiết bị là một dòng
     */
    private function transformTeacherList($devices)
    {
        $flatList = collect();
        foreach ($devices as $device) {
            $myActiveBorrows = $device->borrowDetails->filter(fn($d) => $d->borrowRequest->user_id === auth()->id());

            $flatList->push((object)[
                'device'            => $device,
                'is_borrowed'       => $myActiveBorrows->isNotEmpty(),
                'borrower_id'       => auth()->id(),
                'borrow_request_id' => $myActiveBorrows->isNotEmpty() ? $myActiveBorrows->first()->borrowRequest->id : null,
                'active_borrows'    => $myActiveBorrows,
            ]);
        }
        return $flatList->sortByDesc(fn($i) => ($i->is_borrowed ? 100 : 0) + $i->device->available_qty)->values();
    }
}
