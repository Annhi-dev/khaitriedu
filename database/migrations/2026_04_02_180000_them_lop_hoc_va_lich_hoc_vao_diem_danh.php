<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_records', 'class_room_id')) {
                $table->foreignId('class_room_id')
                    ->nullable()
                    ->after('course_id')
                    ->constrained('lop_hoc')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('attendance_records', 'class_schedule_id')) {
                $table->foreignId('class_schedule_id')
                    ->nullable()
                    ->after('class_room_id')
                    ->constrained('lich_hoc')
                    ->nullOnDelete();
            }
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unique(
                ['class_room_id', 'class_schedule_id', 'student_id', 'attendance_date'],
                'attendance_records_class_schedule_student_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropUnique('attendance_records_class_schedule_student_date_unique');

            if (Schema::hasColumn('attendance_records', 'class_schedule_id')) {
                $table->dropConstrainedForeignId('class_schedule_id');
            }

            if (Schema::hasColumn('attendance_records', 'class_room_id')) {
                $table->dropConstrainedForeignId('class_room_id');
            }
        });
    }
};
