<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_change_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('schedule_change_requests', 'requested_room_id')) {
                $table->foreignId('requested_room_id')
                    ->nullable()
                    ->after('class_schedule_id')
                    ->constrained('rooms')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedule_change_requests', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_change_requests', 'requested_room_id')) {
                $table->dropConstrainedForeignId('requested_room_id');
            }
        });
    }
};

