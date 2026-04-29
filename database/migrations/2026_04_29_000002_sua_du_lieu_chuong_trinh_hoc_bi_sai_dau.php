<?php

use App\Models\KhoaHoc;
use App\Services\CourseCurriculumService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('khoa_hoc')) {
            return;
        }

        $brokenCourses = KhoaHoc::query()
            ->with(['subject.category'])
            ->where('title', 'like', '%?%')
            ->get();

        if ($brokenCourses->isEmpty()) {
            return;
        }

        $curriculumService = app(CourseCurriculumService::class);

        foreach ($brokenCourses as $course) {
            $replacementTitle = match ((int) $course->subject_id) {
                3 => 'Khóa nội bộ - TIN HỌC VĂN PHÒNG',
                12 => 'KhaiTriEdu 2026 - Bồi dưỡng giáo viên phổ thông',
                default => null,
            };

            DB::transaction(function () use ($course, $replacementTitle, $curriculumService): void {
                if ($replacementTitle) {
                    $course->update(['title' => $replacementTitle]);
                }

                $course->modules()->delete();
                $curriculumService->syncCourse($course->fresh(['subject.category', 'modules.lessons']));
            });
        }
    }

    public function down(): void
    {
    }
};
