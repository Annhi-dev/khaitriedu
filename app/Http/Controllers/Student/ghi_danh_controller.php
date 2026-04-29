<?php

namespace App\Http\Controllers\Student;

use App\Exceptions\EnrollmentOperationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentClassEnrollmentRequest;
use App\Http\Requests\Student\StoreStudentScheduleRequest;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use App\Services\StudentEnrollmentService;
use Illuminate\Database\QueryException;

class ClassEnrollController extends Controller
{
    public function index(StudentEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_STUDENT);
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

        return view('hoc_vien.ghi_danh.index', compact('current', 'subjects'));
    }

    public function selectClass(MonHoc $subject, StudentEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_STUDENT);
        if ($redirect) {
            return $redirect;
        }

        try {
            $viewData = $enrollmentService->getFixedClassSelectionContext($current, $subject);
        } catch (EnrollmentOperationException $e) {
            return redirect()->route('student.enroll.index')->with('error', $e->getMessage());
        }

        return view('hoc_vien.ghi_danh.chon_lop', ['current' => $current] + $viewData);
    }

    public function requestForm(MonHoc $subject, StudentEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_STUDENT);
        if ($redirect) {
            return $redirect;
        }

        try {
            $viewData = $enrollmentService->getCustomRequestContext($current, $subject);
        } catch (EnrollmentOperationException $e) {
            return redirect()->route('student.enroll.index')->with('error', $e->getMessage());
        }

        return view('hoc_vien.ghi_danh.yeu_cau_lich_hoc', ['current' => $current] + $viewData);
    }

    public function store(
        StoreStudentClassEnrollmentRequest $request,
        MonHoc $subject,
        StudentEnrollmentService $enrollmentService
    ) {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_STUDENT);
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
        MonHoc $subject,
        StudentEnrollmentService $enrollmentService
    ) {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_STUDENT);
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
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_STUDENT);
        if ($redirect) {
            return $redirect;
        }

        return redirect()->route('student.classes.index');
    }
}
