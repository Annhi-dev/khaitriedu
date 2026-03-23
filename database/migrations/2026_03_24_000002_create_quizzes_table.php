<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('passing_score')->default(70); // %
            $table->boolean('is_required')->default(true);
            $table->integer('max_attempts')->default(3);
            $table->timestamps();
            $table->index('lesson_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
