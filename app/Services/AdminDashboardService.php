<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ScheduleChangeRequest;
use App\Models\Subject;
use App\Models\TeacherApplication;
use App\Models\User;

class AdminDashboardService
{
    public function overview(): array
    {
        return [
            'studentCount' => User::where('role', User::ROLE_STUDENT)->count(),
            'teacherCount' => User::where('role', User::ROLE_TEACHER)->count(),
            'subjectCount' => Subject::count(),
            'activeClassCount' => Course::count(),
            'pendingTeacherApplications' => TeacherApplication::where('status', 'pending')->count(),
            'pendingEnrollments' => Enrollment::where('status', 'pending')->where('is_submitted', true)->count(),
            'pendingScheduleChangeRequests' => ScheduleChangeRequest::where('status', ScheduleChangeRequest::STATUS_PENDING)->count(),
        ];
    }
}