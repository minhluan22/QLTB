<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model User
 *
 * Đại diện cho người dùng trong hệ thống.
 * Có 2 vai trò: admin (quản lý) và teacher (giáo viên)
 */
class User extends Authenticatable
{
    use Notifiable;

    // Các trường được phép gán hàng loạt (mass assignment)
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'subject_group',
        'room_name',
        'phone',
        'school',
        'teaching_subject',
        'notes',
        'avatar',
        'status',
    ];

    // Các trường ẩn khi convert sang JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // ==================== HELPER METHODS ====================

    /**
     * Kiểm tra người dùng có phải admin không
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Kiểm tra có phải giáo viên (bao gồm cả room_manager)
     */
    public function isTeacher(): bool
    {
        return in_array($this->role, ['teacher', 'room_manager']);
    }

    /**
     * Kiểm tra có phải giáo viên quản lý phòng không
     */
    public function isRoomManager(): bool
    {
        return $this->role === 'room_manager';
    }

    /**
     * Kiểm tra có thể truy cập báo cáo phòng không (admin hoặc room_manager)
     */
    public function canManageRoom(): bool
    {
        return in_array($this->role, ['admin', 'room_manager']);
    }

    /**
     * Tên hiển thị của role
     */
    public function roleName(): string
    {
        return match($this->role) {
            'admin'        => 'Admin',
            'room_manager' => 'Quản lý phòng',
            default        => 'Giáo viên',
        };
    }

    /**
     * Kiểm tra tài khoản đang chờ duyệt
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Trạng thái hiển thị (Badge)
     */
    public function statusBadge(): string
    {
        return match($this->status) {
            'active'  => '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 rounded-pill">Đã duyệt</span>',
            'pending' => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 rounded-pill">Chờ duyệt</span>',
            default   => '<span class="badge bg-secondary-subtle text-secondary px-3 rounded-pill">Không xác định</span>',
        };
    }

    /**
     * Kiểm tra giáo viên có dạy môn thực hành (Lý / Hóa / Sinh) không.
     * Dùng để ẩn/hiện menu "Báo cáo tiết".
     */
    public function isLabTeacher(): bool
    {
        $s = mb_strtolower(trim($this->teaching_subject ?? ''));

        return str_contains($s, 'lý')    || str_contains($s, 'ly')
            || str_contains($s, 'hóa')   || str_contains($s, 'hoa')
            || str_contains($s, 'sinh')
            || str_contains($s, 'công nghệ') || str_contains($s, 'cong nghe');
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Giáo viên có nhiều yêu cầu mượn
     */
    public function borrowRequests()
    {
        return $this->hasMany(BorrowRequest::class);
    }

    /**
     * Admin đã duyệt nhiều yêu cầu
     */
    public function approvedRequests()
    {
        return $this->hasMany(BorrowRequest::class, 'approved_by');
    }

    /**
     * Room manager có nhiều báo cáo phòng
     */
    public function roomReports()
    {
        return $this->hasMany(\App\Models\RoomReport::class, 'reported_by');
    }

    /**
     * Admin đã nhập nhiều lô hàng
     */
    public function imports()
    {
        return $this->hasMany(Import::class, 'imported_by');
    }
}
