<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$room    = App\Models\Room::first();
$teacher = App\Models\User::where('role', 'teacher')->first();

if (!$room || !$teacher) {
    echo "Missing room or teacher\n";
    exit(1);
}

// Them 15 bao cao tiet
for ($i = 1; $i <= 15; $i++) {
    App\Models\LessonReport::create([
        'room_id'      => $room->id,
        'teacher_id'   => $teacher->id,
        'lesson_date'  => now()->subDays($i),
        'session'      => $i % 2 === 0 ? 'sang' : 'chieu',
        'period_count' => rand(2, 4),
        'class_name'   => '8A' . rand(1, 9),
        'teacher_note' => null,
        'status'       => 'pending',
    ]);
}
echo "Da them 15 bao cao tiet thanh cong!\n";

// Them 10 bao hong
$device = App\Models\Device::first();
if ($device) {
    for ($j = 1; $j <= 10; $j++) {
        App\Models\Damage::create([
            'device_id'     => $device->id,
            'damage_type'   => $j % 3 === 0 ? 'mat' : 'hong',
            'quantity'      => rand(1, 3),
            'detected_date' => now()->subDays($j),
            'severity'      => 'minor',
            'description'   => 'Test bao hong thu ' . $j,
            'reported_by'   => $teacher->id,
        ]);
    }
    echo "Da them 10 bao hong thanh cong!\n";
}
