<?php

namespace App\Http\Controllers\Student;

use App\Exceptions\EnrollmentOperationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentClassEnrollmentRequest;
use App\Http\Requests\Student\StoreStudentScheduleRequest;
use App\Models\Subject;
use App\Models\User;
use App\Services\StudentEnrollmentService;
use Illuminate\Database\QueryException;

class ClassEnrollController extends Controller
{
    public function index(StudentEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);
        if ($redirect) {
            return $redirect;
        }

        try {
            $subjects = $enrollmentService->paginateAvailableSubjects($current);
        } catch (QueryException $e) {
            if ($this->isMissingTableException($e, ['lop_hoc'])) {
                return redirect()->route('student.dashboard')->with('error', 'He thong chua duoc khoi tao day du. Vui long chay migration tu CLI truoc khi mo cong dang ky.');
            }

            throw $e;
        }

        return view('student.enroll.index', compact('current', 'subjects'));
    }

    public function selectClass(Subject $subject, StudentEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);
        if ($redirect) {
            return $redirect;
        }

        try {
            $viewData = $enrollmentService->getFixedClassSelectionContext($current, $subject);
        } catch (EnrollmentOperationException $e) {
            return redirect()->route('student.enroll.index')->with('error', $e->getMessage());
        }

        return view('student.enroll.select_class', ['current' => $current] + $viewData);
    }

    public function requestForm(Subject $subject, StudentEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);
        if ($redirect) {
            return $redirect;
        }

        try {
            $viewData = $enrollmentService->getCustomRequestContext($current, $subject);
        } catch (EnrollmentOperationException $e) {
            return redirect()->route('student.enroll.index')->with('error', $e->getMessage());
        }

        return view('student.enroll.request_form', ['current' => $current] + $viewData);
    }

    public function store(
        StoreStudentClassEnrollmentRequest $request,
        Subject $subject,
        StudentEnrollmentService $enrollmentService
    ) {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);
        if ($redirect) {
            return $redirect;
        }

        try {
            $message = $enrollmentService->submitFixedClassEnrollment($current, $subject, $request->integer('lop_hoc_id'));
        } catch (EnrollmentOperationException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('student.enroll.my-classes')->with('status', $message);
    }

    public function storeCustomRequest(
        StoreStudentScheduleRequest $request,
        Subject $subject,
        StudentEnrollmentService $enrollmentService
    ) {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);
        if ($redirect) {
            return $redirect;
        }

        try {
            $message = $enrollmentService->submitCustomScheduleRequest($current, $subject, $request->validated());
        } catch (EnrollmentOperationException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('student.enroll.my-classes')->with('status', $message);
    }

    public function myClasses(StudentEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);
        if ($redirect) {
            return $redirect;
        }

        $enrollments = $enrollmentService->paginateStudentEnrollments($current);

        return view('student.enroll.my_classes', compact('current', 'enrollments'));
    }
}
