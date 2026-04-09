<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Damage (Báo hỏng / Báo mất thiết bị)
 *
 * damage_type = 'hỏng' → tăng device.damaged_qty
 * damage_type = 'mất'  → tăng device.lost_qty
 *
 * Cả hai loại đều làm giảm available_qty của thiết bị.
 */
class Damage extends Model
{
    protected $fillable = [
        'borrow_detail_id',
        'device_id',
        'damage_type',      // hỏng | mất
        'quantity',
        'detected_date',    // Ngày phát hiện
        'description',      // Mô tả chi tiết
        'cause',            // Nguyên nhân (Gãy, Cong, Rơi...)
        'severity',         // Mức độ (minor, moderate, severe)
        'resolution',       // Hướng xử lý (Thay mới, Sửa chữa...)
        'reported_by',
    ];

    protected $casts = [
        'detected_date' => 'date',
        'quantity'      => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    public function borrowDetail()
    {
        return $this->belongsTo(BorrowDetail::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    // ==================== HELPER METHODS ====================

    /** Badge loại sự cố */
    public function typeBadge(): string
    {
        return match($this->damage_type) {
            'hỏng' => '<span class="badge bg-warning text-dark"><i class="bi bi-tools me-1"></i>Hỏng</span>',
            'mất'  => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Mất</span>',
            default => '<span class="badge bg-secondary">Không rõ</span>',
        };
    }

    /** Nhãn mức độ hỏng */
    public function severityLabel(): string
    {
        return match($this->severity) {
            'minor'    => 'Hỏng nhẹ',
            'moderate' => 'Hỏng vừa',
            'severe'   => 'Hỏng nặng',
            default    => 'Không rõ',
        };
    }

    /** Badge mức độ hỏng */
    public function severityBadge(): string
    {
        return match($this->severity) {
            'minor'    => '<span class="badge bg-warning text-dark">Hỏng nhẹ</span>',
            'moderate' => '<span class="badge" style="background:#fd7e14;color:#fff">Hỏng vừa</span>',
            'severe'   => '<span class="badge bg-danger">Hỏng nặng</span>',
            default    => '<span class="badge bg-secondary">Không rõ</span>',
        };
    }
}
