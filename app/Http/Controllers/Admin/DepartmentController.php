<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignDepartmentTeacherRequest;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\User;
use App\Services\AdminDepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status']);
        $departments = $departmentService->paginateDepartments($filters);
        $summary = $departmentService->summary();

        return view('admin.departments.index', compact('current', 'filters', 'departments', 'summary'));
    }

    public function create()
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.departments.create', [
            'current' => $current,
            'department' => new Department(['status' => Department::STATUS_ACTIVE]),
        ]);
    }

    public function store(StoreDepartmentRequest $request, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $department = $departmentService->createDepartment($request->validated());

        return redirect()->route('admin.departments.edit', $department)->with('status', 'Phòng ban đã được tạo thành công.');
    }

    public function edit(Department $department, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $department->loadCount('teachers');
        $teachers = $departmentService->teachersInDepartment($department);
        $assignableTeachers = $departmentService->assignableTeachers($department);

        return view('admin.departments.edit', compact('current', 'department', 'teachers', 'assignableTeachers'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $departmentService->updateDepartment($department, $request->validated());

        return redirect()->route('admin.departments.edit', $department)->with('status', 'Phòng ban đã được cập nhật.');
    }

    public function deactivate(Department $department, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $message = $departmentService->deactivateDepartment($department);

        return redirect()->route('admin.departments.index')->with('status', $message);
    }

    public function activate(Department $department, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $departmentService->activateDepartment($department);

        return redirect()->route('admin.departments.index')->with('status', 'Phòng ban đã được kích hoạt lại.');
    }

    public function assignTeacher(AssignDepartmentTeacherRequest $request, Department $department, AdminDepartmentService $departmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacher = User::query()
            ->teachers()
            ->with('department')
            ->findOrFail((int) $request->validated('teacher_id'));

        $message = $departmentService->assignTeacher($department, $teacher);

        return redirect()->route('admin.departments.edit', $department)->with('status', $message);
    }
}
