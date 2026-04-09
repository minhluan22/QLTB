<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Exports\DevicesMultipleSheetsExport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * DeviceController - CRUD đầy đủ cho thiết bị
 *
 * Fields mới: subject, unit, specification, country, unit_price
 */
class DeviceController extends Controller
{
    /**
     * Xuất Excel Tổng hợp (2 Sheet) kèm Filter
     */
    public function export(Request $request)
    {
        $filters = $request->only(['subject_group', 'devices', 'import_month', 'import_year']);
        return Excel::download(new DevicesMultipleSheetsExport($filters), 'thiet-bi-tong-hop.xlsx');
    }

    /**
     * Danh sách thiết bị (có tìm kiếm và lọc)
     */
    public function index(Request $request)
    {
        $query = Device::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name',    'like', '%' . $request->search . '%')
                  ->orWhere('code',  'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        if ($request->filled('subject_group')) {
            $query->where('subject_group', $request->subject_group);
        }

        $devices    = $query->latest()->paginate(10);
        $categories = Device::distinct()->pluck('category')->filter()->sort()->values();
        $subjects   = Device::distinct()->pluck('subject')->filter()->sort()->values();
        $subjectGroups = Device::distinct()->pluck('subject_group')->filter()->sort()->values();

        return view('devices.index', compact('devices', 'categories', 'subjects', 'subjectGroups'));
    }

    /**
     * Form thêm thiết bị mới
     */
    public function create()
    {
        $categories = Device::distinct()->pluck('category')->filter()->sort()->values();
        $subjects   = Device::distinct()->pluck('subject')->filter()->sort()->values();
        return view('devices.create', compact('categories', 'subjects'));
    }

    /**
     * Lưu thiết bị mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'          => ['required', 'string', 'max:50', 'unique:devices'],
            'name'          => ['required', 'string', 'max:255'],
            'category'      => ['nullable', 'string', 'max:100'],
            'subject'       => ['nullable', 'string', 'max:100'],
            'subject_group' => ['nullable', 'string', 'max:100'],
            'unit'          => ['nullable', 'string', 'max:50'],
            'specification' => ['nullable', 'string', 'max:255'],
            'country'       => ['nullable', 'string', 'max:100'],
            'unit_price'    => ['nullable', 'numeric', 'min:0'],
            'quantity'      => ['required', 'integer', 'min:1'],
            'description'   => ['nullable', 'string'],
        ], [
            'code.required'     => 'Mã thiết bị không được để trống.',
            'code.unique'       => 'Mã thiết bị đã tồn tại.',
            'name.required'     => 'Tên thiết bị không được để trống.',
            'quantity.required' => 'Số lượng không được để trống.',
            'quantity.min'      => 'Số lượng phải lớn hơn 0.',
        ]);

        $validated['available_qty'] = $validated['quantity'];
        $validated['status']        = 'available';
        $validated['unit']          = $validated['unit'] ?? 'Cái';

        $device = Device::create($validated);

        // Tạo lịch sử nhập kho ban đầu
        \App\Models\Import::create([
            'device_id'       => $device->id,
            'quantity'        => $device->quantity,
            'price'           => $device->unit_price,
            'supplier'        => $request->supplier,
            'country'         => $request->country,
            'brand'           => $request->brand,
            'production_year' => $request->production_year,
            'import_date'     => $request->import_date ?? now(),
            'imported_by'     => auth()->id(),
            'note'            => 'Khởi tạo thiết bị ban đầu'
        ]);

        return redirect()->route('devices.index')
            ->with('success', 'Đã thêm thiết bị "' . $validated['name'] . '" thành công!');
    }

    /**
     * Nhập thêm thiết bị (Import lần 2, 3...)
     */
    public function importStore(Request $request, Device $device)
    {
        $validated = $request->validate([
            'import_qty'      => ['required', 'integer', 'min:1'],
            'price'           => ['nullable', 'numeric', 'min:0'],
            'supplier'        => ['nullable', 'string', 'max:255'],
            'country'         => ['nullable', 'string', 'max:255'],
            'brand'           => ['nullable', 'string', 'max:255'],
            'production_year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'import_date'     => ['required', 'date'],
            'note'            => ['nullable', 'string']
        ], [
            'import_qty.required' => 'Vui lòng nhập số lượng.',
            'import_qty.min'      => 'Số lượng nhập phải lớn hơn 0.',
            'import_date.required'=> 'Vui lòng chọn ngày nhập.',
            'production_year.integer'=> 'Năm sản xuất phải là số.',
        ]);

        // Tạo lịch sử nhập kho
        \App\Models\Import::create([
            'device_id'       => $device->id,
            'quantity'        => $validated['import_qty'],
            'price'           => $validated['price'],
            'supplier'        => $validated['supplier'],
            'country'         => $validated['country'],
            'brand'           => $validated['brand'],
            'production_year' => $validated['production_year'],
            'import_date'     => $validated['import_date'],
            'imported_by'     => auth()->id(),
            'note'            => $validated['note']
        ]);

        // Cập nhật số lượng thiết bị
        $device->quantity += $validated['import_qty'];
        $device->available_qty += $validated['import_qty'];
        $device->save();
        $device->updateStatus();

        return redirect()->route('devices.show', $device)
            ->with('success', 'Đã nhập thêm ' . $validated['import_qty'] . ' ' . ($device->unit ?? 'cái') . ' thành công!');
    }

