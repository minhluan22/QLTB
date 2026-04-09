<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model BorrowRequest (Phiếu mượn thiết bị)
 *
 * Giáo viên tạo phiếu → Hệ thống tự động kiểm tra kho → Đang mượn
 * Status: borrowing → returned / overdue
 */
class BorrowRequest extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'purpose',
        'class_name',           // Lớp học sử dụng
        'borrow_date',
        'expected_return_date',
        'admin_note',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'borrow_date'          => 'date',
        'expected_return_date' => 'date',
        'approved_at'          => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    /** Yêu cầu thuộc về giáo viên nào */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Admin đã duyệt yêu cầu này */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Yêu cầu có nhiều chi tiết thiết bị mượn */
    public function borrowDetails()
    {
        return $this->hasMany(BorrowDetail::class);
    }

    /** Thông tin trả thiết bị */
    public function returnRecord()
    {
        return $this->hasOne(DeviceReturn::class);
    }

    // ==================== HELPER METHODS ====================

    /** Nhãn trạng thái hiển thị */
    public function statusLabel(): string
    {
        return match($this->status) {
            'borrowing' => 'Đang mượn',
            'returned'  => 'Đã trả',
            'overdue'   => 'Quá hạn',
            default     => 'Không rõ',
        };
    }

    /** Badge Bootstrap theo trạng thái */
    public function statusBadge(): string
    {
        return match($this->status) {
            'borrowing' => '<span class="badge bg-primary">Đang mượn</span>',
            'returned'  => '<span class="badge bg-success">Đã trả</span>',
            'overdue'   => '<span class="badge bg-danger">Quá hạn</span>',
            default     => '<span class="badge bg-secondary">Không rõ</span>',
        };
    }
}
