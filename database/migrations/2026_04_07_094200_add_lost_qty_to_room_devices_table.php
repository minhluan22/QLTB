<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_devices', function (Blueprint $table) {
            $table->unsignedInteger('lost_qty')->default(0)->after('consumed_qty')
                  ->comment('Số lượng mất/thất lạc');
        });
    }

    public function down(): void
    {
        Schema::table('room_devices', function (Blueprint $table) {
            $table->dropColumn('lost_qty');
        });
    }
};
