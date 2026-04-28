<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\ClassRoom;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\TeacherEvaluation;
use App\Models\User;
use Illuminate\Support\Collection;

class StudentClassService
{
    public function getStudentClasses(User $student): Collection
    {
        $enrollments = Enrollment::query()
            ->where('user_id', $student->id)
            ->whereIn('status', $this->accessibleStatuses())
            ->with([
                'subject.category',
                'course.subject.category',
                'assignedTeacher',
                'classRoom.subject.category',
                'classRoom.course.subject.category',
                'classRoom.room',
                'classRoom.teacher',
                'classRoom.schedules.room',
                'course.quizzes.questions.options',
                'classRoom.quizzes.questions.options',
                'grades.module',
                'grades.classRoom.room',
                'grades.teacher',
                'attendanceRecords.classSchedule.room',
                'attendanceRecords.teacher',
            ])
            ->orderByDesc('id')
            ->get();

        Enrollment::syncDisplayStatusesByClass($enrollments);

        return $enrollments;
    }

    public function getStudentClassDetail(User $student, Enrollment $enrollment): array
    {
        abort_unless((int) $enrollment->user_id === (int) $student->id, 403);

        $enrollment = Enrollment::query()
            ->with([
                'subject.category',
                'course.subject.category',
                'course.modules.lessons.quiz.questions',
                'assignedTeacher',
                'classRoom.subject.category',
                'classRoom.course.subject.category',
                'classRoom.room',
                'classRoom.teacher',
                'classRoom.schedules.room',
                'classRoom.enrollments.user',
                'grades.module',
                'grades.classRoom.room',
                'grades.teacher',
                'attendanceRecords.classSchedule.room',
                'attendanceRecords.teacher',
            ])
            ->findOrFail($enrollment->id);

        Enrollment::syncDisplayStatusesByClass(collect([$enrollment]));

        $grades = $this->getStudentGrades($enrollment);
        $attendanceRecords = $this->getStudentAttendance($enrollment);
        $quizzes = $this->getAvailableQuizzes($enrollment);
        $classmates = $this->getClassmates($enrollment);
        $evaluation = $this->getEvaluation($enrollment);

        return [
            'enrollment' => $enrollment,
            'classRoom' => $enrollment->classRoom,
            'course' => $enrollment->course,
            'subject' => $enrollment->subject,
            'classmates' => $classmates,
            'grades' => $grades,
            'attendanceRecords' => $attendanceRecords,
            'attendanceSummary' => $this->attendanceSummary($attendanceRecords),
            'quizzes' => $quizzes,
            'evaluation' => $evaluation,
            'evaluationOptions' => range(1, 5),
        ];
    }

    public function getClassmates(Enrollment $enrollment): Collection
    {
        $classRoom = $enrollment->classRoom;

        if (! $classRoom) {
            return collect();
        }

        $students = $classRoom->enrollments
            ->filter(fn (Enrollment $classEnrollment) => in_array($classEnrollment->normalizedStatus(), Enrollment::courseAccessStatuses(), true))
            ->map(fn (Enrollment $classEnrollment) => $classEnrollment->user)
            ->filter()
            ->unique(fn (User $student) => (int) $student->id)
            ->sortBy(fn (User $student) => mb_strtolower((string) $student->displayName()))
            ->values();

        return $students;
    }

    public function getStudentGrades(Enrollment $enrollment): Collection
    {
        return Grade::query()
            ->where(function ($query) use ($enrollment): void {
                $query->where('enrollment_id', $enrollment->id);

                if ($enrollment->lop_hoc_id !== null) {
                    $query->orWhere(function ($builder) use ($enrollment): void {
                        $builder->where('class_room_id', $enrollment->lop_hoc_id)
                            ->where('student_id', $enrollment->user_id);
                    });
                }
            })
            ->with(['module', 'classRoom.room', 'teacher', 'enrollment'])
            ->orderByDesc('id')
            ->get();
    }

    public function getStudentAttendance(Enrollment $enrollment): Collection
    {
        return AttendanceRecord::query()
            ->where(function ($query) use ($enrollment): void {
                $query->where('enrollment_id', $enrollment->id);

                if ($enrollment->lop_hoc_id !== null) {
                    $query->orWhere(function ($builder) use ($enrollment): void {
                        $builder->where('class_room_id', $enrollment->lop_hoc_id)
                            ->where('student_id', $enrollment->user_id);
                    });
                }
            })
            ->with(['classSchedule.room', 'classRoom.room', 'teacher', 'course'])
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->get();
    }

