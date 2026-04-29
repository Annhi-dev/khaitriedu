<?php

namespace App\Services;

use App\Models\KhoaHoc;
use App\Models\HocPhan;
use Illuminate\Support\Collection;

class AdminCourseModuleService
{
    public function getCourseDetail(KhoaHoc $course): array
    {
        $course->load(['subject.category', 'teacher']);
        $course->loadCount('enrollments');

        $modules = $course->modules()
            ->withCount(['lessons', 'quizzes'])
            ->orderBy('position')
            ->get();

        return [
            'course' => $course,
            'modules' => $modules,
        ];
    }

    public function createModule(KhoaHoc $course, array $data): HocPhan
    {
        $position = $data['position'] ?? ((int) $course->modules()->max('position') + 1);

        return $course->modules()->create([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'session_count' => $data['session_count'] ?? null,
            'duration' => $data['duration'] ?? null,
            'status' => $data['status'],
            'position' => $position,
        ]);
    }

    public function updateModule(KhoaHoc $course, HocPhan $module, array $data): HocPhan
    {
        $this->ensureModuleBelongsToCourse($course, $module);

        $module->update([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'session_count' => $data['session_count'] ?? null,
            'duration' => $data['duration'] ?? null,
            'status' => $data['status'],
            'position' => $data['position'] ?? $module->position,
        ]);

        return $module;
    }

    public function deleteModule(KhoaHoc $course, HocPhan $module): array
    {
        $this->ensureModuleBelongsToCourse($course, $module);

        $module->delete();

        return [
            'type' => 'status',
            'message' => 'HocPhan và toàn bộ dữ liệu liên quan đã được xóa.',
        ];
    }

    public function reorderModules(KhoaHoc $course, array $positions): void
    {
        $modules = $course->modules()->get()->keyBy('id');

        foreach ($positions as $moduleId => $position) {
            $module = $modules->get((int) $moduleId);

            if (! $module) {
                continue;
            }

            $module->update([
                'position' => max(1, (int) $position),
            ]);
        }
    }

    protected function ensureModuleBelongsToCourse(KhoaHoc $course, HocPhan $module): void
    {
        if ($module->course_id !== $course->id) {
            abort(404);
        }
    }
}
