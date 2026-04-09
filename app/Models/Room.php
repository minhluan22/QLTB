<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['name', 'subject', 'manager_id', 'location', 'description'];

    const SUBJECTS = [
        'ly'   => 'Phòng Lý',
        'hoa'  => 'Phòng Hóa',
        'sinh' => 'Phòng Sinh',
    ];

    // Từ field teaching_subject của User sang subject key của Room
    const SUBJECT_MAP = [
        'Vật Lý'    => 'ly',
        'Hoá Học'   => 'hoa',
        'Sinh Học'  => 'sinh',
        'Vật lý'    => 'ly',
        'Hoá học'   => 'hoa',
        'Sinh học'  => 'sinh',
    ];

    // ===== RELATIONSHIPS =====

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function devices()
    {
        return $this->hasMany(RoomDevice::class);
    }

    public function lessonReports()
    {
        return $this->hasMany(LessonReport::class);
    }

    // ===== HELPERS =====

    public function subjectLabel(): string
    {
        return self::SUBJECTS[$this->subject] ?? $this->name;
    }

    public function availableDevices()
    {
        return $this->devices()->where('quantity', '>', 0)->get();
    }

    public function totalDeviceCount(): int
    {
        return $this->devices()->sum('quantity');
    }

    public function pendingReportsCount(): int
    {
        return $this->lessonReports()->where('status', 'pending')->count();
    }
}
