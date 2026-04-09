<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonReport extends Model
{
    protected $fillable = [
        'room_id', 'teacher_id', 'subject', 'lesson_date', 'session', 'period_count',
        'class_name', 'teacher_note', 'manager_note',
        'status', 'confirmed_by', 'confirmed_at',
    ];

    protected $casts = [
        'lesson_date'  => 'date',
        'confirmed_at' => 'datetime',
    ];

    const SESSION = [
        'sang'  => 'Sáng',
        'chieu' => 'Chiều',
    ];

    const STATUS = [
        'pending'   => ['label' => 'Chờ xác nhận', 'color' => 'warning'],
        'confirmed' => ['label' => 'Đã xác nhận',  'color' => 'success'],
        'rejected'  => ['label' => 'Bị từ chối',   'color' => 'danger'],
    ];

    // ===== RELATIONSHIPS =====

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function deviceUsages()
    {
        return $this->hasMany(LessonReportDevice::class);
    }

    public function issues()
    {
        return $this->hasMany(LessonReportIssue::class);
    }

    // ===== SCOPES =====

    public function scopePending($q)   { return $q->where('status', 'pending'); }
    public function scopeConfirmed($q) { return $q->where('status', 'confirmed'); }
    public function scopeForRoom($q, $roomId) { return $q->where('room_id', $roomId); }

    // ===== HELPERS =====

    public function statusLabel(): string  { return self::STATUS[$this->status]['label'] ?? $this->status; }
    public function statusColor(): string  { return self::STATUS[$this->status]['color'] ?? 'secondary'; }
    public function sessionLabel(): string { return self::SESSION[$this->session] ?? $this->session; }
    public function isPending(): bool      { return $this->status === 'pending'; }
    public function isConfirmed(): bool    { return $this->status === 'confirmed'; }
    public function hasIssues(): bool      { return $this->issues()->exists(); }
}
