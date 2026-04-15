<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('danh_muc')) {
            Schema::create('danh_muc', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('image_path')->nullable();
                $table->string('program')->nullable();
                $table->string('level')->nullable();
                $table->string('status')->default('active');
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mon_hoc')) {
            Schema::create('mon_hoc', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 12, 2)->default(0);
                $table->string('image')->nullable();
                $table->foreignId('category_id')->nullable()->constrained('danh_muc')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('khoa_hoc')) {
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
    }

    public function down(): void
    {
    }
};