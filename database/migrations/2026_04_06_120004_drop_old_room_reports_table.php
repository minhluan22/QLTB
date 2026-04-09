<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Xóa bảng room_reports cũ (đã thay thế bằng lesson_reports)
        Schema::dropIfExists('room_reports');
    }

    public function down(): void
    {
        // Không restore lại bảng cũ vì đã có hệ thống mới thay thế
    }
};
