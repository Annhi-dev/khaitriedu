<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseTimeSlotRequest;
use App\Http\Requests\Admin\UpdateCourseTimeSlotRequest;
use App\Models\CourseTimeSlot;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class CourseTimeSlotController extends Controller
{
    public function index(Request $request)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status', 'subject_id', 'teacher_id']);

        $timeSlots = CourseTimeSlot::query()
            ->with(['subject.category', 'teacher', 'room'])
            ->withCount('registrations')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->query('search'));

                $query->where(function ($timeSlotQuery) use ($search) {
                    $timeSlotQuery->whereHas('subject', fn ($subjectQuery) => $subjectQuery->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('teacher', fn ($teacherQuery) => $teacherQuery->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('room', fn ($roomQuery) => $roomQuery->where('name', 'like', '%' . $search . '%'))
                        ->orWhere('note', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->filled('subject_id'), fn ($query) => $query->where('subject_id', (int) $request->query('subject_id')))
            ->when($request->filled('teacher_id'), fn ($query) => $query->where('teacher_id', (int) $request->query('teacher_id')))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'configured' => CourseTimeSlot::count(),
            'open' => CourseTimeSlot::where('status', CourseTimeSlot::STATUS_OPEN_FOR_REGISTRATION)->count(),
            'ready' => CourseTimeSlot::where('status', CourseTimeSlot::STATUS_READY_TO_OPEN_CLASS)->count(),
            'opened' => CourseTimeSlot::where('status', CourseTimeSlot::STATUS_CLASS_OPENED)->count(),
        ];

        return view('quan_tri.khung_gio_khoa_hoc.index', compact('current', 'filters', 'timeSlots', 'summary') + $this->formOptions());
    }

    public function create()
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('quan_tri.khung_gio_khoa_hoc.create', [
            'current' => $current,
            'courseTimeSlot' => new CourseTimeSlot([
                'status' => CourseTimeSlot::STATUS_PENDING_OPEN,
                'min_students' => 1,
                'max_students' => 20,
            ]),
        ] + $this->formOptions());
    }

    public function store(StoreCourseTimeSlotRequest $request)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        CourseTimeSlot::create($request->validated());

        return redirect()->route('admin.course-time-slots.index')->with('status', 'Khung gio hoc da duoc tao.');
    }

    public function edit(CourseTimeSlot $courseTimeSlot)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('quan_tri.khung_gio_khoa_hoc.edit', compact('current', 'courseTimeSlot') + $this->formOptions());
    }

    public function update(UpdateCourseTimeSlotRequest $request, CourseTimeSlot $courseTimeSlot)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $courseTimeSlot->update($request->validated());

        return redirect()->route('admin.course-time-slots.index')->with('status', 'Khung gio hoc da duoc cap nhat.');
    }

    private function formOptions(): array
    {
        return [
            'statuses' => CourseTimeSlot::statusOptions(),
            'subjects' => Subject::with('category')->orderBy('name')->get(),
            'teachers' => User::teachers()->orderBy('name')->get(),
            'rooms' => Room::orderBy('code')->get(),
            'dayOptions' => [
                'Monday' => 'Thu 2',
                'Tuesday' => 'Thu 3',
                'Wednesday' => 'Thu 4',
                'Thursday' => 'Thu 5',
                'Friday' => 'Thu 6',
                'Saturday' => 'Thu 7',
                'Sunday' => 'Chu nhat',
            ],
        ];
    }
}
