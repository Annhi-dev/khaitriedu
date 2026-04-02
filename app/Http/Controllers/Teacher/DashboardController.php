<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TeacherScheduleService;

class DashboardController extends Controller
{
    public function index(TeacherScheduleService $service)
    {
        [$user, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        return view('teacher.dashboard', array_merge([
            'current' => $user,
        ], $service->dashboardData($user)));
    }
}
