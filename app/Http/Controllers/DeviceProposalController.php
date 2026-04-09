<?php

namespace App\Http\Controllers;

use App\Models\DeviceProposal;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceProposalController extends Controller
{
    // ================= GIÁO VIÊN =================

    /**
     * Danh sách đề xuất của tôi
     */
    public function index(Request $request)
    {
        $query = DeviceProposal::where('user_id', auth()->id())->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('device_name', 'like', "%$s%")
                  ->orWhere('subject', 'like', "%$s%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $proposals = $query->paginate(10)->withQueryString();

        return view('device-proposals.index', compact('proposals'));
    }

    /**
     * Form tạo đề xuất thiết bị
     */
    public function create()
    {
        return view('device-proposals.create');
    }

    /**
     * Lưu đề xuất mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'device_name' => ['required', 'string', 'max:255'],
            'category'    => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'quantity'    => ['required', 'integer', 'min:1'],
            'purpose'     => ['nullable', 'string'],
            'subject'     => ['nullable', 'string', 'max:100'],
            'note'        => ['nullable', 'string'],
        ]);

        DeviceProposal::create([
            'user_id'     => auth()->id(),
            'device_name' => $request->device_name,
            'category'    => $request->category ?? 'Khác',
            'description' => $request->description,
            'quantity'    => $request->quantity,
            'purpose'     => $request->purpose ?? '',
            'subject'     => $request->subject,
            'note'        => $request->note,
            'status'      => 'pending',
        ]);

        return redirect()->route('device-proposals.index')
            ->with('success', 'Đã gửi đề xuất thiết bị thành công! Vui lòng chờ phê duyệt.');
    }

    // ================= ADMIN & CHUNG =================

    /**
     * Xem chi tiết đề xuất (Giáo viên xem của mình, Admin xem tất cả)
     */
    public function show(DeviceProposal $deviceProposal)
    {
        $user = auth()->user();

        // Chỉ cho phép người đề xuất HOẶC admin xem
        if (!$user->isAdmin() && $deviceProposal->user_id !== $user->id) {
            abort(403);
        }

        return view('device-proposals.show', compact('deviceProposal'));
    }

    /**
     * Danh sách chờ duyệt cho Admin
     */
    public function adminIndex(Request $request)
    {
        $query = DeviceProposal::with('user')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('device_name', 'like', "%$s%")
                  ->orWhere('subject', 'like', "%$s%")
                  ->orWhereHas('user', function($u) use ($s) {
                      $u->where('name', 'like', "%$s%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        $proposals = $query->paginate(10)->withQueryString();
        $teachers = \App\Models\User::where('role', 'teacher')->get();

        return view('device-proposals.admin-index', compact('proposals', 'teachers'));
    }

    /**
     * Export danh sách đề xuất
     */
    public function export(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\DeviceProposalExport($request->all()),
            'danh_sach_de_xuat.xlsx'
        );
    }

    /**
     * Admin Duyệt đề xuất
     */
    public function approve(Request $request, DeviceProposal $deviceProposal)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        if (!$deviceProposal->isPending()) {
            return back()->with('error', 'Đề xuất này đã được xử lý!');
        }

        $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:devices,code'],
            'unit' => ['required', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($deviceProposal, $request) {
            // 1. Thay đổi trạng thái
            $deviceProposal->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // 2. Tạo thiết bị mới
            Device::create([
                'code'          => $request->code,
                'name'          => $deviceProposal->device_name,
                'category'      => $deviceProposal->category,
                'subject'       => $deviceProposal->subject,
                'unit'          => $request->unit,
                'quantity'      => $deviceProposal->quantity,
                'available_qty' => $deviceProposal->quantity,
                'description'   => $deviceProposal->description,
                'status'        => 'available',
                'is_proposed'   => true,
                // Các trường khác như unit_price, specification, country để rỗng
            ]);
        });

        return back()->with('success', 'Đã duyệt đề xuất và thêm thiết bị vào kho hệ thống!');
    }

    /**
     * Admin Từ chối đề xuất
     */
    public function reject(Request $request, DeviceProposal $deviceProposal)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        if (!$deviceProposal->isPending()) {
            return back()->with('error', 'Đề xuất này đã được xử lý!');
        }

        $request->validate([
            'reject_reason' => ['required', 'string'],
        ]);

        $deviceProposal->update([
            'status'        => 'rejected',
            'reject_reason' => $request->reject_reason,
            'approved_by'   => auth()->id(),
            'approved_at'   => now(),
        ]);

        return back()->with('success', 'Đã từ chối đề xuất thiết bị!');
    }
}
