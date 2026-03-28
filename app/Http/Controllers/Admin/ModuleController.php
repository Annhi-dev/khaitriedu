<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseModuleRequest;
use App\Http\Requests\Admin\UpdateCourseModuleRequest;
use App\Models\Course;
use App\Models\Module;
use App\Models\User;
use App\Services\AdminCourseModuleService;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index(Course $course, AdminCourseModuleService $moduleService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.course.modules', array_merge(
            ['current' => $current],
            $moduleService->getCourseDetail($course),
        ));
    }

    public function store(Course $course, StoreCourseModuleRequest $request, AdminCourseModuleService $moduleService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $moduleService->createModule($course, $request->validated());

        return redirect()->route('admin.courses.modules.index', $course)->with('status', 'Module đã được thêm.');
    }

    public function edit(Course $course, Module $module, AdminCourseModuleService $moduleService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        if ($module->course_id !== $course->id) {
            abort(404);
        }

        return view('admin.course.module_edit', array_merge(
            ['current' => $current, 'module' => $module],
            $moduleService->getCourseDetail($course),
        ));
    }

    public function update(Course $course, Module $module, UpdateCourseModuleRequest $request, AdminCourseModuleService $moduleService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $moduleService->updateModule($course, $module, $request->validated());

        return redirect()->route('admin.courses.modules.index', $course)->with('status', 'Module đã được cập nhật.');
    }

    public function destroy(Course $course, Module $module, AdminCourseModuleService $moduleService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $result = $moduleService->deleteModule($course, $module);

        return redirect()->route('admin.courses.modules.index', $course)->with($result['type'], $result['message']);
    }

    public function reorder(Course $course, Request $request, AdminCourseModuleService $moduleService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'positions' => ['required', 'array'],
            'positions.*' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        $moduleService->reorderModules($course, $data['positions']);

        return redirect()->route('admin.courses.modules.index', $course)->with('status', 'Thứ tự module đã được cập nhật.');
    }
}
