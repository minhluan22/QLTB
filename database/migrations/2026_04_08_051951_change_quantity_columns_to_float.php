<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->double('quantity')->default(0)->change();
            $table->double('available_qty')->default(0)->change();
            $table->double('damaged_qty')->default(0)->change();
            $table->double('lost_qty')->default(0)->change();
        });

        Schema::table('room_devices', function (Blueprint $table) {
            $table->double('quantity')->default(0)->change();
            $table->double('broken_qty')->default(0)->change();
            $table->double('consumed_qty')->default(0)->change();
            $table->double('lost_qty')->default(0)->change();
        });

        Schema::table('lesson_report_devices', function (Blueprint $table) {
            $table->double('quantity_used')->default(0)->change();
        });

        Schema::table('lesson_report_issues', function (Blueprint $table) {
            $table->double('broken_qty')->default(0)->change();
            $table->double('consumed_qty')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // down is not strictly necessary for this small patch
    }
};
