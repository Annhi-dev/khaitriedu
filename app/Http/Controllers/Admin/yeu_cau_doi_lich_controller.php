<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewScheduleChangeRequest;
use App\Models\YeuCauDoiLich;
use App\Models\NguoiDung;
use App\Services\AdminScheduleChangeRequestService;
use Illuminate\Http\Request;

class ScheduleChangeRequestController extends Controller
{
    public function index(Request $request, AdminScheduleChangeRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['status', 'search']);
        $requests = $service->paginateRequests($filters);

        return view('quan_tri.yeu_cau_doi_lich.index', compact('current', 'filters', 'requests'));
    }

    public function show(YeuCauDoiLich $scheduleChangeRequest)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $scheduleChangeRequest->load([
            'teacher',
            'course.subject.category',
            'classRoom.subject.category',
            'classRoom.room',
            'classSchedule.room',
            'requestedRoom',
            'reviewer',
        ]);

        return view('quan_tri.yeu_cau_doi_lich.show', compact('current', 'scheduleChangeRequest'));
    }

    public function review(ReviewScheduleChangeRequest $request, YeuCauDoiLich $scheduleChangeRequest, AdminScheduleChangeRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $message = $service->review($scheduleChangeRequest, $request->validated(), $current);

        return redirect()->route('admin.schedule-change-requests.show', $scheduleChangeRequest)->with('status', $message);
    }
}
