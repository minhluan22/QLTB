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
        Schema::create('room_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->string('room_name')->comment('Phòng Lý / Phòng Hóa / Phòng Sinh');
            $table->date('report_date');
            $table->text('device_condition')->comment('Tình trạng thiết bị trong phòng');
            $table->text('issues')->nullable()->comment('Vấn đề phát sinh');
            $table->text('actions_taken')->nullable()->comment('Hành động đã thực hiện');
            $table->enum('status', ['pending', 'reviewed'])->default('pending');
            $table->text('admin_note')->nullable()->comment('Ghi chú phản hồi của admin');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_reports');
    }
};
