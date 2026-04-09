<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm các cột mới vào bảng devices:
 * - subject       : Môn học (Toán, Lý, Hóa...)
 * - unit          : Đơn vị (Cái, Bộ, Chiếc...)
 * - specification : Quy cách (180°, 45°...)
 * - country       : Nước sản xuất
 * - unit_price    : Đơn giá (VNĐ)
 * - damaged_qty   : Số lượng hỏng tích lũy
 * - lost_qty      : Số lượng mất tích lũy
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('category');        // Môn học
            $table->string('unit')->default('Cái')->after('subject');       // Đơn vị tính
            $table->string('specification')->nullable()->after('unit');      // Quy cách
            $table->string('country')->nullable()->after('specification');   // Nước SX
            $table->decimal('unit_price', 15, 2)->nullable()->after('country'); // Đơn giá
            $table->integer('damaged_qty')->default(0)->after('available_qty'); // Số lượng hỏng
            $table->integer('lost_qty')->default(0)->after('damaged_qty');       // Số lượng mất
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'subject', 'unit', 'specification', 'country',
                'unit_price', 'damaged_qty', 'lost_qty',
            ]);
        });
    }
};
