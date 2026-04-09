<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonReportIssue extends Model
{
    protected $fillable = [
        'lesson_report_id', 'room_device_id', 'broken_qty', 'consumed_qty', 'lost_qty', 'note',
    ];

    public function lessonReport()  { return $this->belongsTo(LessonReport::class); }
    public function device()        { return $this->belongsTo(RoomDevice::class, 'room_device_id'); }

    public function hasProblems(): bool
    {
        return $this->broken_qty > 0 || $this->consumed_qty > 0 || $this->lost_qty > 0;
    }
}
