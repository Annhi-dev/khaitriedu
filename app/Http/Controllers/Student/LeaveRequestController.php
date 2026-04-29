<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreLeaveRequest;
use App\Models\YeuCauXinPhep;
use App\Models\NguoiDung;
use App\Services\LeaveRequestService;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(LeaveRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        return view('hoc_vien.xin_phep.index', array_merge([
            'current' => $current,
            'requests' => $service->studentRequests($current),
        ], $service->studentPageData($current)));
    }

    public function create(LeaveRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        $pageData = $service->studentPageData($current);

        if ($pageData['availableEnrollments']->isEmpty()) {
            return redirect()->route('student.dashboard')->with('error', 'Bạn chưa có lớp học phù hợp để gửi xin phép nghỉ.');
        }

        return view('hoc_vien.xin_phep.create', ['current' => $current] + $pageData);
    }

    public function store(StoreLeaveRequest $request, LeaveRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        $leaveRequest = $service->createRequest($current, $request->validated());

        return redirect()->route('student.leave-requests.show', $leaveRequest)->with('status', 'Yêu cầu xin phép nghỉ đã được gửi đến giảng viên.');
    }

    public function show(YeuCauXinPhep $leaveRequest, LeaveRequestService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        $leaveRequest = $service->findForStudent($current, $leaveRequest);

        return view('hoc_vien.xin_phep.show', compact('current', 'leaveRequest'));
    }
}
