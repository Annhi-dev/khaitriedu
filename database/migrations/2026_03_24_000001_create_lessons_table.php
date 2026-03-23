<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content');
            $table->integer('order')->default(0);
            $table->integer('duration')->nullable(); // phút
            $table->string('video_url')->nullable();
            $table->timestamps();
            $table->index('module_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
