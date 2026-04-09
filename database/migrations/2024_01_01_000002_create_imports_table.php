<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Bảng imports (nhập thiết bị)
 * Ghi lại các lần nhập thiết bị vào kho
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')              // Thiết bị được nhập
                  ->constrained()
                  ->onDelete('cascade');
            $table->integer('quantity');                // Số lượng nhập
            $table->decimal('price', 15, 2)->nullable();// Giá nhập (VNĐ)
            $table->string('supplier')->nullable();     // Nhà cung cấp
            $table->date('import_date');                // Ngày nhập
            $table->text('note')->nullable();           // Ghi chú
            $table->foreignId('imported_by')            // Admin thực hiện nhập
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};
