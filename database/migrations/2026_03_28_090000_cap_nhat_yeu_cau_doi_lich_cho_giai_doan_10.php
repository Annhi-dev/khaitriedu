<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_change_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('schedule_change_requests', 'current_schedule')) {
                $table->text('current_schedule')->nullable()->after('course_id');
            }

            if (! Schema::hasColumn('schedule_change_requests', 'requested_day_of_week')) {
                $table->string('requested_day_of_week', 20)->nullable()->after('current_schedule');
            }

            if (! Schema::hasColumn('schedule_change_requests', 'requested_end_date')) {
                $table->date('requested_end_date')->nullable()->after('requested_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedule_change_requests', function (Blueprint $table) {
            $columns = [];

            foreach (['current_schedule', 'requested_day_of_week', 'requested_end_date'] as $column) {
                if (Schema::hasColumn('schedule_change_requests', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};