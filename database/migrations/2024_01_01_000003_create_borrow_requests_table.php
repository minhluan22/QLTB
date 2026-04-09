<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Bảng borrow_requests (yêu cầu mượn)
 * Giáo viên tạo yêu cầu mượn, admin duyệt
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')                // Giáo viên tạo yêu cầu
                  ->constrained()
                  ->onDelete('cascade');
            $table->enum('status', [
                'pending',   // Chờ duyệt
                'approved',  // Đã duyệt
                'rejected',  // Từ chối
                'returned'   // Đã trả
            ])->default('pending');
            $table->text('purpose')->nullable();        // Mục đích mượn
            $table->date('borrow_date');                // Ngày mượn dự kiến
            $table->date('expected_return_date');       // Ngày trả dự kiến
            $table->text('admin_note')->nullable();     // Ghi chú của admin khi duyệt/từ chối
            $table->foreignId('approved_by')            // Admin duyệt
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('approved_at')->nullable(); // Thời gian duyệt
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrow_requests');
    }
};
