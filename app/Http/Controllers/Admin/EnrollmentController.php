<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewEnrollmentRequest;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\AdminEnrollmentService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request, AdminEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status', 'request_source']);
        $enrollments = $enrollmentService->paginateEnrollments($filters);
        $statusOptions = Enrollment::statusOptions();
        $requestSourceOptions = Enrollment::requestSourceOptions();

        return view('admin.enrollments.index', compact('current', 'filters', 'enrollments', 'statusOptions', 'requestSourceOptions'));
    }

    public function show(Enrollment $enrollment, AdminEnrollmentService $enrollmentService)
    {
        return $enrollment->isFixedClassEnrollment()
            ? $this->showFixedClass($enrollment, $enrollmentService)
            : $this->showCustomSchedule($enrollment, $enrollmentService);
    }

    public function showFixedClass(Enrollment $enrollment, AdminEnrollmentService $enrollmentService)
    {
        return $this->renderDetailView('admin.enrollments.fixed_class', $enrollment, $enrollmentService);
    }

    public function showCustomSchedule(Enrollment $enrollment, AdminEnrollmentService $enrollmentService)
    {
        return $this->renderDetailView('admin.enrollments.custom_schedule', $enrollment, $enrollmentService);
    }

    public function review(ReviewEnrollmentRequest $request, Enrollment $enrollment, AdminEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $message = $enrollmentService->reviewEnrollment($enrollment, $request->validated(), $current);

        return redirect()->route('admin.enrollments.show', $enrollment)->with('status', $message);
    }

    protected function renderDetailView(string $view, Enrollment $enrollment, AdminEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view($view, array_merge(
            ['current' => $current],
            $enrollmentService->getEnrollmentDetail($enrollment),
        ));
    }
}
