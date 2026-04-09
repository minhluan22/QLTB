<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thêm cột room_name (phòng quản lý) cho giáo viên quản lý phòng
        Schema::table('users', function (Blueprint $table) {
            $table->string('room_name')->nullable()->after('subject_group')
                  ->comment('Phòng quản lý: Phòng Lý, Phòng Hóa, Phòng Sinh');
        });

        // Cập nhật enum role
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('teacher')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('room_name');
            $table->string('role')->default('teacher')->change();
        });
    }
};
