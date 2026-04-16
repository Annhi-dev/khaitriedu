<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\ClassRoom;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\TeacherEvaluation;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class TeacherClassroomService
{
    public function getAssignedClasses(User $teacher): Collection
    {
        return ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->with(['subject.category', 'room', 'schedules'])
            ->withCount([
                'enrollments as students_count' => fn ($query) => $query->whereIn('status', Enrollment::courseAccessStatuses()),
            ])
            ->orderByDesc('id')
            ->get();
    }

    public function getClassDetail(User $teacher, ClassRoom $classRoom, array $filters): array
    {
        $ownedClass = $this->resolveOwnedClass($teacher, $classRoom);

        $enrollments = Enrollment::query()
            ->where('lop_hoc_id', $ownedClass->id)
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->with(['user', 'course', 'classRoom'])
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->user !== null)
            ->sortBy(fn (Enrollment $enrollment) => mb_strtolower($enrollment->user->name))
            ->values();

        Enrollment::syncDisplayStatusesByClass($enrollments);

        $ownedClass->setRelation('enrollments', $enrollments);
        $ownedClass->setRelation('schedules', $ownedClass->schedules->sortBy(function ($schedule) {
            return array_search($schedule->day_of_week, array_keys(\App\Models\ClassSchedule::$dayOptions), true);
        })->values());

        $selectedSchedule = $ownedClass->schedules->firstWhere('id', (int) ($filters['schedule_id'] ?? 0))
            ?? $ownedClass->schedules->first();
        $selectedDate = $filters['date'] ?? now()->toDateString();

        $attendanceMap = AttendanceRecord::query()
            ->where('class_room_id', $ownedClass->id)
            ->when($selectedSchedule, fn ($query) => $query->where('class_schedule_id', $selectedSchedule->id))
            ->whereDate('attendance_date', $selectedDate)
            ->get()
            ->keyBy('student_id');

        $gradeColumns = $this->gradeColumns($ownedClass);
        $gradeMap = Grade::query()
            ->where('class_room_id', $ownedClass->id)
            ->whereIn('test_name', $gradeColumns->pluck('name')->all())
            ->get()
            ->keyBy(fn (Grade $grade) => $grade->student_id . '|' . $grade->test_name);

        $averageScoreMap = $this->averageScoreMap($enrollments, $gradeColumns, $gradeMap);

        $evaluationHistory = TeacherEvaluation::query()
            ->where('class_room_id', $ownedClass->id)
            ->with('student')
            ->latest()
            ->take(8)
            ->get();

        $evaluationStudentOptions = $enrollments
            ->map(fn (Enrollment $enrollment) => $enrollment->user)
            ->filter(fn (?User $student) => $student !== null)
            ->merge(
                $evaluationHistory
                    ->map(fn (TeacherEvaluation $evaluation) => $evaluation->student)
                    ->filter(fn (?User $student) => $student !== null)
            )
            ->unique(fn (User $student) => (int) $student->id)
            ->sortBy(fn (User $student) => mb_strtolower((string) $student->name))
            ->values();

        $selectedStudentId = (int) ($filters['student_id'] ?? ($evaluationStudentOptions->first()?->id ?? 0));

        if ($selectedStudentId !== 0 && ! $evaluationStudentOptions->contains(fn (User $student) => (int) $student->id === $selectedStudentId)) {
            $selectedStudentId = (int) ($evaluationStudentOptions->first()?->id ?? 0);
        }

        $currentEvaluation = $selectedStudentId === 0
            ? null
            : TeacherEvaluation::query()
                ->where('class_room_id', $ownedClass->id)
                ->where('student_id', $selectedStudentId)
                ->first();

        return [
            'classRoom' => $ownedClass,
            'enrollments' => $enrollments,
            'selectedSchedule' => $selectedSchedule,
            'selectedDate' => $selectedDate,
            'attendanceMap' => $attendanceMap,
            'gradeColumns' => $gradeColumns,
            'gradeMap' => $gradeMap,
            'averageScoreMap' => $averageScoreMap,
            'selectedStudentId' => $selectedStudentId,
            'currentEvaluation' => $currentEvaluation,
            'evaluationHistory' => $evaluationHistory,
            'evaluationStudentOptions' => $evaluationStudentOptions,
            'gradeWeightsSupported' => $this->supportsGradeWeights(),
        ];
    }

    public function storeAttendance(User $teacher, ClassRoom $classRoom, array $data): void
    {
        $ownedClass = $this->resolveOwnedClass($teacher, $classRoom, ['schedules']);
        $schedule = $ownedClass->schedules->firstWhere('id', (int) $data['class_schedule_id']);

        if (! $schedule) {
            throw ValidationException::withMessages([
                'class_schedule_id' => 'Buổi học được chọn không thuộc lớp này.',
            ]);
        }

        $enrollments = $this->activeEnrollmentMap($ownedClass);

        foreach ($data['attendance'] as $studentId => $row) {
            $enrollment = $enrollments->get((int) $studentId);

            if (! $enrollment) {
                continue;
            }

            AttendanceRecord::updateOrCreate(
                [
                    'class_room_id' => $ownedClass->id,
                    'class_schedule_id' => $schedule->id,
                    'student_id' => $enrollment->user_id,
                    'attendance_date' => $data['attendance_date'],
                ],
                [
                    'course_id' => $enrollment->course_id,
                    'enrollment_id' => $enrollment->id,
                    'teacher_id' => $teacher->id,
                    'status' => $row['status'],
                    'note' => $row['note'] ?? null,
                    'recorded_at' => now(),
                ]
            );
        }
    }

    public function storeGrades(User $teacher, ClassRoom $classRoom, array $data): void
    {
        $ownedClass = $this->resolveOwnedClass($teacher, $classRoom);
        $enrollments = $this->activeEnrollmentMap($ownedClass);
        $gradeColumns = $this->gradeColumns($ownedClass);

        foreach ($enrollments as $enrollment) {
            $studentScores = $data['scores'][(string) $enrollment->user_id]
                ?? $data['scores'][$enrollment->user_id]
                ?? [];

            foreach ($gradeColumns as $column) {
                $index = (string) $column['index'];
                $score = $this->normalizeScore($studentScores[$index] ?? null);

                $payload = [
                    'enrollment_id' => $enrollment->id,
                    'teacher_id' => $teacher->id,
                    'score' => $score,
                    'grade' => $this->scoreToGrade($score),
                ];

                if ($this->supportsGradeWeightSnapshots()) {
                    $payload['weight'] = $this->normalizeWeight($column['weight'] ?? 1);
                }

                Grade::updateOrCreate(
                    [
                        'class_room_id' => $ownedClass->id,
                        'student_id' => $enrollment->user_id,
                        'test_name' => $column['name'],
                    ],
                    $payload
                );
            }
        }
    }

    public function gradeColumnsForClass(ClassRoom $classRoom): Collection
    {
        return $this->gradeColumns($classRoom);
    }

    public function gradeWeightsSupported(): bool
    {
        return $this->supportsGradeWeights();
    }

    public function updateGradeWeights(ClassRoom $classRoom, array $weights): void
    {
        if (! $this->supportsGradeWeights()) {
            throw ValidationException::withMessages([
                'weights' => 'Cơ sở dữ liệu chưa sẵn sàng để lưu hệ số.',
            ]);
        }

        $gradeWeights = $this->normalizedGradeWeights($classRoom, $weights);
        $classRoom->forceFill([
            'grade_weights' => $gradeWeights,
        ])->save();

        $columns = $this->gradeColumns($classRoom->fresh());

        if ($this->supportsGradeWeightSnapshots()) {
            foreach ($columns as $column) {
                Grade::query()
                    ->where('class_room_id', $classRoom->id)
                    ->where('test_name', $column['name'])
                    ->update([
                        'weight' => $column['weight'],
                    ]);
            }
        }
    }

    public function storeEvaluation(User $teacher, ClassRoom $classRoom, array $data): void
    {
        $ownedClass = $this->resolveOwnedClass($teacher, $classRoom);

        if (! $this->canEvaluateStudent($ownedClass, (int) $data['student_id'])) {
            throw ValidationException::withMessages([
                'student_id' => 'Học viên được chọn không thuộc lớp này.',
            ]);
        }

        TeacherEvaluation::updateOrCreate(
            [
                'class_room_id' => $ownedClass->id,
                'student_id' => $data['student_id'],
            ],
            [
                'teacher_id' => $teacher->id,
                'rating' => $data['rating'],
                'comments' => $data['comments'] ?? null,
            ]
        );
    }

    protected function canEvaluateStudent(ClassRoom $classRoom, int $studentId): bool
    {
        if ($studentId <= 0) {
            return false;
        }

        $hasEligibleEnrollment = Enrollment::query()
            ->where('lop_hoc_id', $classRoom->id)
            ->where('user_id', $studentId)
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->exists();

        if ($hasEligibleEnrollment) {
            return true;
        }

        return TeacherEvaluation::query()
            ->where('class_room_id', $classRoom->id)
            ->where('student_id', $studentId)
            ->exists();
    }

    protected function resolveOwnedClass(User $teacher, ClassRoom $classRoom, array $with = []): ClassRoom
    {
        $query = ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->with(array_merge(['subject.category', 'room', 'teacher', 'schedules'], $with));

        $ownedClass = $query->find($classRoom->id);

        if (! $ownedClass) {
            throw (new ModelNotFoundException())->setModel(ClassRoom::class, [$classRoom->id]);
        }

        return $ownedClass;
    }

    protected function activeEnrollmentMap(ClassRoom $classRoom): Collection
    {
        return Enrollment::query()
            ->where('lop_hoc_id', $classRoom->id)
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->with('user')
            ->get()
            ->keyBy('user_id');
    }

    protected function scoreToGrade(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }

        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }

    protected function normalizeScore(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    protected function gradeColumns(ClassRoom $classRoom): Collection
    {
        $configuredCount = $classRoom->subject?->resolvedTestCount() ?? \App\Models\Subject::DEFAULT_TEST_COUNT;
        $classWeights = collect($classRoom->grade_weights ?? [])
            ->mapWithKeys(fn ($weight, $index) => [(string) $index => $this->normalizeWeight($weight)]);
        $selectColumns = ['test_name'];

        if ($this->supportsGradeWeightSnapshots()) {
            $selectColumns[] = 'weight';
        }

        $existingGrades = Grade::query()
            ->where('class_room_id', $classRoom->id)
            ->whereNotNull('test_name')
            ->where('test_name', '!=', '')
            ->get($selectColumns);

        $existingTestNames = $existingGrades
            ->pluck('test_name')
            ->unique()
            ->values();

        $weightMap = $existingGrades
            ->groupBy('test_name')
            ->map(fn (Collection $grades) => $this->normalizeWeight($grades->first()->weight ?? 1));

        return collect(range(1, $configuredCount))
            ->map(function (int $index) use ($existingTestNames, $weightMap, $classWeights) {
                $name = $existingTestNames->get($index - 1) ?? ('Kiểm tra ' . $index);
                $weight = $classWeights->get((string) $index);

                if ($weight === null && $this->supportsGradeWeightSnapshots()) {
                    $weight = $weightMap->get($name);
                }

                return [
                    'index' => $index,
                    'name' => $name,
                    'weight' => $this->normalizeWeight($weight ?? 1),
                ];
            });
    }

    protected function averageScoreMap(Collection $enrollments, Collection $gradeColumns, Collection $gradeMap): Collection
    {
        return $enrollments->mapWithKeys(function (Enrollment $enrollment) use ($gradeColumns, $gradeMap) {
            $numerator = 0.0;
            $denominator = 0;
            $hasScore = false;

            foreach ($gradeColumns as $column) {
                $weight = $this->normalizeWeight($column['weight'] ?? 1);
                $grade = $gradeMap->get($enrollment->user_id . '|' . $column['name']);
                $score = $grade && $grade->score !== null
                    ? (float) $grade->score
                    : null;

                $denominator += $weight;

                if ($score !== null) {
                    $numerator += $score * $weight;
                    $hasScore = true;
                }
            }

            $average = $hasScore && $denominator > 0
                ? round($numerator / $denominator, 2)
                : null;

            return [$enrollment->user_id => $average];
        });
    }

    protected function normalizeWeight(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 1;
        }

        return max(1, (int) $value);
    }

    protected function normalizedGradeWeights(ClassRoom $classRoom, array $weights): array
    {
        $configuredCount = $classRoom->subject?->resolvedTestCount() ?? \App\Models\Subject::DEFAULT_TEST_COUNT;
        $normalized = [];

        for ($index = 1; $index <= $configuredCount; $index++) {
            $normalized[$index] = $this->normalizeWeight($weights[$index] ?? $weights[(string) $index] ?? 1);
        }

        return $normalized;
    }

    protected function supportsGradeWeights(): bool
    {
        return Schema::hasColumn((new ClassRoom())->getTable(), 'grade_weights');
    }

    protected function supportsGradeWeightSnapshots(): bool
    {
        return Schema::hasColumn((new Grade())->getTable(), 'weight');
    }
}
