<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teacher\StoreTeacherScheduleChangeRequest;
use App\Models\Course;
use App\Models\User;
use App\Services\TeacherScheduleChangeRequestService;
use Illuminate\Http\Request;

class TeacherScheduleChangeRequestController extends Controller
{
    public function index(Request $request, TeacherScheduleChangeRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['status', 'search']);
        $requests = $service->paginateRequests($current, $filters);

        return view('teacher.schedule_change_requests.index', compact('current', 'filters', 'requests'));
    }

    public function create(Course $course)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);
        if ($redirect) {
            return $redirect;
        }

        $course = Course::query()
            ->where('teacher_id', $current->id)
            ->with([
                'subject.category',
                'scheduleChangeRequests' => fn ($query) => $query->where('teacher_id', $current->id)->with('reviewer')->latest(),
            ])
            ->find($course->id);

        if (! $course) {
            return redirect()->route('teacher.courses')->with('error', 'Bạn không có quyền gửi yêu cầu đổi lịch cho lớp học này.');
        }

        if (! in_array($course->status, Course::schedulingStatuses(), true) || ! $course->day_of_week || ! $course->start_date || ! $course->start_time || ! $course->end_time) {
            return redirect()->route('teacher.course.show', $course->id)->with('error', 'Lớp học này chưa có lịch chính thức để gửi yêu cầu đổi lịch.');
        }

        return view('teacher.schedule_change_requests.create', compact('current', 'course'));
    }

    public function store(StoreTeacherScheduleChangeRequest $request, Course $course, TeacherScheduleChangeRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);
        if ($redirect) {
            return $redirect;
        }

        $course = Course::query()->where('teacher_id', $current->id)->find($course->id);

        if (! $course) {
            return redirect()->route('teacher.courses')->with('error', 'Bạn không có quyền gửi yêu cầu đổi lịch cho lớp học này.');
        }

        $service->createRequest($course, $current, $request->validated());

        return redirect()->route('teacher.schedule-change-requests.index')->with('status', 'Yêu cầu đổi lịch đã được gửi tới admin.');
    }
}