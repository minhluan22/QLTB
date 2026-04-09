<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm class_name vào bảng borrow_requests
 * Ghi nhận lớp học sử dụng thiết bị
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_requests', function (Blueprint $table) {
            $table->string('class_name')->nullable()->after('purpose'); // Lớp sử dụng
        });
    }

    public function down(): void
    {
        Schema::table('borrow_requests', function (Blueprint $table) {
            $table->dropColumn('class_name');
        });
    }
};
