<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\ReviewLeaveRequest;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveRequestService;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request, LeaveRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status']);

        return view('giao_vien.xin_phep.index', array_merge([
            'current' => $current,
            'filters' => $filters,
            'requests' => $service->teacherRequests($current, $filters),
        ], $service->teacherPageData($current, $filters)));
    }

    public function show(LeaveRequest $leaveRequest, LeaveRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        $leaveRequest = $service->findForTeacher($current, $leaveRequest);

        return view('giao_vien.xin_phep.show', compact('current', 'leaveRequest'));
    }

    public function review(ReviewLeaveRequest $request, LeaveRequest $leaveRequest, LeaveRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        $message = $service->reviewRequest($leaveRequest, $current, $request->validated());

        return redirect()->route('teacher.leave-requests.show', $leaveRequest)->with('status', $message);
    }
}
