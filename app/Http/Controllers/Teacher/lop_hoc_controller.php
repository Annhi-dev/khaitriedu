<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherAttendanceRequest;
use App\Http\Requests\Teacher\StoreTeacherClassGradesRequest;
use App\Http\Requests\Teacher\StoreTeacherEvaluationRequest;
use App\Models\LopHoc;
use App\Models\NguoiDung;
use App\Services\TeacherClassroomService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TeacherClassroomController extends Controller
{
    public function index(TeacherClassroomService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        return view('giao_vien.lop_hoc.index', [
            'current' => $current,
            'classes' => $service->getAssignedClasses($current),
        ]);
    }

    public function show(Request $request, LopHoc $classRoom, TeacherClassroomService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        try {
            $detail = $service->getClassDetail($current, $classRoom, $request->only(['schedule_id', 'date', 'student_id']));
        } catch (ModelNotFoundException) {
            return redirect()->route('teacher.classes.index')->with('error', 'Bạn không có quyền truy cập lớp học này.');
        }

        $activeTab = $request->query('tab', 'students');

        if (! in_array($activeTab, ['students', 'attendance', 'grades', 'evaluations'], true)) {
            $activeTab = 'students';
        }

        return view('giao_vien.lop_hoc.show', array_merge($detail, [
            'current' => $current,
            'activeTab' => $activeTab,
        ]));
    }

    public function storeAttendance(StoreTeacherAttendanceRequest $request, LopHoc $classRoom, TeacherClassroomService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        try {
            $service->storeAttendance($current, $classRoom, $request->validated());
        } catch (ModelNotFoundException) {
            return redirect()->route('teacher.classes.index')->with('error', 'Bạn không có quyền cập nhật lớp học này.');
        }

        return redirect()->route('teacher.classes.show', [
            'classRoom' => $classRoom->id,
            'tab' => 'attendance',
            'schedule_id' => $request->validated('class_schedule_id'),
            'date' => $request->validated('attendance_date'),
        ])->with('status', 'Đã lưu điểm danh cho buổi học.');
    }

    public function storeGrades(StoreTeacherClassGradesRequest $request, LopHoc $classRoom, TeacherClassroomService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        try {
            $service->storeGrades($current, $classRoom, $request->validated());
        } catch (ModelNotFoundException) {
            return redirect()->route('teacher.classes.index')->with('error', 'Bạn không có quyền cập nhật lớp học này.');
        }

        return redirect()->route('teacher.classes.show', [
            'classRoom' => $classRoom->id,
            'tab' => 'grades',
        ])->with('status', 'Đã cập nhật bảng điểm của lớp.');
    }

    public function storeEvaluation(StoreTeacherEvaluationRequest $request, LopHoc $classRoom, TeacherClassroomService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        try {
            $service->storeEvaluation($current, $classRoom, $request->validated());
        } catch (ModelNotFoundException) {
            return redirect()->route('teacher.classes.index')->with('error', 'Bạn không có quyền cập nhật lớp học này.');
        }

        return redirect()->route('teacher.classes.show', [
            'classRoom' => $classRoom->id,
            'tab' => 'evaluations',
            'student_id' => $request->validated('student_id'),
        ])->with('status', 'Đã lưu đánh giá học viên.');
    }
}
