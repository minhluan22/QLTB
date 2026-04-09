<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model BorrowDetail (Chi tiết mượn)
 *
 * Mỗi yêu cầu mượn có thể gồm nhiều thiết bị.
 * Bảng này lưu từng dòng: mượn bao nhiêu cái thiết bị gì.
 */
class BorrowDetail extends Model
{
    protected $fillable = [
        'borrow_request_id',
        'device_id',
        'quantity',
    ];

    // ==================== RELATIONSHIPS ====================

    /** Chi tiết thuộc yêu cầu mượn nào */
    public function borrowRequest()
    {
        return $this->belongsTo(BorrowRequest::class);
    }

    /** Chi tiết liên quan đến thiết bị nào */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /** Chi tiết có các báo hỏng liên quan */
    public function damages()
    {
        return $this->hasMany(Damage::class);
    }
}
