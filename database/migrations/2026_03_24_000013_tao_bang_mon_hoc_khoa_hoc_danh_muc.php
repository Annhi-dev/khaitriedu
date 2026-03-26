<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('mon_hoc', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('image')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('danh_muc')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('khoa_hoc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('mon_hoc')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->string('schedule')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khoa_hoc');
        Schema::dropIfExists('mon_hoc');
        Schema::dropIfExists('danh_muc');
    }
};