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
        $recentEnrollments = Enrollment::query()
            ->with(['user', 'subject.category', 'course.subject'])
            ->latest('submitted_at')
            ->latest('id')
            ->limit(6)
            ->get();

        $pendingTeacherApplicationsList = TeacherApplication::query()
            ->where('status', TeacherApplication::STATUS_PENDING)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $pendingScheduleRequestsList = ScheduleChangeRequest::query()
            ->with(['teacher', 'course.subject'])
            ->where('status', ScheduleChangeRequest::STATUS_PENDING)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $recentCourses = Course::query()
            ->with(['subject.category', 'teacher'])
            ->latest('id')
            ->limit(5)
            ->get();

        return [
            'studentCount' => User::where('role', User::ROLE_STUDENT)->count(),
            'teacherCount' => User::where('role', User::ROLE_TEACHER)->count(),
            'subjectCount' => Subject::count(),
            'activeClassCount' => Course::count(),
            'pendingTeacherApplications' => TeacherApplication::where('status', TeacherApplication::STATUS_PENDING)->count(),
            'pendingEnrollments' => Enrollment::where('status', Enrollment::STATUS_PENDING)->where('is_submitted', true)->count(),
            'pendingScheduleChangeRequests' => ScheduleChangeRequest::where('status', ScheduleChangeRequest::STATUS_PENDING)->count(),
            'recentEnrollments' => $recentEnrollments,
            'pendingTeacherApplicationsList' => $pendingTeacherApplicationsList,
            'pendingScheduleRequestsList' => $pendingScheduleRequestsList,
            'recentCourses' => $recentCourses,
        ];
    }
}