    /**
     * Cập nhật thông tin phiếu nhập cũ
     */
    public function updateImport(Request $request, Device $device, \App\Models\Import $import)
    {
        // Kiểm tra đúng thiết bị
        if ($import->device_id !== $device->id) {
            abort(404);
        }

        $validated = $request->validate([
            'edit_qty'        => ['required', 'integer', 'min:0'],
            'price'           => ['nullable', 'numeric', 'min:0'],
            'supplier'        => ['nullable', 'string', 'max:255'],
            'country'         => ['nullable', 'string', 'max:255'],
            'brand'           => ['nullable', 'string', 'max:255'],
            'production_year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'import_date'     => ['required', 'date'],
            'note'            => ['nullable', 'string']
        ], [
            'edit_qty.required' => 'Vui lòng nhập số lượng.',
            'edit_qty.min'      => 'Số lượng nhập không được âm.',
            'import_date.required'=> 'Vui lòng chọn ngày nhập.',
            'production_year.integer'=> 'Năm sản xuất phải là số.',
        ]);

        $oldQty = $import->quantity;
        $newQty = $validated['edit_qty'];
        $diff = $newQty - $oldQty;

        // BẢO VỆ TỒN KHO: Nếu lượng giảm (diff < 0), không được làm âm available_qty của thiết bị
        if ($diff < 0 && ($device->available_qty + $diff < 0)) {
            return back()->with('error', 'Lỗi: Không thể giảm số lượng kho! Thiết bị hiện chỉ còn (' . $device->available_qty . ') cái chưa cho mượn/hỏng. Nếu giảm (' . abs($diff) . ') cái thì kho sẽ bị âm!');
        }

        // 1. Cập nhật phiếu nhập
        $import->update([
            'quantity'        => $newQty,
            'price'           => $validated['price'],
            'supplier'        => $validated['supplier'],
            'country'         => $validated['country'],
            'brand'           => $validated['brand'],
            'production_year' => $validated['production_year'],
            'import_date'     => $validated['import_date'],
            'note'            => $validated['note']
        ]);

        // 2. Cập nhật thông số Tồn Kho Thiết bị thông qua Diff
        if ($diff != 0) {
            $device->quantity += $diff;
            $device->available_qty += $diff;
            $device->save();
            $device->updateStatus();
        }

        return redirect()->route('devices.show', $device)
            ->with('success', 'Đã cập nhật Phiếu nhập và tự động tính lại tồn kho!');
    }

    /**
     * Xem chi tiết một thiết bị
     */
    public function show(Device $device)
    {
        $imports       = $device->imports()->with('importer')->latest()->paginate(5, ['*'], 'import_page');
        $borrowDetails = $device->borrowDetails()->with('borrowRequest.user')->latest()->paginate(5, ['*'], 'borrow_page');
        $damages       = $device->damages()->with('reporter')->latest()->paginate(5, ['*'], 'damage_page');

        return view('devices.show', compact('device', 'imports', 'borrowDetails', 'damages'));
    }

    /**
     * Form chỉnh sửa thiết bị
     */
    public function edit(Device $device)
    {
        $categories = Device::distinct()->pluck('category')->filter()->sort()->values();
        $subjects   = Device::distinct()->pluck('subject')->filter()->sort()->values();
        return view('devices.edit', compact('device', 'categories', 'subjects'));
    }

    /**
     * Cập nhật thông tin thiết bị
     */
    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'code'          => ['required', 'string', 'max:50', 'unique:devices,code,' . $device->id],
            'name'          => ['required', 'string', 'max:255'],
            'category'      => ['nullable', 'string', 'max:100'],
            'subject'       => ['nullable', 'string', 'max:100'],
            'subject_group' => ['nullable', 'string', 'max:100'],
            'unit'          => ['nullable', 'string', 'max:50'],
            'specification' => ['nullable', 'string', 'max:255'],
            'country'       => ['nullable', 'string', 'max:100'],
            'unit_price'    => ['nullable', 'numeric', 'min:0'],
            'status'        => ['required', 'in:available,borrowed,maintenance,damaged'],
            'quantity'      => ['required', 'integer', 'min:1'],
            'damaged_qty'   => ['nullable', 'integer', 'min:0'],
            'lost_qty'      => ['nullable', 'integer', 'min:0'],
            'description'   => ['nullable', 'string'],
        ]);

        $oldQuantity = $device->quantity;
        $oldDamaged  = $device->damaged_qty;
        $oldLost     = $device->lost_qty;

        $device->update($validated);

        // Calculate differences to adjust available_qty
        $diffQuantity = $device->quantity - $oldQuantity;
        $diffDamaged  = ($validated['damaged_qty'] ?? $oldDamaged) - $oldDamaged;
        $diffLost     = ($validated['lost_qty'] ?? $oldLost) - $oldLost;

        $device->available_qty = $device->available_qty + $diffQuantity - $diffDamaged - $diffLost;
        
        // Final fallback just to logically bound
        if ($device->available_qty < 0) {
            $device->available_qty = 0;
        }

        $device->save();
        $device->updateStatus(); // Auto sync status based on quantities

        return redirect()->route('devices.index')
            ->with('success', 'Đã cập nhật thiết bị "' . $device->name . '" thành công!');
    }

    /**
     * Xoá thiết bị
     */
    public function destroy(Device $device)
    {
        if ($device->borrowedQty() > 0) {
            return back()->with('error', 'Không thể xoá thiết bị đang được mượn!');
        }

        $name = $device->name;
        $device->delete();

        return redirect()->route('devices.index')
            ->with('success', 'Đã xoá thiết bị "' . $name . '".');
    }
}
