<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Bảng returns (trả thiết bị)
 * Ghi lại thông tin khi giáo viên trả thiết bị
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_request_id')      // Yêu cầu mượn được trả
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('returned_by')            // Người trả
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->date('return_date');                 // Ngày trả thực tế
            $table->text('note')->nullable();            // Ghi chú khi trả
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
