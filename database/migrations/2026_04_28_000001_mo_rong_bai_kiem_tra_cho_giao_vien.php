<?php

use App\Models\Quiz;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bai_kiem_tra', function (Blueprint $table): void {
            if (! Schema::hasColumn('bai_kiem_tra', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            }

            if (! Schema::hasColumn('bai_kiem_tra', 'course_id')) {
                $table->foreignId('course_id')->nullable()->constrained('khoa_hoc')->nullOnDelete();
            }

            if (! Schema::hasColumn('bai_kiem_tra', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->constrained('mon_hoc')->nullOnDelete();
            }

            if (! Schema::hasColumn('bai_kiem_tra', 'lop_hoc_id')) {
                $table->foreignId('lop_hoc_id')->nullable()->constrained('lop_hoc')->nullOnDelete();
            }

            if (! Schema::hasColumn('bai_kiem_tra', 'duration_minutes')) {
                $table->unsignedSmallInteger('duration_minutes')->nullable();
            }

            if (! Schema::hasColumn('bai_kiem_tra', 'total_score')) {
                $table->decimal('total_score', 6, 2)->nullable();
            }

            if (! Schema::hasColumn('bai_kiem_tra', 'status')) {
                $table->string('status', 20)->default(Quiz::STATUS_DRAFT);
            }

            if (! Schema::hasColumn('bai_kiem_tra', 'published_at')) {
                $table->timestamp('published_at')->nullable();
            }

            $table->index(['teacher_id', 'status']);
            $table->index(['course_id', 'status']);
            $table->index(['subject_id', 'status']);
            $table->index(['lop_hoc_id', 'status']);
        });

        Quiz::query()
            ->with(['lesson.module.course', 'questions'])
            ->chunkById(100, function ($quizzes): void {
                foreach ($quizzes as $quiz) {
                    $course = $quiz->lesson?->module?->course;

                    if (! $course) {
                        continue;
                    }

                    $totalScore = (float) $quiz->questions->sum(fn ($question) => (float) $question->points);

                    $quiz->forceFill([
                        'teacher_id' => $quiz->teacher_id ?? $course->teacher_id,
                        'course_id' => $quiz->course_id ?? $course->id,
                        'subject_id' => $quiz->subject_id ?? $course->subject_id,
                        'status' => $quiz->status ?? Quiz::STATUS_PUBLISHED,
                        'duration_minutes' => $quiz->duration_minutes ?? $quiz->lesson?->duration ?? 15,
                        'total_score' => $quiz->total_score ?? ($totalScore > 0 ? $totalScore : 10),
                        'published_at' => $quiz->published_at ?? $quiz->created_at ?? now(),
                    ])->save();
                }
            });
    }

    public function down(): void
    {
        Schema::table('bai_kiem_tra', function (Blueprint $table): void {
            if (Schema::hasColumn('bai_kiem_tra', 'published_at')) {
                $table->dropColumn('published_at');
            }

            if (Schema::hasColumn('bai_kiem_tra', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('bai_kiem_tra', 'total_score')) {
                $table->dropColumn('total_score');
            }

            if (Schema::hasColumn('bai_kiem_tra', 'duration_minutes')) {
                $table->dropColumn('duration_minutes');
            }

            if (Schema::hasColumn('bai_kiem_tra', 'lop_hoc_id')) {
                $table->dropConstrainedForeignId('lop_hoc_id');
            }

            if (Schema::hasColumn('bai_kiem_tra', 'subject_id')) {
                $table->dropConstrainedForeignId('subject_id');
            }

            if (Schema::hasColumn('bai_kiem_tra', 'course_id')) {
                $table->dropConstrainedForeignId('course_id');
            }

            if (Schema::hasColumn('bai_kiem_tra', 'teacher_id')) {
                $table->dropConstrainedForeignId('teacher_id');
            }
        });
    }
};
