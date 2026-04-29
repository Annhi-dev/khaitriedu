<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\NguoiDung;
use App\Services\AdminTeacherService;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request, AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status', 'department_id']);
        $teachers = $teacherService->paginateTeachers($filters);
        $summary = $teacherService->summary();
        $departments = $teacherService->departmentOptions();

        return view('quan_tri.giao_vien.index', compact('current', 'filters', 'teachers', 'departments', 'summary'));
    }

    public function create(AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $departments = $teacherService->departmentOptions();

        return view('quan_tri.giao_vien.create', compact('current', 'departments'));
    }

    public function store(StoreTeacherRequest $request, AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = $teacherService->createTeacher($request->validated());

        return redirect()->route('admin.teachers.show', $teacher)->with('status', 'Giảng viên đã được tạo thành công.');
    }

    public function show(NguoiDung $teacher, AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = $this->resolveTeacher($teacher);

        return view('quan_tri.giao_vien.show', array_merge(
            ['current' => $current],
            $teacherService->getTeacherDetail($teacher),
        ));
    }

    public function edit(NguoiDung $teacher, AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = $this->resolveTeacher($teacher);
        $departments = $teacherService->departmentOptions();

        return view('quan_tri.giao_vien.edit', compact('current', 'teacher', 'departments'));
    }

    public function update(UpdateTeacherRequest $request, NguoiDung $teacher, AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = $this->resolveTeacher($teacher);
        $teacherService->updateTeacher($teacher, $request->validated());

        return redirect()->route('admin.teachers.show', $teacher)->with('status', 'Thông tin giảng viên đã được cập nhật.');
    }

    public function lock(NguoiDung $teacher, AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = $this->resolveTeacher($teacher);
        $teacherService->lockTeacher($teacher);

        return redirect()->route('admin.teachers.show', $teacher)->with('status', 'Tài khoản giảng viên đã được khóa.');
    }

    public function unlock(NguoiDung $teacher, AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = $this->resolveTeacher($teacher);
        $teacherService->unlockTeacher($teacher);

        return redirect()->route('admin.teachers.show', $teacher)->with('status', 'Tài khoản giảng viên đã được mở khóa.');
    }

    protected function resolveTeacher(NguoiDung $teacher): NguoiDung
    {
        abort_if(! $teacher->isTeacher(), 404);

        return $teacher;
    }
}
