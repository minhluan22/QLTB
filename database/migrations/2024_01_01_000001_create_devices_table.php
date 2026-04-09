<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Bảng devices (thiết bị)
 * Lưu thông tin các thiết bị trong trường học
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();           // Mã thiết bị (VD: TB001)
            $table->string('name');                     // Tên thiết bị
            $table->string('category')->nullable();     // Danh mục (máy tính, máy chiếu, ...)
            $table->integer('quantity')->default(0);    // Tổng số lượng
            $table->integer('available_qty')->default(0); // Số lượng có thể mượn
            $table->enum('status', [
                'available',   // Sẵn sàng
                'borrowed',    // Đang được mượn
                'maintenance', // Đang bảo trì
                'damaged'      // Hỏng
            ])->default('available');
            $table->text('description')->nullable();    // Mô tả
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
