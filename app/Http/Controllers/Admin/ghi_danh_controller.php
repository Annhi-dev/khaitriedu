<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewEnrollmentRequest;
use App\Models\LopHoc;
use App\Models\GhiDanh;
use App\Models\NguoiDung;
use App\Services\AdminEnrollmentService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request, AdminEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status', 'request_source', 'student_id', 'class_room_id']);
        $enrollments = $enrollmentService->paginateEnrollments($filters);
        $statusOptions = GhiDanh::statusOptions();
        $requestSourceOptions = GhiDanh::requestSourceOptions();
        $studentOptions = NguoiDung::query()
            ->students()
            ->whereHas('enrollments')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        $classRoomOptions = LopHoc::query()
            ->has('enrollments')
            ->with(['subject', 'course', 'room'])
            ->orderByDesc('id')
            ->get();

        return view('quan_tri.ghi_danh.index', compact(
            'current',
            'filters',
            'enrollments',
            'statusOptions',
            'requestSourceOptions',
            'studentOptions',
            'classRoomOptions',
        ));
    }

    public function show(GhiDanh $enrollment, AdminEnrollmentService $enrollmentService)
    {
        return $enrollment->isFixedClassEnrollment()
            ? $this->showFixedClass($enrollment, $enrollmentService)
            : $this->showCustomSchedule($enrollment, $enrollmentService);
    }

    public function showFixedClass(GhiDanh $enrollment, AdminEnrollmentService $enrollmentService)
    {
        return $this->renderDetailView('quan_tri.ghi_danh.lop_co_dinh', $enrollment, $enrollmentService);
    }

    public function showCustomSchedule(GhiDanh $enrollment, AdminEnrollmentService $enrollmentService)
    {
        return $this->renderDetailView('quan_tri.ghi_danh.lich_tuy_chon', $enrollment, $enrollmentService);
    }

    public function review(ReviewEnrollmentRequest $request, GhiDanh $enrollment, AdminEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $message = $enrollmentService->reviewEnrollment($enrollment, $request->validated(), $current);

        return redirect()->route('admin.enrollments.show', $enrollment)->with('status', $message);
    }

    protected function renderDetailView(string $view, GhiDanh $enrollment, AdminEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view($view, array_merge(
            ['current' => $current],
            $enrollmentService->getEnrollmentDetail($enrollment),
        ));
    }
}
