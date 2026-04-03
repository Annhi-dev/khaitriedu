<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lop_hoc', function (Blueprint $table) {
            if (! Schema::hasColumn('lop_hoc', 'course_id')) {
                $table->foreignId('course_id')
                    ->nullable()
                    ->after('subject_id')
                    ->constrained('khoa_hoc')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('lop_hoc', 'name')) {
                $table->string('name')->nullable()->after('course_id');
            }
        });

        Schema::table('lich_hoc', function (Blueprint $table) {
            if (! Schema::hasColumn('lich_hoc', 'teacher_id')) {
                $table->foreignId('teacher_id')
                    ->nullable()
                    ->after('lop_hoc_id')
                    ->constrained('nguoi_dung')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('lich_hoc', 'room_id')) {
                $table->foreignId('room_id')
                    ->nullable()
                    ->after('teacher_id')
                    ->constrained('rooms')
                    ->nullOnDelete();
            }
        });

        if (! Schema::hasTable('custom_schedule_requests')) {
            Schema::create('custom_schedule_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('nguoi_dung')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('mon_hoc')->cascadeOnDelete();
                $table->foreignId('course_id')->nullable()->constrained('khoa_hoc')->nullOnDelete();
                $table->foreignId('preferred_teacher_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
                $table->json('requested_days')->nullable();
                $table->string('requested_time');
                $table->string('status', 20)->default('pending');
                $table->text('notes')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('nguoi_dung')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['student_id', 'status']);
                $table->index(['subject_id', 'status']);
                $table->index(['preferred_teacher_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('custom_schedule_requests')) {
            Schema::dropIfExists('custom_schedule_requests');
        }

        Schema::table('lich_hoc', function (Blueprint $table) {
            if (Schema::hasColumn('lich_hoc', 'room_id')) {
                $table->dropConstrainedForeignId('room_id');
            }

            if (Schema::hasColumn('lich_hoc', 'teacher_id')) {
                $table->dropConstrainedForeignId('teacher_id');
            }
        });

        Schema::table('lop_hoc', function (Blueprint $table) {
            if (Schema::hasColumn('lop_hoc', 'name')) {
                $table->dropColumn('name');
            }

            if (Schema::hasColumn('lop_hoc', 'course_id')) {
                $table->dropConstrainedForeignId('course_id');
            }
        });
    }
};
