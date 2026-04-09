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
        // Gỡ bỏ ràng buộc check constraint cũ của PostgreSQL một cách trực tiếp
        Schema::table('users', function (Blueprint $table) {
            if (config('database.default') === 'pgsql') {
                DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            }
            
            // Sau đó mới chuyển sang string
            $table->string('role', 50)->default('teacher')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'teacher'])->default('teacher')->change();
        });
    }
};
