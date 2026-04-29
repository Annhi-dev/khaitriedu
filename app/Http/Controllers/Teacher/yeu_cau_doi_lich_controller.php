<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherClassScheduleChangeRequest;
use App\Http\Requests\Teacher\StoreTeacherScheduleChangeRequest;
use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\NguoiDung;
use App\Services\TeacherScheduleChangeRequestService;
use Illuminate\Http\Request;

class ScheduleChangeRequestController extends Controller
{
    public function index(Request $request, TeacherScheduleChangeRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_TEACHER);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['status', 'search']);
        $requests = $service->paginateRequests($current, $filters);

        return view('giao_vien.yeu_cau_doi_lich.index', compact('current', 'filters', 'requests'));
    }

    public function create(KhoaHoc $course)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_TEACHER);
        if ($redirect) {
            return $redirect;
        }

        $course = KhoaHoc::query()
            ->where('teacher_id', $current->id)
            ->with([
                'subject.category',
                'scheduleChangeRequests' => fn ($query) => $query->where('teacher_id', $current->id)->with('reviewer')->latest(),
            ])
            ->find($course->id);

        if (! $course) {
            return redirect()->route('teacher.courses')->with('error', 'Bạn không có quyền gửi yêu cầu dời buổi cho lớp học này.');
        }

        if (! in_array($course->status, KhoaHoc::schedulingStatuses(), true) || ! $course->day_of_week || ! $course->start_date || ! $course->start_time || ! $course->end_time) {
            return redirect()->route('teacher.course.show', $course->id)->with('error', 'Lớp học này chưa có lịch chính thức để gửi yêu cầu dời buổi.');
        }

        return view('giao_vien.yeu_cau_doi_lich.create', compact('current', 'course'));
    }

    public function store(StoreTeacherScheduleChangeRequest $request, KhoaHoc $course, TeacherScheduleChangeRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_TEACHER);
        if ($redirect) {
            return $redirect;
        }

        $course = KhoaHoc::query()->where('teacher_id', $current->id)->find($course->id);

        if (! $course) {
            return redirect()->route('teacher.courses')->with('error', 'Bạn không có quyền gửi yêu cầu dời buổi cho lớp học này.');
        }

        $service->createRequest($course, $current, $request->validated());

        return redirect()->route('teacher.schedule-change-requests.index')->with('status', 'Yêu cầu dời buổi đã được gửi tới admin.');
    }

    public function storeForSchedule(StoreTeacherClassScheduleChangeRequest $request, LichHoc $schedule, TeacherScheduleChangeRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_TEACHER);
        if ($redirect) {
            return $redirect;
        }

        $service->createForClassSchedule($schedule, $current, $request->validated());

        return redirect()->route('teacher.schedules.index')->with('status', 'Yêu cầu dời buổi cho buổi học đã được gửi tới admin.');
    }
}
