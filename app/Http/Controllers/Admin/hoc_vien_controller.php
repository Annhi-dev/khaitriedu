<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\NguoiDung;
use App\Services\AdminStudentService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request, AdminStudentService $studentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status']);
        $students = $studentService->paginateStudents($filters);
        $summary = $studentService->summary();

        return view('quan_tri.hoc_vien.index', compact('current', 'filters', 'students', 'summary'));
    }

    public function create()
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('quan_tri.hoc_vien.create', compact('current'));
    }

    public function store(StoreStudentRequest $request, AdminStudentService $studentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $student = $studentService->createStudent($request->validated());

        return redirect()->route('admin.students.show', $student)->with('status', 'Học viên đã được tạo thành công.');
    }

    public function show(NguoiDung $student, AdminStudentService $studentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $student = $this->resolveStudent($student);

        return view('quan_tri.hoc_vien.show', array_merge(
            ['current' => $current],
            $studentService->getStudentDetail($student),
        ));
    }

    public function edit(NguoiDung $student)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $student = $this->resolveStudent($student);

        return view('quan_tri.hoc_vien.edit', compact('current', 'student'));
    }

    public function update(UpdateStudentRequest $request, NguoiDung $student, AdminStudentService $studentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $student = $this->resolveStudent($student);
        $studentService->updateStudent($student, $request->validated());

        return redirect()->route('admin.students.show', $student)->with('status', 'Thông tin học viên đã được cập nhật.');
    }

    public function lock(NguoiDung $student, AdminStudentService $studentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $student = $this->resolveStudent($student);
        $studentService->lockStudent($student);

        return redirect()->route('admin.students.show', $student)->with('status', 'Tài khoản học viên đã được khóa.');
    }

    public function unlock(NguoiDung $student, AdminStudentService $studentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $student = $this->resolveStudent($student);
        $studentService->unlockStudent($student);

        return redirect()->route('admin.students.show', $student)->with('status', 'Tài khoản học viên đã được mở khóa.');
    }

    protected function resolveStudent(NguoiDung $student): NguoiDung
    {
        abort_if(! $student->isStudent(), 404);

        return $student;
    }
}
