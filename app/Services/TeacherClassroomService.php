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
            ->with(['user', 'course'])
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->user !== null)
            ->sortBy(fn (Enrollment $enrollment) => mb_strtolower($enrollment->user->name))
            ->values();

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

        $testNames = Grade::query()
            ->where('class_room_id', $ownedClass->id)
            ->whereNotNull('test_name')
            ->distinct()
            ->orderBy('test_name')
            ->pluck('test_name');

        $selectedTestName = trim((string) ($filters['test_name'] ?? ''));

        if ($selectedTestName === '') {
            $selectedTestName = $testNames->first() ?? 'Kiểm tra 1';
        }

        $gradeMap = Grade::query()
            ->where('class_room_id', $ownedClass->id)
            ->where('test_name', $selectedTestName)
            ->get()
            ->keyBy('student_id');

        $selectedStudentId = (int) ($filters['student_id'] ?? ($enrollments->first()?->user_id ?? 0));

        if ($selectedStudentId !== 0 && ! $enrollments->contains(fn (Enrollment $enrollment) => (int) $enrollment->user_id === $selectedStudentId)) {
            $selectedStudentId = (int) ($enrollments->first()?->user_id ?? 0);
        }

        $currentEvaluation = $selectedStudentId === 0
            ? null
            : TeacherEvaluation::query()
                ->where('class_room_id', $ownedClass->id)
                ->where('student_id', $selectedStudentId)
                ->first();

        $evaluationHistory = TeacherEvaluation::query()
            ->where('class_room_id', $ownedClass->id)
            ->with('student')
            ->latest()
            ->take(8)
            ->get();

        return [
            'classRoom' => $ownedClass,
            'enrollments' => $enrollments,
            'selectedSchedule' => $selectedSchedule,
            'selectedDate' => $selectedDate,
            'attendanceMap' => $attendanceMap,
            'testNames' => $testNames,
            'selectedTestName' => $selectedTestName,
            'gradeMap' => $gradeMap,
            'selectedStudentId' => $selectedStudentId,
            'currentEvaluation' => $currentEvaluation,
            'evaluationHistory' => $evaluationHistory,
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

        foreach ($data['grades'] as $studentId => $row) {
            $enrollment = $enrollments->get((int) $studentId);

            if (! $enrollment) {
                continue;
            }

            $score = array_key_exists('score', $row) && $row['score'] !== null && $row['score'] !== ''
                ? (float) $row['score']
                : null;

            Grade::updateOrCreate(
                [
                    'class_room_id' => $ownedClass->id,
                    'student_id' => $enrollment->user_id,
                    'test_name' => $data['test_name'],
                ],
                [
                    'enrollment_id' => $enrollment->id,
                    'teacher_id' => $teacher->id,
                    'score' => $score,
                    'grade' => $this->scoreToGrade($score),
                    'feedback' => $row['feedback'] ?? null,
                ]
            );
        }
    }

    public function storeEvaluation(User $teacher, ClassRoom $classRoom, array $data): void
    {
        $ownedClass = $this->resolveOwnedClass($teacher, $classRoom);

        if (! $this->activeEnrollmentMap($ownedClass)->has((int) $data['student_id'])) {
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
}
