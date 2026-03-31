<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassEnrollController extends Controller
{
    /**
     * Danh sách khóa học student có thể đăng ký.
     */
    public function index()
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);
        if ($redirect) return $redirect;

        try {
            $subjects = Subject::with(['category', 'classRooms'])
                ->publiclyAvailable()
                ->orderBy('name')
                ->paginate(12);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), "Table 'edukhaitri.lop_hoc' doesn't exist") || str_contains($e->getMessage(), "1146")) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                return redirect()->route('student.enroll.index')->with('status', 'Hệ thống đã tự động chạy Database Migration. Vui lòng tải lại trang!');
            }
            throw $e;
        }

        return view('student.enroll.index', compact('current', 'subjects'));
    }

    /**
     * Hiển thị các lớp học available của một khóa gốc cụ thể.
     */
    public function selectClass(Subject $subject)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);
        if ($redirect) return $redirect;

        $student = Auth::user();

        // Các lớp open, có slot trống
        $classes = ClassRoom::with(['subject', 'room', 'teacher', 'schedules'])
            ->withCount('enrollments')
            ->where('subject_id', $subject->id)
            ->where('status', ClassRoom::STATUS_OPEN)
            ->get()
            ->filter(fn ($c) => ! $c->isFull());

        // Kiểm tra học viên đã đăng ký lớp nào trong khóa này chưa
        $existingEnrollment = Enrollment::where('user_id', $student->id)
            ->where('subject_id', $subject->id)
            ->whereNotIn('status', [Enrollment::STATUS_REJECTED])
            ->latest()
            ->first();

        return view('student.enroll.select_class', compact(
            'current',
            'subject',
            'classes',
            'existingEnrollment'
        ));
    }

    /**
     * Lưu đăng ký lớp học.
     */
    public function store(Request $request, Subject $subject)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);
        if ($redirect) return $redirect;

        $data = $request->validate([
            'lop_hoc_id' => 'required|exists:lop_hoc,id',
        ]);

        $student  = Auth::user();
        $classObj = ClassRoom::with('room')->findOrFail($data['lop_hoc_id']);

        // Kiểm tra lớp có thuộc khóa không
        if ($classObj->subject_id != $subject->id) {
            return back()->withErrors(['error' => 'Lớp học không thuộc khóa này.']);
        }

        // Kiểm tra đã đăng ký chưa
        $already = Enrollment::where('user_id', $student->id)
            ->where('lop_hoc_id', $classObj->id)
            ->exists();

        if ($already) {
            return back()->withErrors(['error' => 'Bạn đã đăng ký lớp này rồi.']);
        }

        // Kiểm tra đã đăng ký khóa này chưa (bất kể lớp nào)
        $alreadyInSubject = Enrollment::where('user_id', $student->id)
            ->where('subject_id', $subject->id)
            ->whereNotIn('status', [Enrollment::STATUS_REJECTED])
            ->exists();

        if ($alreadyInSubject) {
            return back()->withErrors(['error' => 'Bạn đã đăng ký khóa học này rồi. Hãy chờ admin xét duyệt hoặc liên hệ để thay lớp.']);
        }

        // Kiểm tra sức chứa
        if ($classObj->isFull()) {
            return back()->withErrors(['error' => 'Lớp này đã đủ chỗ. Vui lòng chọn lớp khác.']);
        }

        Enrollment::create([
            'user_id'      => $student->id,
            'subject_id'   => $subject->id,
            'lop_hoc_id'   => $classObj->id,
            'status'       => Enrollment::STATUS_PENDING,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        // Nếu lớp vừa đầy → tự update status
        if ($classObj->isFull()) {
            $classObj->update(['status' => ClassRoom::STATUS_FULL]);
        }

        return redirect()->route('student.enroll.index')
            ->with('status', 'Đăng ký thành công! Vui lòng chờ admin xét duyệt.');
    }

    /**
     * Danh sách các lớp học viên đã đăng ký.
     */
    public function myClasses()
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);
        if ($redirect) return $redirect;

        $student = Auth::user();

        $enrollments = Enrollment::with(['subject', 'classRoom.schedules', 'classRoom.room', 'classRoom.teacher'])
            ->where('user_id', $student->id)
            ->whereNotNull('lop_hoc_id')
            ->latest()
            ->paginate(10);

        return view('student.enroll.my_classes', compact('current', 'enrollments'));
    }
}
