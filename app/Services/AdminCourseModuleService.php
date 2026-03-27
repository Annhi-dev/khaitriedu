<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Support\Collection;

class AdminCourseModuleService
{
    public function getCourseDetail(Course $course): array
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

    public function createModule(Course $course, array $data): Module
    {
        $position = $data['position'] ?? ((int) $course->modules()->max('position') + 1);

        return $course->modules()->create([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'duration' => $data['duration'] ?? null,
            'status' => $data['status'],
            'position' => $position,
        ]);
    }

    public function updateModule(Course $course, Module $module, array $data): Module
    {
        $this->ensureModuleBelongsToCourse($course, $module);

        $module->update([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'duration' => $data['duration'] ?? null,
            'status' => $data['status'],
            'position' => $data['position'] ?? $module->position,
        ]);

        return $module;
    }

    public function deleteModule(Course $course, Module $module): array
    {
        $this->ensureModuleBelongsToCourse($course, $module);

        if ($module->lessons()->exists() || $module->quizzes()->exists()) {
            $module->update(['status' => Module::STATUS_UNPUBLISHED]);

            return [
                'type' => 'error',
                'message' => 'Module đang có bài học hoặc quiz liên kết nên đã được chuyển sang trạng thái ẩn thay vì xóa cứng.',
            ];
        }

        $module->delete();

        return [
            'type' => 'status',
            'message' => 'Module đã được xóa.',
        ];
    }

    public function reorderModules(Course $course, array $positions): void
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

    protected function ensureModuleBelongsToCourse(Course $course, Module $module): void
    {
        if ($module->course_id !== $course->id) {
            abort(404);
        }
    }
}