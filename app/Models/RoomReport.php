<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomReport extends Model
{
    protected $fillable = [
        'reported_by',
        'room_name',
        'report_date',
        'device_condition',
        'issues',
        'actions_taken',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'report_date'  => 'date',
        'reviewed_at'  => 'datetime',
    ];

    // ========== CONSTANTS ==========

    const ROOMS = [
        'Phòng Lý'  => 'Phòng Lý',
        'Phòng Hóa' => 'Phòng Hóa',
        'Phòng Sinh' => 'Phòng Sinh',
    ];

    const STATUS_LABELS = [
        'pending'  => ['label' => 'Chờ xem xét', 'color' => 'warning'],
        'reviewed' => ['label' => 'Đã xem xét',  'color' => 'success'],
    ];

    // ========== RELATIONSHIPS ==========

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ========== SCOPES ==========

    public function scopeByRoom($query, $room)
    {
        return $query->where('room_name', $room);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ========== HELPERS ==========

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status]['label'] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUS_LABELS[$this->status]['color'] ?? 'secondary';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
