<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thong_bao_chung', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('nguoi_dung')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained('khoa_hoc')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
            $table->index(['course_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thong_bao_chung');
    }
};
