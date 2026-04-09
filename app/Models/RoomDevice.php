<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomDevice extends Model
{
    protected $fillable = [
        'room_id', 'name', 'unit', 'quantity',
        'broken_qty', 'consumed_qty', 'lost_qty', 'note',
    ];

    // ===== RELATIONSHIPS =====

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function lessonDevices()
    {
        return $this->hasMany(LessonReportDevice::class);
    }

    public function lessonIssues()
    {
        return $this->hasMany(LessonReportIssue::class);
    }

    // ===== HELPERS =====

    public function availableQty(): int
    {
        return max(0, $this->quantity - $this->broken_qty - $this->consumed_qty - $this->lost_qty);
    }

    public function statusColor(): string
    {
        $ratio = $this->availableQty() / max(1, $this->quantity);
        if ($ratio <= 0)    return 'danger';
        if ($ratio <= 0.25) return 'warning';
        return 'success';
    }
}
