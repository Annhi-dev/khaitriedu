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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('preferred_schedule');
            $table->time('end_time')->nullable()->after('start_time');
            $table->string('preferred_days')->nullable()->comment('JSON array of days: ["Monday","Tuesday",...]')->after('end_time');
            $table->boolean('is_submitted')->default(false)->after('preferred_days')->comment('true = học viên đã submit, chỉ có thể edit; false = chưa submit');
            $table->timestamp('submitted_at')->nullable()->after('is_submitted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'preferred_days', 'is_submitted', 'submitted_at']);
        });
    }
};
