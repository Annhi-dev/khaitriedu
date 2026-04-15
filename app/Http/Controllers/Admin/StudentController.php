<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\User;
use App\Services\AdminStudentService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request, AdminStudentService $studentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status']);
        $students = $studentService->paginateStudents($filters);
        $summary = $studentService->summary();

        return view('admin.students.index', compact('current', 'filters', 'students', 'summary'));
    }

    public function create()
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.students.create', compact('current'));
    }

    public function store(StoreStudentRequest $request, AdminStudentService $studentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $student = $studentService->createStudent($request->validated());

        return redirect()->route('admin.students.show', $student)->with('status', 'Học viên đã được tạo thành công.');
    }

    public function show(User $student, AdminStudentService $studentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $student = $this->resolveStudent($student);

        return view('admin.students.show', array_merge(
            ['current' => $current],
            $studentService->getStudentDetail($student),
        ));
    }

    public function edit(User $student)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $student = $this->resolveStudent($student);

        return view('admin.students.edit', compact('current', 'student'));
    }

    public function update(UpdateStudentRequest $request, User $student, AdminStudentService $studentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $student = $this->resolveStudent($student);
        $studentService->updateStudent($student, $request->validated());

        return redirect()->route('admin.students.show', $student)->with('status', 'Thông tin học viên đã được cập nhật.');
    }

    public function lock(User $student, AdminStudentService $studentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $student = $this->resolveStudent($student);
        $studentService->lockStudent($student);

        return redirect()->route('admin.students.show', $student)->with('status', 'Tài khoản học viên đã được khóa.');
    }

    public function unlock(User $student, AdminStudentService $studentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $student = $this->resolveStudent($student);
        $studentService->unlockStudent($student);

        return redirect()->route('admin.students.show', $student)->with('status', 'Tài khoản học viên đã được mở khóa.');
    }

    protected function resolveStudent(User $student): User
    {
        abort_if(! $student->isStudent(), 404);

        return $student;
    }
}
