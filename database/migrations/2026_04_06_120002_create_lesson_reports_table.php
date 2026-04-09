<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->date('lesson_date');
            $table->unsignedTinyInteger('period_count')->default(1); // Số tiết dạy
            $table->string('class_name');                             // Tên lớp
            $table->text('teacher_note')->nullable();                 // Ghi chú của giáo viên
            $table->text('manager_note')->nullable();                 // Ghi chú xác nhận của QL phòng
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->foreignId('confirmed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_reports');
    }
};
