<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_reports', function (Blueprint $table) {
            // Thêm cột buổi dạy sau lesson_date
            $table->enum('session', ['sang', 'chieu'])
                  ->default('sang')
                  ->after('lesson_date')
                  ->comment('Buổi dạy: sang = Sáng, chieu = Chiều');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_reports', function (Blueprint $table) {
            $table->dropColumn('session');
        });
    }
};
