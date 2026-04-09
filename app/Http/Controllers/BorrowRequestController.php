<?php

namespace App\Http\Controllers;

use App\Models\BorrowRequest;
use App\Models\BorrowDetail;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * BorrowRequestController - Quản lý mượn thiết bị trực tiếp
 *
 * Teacher: tạo phiếu mượn (trực tiếp nhận đồ), xem trạng thái
 * Admin: xem trạng thái, quản lý trả đồ
 */
class BorrowRequestController extends Controller
{
    /**
     * Danh sách phiếu mượn
     * Admin: xem tất cả | Teacher: chỉ xem của mình
     */
    public function index(Request $request)
    {
        // Tự động kiểm tra và cập nhật các phiếu quá hạn
        BorrowRequest::whereIn('status', ['borrowing'])
            ->whereDate('expected_return_date', '<', now())
            ->update(['status' => 'overdue']);

        $query = BorrowRequest::with(['user', 'borrowDetails.device']);

        // Teacher chỉ thấy yêu cầu của mình
        if (auth()->user()->isTeacher()) {
            $query->where('user_id', auth()->id());
        }

        // Lọc theo trạng thái (từ tab filters hoặc select)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc nâng cao
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('purpose', 'LIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('borrower') && auth()->user()->isAdmin()) {
            $query->whereHas('user', function($u) use ($request) {
                $u->where('name', 'LIKE', '%' . $request->borrower . '%');
            });
        }

        if ($request->filled('subject_group')) {
            $query->whereHas('borrowDetails.device', function($d) use ($request) {
                $d->where('subject_group', $request->subject_group);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('borrow_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expected_return_date', '<=', $request->date_to);
        }

        $requests = $query->latest()->paginate(10);

        return view('borrow-requests.index', compact('requests'));
    }

    /**
     * Form tạo yêu cầu mượn mới (Teacher)
     */
    public function create()
    {
        // Chỉ thiết bị đang available mới hiện ra
        $devices = Device::where('status', 'available')
            ->where('available_qty', '>', 0)
            ->orderBy('name')
            ->get();

        return view('borrow-requests.create', compact('devices'));
    }

    /**
     * Lưu yêu cầu mượn mới
     * Dùng DB::transaction để đảm bảo toàn vẹn dữ liệu
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purpose'              => ['required', 'string', 'max:500'],
            'class_name'           => ['nullable', 'string', 'max:100'],
            'borrow_date'          => ['required', 'date', 'after_or_equal:today'],
            'expected_return_date' => ['required', 'date', 'after_or_equal:borrow_date'],
            'devices'              => ['required', 'array', 'min:1'],
            'devices.*.device_id'  => ['required', 'exists:devices,id'],
            'devices.*.quantity'   => ['required', 'integer', 'min:1'],
        ], [
            'purpose.required'              => 'Mục đích mượn không được để trống.',
            'borrow_date.required'          => 'Ngày mượn không được để trống.',
            'borrow_date.after_or_equal'    => 'Ngày mượn phải từ hôm nay trở đi.',
            'expected_return_date.required' => 'Ngày trả dự kiến không được để trống.',
            'expected_return_date.after_or_equal'    => 'Ngày trả phải từ ngày mượn trở đi.',
            'devices.required'              => 'Vui lòng chọn ít nhất 1 thiết bị.',
        ]);

        DB::transaction(function () use ($validated) {
            // Tạo phiếu mượn (trạng thái borrowing luôn)
            $borrowRequest = BorrowRequest::create([
                'user_id'              => auth()->id(),
                'status'               => 'borrowing',
                'purpose'              => $validated['purpose'],
                'class_name'           => $validated['class_name'] ?? null,
                'borrow_date'          => $validated['borrow_date'],
                'expected_return_date' => $validated['expected_return_date'],
            ]);

            // Kiểm tra và lưu từng chi tiết thiết bị
            foreach ($validated['devices'] as $item) {
                $device = Device::findOrFail($item['device_id']);

                // Kiểm tra đủ số lượng
                if ($device->available_qty < $item['quantity']) {
                    throw new \Exception('Thiết bị "' . $device->name . '" không đủ số lượng. Chỉ còn ' . $device->available_qty . ' cái.');
                }

                BorrowDetail::create([
                    'borrow_request_id' => $borrowRequest->id,
                    'device_id'         => $device->id,
                    'quantity'          => $item['quantity'],
                ]);
                
                // Trực tiếp trừ số lượng available
                $device->available_qty -= $item['quantity'];
                $device->updateStatus();
            }
        });

        return redirect()->route('borrow-requests.index')
            ->with('success', 'Đã mượn thiết bị thành công!');
    }

    /**
     * Xem chi tiết phiếu mượn
     */
    public function show(BorrowRequest $borrowRequest)
    {
        // Tự động cập nhật nếu phiếu đang show bị quá hạn
        if (in_array($borrowRequest->status, ['borrowing']) && $borrowRequest->expected_return_date < now()->startOfDay()) {
            $borrowRequest->update(['status' => 'overdue']);
        }

        // Teacher chỉ được xem yêu cầu của mình
        if (auth()->user()->isTeacher() && $borrowRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $borrowRequest->load(['user', 'borrowDetails.device', 'approver', 'returnRecord']);

        return view('borrow-requests.show', compact('borrowRequest'));
    }

    /**
     * Form sửa yêu cầu mượn
     */
    public function edit(BorrowRequest $borrowRequest)
    {
        if (auth()->user()->isTeacher() && $borrowRequest->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($borrowRequest->status, ['borrowing', 'overdue'])) {
            return redirect()->route('borrow-requests.index')->with('error', 'Phiếu mượn này đã kết thúc, không thể sửa.');
        }

        $borrowRequest->load('borrowDetails.device');
        
        $devices = Device::where('status', '!=', 'maintenance')->orderBy('name')->get();

        return view('borrow-requests.edit', compact('borrowRequest', 'devices'));
    }

    /**
     * Cập nhật yêu cầu mượn
     */
    public function update(Request $request, BorrowRequest $borrowRequest)
    {
        if (auth()->user()->isTeacher() && $borrowRequest->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($borrowRequest->status, ['borrowing', 'overdue'])) {
            return back()->with('error', 'Phiếu mượn này đã kết thúc, không thể sửa.');
        }

        $validated = $request->validate([
            'purpose'              => ['required', 'string', 'max:500'],
            'borrow_date'          => ['required', 'date'],
            'expected_return_date' => ['required', 'date', 'after_or_equal:borrow_date'],
            'devices'              => ['required', 'array', 'min:1'],
            'devices.*.device_id'  => ['required', 'exists:devices,id'],
            'devices.*.quantity'   => ['required', 'integer', 'min:1'],
        ], [
            'expected_return_date.after_or_equal' => 'Ngày trả dự kiến phải từ ngày mượn trở đi.',
            'devices.required'              => 'Vui lòng chọn ít nhất 1 thiết bị.',
        ]);

        try {
            DB::transaction(function () use ($validated, $borrowRequest) {
                // Trả lại kho
                foreach ($borrowRequest->borrowDetails as $detail) {
                    $dev = $detail->device;
                    $dev->available_qty += $detail->quantity;
                    $dev->save();
                }

                $borrowRequest->borrowDetails()->delete();

                $borrowRequest->update([
                    'purpose'              => $validated['purpose'],
                    'borrow_date'          => $validated['borrow_date'],
                    'expected_return_date' => $validated['expected_return_date'],
                ]);

                if ($borrowRequest->status === 'overdue' && \Carbon\Carbon::parse($validated['expected_return_date'])->startOfDay() >= now()->startOfDay()) {
                    $borrowRequest->update(['status' => 'borrowing']);
                }

                foreach ($validated['devices'] as $item) {
                    $device = Device::findOrFail($item['device_id']);
                    if ($device->available_qty < $item['quantity']) {
                        throw new \Exception('Thiết bị "' . $device->name . '" không đủ số lượng. Kho hiện chỉ còn ' . $device->available_qty . ' cái.');
                    }

                    BorrowDetail::create([
                        'borrow_request_id' => $borrowRequest->id,
                        'device_id'         => $device->id,
                        'quantity'          => $item['quantity'],
                    ]);
                    
                    $device->available_qty -= $item['quantity'];
                    $device->updateStatus();
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('borrow-requests.index')->with('success', 'Đã cập nhật phiếu mượn thành công!');
    }

    /**
     * Giáo viên tự trả thiết bị
     */
    public function teacherReturn(Request $request, BorrowRequest $borrowRequest)
    {
        if (auth()->user()->isTeacher() && $borrowRequest->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($borrowRequest->status, ['borrowing', 'overdue'])) {
            return back()->with('error', 'Phiếu này không ở trạng thái cần trả.');
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

        DB::transaction(function () use ($borrowRequest, $request, $nQty, $dQty, $lQty, $returnQty) {
            $isSinglePartial = false;
            $detailCount = $borrowRequest->borrowDetails->count();

            // Nếu đây là phiếu mượn 1 học cụ VÀ có truyền số lượng trả khác số lượng đã mượn
            if ($detailCount === 1 && $returnQty > 0) {
                $detail = $borrowRequest->borrowDetails->first();
                if ($returnQty < $detail->quantity) {
                    $isSinglePartial = true;
                    $detail->quantity -= $returnQty;
                    $detail->save();

                    $activeRequest = $borrowRequest->replicate();
                    $activeRequest->status = 'returned';
                    $activeRequest->save();

                    BorrowDetail::create([
                        'borrow_request_id' => $activeRequest->id,
                        'device_id' => $detail->device_id,
                        'quantity' => $returnQty,
                    ]);
                } else {
                    $borrowRequest->update(['status' => 'returned']);
                    $activeRequest = $borrowRequest;
                }
            } else {
                $borrowRequest->update(['status' => 'returned']);
                $activeRequest = $borrowRequest;
            }

            // Ghi nhận trả lại kho
            foreach ($activeRequest->borrowDetails as $detail) {
                $device = $detail->device;
                
                // For single detail, we use the specific nQty, dQty, lQty
                // For multiple details, we currently assume the modal didn't show quantities and they just returned full.
                // Wait, if $detailCount > 1, the modal removes the inputs and required attrs. So the fallback should be to return all as normal if not specified.
                $item_nQty = $detailCount === 1 ? $nQty : $detail->quantity;
                $item_dQty = $detailCount === 1 ? $dQty : 0;
                $item_lQty = $detailCount === 1 ? $lQty : 0;

                if ($item_nQty > 0) {
                    $device->available_qty += $item_nQty;
                }
                if ($item_dQty > 0) {
                    $device->damaged_qty += $item_dQty;
                    \App\Models\Damage::create([
                        'borrow_detail_id' => $detail->id,
                        'device_id' => $device->id,
                        'damage_type' => 'hỏng',
                        'quantity' => $item_dQty,
                        'detected_date' => $request->damaged_detected_date ?? now(),
                        'severity' => $request->damaged_severity,
                        'cause' => $request->damaged_cause,
                        'resolution' => $request->damaged_resolution,
                        'description' => $request->damaged_note ?? 'Báo hỏng khi trả danh sách',
                        'reported_by' => auth()->id()
                    ]);
                }
                if ($item_lQty > 0) {
                    $device->lost_qty += $item_lQty;
                    \App\Models\Damage::create([
                        'borrow_detail_id' => $detail->id,
                        'device_id' => $device->id,
                        'damage_type' => 'mất',
                        'quantity' => $item_lQty,
                        'detected_date' => $request->lost_detected_date ?? now(),
                        'cause' => $request->lost_cause,
                        'resolution' => $request->lost_resolution,
                        'description' => $request->lost_note ?? 'Báo mất khi trả danh sách',
                        'reported_by' => auth()->id()
                    ]);
                }
                
                $device->updateStatus();
            }

            // Ghi nhận log
            \App\Models\DeviceReturn::create([
                'borrow_request_id' => $activeRequest->id,
                'returned_by'       => auth()->id(),
                'return_date'       => now(),
                'note'              => $request->normal_note
            ]);
        });

        return back()->with('success', 'Đã lưu thông tin trả thiết bị thành công!');
    }

    /**
     * Xóa phiếu mượn (Chỉ admin)
     */
    public function destroy(BorrowRequest $borrowRequest)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        try {
            DB::transaction(function () use ($borrowRequest) {
                // Phục hồi lượng tồn kho nếu đang trong quá trình mượn
                if (in_array($borrowRequest->status, ['borrowing', 'overdue'])) {
                    foreach ($borrowRequest->borrowDetails as $detail) {
                        $dev = $detail->device;
                        $dev->available_qty += $detail->quantity;
                        $dev->updateStatus();
                    }
                }
                
                $borrowRequest->borrowDetails()->delete();
                $borrowRequest->delete();
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa phiếu mượn: ' . $e->getMessage());
        }

        return redirect()->route('borrow-requests.index')->with('success', 'Đã xóa phiếu mượn rác thành công.');
    }
}
