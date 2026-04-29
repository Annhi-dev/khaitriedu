<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassRoomRequest;
use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\PhongHoc;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use App\Services\TeacherClassroomService;
use App\Services\AdminClassRoomService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ClassRoomController extends Controller
{
    public function index(Request $request)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $query = LopHoc::with(['subject', 'course', 'room', 'teacher', 'schedules'])
            ->withCount('enrollments')
            ->latest();

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        try {
            $classes = $query->paginate(15)->withQueryString();
        } catch (QueryException $e) {
            if ($this->isMissingTableException($e, ['lop_hoc'])) {
                return redirect()->route('admin.dashboard')->with('error', 'Bang lop hoc chua san sang. Vui long chay migration tu CLI truoc khi quan tri.');
            }

            throw $e;
        }

        $subjects = MonHoc::whereIn('status', [MonHoc::STATUS_OPEN, MonHoc::STATUS_DRAFT])->orderBy('name')->get();

        return view('quan_tri.lop_hoc.index', compact('current', 'classes', 'subjects'));
    }

    public function create()
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $subjects = MonHoc::with('category')->whereIn('status', [MonHoc::STATUS_OPEN, MonHoc::STATUS_DRAFT])->orderBy('name')->get();
        $rooms = PhongHoc::where('status', PhongHoc::STATUS_ACTIVE)->orderBy('name')->get();
        $teachers = NguoiDung::teachers()->where('status', NguoiDung::STATUS_ACTIVE)->orderBy('name')->get();
        $courses = KhoaHoc::query()
            ->with('subject')
            ->orderBy('title')
            ->get();
        $days = LichHoc::$dayOptions;

        return view('quan_tri.lop_hoc.create', compact('current', 'subjects', 'rooms', 'teachers', 'courses', 'days'));
    }

    public function store(StoreClassRoomRequest $request, AdminClassRoomService $classRoomService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $classRoomService->create($request->validated());

        return redirect()->route('admin.classes.index')
            ->with('status', 'Lớp học đã được tạo thành công.');
    }

    public function show(LopHoc $class, TeacherClassroomService $gradeService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $class->load(['subject', 'course', 'room', 'teacher', 'schedules', 'enrollments.user']);
        $gradeColumns = $gradeService->gradeColumnsForClass($class);
        $gradeWeightsSupported = $gradeService->gradeWeightsSupported();

        return view('quan_tri.lop_hoc.show', compact('current', 'class', 'gradeColumns', 'gradeWeightsSupported'));
    }

    public function updateGradeWeights(Request $request, LopHoc $class, TeacherClassroomService $gradeService)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'weights' => ['required', 'array', 'min:1'],
            'weights.*' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $gradeService->updateGradeWeights($class, $data['weights']);

        return redirect()->route('admin.classes.show', $class)->with('status', 'Đã cập nhật hệ số các lần kiểm tra.');
    }

    public function destroy(LopHoc $class)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        if ($class->enrollments()->exists()) {
            return back()->withErrors(['error' => 'Khong the xoa lop da co hoc vien dang ky.']);
        }

        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('status', 'Da xoa lop hoc.');
    }
}
