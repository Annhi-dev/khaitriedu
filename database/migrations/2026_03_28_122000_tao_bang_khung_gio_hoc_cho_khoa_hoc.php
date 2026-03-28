<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('mon_hoc')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('day_of_week', 20)->nullable();
            $table->date('slot_date')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamp('registration_open_at')->nullable();
            $table->timestamp('registration_close_at')->nullable();
            $table->unsignedInteger('min_students')->default(1);
            $table->unsignedInteger('max_students')->default(20);
            $table->string('status', 30)->default('pending_open');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['subject_id', 'status'], 'cts_subject_status_idx');
            $table->index(['teacher_id', 'day_of_week', 'start_time', 'end_time'], 'cts_teacher_time_idx');
            $table->index(['room_id', 'day_of_week', 'start_time', 'end_time'], 'cts_room_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_time_slots');
    }
};
