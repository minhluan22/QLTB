<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    // Admin: danh sách tất cả phòng
    public function index()
    {
        $rooms = Room::with(['manager', 'devices'])->get();
        return view('rooms.index', compact('rooms'));
    }

    // Admin: form tạo phòng mới
    public function create()
    {
        $managers = User::where('role', 'room_manager')->get();
        return view('rooms.create', compact('managers'));
    }

    // Admin: lưu phòng mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'subject'    => ['required', 'in:ly,hoa,sinh'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'location'   => ['nullable', 'string', 'max:100'],
            'description'=> ['nullable', 'string'],
        ], [
            'name.required'    => 'Tên phòng không được để trống.',
            'subject.required' => 'Vui lòng chọn môn học.',
            'subject.in'       => 'Môn học không hợp lệ.',
        ]);

        Room::create($validated);
        return redirect()->route('rooms.index')->with('success', 'Đã tạo phòng ' . $validated['name'] . '!');
    }

    // Admin: form sửa phòng
    public function edit(Room $room)
    {
        $managers = User::where('role', 'room_manager')->get();
        return view('rooms.edit', compact('room', 'managers'));
    }

    // Admin: cập nhật phòng
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'subject'    => ['required', 'in:ly,hoa,sinh'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'location'   => ['nullable', 'string', 'max:100'],
            'description'=> ['nullable', 'string'],
        ]);

        $room->update($validated);
        return redirect()->route('rooms.index')->with('success', 'Đã cập nhật phòng ' . $room->name . '!');
    }

    // Admin: xóa phòng
    public function destroy(Room $room)
    {
        if ($room->lessonReports()->exists()) {
            return back()->with('error', 'Không thể xóa phòng đang có báo cáo tiết dạy!');
        }
        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'Đã xóa phòng.');
    }

    // Room manager: xem phòng của mình
    public function myRoom()
    {
        $user = auth()->user();
        $room = Room::with(['manager'])->where('manager_id', $user->id)->first();

        if (!$room) {
            return back()->with('error', 'Bạn chưa được gán quản lý phòng nào. Vui lòng liên hệ Admin.');
        }

        $stats = [
            'total_devices'   => $room->devices()->count(),
            'total_qty'       => $room->devices()->sum('quantity'),
            'broken_qty'      => $room->devices()->sum('broken_qty'),
            'consumed_qty'    => $room->devices()->sum('consumed_qty'),
            'pending_reports' => $room->lessonReports()->where('status', 'pending')->count(),
        ];

        // Phân trang thiết bị trong phòng (10 dòng/trang)
        $devices = $room->devices()->latest()->paginate(10)->withQueryString();

        return view('rooms.my-room', compact('room', 'stats', 'devices'));
    }
}
