<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScheduleEnrollmentRequest;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\AdminScheduleService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['teacher_id', 'student_id', 'course_id', 'date']);
        $schedules = $scheduleService->paginateSchedules($filters);
        $teachers = $scheduleService->teacherOptions();
        $students = $scheduleService->studentOptions();
        $courses = $scheduleService->courseOptions();

        return view('admin.schedules.index', compact('current', 'filters', 'schedules', 'teachers', 'students', 'courses'));
    }

    public function queue(Request $request, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search']);
        $enrollments = $scheduleService->queueEnrollments($filters);

        return view('admin.schedules.queue', compact('current', 'filters', 'enrollments'));
    }

    public function showEnrollment(Enrollment $enrollment, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.schedules.schedule_enrollment', array_merge(
            ['current' => $current],
            $scheduleService->getSchedulingContext($enrollment),
        ));
    }

    public function storeEnrollment(ScheduleEnrollmentRequest $request, Enrollment $enrollment, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $message = $scheduleService->scheduleEnrollment($enrollment, $request->validated(), $current);

        return redirect()->route('admin.schedules.index')->with('status', $message);
    }
}
