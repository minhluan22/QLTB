<?php

namespace App\Http\Controllers;

use App\Models\RoomReport;
use Illuminate\Http\Request;

class RoomReportController extends Controller
{
    /**
     * Danh sách báo cáo phòng
     * - Room manager: chỉ thấy báo cáo của phòng mình
     * - Admin: thấy tất cả, có thể lọc theo phòng / trạng thái
     */
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = RoomReport::with('reporter')->latest();

        if ($user->isRoomManager()) {
            $query->where('room_name', $user->room_name);
        } else {
            // Admin: filter theo phòng
            if ($request->filled('room')) {
                $query->where('room_name', $request->room);
            }
            // Admin: filter theo trạng thái
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        }

        $reports = $query->paginate(15)->withQueryString();

        // Thống kê cho Admin (cards)
        $stats = null;
        if ($user->isAdmin()) {
            $stats = [
                'total'    => RoomReport::count(),
                'pending'  => RoomReport::where('status', 'pending')->count(),
                'reviewed' => RoomReport::where('status', 'reviewed')->count(),
                'by_room'  => RoomReport::selectRaw('room_name, count(*) as total')
                                        ->groupBy('room_name')->pluck('total', 'room_name'),
            ];
        }

        $rooms = array_keys(RoomReport::ROOMS);

        return view('room-reports.index', compact('reports', 'stats', 'rooms'));
    }

    /**
     * Form tạo báo cáo mới (chỉ room_manager)
     */
    public function create()
    {
        $user = auth()->user();
        if (!$user->isRoomManager()) {
            abort(403, 'Chỉ giáo viên quản lý phòng mới có thể tạo báo cáo.');
        }

        return view('room-reports.create', ['roomName' => $user->room_name]);
    }

    /**
     * Lưu báo cáo mới
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isRoomManager()) {
            abort(403);
        }

        $validated = $request->validate([
            'report_date'      => ['required', 'date'],
            'device_condition' => ['required', 'string', 'min:10'],
            'issues'           => ['nullable', 'string'],
            'actions_taken'    => ['nullable', 'string'],
        ], [
            'report_date.required'      => 'Vui lòng chọn ngày báo cáo.',
            'device_condition.required' => 'Vui lòng mô tả tình trạng thiết bị.',
            'device_condition.min'      => 'Mô tả tình trạng phải có ít nhất 10 ký tự.',
        ]);

        RoomReport::create([
            'reported_by'      => $user->id,
            'room_name'        => $user->room_name,
            'report_date'      => $validated['report_date'],
            'device_condition' => $validated['device_condition'],
            'issues'           => $validated['issues'] ?? null,
            'actions_taken'    => $validated['actions_taken'] ?? null,
            'status'           => 'pending',
        ]);

        return redirect()->route('room-reports.index')
            ->with('success', 'Đã gửi báo cáo phòng thành công!');
    }

    /**
     * Chi tiết báo cáo
     */
    public function show(RoomReport $roomReport)
    {
        $user = auth()->user();

        // Room manager chỉ xem báo cáo của phòng mình
        if ($user->isRoomManager() && $roomReport->room_name !== $user->room_name) {
            abort(403);
        }

        $roomReport->load(['reporter', 'reviewer']);
        return view('room-reports.show', compact('roomReport'));
    }

    /**
     * Admin duyệt / phản hồi báo cáo
     */
    public function review(Request $request, RoomReport $roomReport)
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string'],
        ]);

        $roomReport->update([
            'status'      => 'reviewed',
            'admin_note'  => $validated['admin_note'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Đã xem xét báo cáo thành công!');
    }
}
