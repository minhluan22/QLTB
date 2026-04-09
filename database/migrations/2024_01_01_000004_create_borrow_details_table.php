<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Bảng borrow_details (chi tiết mượn)
 * Mỗi yêu cầu mượn có thể bao gồm nhiều thiết bị khác nhau
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_request_id')      // Thuộc yêu cầu mượn nào
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('device_id')              // Thiết bị mượn
                  ->constrained()
                  ->onDelete('cascade');
            $table->integer('quantity');                // Số lượng mượn
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrow_details');
    }
};
