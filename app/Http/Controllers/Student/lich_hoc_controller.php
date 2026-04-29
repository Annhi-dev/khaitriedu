<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Services\StudentScheduleService;

class ScheduleController extends Controller
{
    public function index(StudentScheduleService $scheduleService)
    {
        [$user, $redirect] = $this->requireRole(NguoiDung::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        $viewData = $scheduleService->scheduleData($user);

        return view('hoc_vien.lich_hoc', ['user' => $user] + $viewData);
    }
}
