<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StudentScheduleService;

class ScheduleController extends Controller
{
    public function index(StudentScheduleService $scheduleService)
    {
        [$user, $redirect] = $this->requireRole(User::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        $viewData = $scheduleService->scheduleData($user);

        return view('student.schedule', ['user' => $user] + $viewData);
    }
}
