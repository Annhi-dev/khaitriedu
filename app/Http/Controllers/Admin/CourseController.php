<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Enrollment;
use App\Models\Category;
use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $selectedSubject = null;
        $selectedCategory = null;
        $returnToCategoryId = $request->filled('return_to_category_id')
            ? (int) $request->query('return_to_category_id')
            : null;

        if ($request->filled('subject_id')) {
            $selectedSubject = Subject::with('category')->find((int) $request->query('subject_id'));
            $selectedCategory = $selectedSubject?->category;
        }

        if (! $selectedCategory && $returnToCategoryId) {
            $selectedCategory = Category::find($returnToCategoryId);
        }

        $subjectsQuery = Subject::with('category')->orderBy('name');

        if ($selectedCategory) {
            $subjectsQuery->where('category_id', $selectedCategory->id);
        }

        $subjects = $subjectsQuery->get();

        if ($selectedSubject && ! $subjects->contains('id', $selectedSubject->id)) {
            $subjects = $subjects->prepend($selectedSubject);
        }

        $coursesQuery = Course::with(['subject.category', 'teacher'])
            ->withCount('enrollments')
            ->orderBy('title');

        if ($selectedCategory) {
            $coursesQuery->whereHas('subject', fn ($query) => $query->where('category_id', $selectedCategory->id));
        }

        $courses = $coursesQuery->get();
        $categories = Category::orderBy('name')->get();
        $subjectSuggestions = $subjects->map(function (Subject $subject): array {
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'description' => $subject->description,
                'price' => (float) ($subject->price ?? 0),
                'duration' => $subject->duration,
                'category_id' => $subject->category_id,
            ];
        })->values();
        $nextBatch = $this->resolveNextBatch($courses);

        return view('admin.courses', compact(
            'courses',
            'subjects',
            'subjectSuggestions',
            'categories',
            'current',
            'selectedSubject',
            'selectedCategory',
            'returnToCategoryId',
            'nextBatch',
        ));
    }

    public function show(Course $course)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $course->load([
            'subject.category',
            'teacher',
            'modules' => fn ($query) => $query->with('lessons')->orderBy('position'),
        ])->loadCount('enrollments');
        $teachers = User::teachers()->orderBy('name')->get();
        $subjects = Subject::with('category')->orderBy('name')->get();

        return view('admin.course.show', compact('course', 'teachers', 'subjects', 'current'));
    }

    public function store(Request $request)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $request->merge([
            'subject_name' => trim((string) $request->input('subject_name', '')),
            'title' => trim((string) $request->input('title', '')),
            'description' => $request->filled('description')
                ? trim((string) $request->input('description'))
                : null,
        ]);

        if ($request->filled('subject_id')) {
            $subjectForCategory = Subject::query()->find((int) $request->input('subject_id'));

            if ($subjectForCategory) {
                $request->merge([
                    'category_id' => $subjectForCategory->category_id,
                ]);
            }
        }

        $data = $request->validate([
            'category_id' => 'required|exists:danh_muc,id',
            'subject_id' => 'nullable|exists:mon_hoc,id',
            'subject_name' => 'required_without:subject_id|string|max:255',
            'subject_duration' => 'required_without:subject_id|nullable|integer|min:1|max:120',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'teacher_id' => 'nullable|exists:nguoi_dung,id',
            'schedule' => 'nullable|string|max:255',
            'return_to_category_id' => 'nullable|exists:danh_muc,id',
        ]);

        $course = DB::transaction(function () use ($data): Course {
            if (! empty($data['subject_id'])) {
                $subject = Subject::query()->findOrFail((int) $data['subject_id']);
            } else {
                $subjectName = trim((string) $data['subject_name']);

                $subject = Subject::query()->firstOrCreate(
                    [
                        'category_id' => (int) $data['category_id'],
                        'name' => $subjectName,
                    ],
                    [
                        'description' => $data['description'] ?? null,
                        'price' => $data['price'] ?? 0,
                        'duration' => $data['subject_duration'] ?? 12,
                        'status' => Subject::STATUS_OPEN,
                    ]
                );
            }

            return Course::create([
                'subject_id' => $subject->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'] ?? 0,
                'teacher_id' => $data['teacher_id'] ?? null,
                'schedule' => $data['schedule'] ?? null,
            ]);
        });

        $subject = $course->subject()->with('category')->first();
        $returnToCategoryId = (int) ($data['return_to_category_id'] ?? 0);

        if ($returnToCategoryId > 0 && (int) ($subject?->category_id ?? 0) === $returnToCategoryId) {
            return redirect()->route('admin.categories.show', $returnToCategoryId)->with('status', 'Khóa học đã được thêm vào nhóm học.');
        }

        return redirect()->route('admin.courses')->with('status', 'Khóa học đã được thêm.');
    }

    public function update(Request $request, Course $course)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'subject_id' => 'required|exists:mon_hoc,id',
            'teacher_id' => 'nullable|exists:nguoi_dung,id',
            'schedule' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($course, $data): void {
            $data['price'] = $data['price'] ?? 0;
            $course->update($data);
            $this->syncTeacherAssignments($course);
        });

        return redirect()->route('admin.course.show', $course)->with('status', 'Khóa học đã được cập nhật.');
    }

    public function destroy(Course $course)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $course->delete();

        return redirect()->route('admin.courses')->with('status', 'Khóa học đã xóa.');
    }

    public function apiBaseCourse($id)
    {
        $subject = \App\Models\Subject::findOrFail($id);
        
        $totalClasses = \App\Models\Course::where('subject_id', $subject->id)->count();

        return response()->json([
            'name' => $subject->name,
            'price' => $subject->price ?? 0,
            'capacity' => 30, // Default capacity
            'total_classes' => $totalClasses
        ]);
    }

    public function assign(Request $request, Course $course)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'teacher_id' => 'nullable|exists:nguoi_dung,id',
            'schedule' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($course, $data): void {
            $course->update($data);
            $this->syncTeacherAssignments($course);
        });

        return redirect()->route('admin.courses')->with('status', 'Khóa học đã cập nhật giảng viên và lịch.');
    }

    public function storeSubjectCourse(Request $request, Subject $subject)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'teacher_id' => 'nullable|exists:nguoi_dung,id',
            'schedule' => 'nullable|string|max:255',
        ]);

        Course::create([
            'subject_id' => $subject->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? 0,
            'teacher_id' => $data['teacher_id'] ?? null,
            'schedule' => $data['schedule'] ?? null,
        ]);

        return back()->with('status', 'Khóa học thực tế đã được thêm vào khóa gốc.');
    }

    protected function syncTeacherAssignments(Course $course): void
    {
        $course->loadMissing('classRooms');

        $activeClassRooms = $course->classRooms()
            ->whereNotIn('status', [ClassRoom::STATUS_CLOSED, ClassRoom::STATUS_COMPLETED])
            ->with('schedules')
            ->get();

        foreach ($activeClassRooms as $classRoom) {
            $classRoom->forceFill([
                'teacher_id' => $course->teacher_id,
            ])->save();

            $classRoom->schedules()
                ->update(['teacher_id' => $course->teacher_id]);
        }

        $course->enrollments()
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->update([
                'assigned_teacher_id' => $course->teacher_id,
            ]);
    }

    protected function resolveNextBatch(iterable $courses): int
    {
        $maxBatch = (int) date('y') - 1;

        foreach ($courses as $course) {
            if (preg_match('/Khóa\s+(\d+)/iu', (string) ($course->title ?? ''), $matches)) {
                $batch = (int) $matches[1];

                if ($batch > $maxBatch) {
                    $maxBatch = $batch;
                }
            }
        }

        return $maxBatch + 1;
    }
}
