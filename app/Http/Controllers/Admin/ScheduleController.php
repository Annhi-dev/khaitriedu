<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CheckScheduleConflictRequest;
use App\Http\Requests\Admin\OpenPendingCourseRequest;
use App\Http\Requests\Admin\ScheduleEnrollmentRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\AdminScheduleConflictService;
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

    public function conflicts(CheckScheduleConflictRequest $request, AdminScheduleService $scheduleService, AdminScheduleConflictService $conflictService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->validated();
        $preview = $conflictService->preview($filters);

        return view('admin.schedules.conflicts', array_merge(
            ['current' => $current],
            $preview,
            [
                'filters' => $filters,
                'teachers' => $scheduleService->teacherOptions(),
                'rooms' => $scheduleService->roomOptions(),
                'courses' => $scheduleService->courseOptions(),
                'classRooms' => $scheduleService->classRoomOptions(),
                'dayOptions' => \App\Models\ClassSchedule::$dayOptions,
            ],
        ));
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

    public function showCourse(Course $course, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.schedules.show', array_merge(
            ['current' => $current],
            $scheduleService->getCourseDetailContext($course),
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

    public function showOpenCourse(Course $course, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.schedules.open_course', array_merge(
            ['current' => $current],
            $scheduleService->getOpenCourseContext($course),
        ));
    }

    public function openCourse(OpenPendingCourseRequest $request, Course $course, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $message = $scheduleService->openPendingCourse($course, $request->validated(), $current);

        return redirect()->route('admin.schedules.index')->with('status', $message);
    }
}
