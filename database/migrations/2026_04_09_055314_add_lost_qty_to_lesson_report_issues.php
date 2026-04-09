<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_report_issues', function (Blueprint $table) {
            $table->double('lost_qty')->default(0)->after('consumed_qty');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_report_issues', function (Blueprint $table) {
            $table->dropColumn('lost_qty');
        });
    }
};
