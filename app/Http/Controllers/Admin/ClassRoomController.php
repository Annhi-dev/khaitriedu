<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassRoomRequest;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use App\Services\AdminClassRoomService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ClassRoomController extends Controller
{
    public function index(Request $request)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $query = ClassRoom::with(['subject', 'course', 'room', 'teacher', 'schedules'])
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

        $subjects = Subject::whereIn('status', [Subject::STATUS_OPEN, Subject::STATUS_DRAFT])->orderBy('name')->get();

        return view('admin.classes.index', compact('current', 'classes', 'subjects'));
    }

    public function create()
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $subjects = Subject::with('category')->whereIn('status', [Subject::STATUS_OPEN, Subject::STATUS_DRAFT])->orderBy('name')->get();
        $rooms = Room::where('status', Room::STATUS_ACTIVE)->orderBy('name')->get();
        $teachers = User::teachers()->where('status', User::STATUS_ACTIVE)->orderBy('name')->get();
        $courses = Course::query()
            ->with('subject')
            ->orderBy('title')
            ->get();
        $days = ClassSchedule::$dayOptions;

        return view('admin.classes.create', compact('current', 'subjects', 'rooms', 'teachers', 'courses', 'days'));
    }

    public function store(StoreClassRoomRequest $request, AdminClassRoomService $classRoomService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $classRoomService->create($request->validated());

        return redirect()->route('admin.classes.index')
            ->with('status', 'Lớp học đã được tạo thành công.');
    }

    public function show(ClassRoom $class)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $class->load(['subject', 'course', 'room', 'teacher', 'schedules', 'enrollments.user']);

        return view('admin.classes.show', compact('current', 'class'));
    }

    public function destroy(ClassRoom $class)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
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
