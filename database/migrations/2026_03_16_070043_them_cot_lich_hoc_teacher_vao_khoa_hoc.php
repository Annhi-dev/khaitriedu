<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('khoa_hoc', function (Blueprint $table) {
            $table->string('schedule')->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khoa_hoc', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn(['schedule', 'teacher_id']);
        });
    }
};
