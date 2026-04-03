<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
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

        $query = ClassRoom::with(['subject', 'room', 'teacher', 'schedules'])
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
        $teachers = User::teachers()->orderBy('name')->get();
        $days = ClassSchedule::$dayOptions;

        return view('admin.classes.create', compact('current', 'subjects', 'rooms', 'teachers', 'days'));
    }

    public function store(Request $request)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'subject_id' => 'required|exists:mon_hoc,id',
            'room_id' => 'nullable|exists:rooms,id',
            'teacher_id' => 'nullable|exists:nguoi_dung,id',
            'start_date' => 'nullable|date',
            'note' => 'nullable|string|max:500',
            'schedules' => 'nullable|array',
            'schedules.*.day' => 'required_with:schedules|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'schedules.*.start' => 'required_with:schedules|date_format:H:i',
            'schedules.*.end' => 'required_with:schedules|date_format:H:i|after:schedules.*.start',
        ]);

        $subject = Subject::findOrFail($data['subject_id']);
        $duration = $subject->duration;

        if (! empty($data['schedules'])) {
            foreach ($data['schedules'] as $slot) {
                $days = [$slot['day']];
                $start = $slot['start'];
                $end = $slot['end'];

                if (! empty($data['teacher_id']) && ClassRoom::teacherHasConflict($data['teacher_id'], $days, $start, $end)) {
                    return back()->withInput()->withErrors([
                        'teacher_id' => 'Giang vien da co lop vao khung gio ' . $slot['day'] . ' ' . $start . '-' . $end,
                    ]);
                }

                if (! empty($data['room_id']) && ClassRoom::roomHasConflict($data['room_id'], $days, $start, $end)) {
                    return back()->withInput()->withErrors([
                        'room_id' => 'Phong hoc da duoc su dung vao khung gio ' . $slot['day'] . ' ' . $start . '-' . $end,
                    ]);
                }
            }
        }

        $classRoom = ClassRoom::create([
            'subject_id' => $data['subject_id'],
            'room_id' => $data['room_id'] ?? null,
            'teacher_id' => $data['teacher_id'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'duration' => $duration,
            'note' => $data['note'] ?? null,
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        if (! empty($data['schedules'])) {
            foreach ($data['schedules'] as $slot) {
                ClassSchedule::create([
                    'lop_hoc_id' => $classRoom->id,
                    'day_of_week' => $slot['day'],
                    'start_time' => $slot['start'],
                    'end_time' => $slot['end'],
                ]);
            }
        }

        return redirect()->route('admin.classes.index')
            ->with('status', 'Lop hoc da duoc tao thanh cong.');
    }

    public function show(ClassRoom $class)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $class->load(['subject', 'room', 'teacher', 'schedules', 'enrollments.user']);

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
