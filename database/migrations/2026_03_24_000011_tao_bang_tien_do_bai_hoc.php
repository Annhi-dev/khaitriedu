<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tien_do_bai_hoc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('bai_hoc')->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->integer('time_spent')->default(0); // giây
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'lesson_id']);
            $table->index(['user_id', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tien_do_bai_hoc');
    }
};
