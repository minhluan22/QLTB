<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * UserController - Quản lý tài khoản người dùng (Admin only)
 *
 * Admin có thể:
 * - Xem danh sách giáo viên
 * - Thêm tài khoản giáo viên mới
 * - Sửa thông tin giáo viên
 * - Xóa tài khoản giáo viên
 * - Đặt lại mật khẩu
 */
class UserController extends Controller
{
    /**
     * Danh sách người dùng
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Lọc theo role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Tìm kiếm theo tên hoặc email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    /**
     * Form thêm người dùng mới
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Lưu người dùng mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(6), 'confirmed'],
            'role'     => ['required', 'in:admin,teacher,room_manager'],
            'room_name'        => ['required_if:role,room_manager', 'nullable', 'string', 'in:Phòng Lý,Phòng Hóa,Phòng Sinh'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'subject_group'    => ['nullable', 'string', 'max:255'],
            'teaching_subject' => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string'],
        ], [
            'name.required'           => 'Tên không được để trống.',
            'email.required'          => 'Email không được để trống.',
            'email.unique'            => 'Email này đã được sử dụng.',
            'password.required'       => 'Mật khẩu không được để trống.',
            'password.min'            => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed'      => 'Xác nhận mật khẩu không khớp.',
            'role.required'           => 'Vui lòng chọn vai trò.',
            'room_name.required_if'   => 'Vui lòng chọn phòng quản lý cho giáo viên quản lý phòng.',
            'room_name.in'            => 'Phòng không hợp lệ.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
            'room_name'        => $validated['role'] === 'room_manager' ? ($validated['room_name'] ?? null) : null,
            'phone'            => $validated['phone'] ?? null,
            'subject_group'    => $validated['subject_group'] ?? null,
            'teaching_subject' => $validated['teaching_subject'] ?? null,
            'notes'            => $validated['notes'] ?? null,
            'status'           => 'active', // Admin tạo thì mặc định active
        ]);

        // Đồng bộ manager_id vào bảng rooms
        $this->syncRoomManager($user, $validated['role'], $validated['room_name'] ?? null);

        return redirect()->route('users.index')
            ->with('success', 'Đã tạo tài khoản ' . $validated['name'] . ' thành công!');
    }

    /**
     * Xem thông tin chi tiết người dùng
     */
    public function show(User $user)
    {
        $user->loadCount(['borrowRequests', 'approvedRequests']);
        $recentRequests = $user->borrowRequests()->with('borrowDetails.device')->latest()->take(5)->get();

        return view('users.show', compact('user', 'recentRequests'));
    }

    /**
     * Form sửa thông tin người dùng
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Cập nhật thông tin người dùng
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'email', 'unique:users,email,' . $user->id],
            'role'   => ['required', 'in:admin,teacher,room_manager'],
            'room_name'        => ['required_if:role,room_manager', 'nullable', 'string', 'in:Phòng Lý,Phòng Hóa,Phòng Sinh'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'subject_group'    => ['nullable', 'string', 'max:255'],
            'teaching_subject' => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string'],
        ], [
            'name.required'         => 'Tên không được để trống.',
            'email.required'        => 'Email không được để trống.',
            'email.unique'          => 'Email này đã được sử dụng.',
            'role.required'         => 'Vui lòng chọn vai trò.',
            'room_name.required_if' => 'Vui lòng chọn phòng quản lý cho giáo viên quản lý phòng.',
            'room_name.in'          => 'Phòng không hợp lệ.',
        ]);

        $roomName = $validated['role'] === 'room_manager' ? ($request->room_name ?? null) : null;

        $user->update(array_merge($validated, [
            'room_name' => $roomName,
        ]));

        // Đồng bộ manager_id vào bảng rooms
        $this->syncRoomManager($user, $validated['role'], $roomName);

        return redirect()->route('users.index')
            ->with('success', 'Đã cập nhật thông tin ' . $user->name . ' thành công!');
    }

    /**
     * Xóa người dùng
     */
    public function destroy(User $user)
    {
        // Không cho phép tự xóa chính mình
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Bạn không thể xóa tài khoản của chính mình!');
        }

        // Không cho xóa nếu còn yêu cầu mượn chưa xử lý
        if ($user->borrowRequests()->whereIn('status', ['pending', 'approved'])->exists()) {
            return back()->with('error', 'Không thể xóa! Người dùng này còn yêu cầu mượn chưa xử lý xong.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Đã xóa tài khoản {$name}.");
    }

    /**
     * Đặt lại mật khẩu cho người dùng
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', Password::min(6), 'confirmed'],
        ], [
            'password.required'  => 'Mật khẩu không được để trống.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', "Đã đặt lại mật khẩu cho {$user->name}.");
    }

    /**
     * Phê duyệt tài khoản
     */
    public function approve(User $user)
    {
        $user->update(['status' => 'active']);

        return back()->with('success', "Đã phê duyệt tài khoản giáo viên: {$user->name}.");
    }

    /**
     * Đồng bộ manager_id trong bảng rooms khi admin gán/thay đổi phòng cho room_manager.
     *
     * Map:  users.room_name  →  rooms.name  →  rooms.manager_id
     */
    private function syncRoomManager(User $user, string $role, ?string $roomName): void
    {
        // Nếu đổi sang role khác → xóa manager_id khỏi các phòng user đang quản lý
        if ($role !== 'room_manager') {
            Room::where('manager_id', $user->id)->update(['manager_id' => null]);
            return;
        }

        if (!$roomName) return;

        // Gỡ user khỏi phòng cũ (nếu có)
        Room::where('manager_id', $user->id)
            ->where('name', '!=', $roomName)
            ->update(['manager_id' => null]);

        // Gán user vào phòng mới theo tên
        $room = Room::where('name', $roomName)->first();
        if ($room) {
            $room->update(['manager_id' => $user->id]);
        }
    }
}
