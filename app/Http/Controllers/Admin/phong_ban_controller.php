<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignDepartmentTeacherRequest;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdateDepartmentRequest;
use App\Models\PhongBan;
use App\Models\NguoiDung;
use App\Services\AdminDepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status']);
        $departments = $departmentService->paginateDepartments($filters);
        $summary = $departmentService->summary();

        return view('quan_tri.phong_ban.index', compact('current', 'filters', 'departments', 'summary'));
    }

    public function create()
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('quan_tri.phong_ban.create', [
            'current' => $current,
            'department' => new PhongBan(['status' => PhongBan::STATUS_ACTIVE]),
        ]);
    }

    public function store(StoreDepartmentRequest $request, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $department = $departmentService->createDepartment($request->validated());

        return redirect()->route('admin.departments.edit', $department)->with('status', 'Phòng ban đã được tạo thành công.');
    }

    public function edit(PhongBan $department, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $department->loadCount('teachers');
        $teachers = $departmentService->teachersInDepartment($department);
        $assignableTeachers = $departmentService->assignableTeachers($department);

        return view('quan_tri.phong_ban.edit', compact('current', 'department', 'teachers', 'assignableTeachers'));
    }

    public function update(UpdateDepartmentRequest $request, PhongBan $department, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $departmentService->updateDepartment($department, $request->validated());

        return redirect()->route('admin.departments.edit', $department)->with('status', 'Phòng ban đã được cập nhật.');
    }

    public function deactivate(PhongBan $department, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $message = $departmentService->deactivateDepartment($department);

        return redirect()->route('admin.departments.index')->with('status', $message);
    }

    public function activate(PhongBan $department, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $departmentService->activateDepartment($department);

        return redirect()->route('admin.departments.index')->with('status', 'Phòng ban đã được kích hoạt lại.');
    }

    public function assignTeacher(AssignDepartmentTeacherRequest $request, PhongBan $department, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = NguoiDung::query()
            ->teachers()
            ->with('department')
            ->findOrFail((int) $request->validated('teacher_id'));

        $message = $departmentService->assignTeacher($department, $teacher);

        return redirect()->route('admin.departments.edit', $department)->with('status', $message);
    }
}
