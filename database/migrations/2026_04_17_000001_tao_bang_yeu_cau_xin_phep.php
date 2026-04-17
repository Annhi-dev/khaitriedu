<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yeu_cau_xin_phep', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('dang_ky')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('khoa_hoc')->cascadeOnDelete();
            $table->foreignId('class_room_id')->nullable()->constrained('lop_hoc')->cascadeOnDelete();
            $table->foreignId('class_schedule_id')->nullable()->constrained('lich_hoc')->nullOnDelete();
            $table->date('attendance_date');
            $table->text('reason');
            $table->text('note')->nullable();
            $table->string('status')->default('pending');
            $table->text('teacher_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'status']);
            $table->index(['teacher_id', 'status']);
            $table->index(['class_room_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yeu_cau_xin_phep');
    }
};
