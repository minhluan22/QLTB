<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Bảng users
 * Lưu thông tin người dùng với 2 vai trò: admin và teacher
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Họ tên
            $table->string('email')->unique();               // Email đăng nhập
            $table->string('password');                      // Mật khẩu (đã hash)
            $table->enum('role', ['admin', 'teacher'])       // Vai trò
                  ->default('teacher');
            $table->string('phone')->nullable();             // Số điện thoại
            $table->string('school')->nullable();            // Trường/Đơn vị
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
