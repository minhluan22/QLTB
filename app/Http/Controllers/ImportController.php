<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\Device;
use Illuminate\Http\Request;

/**
 * ImportController - Quản lý nhập thiết bị
 *
 * Admin nhập thiết bị mới vào kho, hệ thống tự cộng số lượng vào thiết bị.
 */
class ImportController extends Controller
{
    /**
     * Danh sách các lần nhập hàng
     */
    public function index(Request $request)
    {
        $query = Import::with(['device', 'importer']);

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        $imports = $query->latest()->paginate(15);
        $devices = Device::orderBy('name')->get();

        return view('imports.index', compact('imports', 'devices'));
    }

    /**
     * Form nhập thiết bị
     */
    public function create()
    {
        $devices = Device::orderBy('name')->get();
        return view('imports.create', compact('devices'));
    }

    /**
     * Xử lý nhập thiết bị: cập nhật số lượng thiết bị
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id'   => ['required', 'exists:devices,id'],
            'quantity'    => ['required', 'integer', 'min:1'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'supplier'    => ['nullable', 'string', 'max:255'],
            'import_date' => ['required', 'date'],
            'note'        => ['nullable', 'string'],
        ], [
            'device_id.required'   => 'Vui lòng chọn thiết bị.',
            'device_id.exists'     => 'Thiết bị không tồn tại.',
            'quantity.required'    => 'Số lượng không được để trống.',
            'quantity.min'         => 'Số lượng phải lớn hơn 0.',
            'import_date.required' => 'Ngày nhập không được để trống.',
        ]);

        $validated['imported_by'] = auth()->id();

        // Lưu bản ghi nhập hàng
        Import::create($validated);

        // Cập nhật số lượng thiết bị
        $device = Device::findOrFail($validated['device_id']);
        $device->quantity      += $validated['quantity'];
        $device->available_qty += $validated['quantity'];

        // Cập nhật đơn giá mới nhất nếu có
        if (!empty($validated['price'])) {
            $device->unit_price = $validated['price'];
        }

        if ($device->status !== 'maintenance' && $device->status !== 'damaged') {
            $device->status = 'available';
        }
        $device->save();

        return redirect()->route('imports.index')
            ->with('success', 'Đã nhập ' . $validated['quantity'] . ' ' . $device->name . ' thành công!');
    }
}
