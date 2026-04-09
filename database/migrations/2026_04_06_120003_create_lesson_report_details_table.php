<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Thiết bị đã sử dụng trong tiết dạy
        Schema::create('lesson_report_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_report_id')->constrained('lesson_reports')->onDelete('cascade');
            $table->foreignId('room_device_id')->constrained('room_devices')->onDelete('cascade');
            $table->unsignedInteger('quantity_used')->default(1);
            $table->timestamps();
        });

        // Thiết bị hỏng hoặc tiêu hao trong tiết dạy
        Schema::create('lesson_report_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_report_id')->constrained('lesson_reports')->onDelete('cascade');
            $table->foreignId('room_device_id')->constrained('room_devices')->onDelete('cascade');
            $table->unsignedInteger('broken_qty')->default(0);    // Số hỏng
            $table->unsignedInteger('consumed_qty')->default(0);  // Số tiêu hao
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_report_issues');
        Schema::dropIfExists('lesson_report_devices');
    }
};
