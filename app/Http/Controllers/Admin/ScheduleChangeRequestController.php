<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewScheduleChangeRequest;
use App\Models\ScheduleChangeRequest;
use App\Models\User;
use App\Services\AdminScheduleChangeRequestService;
use Illuminate\Http\Request;

class ScheduleChangeRequestController extends Controller
{
    public function index(Request $request, AdminScheduleChangeRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['status', 'search']);
        $requests = $service->paginateRequests($filters);

        return view('admin.schedule_change_requests.index', compact('current', 'filters', 'requests'));
    }

    public function show(ScheduleChangeRequest $scheduleChangeRequest)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $scheduleChangeRequest->load(['teacher', 'course.subject.category', 'classRoom.subject.category', 'classSchedule', 'reviewer']);

        return view('admin.schedule_change_requests.show', compact('current', 'scheduleChangeRequest'));
    }

    public function review(ReviewScheduleChangeRequest $request, ScheduleChangeRequest $scheduleChangeRequest, AdminScheduleChangeRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $message = $service->review($scheduleChangeRequest, $request->validated(), $current);

        return redirect()->route('admin.schedule-change-requests.show', $scheduleChangeRequest)->with('status', $message);
    }
}
