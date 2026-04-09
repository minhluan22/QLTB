<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceProposal extends Model
{
    protected $fillable = [
        'user_id',
        'device_name',
        'category',
        'description',
        'quantity',
        'purpose',
        'subject',
        'note',
        'status',
        'reject_reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    const STATUS = [
        'pending'   => ['label' => 'Chờ duyệt',   'color' => 'warning'],
        'approved'  => ['label' => 'Đã duyệt',    'color' => 'success'],
        'rejected'  => ['label' => 'Từ chối',     'color' => 'danger'],
    ];

    // ===== RELATIONSHIPS =====

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ===== SCOPES & HELPERS =====

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function statusLabel(): string
    {
        return self::STATUS[$this->status]['label'] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUS[$this->status]['color'] ?? 'secondary';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
