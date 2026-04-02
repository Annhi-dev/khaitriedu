<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ScheduleChangeRequest;
use App\Models\User;
use App\Services\TeacherScheduleService;

class ScheduleController extends Controller
{
    public function index(TeacherScheduleService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        return view('teacher.schedules.index', [
            'current' => $current,
            'weekSchedule' => $service->weekSchedule($current),
            'pendingRequestsCount' => ScheduleChangeRequest::query()
                ->where('teacher_id', $current->id)
                ->where('status', ScheduleChangeRequest::STATUS_PENDING)
                ->count(),
        ]);
    }
}
