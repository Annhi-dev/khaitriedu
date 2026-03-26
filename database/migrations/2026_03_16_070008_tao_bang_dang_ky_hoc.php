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
        Schema::create('dang_ky', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('khoa_hoc')->cascadeOnDelete();
            $table->string('preferred_schedule')->nullable();
            $table->foreignId('assigned_teacher_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dang_ky');
    }
};
