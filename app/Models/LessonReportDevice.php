<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonReportDevice extends Model
{
    protected $fillable = ['lesson_report_id', 'room_device_id', 'quantity_used'];

    public function lessonReport()  { return $this->belongsTo(LessonReport::class); }
    public function device()        { return $this->belongsTo(RoomDevice::class, 'room_device_id'); }
}
