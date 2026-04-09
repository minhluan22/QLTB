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
        Schema::table('imports', function (Blueprint $table) {
            $table->string('country')->nullable()->after('supplier')->comment('Nước sản xuất');
            $table->string('brand')->nullable()->after('country')->comment('Nhãn hiệu');
            $table->integer('production_year')->nullable()->after('brand')->comment('Năm sản xuất');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn(['country', 'brand', 'production_year']);
        });
    }
};
