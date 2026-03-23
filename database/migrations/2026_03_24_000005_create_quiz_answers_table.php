<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('option_id')->nullable()->constrained('options')->onDelete('set null');
            $table->text('answer_text')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('attempt')->default(1);
            $table->timestamps();
            $table->index(['user_id', 'quiz_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
