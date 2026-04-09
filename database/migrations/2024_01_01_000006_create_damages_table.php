<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Bảng damages (báo hỏng)
 * Giáo viên báo hỏng thiết bị khi trả hoặc trong quá trình sử dụng
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_detail_id')       // Chi tiết mượn bị hỏng
                  ->nullable()
                  ->constrained()
                  ->onDelete('set null');
            $table->foreignId('device_id')              // Thiết bị bị hỏng
                  ->constrained()
                  ->onDelete('cascade');
            $table->integer('quantity')->default(1);    // Số lượng bị hỏng
            $table->text('description');                // Mô tả lỗi
            $table->enum('severity', [
                'minor',    // Hỏng nhẹ
                'moderate', // Hỏng vừa
                'severe'    // Hỏng nặng
            ])->default('minor');
            $table->foreignId('reported_by')            // Người báo hỏng
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damages');
    }
};
