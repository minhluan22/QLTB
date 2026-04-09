<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm các cột mới vào bảng damages:
 * - damage_type   : Loại sự cố (hỏng / mất)
 * - detected_date : Ngày phát hiện
 * - cause         : Nguyên nhân (Gãy, Cong, Rơi...)
 * - resolution    : Hướng xử lý (Thay mới, Sửa chữa, Thanh lý...)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('damages', function (Blueprint $table) {
            $table->enum('damage_type', ['hỏng', 'mất'])->default('hỏng')->after('quantity');
            $table->date('detected_date')->nullable()->after('damage_type');
            $table->string('cause')->nullable()->after('detected_date');
            $table->string('resolution')->nullable()->after('cause');
        });
    }

    public function down(): void
    {
        Schema::table('damages', function (Blueprint $table) {
            $table->dropColumn(['damage_type', 'detected_date', 'cause', 'resolution']);
        });
    }
};
