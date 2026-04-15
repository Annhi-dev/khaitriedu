<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diem', function (Blueprint $table) {
            if (! Schema::hasColumn('diem', 'class_room_id')) {
                $table->foreignId('class_room_id')
                    ->nullable()
                    ->after('module_id')
                    ->constrained('lop_hoc')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('diem', 'student_id')) {
                $table->foreignId('student_id')
                    ->nullable()
                    ->after('class_room_id')
                    ->constrained('nguoi_dung')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('diem', 'teacher_id')) {
                $table->foreignId('teacher_id')
                    ->nullable()
                    ->after('student_id')
                    ->constrained('nguoi_dung')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('diem', 'test_name')) {
                $table->string('test_name')->nullable()->after('teacher_id');
            }
        });

        Schema::table('diem', function (Blueprint $table) {
            $table->unique(['class_room_id', 'student_id', 'test_name'], 'grades_class_student_test_unique');
        });
    }

    public function down(): void
    {
        Schema::table('diem', function (Blueprint $table) {
            $table->dropUnique('grades_class_student_test_unique');

            if (Schema::hasColumn('diem', 'test_name')) {
                $table->dropColumn('test_name');
            }

            if (Schema::hasColumn('diem', 'teacher_id')) {
                $table->dropConstrainedForeignId('teacher_id');
            }

            if (Schema::hasColumn('diem', 'student_id')) {
                $table->dropConstrainedForeignId('student_id');
            }

            if (Schema::hasColumn('diem', 'class_room_id')) {
                $table->dropConstrainedForeignId('class_room_id');
            }
        });
    }
};
