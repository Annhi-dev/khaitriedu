<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_room_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->unique(['class_room_id', 'student_id'], 'teacher_evaluations_class_student_unique');
            $table->index(['teacher_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_evaluations');
    }
};
