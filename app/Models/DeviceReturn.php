<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model DeviceReturn (Trả thiết bị)
 *
 * Ghi nhận sự kiện giáo viên trả thiết bị.
 * Tên class là DeviceReturn (không dùng Return vì trùng keyword PHP)
 */
class DeviceReturn extends Model
{
    // Chỉ định rõ tên bảng vì class name khác bảng
    protected $table = 'returns';

    protected $fillable = [
        'borrow_request_id',
        'returned_by',
        'return_date',
        'note',
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    // ==================== RELATIONSHIPS ====================

    /** Bản ghi trả thuộc yêu cầu mượn nào */
    public function borrowRequest()
    {
        return $this->belongsTo(BorrowRequest::class);
    }

    /** Ai đã trả */
    public function returner()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    /** Alias cho returner (dùng trong eager loading) */
    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}
