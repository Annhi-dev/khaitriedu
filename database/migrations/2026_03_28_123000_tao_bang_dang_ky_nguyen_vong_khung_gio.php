<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slot_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('mon_hoc')->cascadeOnDelete();
            $table->string('status', 30)->default('pending');
            $table->text('note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['subject_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_registrations');
    }
};