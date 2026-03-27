<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ReviewEnrollmentRequest;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\AdminEnrollmentService;
use Illuminate\Http\Request;

class AdminEnrollmentController extends Controller
{
    public function index(Request $request, AdminEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status']);
        $enrollments = $enrollmentService->paginateEnrollments($filters);
        $statusOptions = Enrollment::filterableStatuses();

        return view('admin.enrollments.index', compact('current', 'filters', 'enrollments', 'statusOptions'));
    }

    public function show(Enrollment $enrollment, AdminEnrollmentService $enrollmentService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.enrollments.show', array_merge(
            ['current' => $current],
            $enrollmentService->getEnrollmentDetail($enrollment),
        ));
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
}