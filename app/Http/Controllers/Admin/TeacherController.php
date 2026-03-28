<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\User;
use App\Services\AdminTeacherService;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request, AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status']);
        $teachers = $teacherService->paginateTeachers($filters);

        return view('admin.teachers.index', compact('current', 'filters', 'teachers'));
    }

    public function create()
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.teachers.create', compact('current'));
    }

    public function store(StoreTeacherRequest $request, AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = $teacherService->createTeacher($request->validated());

        return redirect()->route('admin.teachers.show', $teacher)->with('status', 'Giảng viên đã được tạo thành công.');
    }

    public function show(User $teacher, AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = $this->resolveTeacher($teacher);

        return view('admin.teachers.show', array_merge(
            ['current' => $current],
            $teacherService->getTeacherDetail($teacher),
        ));
    }

    public function edit(User $teacher)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = $this->resolveTeacher($teacher);

        return view('admin.teachers.edit', compact('current', 'teacher'));
    }

    public function update(UpdateTeacherRequest $request, User $teacher, AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = $this->resolveTeacher($teacher);
        $teacherService->updateTeacher($teacher, $request->validated());

        return redirect()->route('admin.teachers.show', $teacher)->with('status', 'Thông tin giảng viên đã được cập nhật.');
    }

    public function lock(User $teacher, AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = $this->resolveTeacher($teacher);
        $teacherService->lockTeacher($teacher);

        return redirect()->route('admin.teachers.show', $teacher)->with('status', 'Tài khoản giảng viên đã được khóa.');
    }

    public function unlock(User $teacher, AdminTeacherService $teacherService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = $this->resolveTeacher($teacher);
        $teacherService->unlockTeacher($teacher);

        return redirect()->route('admin.teachers.show', $teacher)->with('status', 'Tài khoản giảng viên đã được mở khóa.');
    }

    protected function resolveTeacher(User $teacher): User
    {
        abort_if($teacher->role !== User::ROLE_TEACHER, 404);

        return $teacher;
    }
}