    public function getAvailableQuizzes(Enrollment $enrollment): Collection
    {
        $course = $enrollment->course;
        $classRoom = $enrollment->classRoom;

        if (! $course) {
            return collect();
        }

        $course->loadMissing(['modules.lessons.quiz.questions.options']);
        $classRoom?->loadMissing(['quizzes.questions.options']);

        $quizzes = Quiz::query()
            ->published()
            ->where(function ($query) use ($course, $classRoom, $enrollment): void {
                $query->where('course_id', $course->id)
                    ->orWhereHas('lesson.module', fn ($lessonQuery) => $lessonQuery->where('course_id', $course->id));

                if ($classRoom) {
                    $query->orWhere('lop_hoc_id', $classRoom->id);
                }
            })
            ->with([
                'teacher',
                'course.subject',
                'subject',
                'classRoom.course.subject',
                'classRoom.teacher',
                'lesson.module.course.subject',
                'questions.options',
            ])
            ->get()
            ->unique(fn (Quiz $quiz) => (int) $quiz->id)
            ->values();

        return $quizzes->map(function ($quiz) use ($enrollment, $course) {
            $answers = QuizAnswer::query()
                ->where('user_id', $enrollment->user_id)
                ->where('quiz_id', $quiz->id)
                ->with('question')
                ->orderByDesc('attempt')
                ->orderByDesc('id')
                ->get();

            $latestAttempt = (int) ($answers->max('attempt') ?? 0);
            $latestAttemptAnswers = $latestAttempt > 0
                ? $answers->where('attempt', $latestAttempt)
                : collect();

            $totalPoints = (int) $quiz->questions->sum(fn ($question) => (int) $question->points);
            $earnedPoints = (int) $latestAttemptAnswers->sum(fn ($answer) => $answer->is_correct ? (int) ($answer->question?->points ?? 0) : 0);
            $latestScore = $latestAttempt > 0 && $totalPoints > 0
                ? round(($earnedPoints / $totalPoints) * 100, 2)
                : null;
            $maxAttempts = (int) ($quiz->max_attempts ?: 0);
            $remainingAttempts = $maxAttempts > 0
                ? max(0, $maxAttempts - $latestAttempt)
                : null;

            $quiz->setAttribute('attempt_count', $latestAttempt);
            $quiz->setAttribute('latest_score', $latestScore);
            $quiz->setAttribute('passed', $latestScore !== null && $latestScore >= (int) ($quiz->passing_score ?: 70));
            $quiz->setAttribute('student_quiz_url', route('courses.quiz.show', [$course->id, $quiz->id]));
            $quiz->setAttribute('remaining_attempts', $remainingAttempts);
            $quiz->setAttribute('can_attempt', $maxAttempts === 0 || $remainingAttempts > 0);
            $quiz->setAttribute('target_label', $quiz->targetLabel());

            return $quiz;
        })->sortBy(fn ($quiz) => mb_strtolower((string) $quiz->title))->values();
    }

    public function getEvaluation(Enrollment $enrollment): ?TeacherEvaluation
    {
        if ($enrollment->lop_hoc_id === null) {
            return null;
        }

        return TeacherEvaluation::query()
            ->where('class_room_id', $enrollment->lop_hoc_id)
            ->where('student_id', $enrollment->user_id)
            ->first();
    }

    public function saveEvaluation(User $student, Enrollment $enrollment, array $data): TeacherEvaluation
    {
        abort_unless((int) $enrollment->user_id === (int) $student->id, 403);

        if ($enrollment->lop_hoc_id === null) {
            throw new \InvalidArgumentException('Không thể đánh giá khi lớp chưa được xếp.');
        }

        return TeacherEvaluation::updateOrCreate(
            [
                'class_room_id' => $enrollment->lop_hoc_id,
                'student_id' => $student->id,
            ],
            [
                'teacher_id' => $enrollment->classRoom?->teacher_id ?? $enrollment->assigned_teacher_id,
                'rating' => $data['rating'],
                'comments' => $data['comments'] ?? null,
            ]
        );
    }

    public function accessibleStatuses(): array
    {
        return [
            Enrollment::STATUS_APPROVED,
            Enrollment::STATUS_ENROLLED,
            Enrollment::STATUS_SCHEDULED,
            Enrollment::STATUS_ACTIVE,
            Enrollment::STATUS_COMPLETED,
            Enrollment::LEGACY_STATUS_CONFIRMED,
        ];
    }

    protected function attendanceSummary(Collection $attendanceRecords): array
    {
        $total = $attendanceRecords->count();
        $present = $attendanceRecords->where('status', AttendanceRecord::STATUS_PRESENT)->count();
        $absent = $attendanceRecords->where('status', AttendanceRecord::STATUS_ABSENT)->count();
        $late = $attendanceRecords->where('status', AttendanceRecord::STATUS_LATE)->count();
        $excused = $attendanceRecords->where('status', AttendanceRecord::STATUS_EXCUSED)->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'present_rate' => $total > 0 ? (int) round((($present + $late + $excused) / $total) * 100) : 0,
            'recent' => $attendanceRecords->take(5)->values(),
        ];
    }
}
