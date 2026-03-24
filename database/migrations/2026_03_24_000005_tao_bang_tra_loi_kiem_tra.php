<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tra_loi_kiem_tra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('nguoi_dung')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('bai_kiem_tra')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('cau_hoi')->onDelete('cascade');
            $table->foreignId('option_id')->nullable()->constrained('lua_chon')->onDelete('set null');
            $table->text('answer_text')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('attempt')->default(1);
            $table->timestamps();
            $table->index(['user_id', 'quiz_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tra_loi_kiem_tra');
    }
};
