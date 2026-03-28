<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('khoa_hoc')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('dang_ky')->nullOnDelete();
            $table->foreignId('student_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('status', 20)->default('present');
            $table->text('note')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'student_id', 'attendance_date'], 'attendance_records_course_student_date_unique');
            $table->index(['teacher_id', 'attendance_date']);
            $table->index(['course_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};