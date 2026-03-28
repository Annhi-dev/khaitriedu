<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\User;

class ScheduleController extends Controller
{
    public function index()
    {
        [$user, $redirect] = $this->requireRole(User::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        $enrollments = Enrollment::where('user_id', $user->id)
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->whereNotNull('course_id')
            ->with(['course.subject', 'assignedTeacher'])
            ->orderByDesc('id')
            ->get();

        return view('student.schedule', compact('user', 'enrollments'));
    }
}
