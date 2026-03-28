<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewTeacherApplicationRequest;
use App\Models\TeacherApplication;
use App\Models\User;
use App\Services\AdminTeacherApplicationService;
use Illuminate\Http\Request;

class TeacherApplicationController extends Controller
{
    public function index(Request $request, AdminTeacherApplicationService $applicationService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status']);
        $applications = $applicationService->paginateApplications($filters);

        return view('admin.teacher_applications.index', compact('applications', 'current', 'filters'));
    }

    public function show(TeacherApplication $teacherApplication, AdminTeacherApplicationService $applicationService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $teacherApplication->load('reviewer');
        $relatedUser = $applicationService->resolveRelatedUser($teacherApplication);

        return view('admin.teacher_applications.show', [
            'application' => $teacherApplication,
            'current' => $current,
            'relatedUser' => $relatedUser,
        ]);
    }

    public function review(ReviewTeacherApplicationRequest $request, TeacherApplication $teacherApplication, AdminTeacherApplicationService $applicationService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $applicationService->review($teacherApplication, $request->validated(), $current);

        return redirect()->route('admin.teacher-applications.show', $teacherApplication)->with('status', 'Đã cập nhật trạng thái hồ sơ ứng tuyển.');
    }
}