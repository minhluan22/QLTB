<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('subject_group')->nullable()->after('role')
                  ->comment('Tổ chuyên môn: Toán, Lý, Hóa, Sinh, Văn, Sử, Địa, GDCD, Tin, Ngoại ngữ, Thể dục, Nghề');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('subject_group');
        });
    }
};
