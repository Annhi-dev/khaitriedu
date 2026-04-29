<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ScheduleHelper;
use App\Http\Controllers\Controller;
use App\Models\LopHoc;
use App\Models\GhiDanh;
use App\Models\NhomHoc;
use App\Models\KhoaHoc;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use App\Services\AdminCourseScheduleService;
use App\Services\AdminScheduleConflictService;
use App\Services\AdminScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $selectedSubject = null;
        $selectedCategory = null;
        $returnToCategoryId = $request->filled('return_to_category_id')
            ? (int) $request->query('return_to_category_id')
            : null;

        if ($request->filled('subject_id')) {
            $selectedSubject = MonHoc::with('category')->find((int) $request->query('subject_id'));
            $selectedCategory = $selectedSubject?->category;
        }

        if (! $selectedCategory && $returnToCategoryId) {
            $selectedCategory = NhomHoc::find($returnToCategoryId);
        }

        $subjectsQuery = MonHoc::with('category')->orderBy('name');

        if ($selectedCategory) {
            $subjectsQuery->where('category_id', $selectedCategory->id);
        }

        $subjects = $subjectsQuery->get();

        if ($selectedSubject && ! $subjects->contains('id', $selectedSubject->id)) {
            $subjects = $subjects->prepend($selectedSubject);
        }

        $coursesQuery = KhoaHoc::with(['subject.category', 'teacher'])
            ->withCount('enrollments')
            ->orderBy('title');

        if ($selectedCategory) {
            $coursesQuery->whereHas('subject', fn ($query) => $query->where('category_id', $selectedCategory->id));
        }

        $courses = $coursesQuery->get();
        $categories = NhomHoc::orderBy('name')->get();
        $subjectSuggestions = $subjects->map(function (MonHoc $subject): array {
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

        return view('quan_tri.khoa_hoc', compact(
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

    public function show(KhoaHoc $course)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $course->load([
            'subject.category',
            'teacher',
            'modules' => fn ($query) => $query->with('lessons')->orderBy('position'),
        ])->loadCount('enrollments');
        $teachers = NguoiDung::teachers()->orderBy('name')->get();
        $subjects = MonHoc::with('category')->orderBy('name')->get();

        return view('quan_tri.khoa_hoc.show', compact('course', 'teachers', 'subjects', 'current'));
    }

    public function store(Request $request)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
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
            $subjectForCategory = MonHoc::query()->find((int) $request->input('subject_id'));

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

        $course = DB::transaction(function () use ($data): KhoaHoc {
            if (! empty($data['subject_id'])) {
                $subject = MonHoc::query()->findOrFail((int) $data['subject_id']);
            } else {
                $subjectName = trim((string) $data['subject_name']);

                $subject = MonHoc::query()->firstOrCreate(
                    [
                        'category_id' => (int) $data['category_id'],
                        'name' => $subjectName,
                    ],
                    [
                        'description' => $data['description'] ?? null,
                        'price' => $data['price'] ?? 0,
                        'duration' => $data['subject_duration'] ?? 12,
                        'status' => MonHoc::STATUS_OPEN,
                    ]
                );
            }

            return KhoaHoc::create([
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

    public function update(
        Request $request,
        KhoaHoc $course,
        AdminScheduleConflictService $conflictService,
        AdminScheduleService $scheduleService,
        AdminCourseScheduleService $courseScheduleService
    )
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
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
            'meeting_days' => ['nullable', 'array'],
            'meeting_days.*' => ['string', Rule::in(array_keys(KhoaHoc::dayOptions()))],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
        ]);

        $meetingDays = $courseScheduleService->normalizeMeetingDays($request->input('meeting_days', []));
        $structuredSchedule = $meetingDays !== []
            || $request->filled('start_date')
            || $request->filled('end_date')
            || $request->filled('start_time')
            || $request->filled('end_time');

        $courseData = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? 0,
            'subject_id' => $data['subject_id'],
            'teacher_id' => $data['teacher_id'] ?? null,
        ];

        if ($structuredSchedule) {
            $scheduleData = $courseScheduleService->buildStructuredScheduleData($course, $request);

            if ($scheduleData['meeting_days'] === []) {
                throw ValidationException::withMessages([
                    'meeting_days' => 'Vui lòng chọn ít nhất một ngày học hoặc bỏ trống phần lịch chi tiết.',
                ]);
            }

            foreach ([
                'start_date' => $scheduleData['start_date'],
                'end_date' => $scheduleData['end_date'],
                'start_time' => $scheduleData['start_time'],
                'end_time' => $scheduleData['end_time'],
            ] as $field => $value) {
                if ($value === null || $value === '') {
                    throw ValidationException::withMessages([
                        $field => 'Vui lòng nhập đủ ngày bắt đầu, ngày kết thúc, giờ bắt đầu và giờ kết thúc.',
                    ]);
                }
            }

            $preview = $conflictService->previewCourse([
                'course_id' => $course->id,
                'class_room_id' => $scheduleData['class_room_id'],
                'teacher_id' => $scheduleData['teacher_id'],
                'room_id' => $scheduleData['room_id'],
                'day_of_week' => $scheduleData['meeting_days'],
                'start_date' => $scheduleData['start_date'],
                'end_date' => $scheduleData['end_date'],
                'start_time' => $scheduleData['start_time'],
                'end_time' => $scheduleData['end_time'],
                'exclude_course_id' => $course->id,
                'exclude_class_room_id' => $scheduleData['class_room_id'],
            ]);

            $conflictLabels = $courseScheduleService->collectConflictLabels($preview);

            if ($conflictLabels !== []) {
                throw ValidationException::withMessages([
                    'meeting_days' => $courseScheduleService->buildConflictMessage($conflictLabels),
                ]);
            }

            $courseData = array_merge($courseData, [
                'day_of_week' => $scheduleData['meeting_days'][0] ?? null,
                'meeting_days' => $scheduleData['meeting_days'],
                'start_date' => $scheduleData['start_date'],
                'end_date' => $scheduleData['end_date'],
                'start_time' => $scheduleData['start_time'],
                'end_time' => $scheduleData['end_time'],
                'schedule' => $courseScheduleService->buildScheduleLabel(
                    $scheduleData['meeting_days'],
                    $scheduleData['start_time'],
                    $scheduleData['end_time'],
                    $scheduleData['start_date'],
                    $scheduleData['end_date']
                ),
            ]);
        } else {
            $courseData['schedule'] = $data['schedule'] ?? null;
        }

        DB::transaction(function () use ($course, $courseData, $structuredSchedule, $scheduleService): void {
            $previous = [
                'subject_id' => (int) ($course->subject_id ?? 0),
                'title' => (string) ($course->title ?? ''),
            ];

            $course->update($courseData);
            $this->syncCourseReferences($course, $previous);
            $this->syncTeacherAssignments($course);

            if ($structuredSchedule) {
                $scheduleService->syncCourseSchedule($course);
            }
        });

        if ($structuredSchedule) {
            return redirect()
                ->route('admin.schedules.conflicts', $courseScheduleService->buildConflictRedirectQuery($course, $courseData))
                ->with('status', 'Khóa học đã được cập nhật. Bạn có thể kiểm tra các lịch trùng khác ngay bên dưới.');
        }

        return redirect()->route('admin.course.show', $course)->with('status', 'Khóa học đã được cập nhật.');
    }

    public function previewSchedule(
        Request $request,
        KhoaHoc $course,
        AdminScheduleConflictService $conflictService,
        AdminCourseScheduleService $courseScheduleService
    ) {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $scheduleData = $courseScheduleService->buildStructuredScheduleData($course, $request);

        $preview = $conflictService->previewCourse([
            'course_id' => $course->id,
            'class_room_id' => $scheduleData['class_room_id'],
            'teacher_id' => $scheduleData['teacher_id'],
            'room_id' => $scheduleData['room_id'],
            'day_of_week' => $scheduleData['meeting_days'],
            'start_date' => $scheduleData['start_date'],
            'end_date' => $scheduleData['end_date'],
            'start_time' => $scheduleData['start_time'],
            'end_time' => $scheduleData['end_time'],
            'exclude_course_id' => $course->id,
            'exclude_class_room_id' => $scheduleData['class_room_id'],
        ]);

        return response()->json($courseScheduleService->formatSchedulePreviewResponse($preview));
    }

    protected function collectConflictLabels(array $preview): array
    {
        $labels = [];

        if (($preview['teacherConflicts'] ?? collect())->isNotEmpty()) {
            $labels[] = 'giảng viên';
        }

        if (($preview['roomConflicts'] ?? collect())->isNotEmpty()) {
            $labels[] = 'phòng học';
        }

        if (($preview['studentConflicts'] ?? collect())->isNotEmpty()) {
            $labels[] = 'học viên';
        }

        return $labels;
    }

    protected function buildConflictMessage(array $conflictLabels): string
    {
        return 'Lịch này đang bị trùng ' . implode(', ', $conflictLabels) . '. Hãy chỉnh lại ngày hoặc giờ rồi kiểm tra lại ngay bên dưới.';
    }

    protected function buildConflictRedirectQuery(KhoaHoc $course, array $courseData): array
    {
        return array_filter([
            'course_id' => $course->id,
            'class_room_id' => $course->currentClassRoom()?->id,
            'teacher_id' => $courseData['teacher_id'] ?? null,
            'room_id' => $course->currentClassRoom()?->room_id,
            'day_of_week' => $courseData['meeting_days'] ?? [],
            'start_date' => $courseData['start_date'] ?? null,
            'end_date' => $courseData['end_date'] ?? null,
            'start_time' => $courseData['start_time'] ?? null,
            'end_time' => $courseData['end_time'] ?? null,
            'exclude_course_id' => $course->id,
            'exclude_class_room_id' => $course->currentClassRoom()?->id,
        ], static fn ($value) => $value !== null && $value !== '');
    }

    protected function formatSchedulePreviewResponse(array $preview): array
    {
        $candidate = $preview['candidate'] ?? [];
        $teacherConflicts = $this->formatTeacherConflicts($preview['teacherConflicts'] ?? collect());
        $roomConflicts = $this->formatRoomConflicts($preview['roomConflicts'] ?? collect());
        $studentConflicts = $this->formatStudentConflicts($preview['studentConflicts'] ?? collect());

        return [
            'ready' => (bool) ($candidate['ready'] ?? false),
            'has_conflicts' => (bool) ($preview['hasConflicts'] ?? false),
            'candidate' => [
                'meeting_days_label' => $candidate['meeting_days_label'] ?? '',
                'schedule_label' => $candidate['schedule_label'] ?? '',
                'source_label' => $candidate['source_label'] ?? '',
            ],
            'counts' => [
                'teacher' => count($teacherConflicts),
                'room' => count($roomConflicts),
                'student_groups' => count($studentConflicts),
                'student_items' => array_sum(array_map(
                    fn (array $item) => count($item['conflicts'] ?? []),
                    $studentConflicts
                )),
            ],
            'teacher_conflicts' => $teacherConflicts,
            'room_conflicts' => $roomConflicts,
            'student_conflicts' => $studentConflicts,
        ];
    }

    protected function formatTeacherConflicts($conflicts): array
    {
        return collect($conflicts)
            ->values()
            ->map(function (KhoaHoc $course): array {
                $classRoom = $course->currentClassRoom();

                return [
                    'title' => $course->title,
                    'schedule' => $course->formattedSchedule(),
                    'url' => $classRoom
                        ? route('admin.classes.show', $classRoom)
                        : route('admin.course.show', $course),
                    'edit_url' => route('admin.course.show', $course),
                    'note' => $classRoom
                        ? 'Giảng viên đang có lớp học trùng lịch.'
                        : 'Giảng viên đang có khóa học trùng lịch.',
                ];
            })
            ->all();
    }

    protected function formatRoomConflicts($conflicts): array
    {
        return collect($conflicts)
            ->values()
            ->map(function (LopHoc $classRoom): array {
                return [
                    'title' => $classRoom->displayName(),
                    'schedule' => $classRoom->scheduleSummary(),
                    'room_name' => $classRoom->room?->name ?? '',
                    'url' => route('admin.classes.show', $classRoom),
                    'edit_url' => $classRoom->course
                        ? route('admin.course.show', $classRoom->course)
                        : route('admin.classes.show', $classRoom),
                    'note' => 'Phòng học này đang bị đặt trùng khung giờ.',
                ];
            })
            ->all();
    }

    protected function formatStudentConflicts($conflicts): array
    {
        return collect($conflicts)
            ->values()
            ->map(function (array $group): array {
                return [
                    'student_name' => $group['student_name'] ?? 'Chưa rõ',
                    'student_email' => $group['student_email'] ?? '',
                    'student_url' => $group['student_url'] ?? null,
                    'conflict_count' => (int) ($group['conflict_count'] ?? 0),
                    'conflicts' => collect($group['conflicts'] ?? [])
                        ->map(function (array $conflict): array {
                            return [
                                'course_title' => $conflict['course_title'] ?? '',
                                'schedule' => $conflict['schedule'] ?? '',
                                'candidate_schedule' => $conflict['candidate_schedule'] ?? '',
                                'day_label' => $conflict['day_label'] ?? '',
                                'note' => $conflict['note'] ?? '',
                                'url' => $conflict['url'] ?? null,
                                'edit_url' => $conflict['edit_url'] ?? null,
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->all();
    }

    protected function resolveStartDate(KhoaHoc $course, ?LopHoc $classRoom, mixed $startDate): ?string
    {
        if (is_string($startDate) && $startDate !== '') {
            return $startDate;
        }

        if ($course->start_date) {
            return $course->start_date->format('Y-m-d');
        }

        if ($classRoom?->start_date) {
            return $classRoom->start_date->format('Y-m-d');
        }

        return null;
    }

    protected function resolveEndDate(KhoaHoc $course, ?LopHoc $classRoom, mixed $endDate, ?string $startDate): ?string
    {
        if (is_string($endDate) && $endDate !== '') {
            return $endDate;
        }

        if ($course->end_date) {
            return $course->end_date->format('Y-m-d');
        }

        if ($startDate) {
            $duration = max(1, (int) ($course->subject?->duration ?? $classRoom?->duration ?? 1));

            return Carbon::parse($startDate)->addMonths($duration)->format('Y-m-d');
        }

        return null;
    }

    protected function buildScheduleLabel(array $meetingDays, string $startTime, string $endTime, ?string $startDate, ?string $endDate): string
    {
        $segments = [];

        if ($meetingDays !== []) {
            $segments[] = implode(', ', array_map(function (string $day): string {
                return KhoaHoc::dayOptions()[$day] ?? $day;
            }, $meetingDays));
        }

        if ($startTime !== '' && $endTime !== '') {
            $segments[] = $startTime . ' - ' . $endTime;
        }

        if ($startDate) {
            $startLabel = Carbon::parse($startDate)->format('d/m/Y');
            $endLabel = $endDate ? Carbon::parse($endDate)->format('d/m/Y') : null;
            $segments[] = 'Từ ' . $startLabel . ($endLabel ? ' đến ' . $endLabel : '');
        }

        return $segments !== [] ? implode(' | ', $segments) : 'Chưa có lịch cụ thể';
    }

    public function destroy(KhoaHoc $course)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $course->delete();

        return redirect()->route('admin.courses')->with('status', 'Khóa học đã xóa.');
    }

    public function apiBaseCourse($id)
    {
        $subject = \App\Models\MonHoc::findOrFail($id);
        
        $totalClasses = \App\Models\KhoaHoc::where('subject_id', $subject->id)->count();

        return response()->json([
            'name' => $subject->name,
            'price' => $subject->price ?? 0,
            'capacity' => KhoaHoc::defaultCapacity(),
            'total_classes' => $totalClasses,
        ]);
    }

    public function assign(Request $request, KhoaHoc $course)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
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

    public function storeSubjectCourse(Request $request, MonHoc $subject)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
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

        KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? 0,
            'teacher_id' => $data['teacher_id'] ?? null,
            'schedule' => $data['schedule'] ?? null,
        ]);

        return back()->with('status', 'Khóa học thực tế đã được thêm vào khóa gốc.');
    }

    protected function syncTeacherAssignments(KhoaHoc $course): void
    {
        $course->loadMissing('classRooms');

        $activeClassRooms = $course->classRooms()
            ->whereNotIn('status', [LopHoc::STATUS_CLOSED, LopHoc::STATUS_COMPLETED])
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
            ->whereIn('status', GhiDanh::courseAccessStatuses())
            ->update([
                'assigned_teacher_id' => $course->teacher_id,
            ]);
    }

    protected function syncCourseReferences(KhoaHoc $course, array $previous): void
    {
        $newSubjectId = (int) ($course->subject_id ?? 0);
        $oldTitle = trim((string) ($previous['title'] ?? ''));
        $newTitle = trim((string) ($course->title ?? ''));

        if ($newSubjectId > 0) {
            $course->classRooms()
                ->where(function ($query) use ($newSubjectId) {
                    $query->whereNull('subject_id')
                        ->orWhere('subject_id', '!=', $newSubjectId);
                })
                ->update(['subject_id' => $newSubjectId]);

            $course->enrollments()
                ->where(function ($query) use ($newSubjectId) {
                    $query->whereNull('subject_id')
                        ->orWhere('subject_id', '!=', $newSubjectId);
                })
                ->update(['subject_id' => $newSubjectId]);
        }

        if ($oldTitle !== '' && $oldTitle !== $newTitle) {
            $course->classRooms()
                ->where('name', $oldTitle)
                ->update(['name' => $newTitle]);
        }
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
