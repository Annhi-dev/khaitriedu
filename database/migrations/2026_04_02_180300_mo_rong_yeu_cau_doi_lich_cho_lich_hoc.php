<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_change_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('schedule_change_requests', 'class_room_id')) {
                $table->foreignId('class_room_id')
                    ->nullable()
                    ->after('course_id')
                    ->constrained('lop_hoc')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('schedule_change_requests', 'class_schedule_id')) {
                $table->foreignId('class_schedule_id')
                    ->nullable()
                    ->after('class_room_id')
                    ->constrained('lich_hoc')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedule_change_requests', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_change_requests', 'class_schedule_id')) {
                $table->dropConstrainedForeignId('class_schedule_id');
            }

            if (Schema::hasColumn('schedule_change_requests', 'class_room_id')) {
                $table->dropConstrainedForeignId('class_room_id');
            }
        });
    }
};
