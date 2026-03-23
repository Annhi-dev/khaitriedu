<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->string('question');
            $table->text('description')->nullable();
            $table->enum('type', ['multiple_choice', 'true_false', 'short_answer'])->default('multiple_choice');
            $table->integer('order')->default(0);
            $table->integer('points')->default(1);
            $table->timestamps();
            $table->index('quiz_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
