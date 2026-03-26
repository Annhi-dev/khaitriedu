<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tai_lieu_dinh_kem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->nullable()->constrained('bai_hoc')->onDelete('cascade');
            $table->foreignId('quiz_id')->nullable()->constrained('bai_kiem_tra')->onDelete('cascade');
            $table->string('filename');
            $table->string('file_path');
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index(['lesson_id', 'quiz_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tai_lieu_dinh_kem');
    }
};
