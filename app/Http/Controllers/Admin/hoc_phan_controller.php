<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseModuleRequest;
use App\Http\Requests\Admin\UpdateCourseModuleRequest;
use App\Models\KhoaHoc;
use App\Models\HocPhan;
use App\Models\NguoiDung;
use App\Services\CourseCurriculumService;
use App\Services\AdminCourseModuleService;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index(KhoaHoc $course, AdminCourseModuleService $moduleService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('quan_tri.khoa_hoc.hoc_phan', array_merge(
            ['current' => $current],
            $moduleService->getCourseDetail($course),
        ));
    }

    public function store(KhoaHoc $course, StoreCourseModuleRequest $request, AdminCourseModuleService $moduleService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $moduleService->createModule($course, $request->validated());

        return redirect()->route('admin.courses.modules.index', $course)->with('status', 'HocPhan đã được thêm.');
    }

    public function syncTemplate(KhoaHoc $course, CourseCurriculumService $curriculumService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $report = $curriculumService->syncCourse($course);

        return redirect()
            ->route('admin.courses.modules.index', $course)
            ->with(
                'status',
                sprintf(
                    'Đã sinh curriculum mẫu cho khóa này: tạo %d module, cập nhật %d module, thêm %d buổi học.',
                    $report['modules_created'],
                    $report['modules_updated'],
                    $report['lessons_created'],
                )
            );
    }

    public function edit(KhoaHoc $course, HocPhan $module, AdminCourseModuleService $moduleService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        if ($module->course_id !== $course->id) {
            abort(404);
        }

        $module->loadCount('lessons');

        return view('quan_tri.khoa_hoc.sua_hoc_phan', array_merge(
            ['current' => $current, 'module' => $module],
            $moduleService->getCourseDetail($course),
        ));
    }

    public function update(KhoaHoc $course, HocPhan $module, UpdateCourseModuleRequest $request, AdminCourseModuleService $moduleService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $moduleService->updateModule($course, $module, $request->validated());

        return redirect()->route('admin.courses.modules.index', $course)->with('status', 'HocPhan đã được cập nhật.');
    }

    public function destroy(KhoaHoc $course, HocPhan $module, AdminCourseModuleService $moduleService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $result = $moduleService->deleteModule($course, $module);

        return redirect()->route('admin.courses.modules.index', $course)->with($result['type'], $result['message']);
    }

    public function reorder(KhoaHoc $course, Request $request, AdminCourseModuleService $moduleService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
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
