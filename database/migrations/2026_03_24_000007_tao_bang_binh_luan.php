<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('binh_luan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('nguoi_dung')->onDelete('cascade');
            $table->foreignId('lesson_id')->nullable()->constrained('bai_hoc')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained('khoa_hoc')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('binh_luan')->onDelete('cascade');
            $table->text('content');
            $table->integer('likes')->default(0);
            $table->enum('type', ['question', 'comment', 'feedback'])->default('comment');
            $table->timestamps();
            $table->index(['lesson_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('binh_luan');
    }
};
