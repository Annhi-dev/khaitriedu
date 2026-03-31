<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lop_hoc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('mon_hoc')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->unsignedInteger('duration')->nullable()->comment('Tháng học, kế thừa từ mon_hoc');
            $table->string('status', 20)->default('open')->comment('open, full, closed, completed');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lop_hoc');
    }
};
