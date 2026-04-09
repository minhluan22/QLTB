<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->string('name');                          // Tên thiết bị
            $table->string('unit')->default('cái');         // Đơn vị: cái, bộ, ml, gói...
            $table->unsignedInteger('quantity')->default(0); // Tổng số lượng hiện có
            $table->unsignedInteger('broken_qty')->default(0);   // Số hỏng
            $table->unsignedInteger('consumed_qty')->default(0); // Số đã tiêu hao
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_devices');
    }
};
