<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('khoa_hoc')->nullOnDelete();
            $table->text('current_schedule')->nullable();
            $table->string('requested_day_of_week', 20)->nullable();
            $table->date('requested_date')->nullable();
            $table->date('requested_end_date')->nullable();
            $table->time('requested_start_time')->nullable();
            $table->time('requested_end_time')->nullable();
            $table->text('reason');
            $table->string('status', 20)->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_change_requests');
    }
};