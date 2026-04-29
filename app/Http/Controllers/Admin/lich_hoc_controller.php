<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CheckScheduleConflictRequest;
use App\Http\Requests\Admin\OpenPendingCourseRequest;
use App\Http\Requests\Admin\ScheduleEnrollmentRequest;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\NguoiDung;
use App\Services\AdminScheduleConflictService;
use App\Services\AdminScheduleService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['teacher_id', 'student_id', 'course_id', 'date']);
        $schedules = $scheduleService->paginateSchedules($filters);
        $teachers = $scheduleService->teacherOptions();
        $students = $scheduleService->studentOptions();
        $courses = $scheduleService->courseOptions();

        return view('quan_tri.lich_hoc.index', compact('current', 'filters', 'schedules', 'teachers', 'students', 'courses'));
    }

    public function conflicts(CheckScheduleConflictRequest $request, AdminScheduleService $scheduleService, AdminScheduleConflictService $conflictService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->validated();
        $preview = ! empty($filters['course_id']) || ! empty($filters['class_room_id'])
            ? $conflictService->previewCourse($filters)
            : $conflictService->preview($filters);

        return view('quan_tri.lich_hoc.xung_dot', array_merge(
            ['current' => $current],
            $preview,
            [
                'filters' => $filters,
                'showCleanupReport' => empty($filters['course_id']) && empty($filters['class_room_id']),
                'teachers' => $scheduleService->teacherOptions(),
                'rooms' => $scheduleService->roomOptions(),
                'courses' => $scheduleService->courseOptions(),
                'classRooms' => $scheduleService->classRoomOptions(),
                'dayOptions' => \App\Models\LichHoc::$dayOptions,
            ],
        ));
    }

    public function queue(Request $request, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search']);
        $enrollments = $scheduleService->queueEnrollments($filters);

        return view('quan_tri.lich_hoc.hang_cho', compact('current', 'filters', 'enrollments'));
    }

    public function showEnrollment(GhiDanh $enrollment, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('quan_tri.lich_hoc.xep_lich_hoc_vien', array_merge(
            ['current' => $current],
            $scheduleService->getSchedulingContext($enrollment),
        ));
    }

    public function showCourse(KhoaHoc $course, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('quan_tri.lich_hoc.show', array_merge(
            ['current' => $current],
            $scheduleService->getCourseDetailContext($course),
        ));
    }

    public function storeEnrollment(ScheduleEnrollmentRequest $request, GhiDanh $enrollment, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $message = $scheduleService->scheduleEnrollment($enrollment, $request->validated(), $current);

        return redirect()->route('admin.schedules.index')->with('status', $message);
    }

    public function showOpenCourse(KhoaHoc $course, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('quan_tri.lich_hoc.mo_lop', array_merge(
            ['current' => $current],
            $scheduleService->getOpenCourseContext($course),
        ));
    }

    public function openCourse(OpenPendingCourseRequest $request, KhoaHoc $course, AdminScheduleService $scheduleService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $message = $scheduleService->openPendingCourse($course, $request->validated(), $current);

        return redirect()->route('admin.schedules.index')->with('status', $message);
    }
}
