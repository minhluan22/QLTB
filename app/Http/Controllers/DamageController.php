<?php

namespace App\Http\Controllers;

use App\Models\Damage;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * DamageController - Quản lý báo hỏng / báo mất thiết bị
 *
 * Logic cốt lõi:
 *  - Khi báo HỎNG: device.damaged_qty  += qty  &  device.available_qty -= qty (nếu chưa trừ)
 *  - Khi báo MẤT : device.lost_qty     += qty  &  device.available_qty -= qty (nếu chưa trừ)
 *
 *  Ghi chú: nếu báo hỏng/mất đi kèm khi TRẢ (qua ReturnController),
 *  available_qty đã được cộng lại trước → ở đây phải trừ lại.
 *  Nếu báo hỏng ĐỘC LẬP (thiết bị đang trong kho), trừ available_qty.
 */
class DamageController extends Controller
{
    /**
     * Danh sách báo hỏng / mất
     */
    public function index()
    {
        $query = Damage::with(['device', 'reporter', 'borrowDetail.borrowRequest']);

        // Giáo viên chỉ thấy các báo hỏng do mình báo, Admin thấy toàn bộ
        if (auth()->user()->isTeacher()) {
            $query->where('reported_by', auth()->id());
        }

        // Lọc theo loại
        if (request()->filled('damage_type')) {
            $query->where('damage_type', request('damage_type'));
        }

        // Tìm kiếm và lọc nâng cao cho Admin
        if (auth()->user()->isAdmin()) {
            if (request()->filled('search')) {
                $search = request('search');
                $query->whereHas('device', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            if (request()->filled('from_date')) {
                $query->whereDate('created_at', '>=', request('from_date'));
            }

            if (request()->filled('to_date')) {
                $query->whereDate('created_at', '<=', request('to_date'));
            }
        }

        $damages = $query->latest()->paginate(10);

        return view('damages.index', compact('damages'));
    }

    /**
     * Form báo hỏng / mất độc lập (không qua return)
     */
    public function create()
    {
        $devices = Device::orderBy('name')->get();
        return view('damages.create', compact('devices'));
    }

    /**
     * Xuất Excel báo cáo sự cố (Chỉ dành cho Admin)
     */
    public function export(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DamageExport($request->all()), 'bao-cao-su-co.xlsx');
    }

    /**
     * Lưu báo hỏng / mất
     *
     * Đây là báo cáo ĐỘC LẬP (không gắn với trả thiết bị).
     * Thiết bị có thể đang trong kho hoặc đã trả về nhưng phát hiện sau.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id'     => ['required', 'exists:devices,id'],
            'damage_type'   => ['required', 'in:hỏng,mất'],
            'quantity'      => ['required', 'integer', 'min:1'],
            'detected_date' => ['required', 'date'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'cause'         => ['nullable', 'string', 'max:255'],
            'severity'      => ['required_if:damage_type,hỏng', 'in:minor,moderate,severe'],
            'resolution'    => ['nullable', 'string', 'max:255'],
        ], [
            'device_id.required'     => 'Vui lòng chọn thiết bị.',
            'damage_type.required'   => 'Vui lòng chọn loại sự cố.',
            'quantity.required'      => 'Số lượng không được để trống.',
            'detected_date.required' => 'Ngày phát hiện không được để trống.',
            'severity.required_if'   => 'Vui lòng chọn mức độ hỏng.',
        ]);

        $validated['reported_by'] = auth()->id();

        DB::transaction(function () use ($validated) {
            $device = Device::findOrFail($validated['device_id']);
            $qty    = $validated['quantity'];

            // Kiểm tra số lượng hợp lệ
            if ($validated['damage_type'] === 'hỏng') {
                // Không được báo hỏng nhiều hơn số còn lại khả dụng
                if ($qty > $device->available_qty) {
                    throw new \Exception(
                        "Số lượng báo hỏng ({$qty}) vượt quá số lượng có thể báo ({$device->available_qty})."
                    );
                }
                $device->damaged_qty  += $qty;
                $device->available_qty -= $qty;
            } else {
                // Báo mất
                if ($qty > $device->available_qty) {
                    throw new \Exception(
                        "Số lượng báo mất ({$qty}) vượt quá số lượng có thể báo ({$device->available_qty})."
                    );
                }
                $device->lost_qty     += $qty;
                $device->available_qty -= $qty;
            }

            // Đảm bảo không âm
            $device->available_qty = max(0, $device->available_qty);
            $device->updateStatus();

            // Lưu bản ghi báo hỏng
            Damage::create($validated);
        });

        return redirect()->route('damages.index')
            ->with('success', 'Đã ghi nhận báo ' . $validated['damage_type'] . ' thiết bị thành công!');
    }
}
