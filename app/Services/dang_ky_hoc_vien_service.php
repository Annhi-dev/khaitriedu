<?php

namespace App\Services;

use App\Exceptions\EnrollmentOperationException;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentService
{
    public function paginateAvailableSubjects(User $student): LengthAwarePaginator
    {
        return Subject::query()
            ->with([
                'category',
                'classRooms' => fn ($query) => $query
                    ->with(['room', 'teacher', 'schedules'])
                    ->withCount('enrollments')
                    ->where('status', ClassRoom::STATUS_OPEN),
                'enrollments' => fn ($query) => $query
                    ->where('user_id', $student->id)
                    ->latest('id'),
            ])
            ->publiclyAvailable()
            ->orderBy('name')
            ->paginate(12);
    }

    public function getFixedClassSelectionContext(User $student, Subject $subject): array
    {
        $this->ensureSubjectIsAvailable($subject);
        $subject->loadMissing('category');

        $classes = $this->openClassesForSubject($subject);
        $scheduleConflictMap = $this->buildScheduleConflictMap($student, $classes);

        return [
            'subject' => $subject,
            'classes' => $classes,
            'existingEnrollment' => $this->findSubjectEnrollment($student->id, $subject->id, [
                'classRoom.room',
                'classRoom.teacher',
                'classRoom.schedules',
                'assignedTeacher',
                'course',
            ]),
            'scheduleConflictMap' => $scheduleConflictMap,
            'scheduleConflictCount' => count($scheduleConflictMap),
        ];
    }

    public function getCustomRequestContext(User $student, Subject $subject): array
    {
        $this->ensureSubjectIsAvailable($subject);

        $subject->load([
            'category',
            'classRooms' => fn ($query) => $query
                ->with(['room', 'teacher', 'schedules'])
                ->withCount('enrollments')
                ->where('status', ClassRoom::STATUS_OPEN),
        ]);

        return [
            'subject' => $subject,
            'existingEnrollment' => $this->findSubjectEnrollment($student->id, $subject->id, [
                'classRoom.room',
                'classRoom.teacher',
                'classRoom.schedules',
                'assignedTeacher',
                'course',
            ]),
            'openClasses' => $subject->classRooms
                ->filter(fn (ClassRoom $classRoom) => ! $classRoom->isFull())
                ->values(),
            'dayOptions' => ClassSchedule::$dayOptions,
        ];
    }

    public function submitFixedClassEnrollment(User $student, Subject $subject, int $classRoomId): string
    {
        $this->ensureSubjectIsAvailable($subject);

        return DB::transaction(function () use ($student, $subject, $classRoomId) {
            $classRoom = ClassRoom::query()
                ->with(['room', 'teacher', 'schedules'])
                ->withCount('enrollments')
                ->lockForUpdate()
                ->findOrFail($classRoomId);

            if ((int) $classRoom->subject_id !== (int) $subject->id) {
                throw new EnrollmentOperationException('Lớp học không thuộc khóa học này.');
            }

            $blockReason = $classRoom->enrollmentBlockReason();

            if ($classRoom->isFull()) {
                $this->syncClassStatus($classRoom);
            }

            if ($blockReason) {
                throw new EnrollmentOperationException($blockReason);
            }

            $existingEnrollment = $this->findSubjectEnrollment($student->id, $subject->id);
            $existingClassEnrollment = Enrollment::query()
                ->where('user_id', $student->id)
                ->where('lop_hoc_id', $classRoom->id)
                ->latest('id')
                ->first();

            if ($existingClassEnrollment && $existingClassEnrollment->hasCourseAccess()) {
                return 'Bạn đã ghi danh vào lớp cố định này rồi.';
            }

            if ($existingEnrollment && $existingEnrollment->hasCourseAccess() && (int) $existingEnrollment->lop_hoc_id !== (int) $classRoom->id) {
                throw new EnrollmentOperationException('Bạn đã có lớp cho khóa học này. Nếu muốn đổi lớp, vui lòng liên hệ admin.');
            }

            $targetEnrollment = $existingClassEnrollment ?? $existingEnrollment;

            $this->ensureStudentHasNoScheduleConflict($student, $classRoom, $targetEnrollment?->id);

            $payload = [
                'subject_id' => $subject->id,
                'course_id' => $classRoom->course_id,
                'lop_hoc_id' => $classRoom->id,
                'preferred_schedule' => null,
                'assigned_teacher_id' => $classRoom->teacher_id,
                'status' => Enrollment::STATUS_ENROLLED,
                'note' => null,
                'schedule' => $classRoom->schedules->isNotEmpty() ? $classRoom->scheduleSummary() : null,
                'start_time' => null,
                'end_time' => null,
                'preferred_days' => null,
                'is_submitted' => true,
                'submitted_at' => now(),
                'reviewed_by' => null,
                'reviewed_at' => null,
            ];

            if ($targetEnrollment) {
                $targetEnrollment->fill($payload)->save();
            } else {
                Enrollment::create(array_merge($payload, [
                    'user_id' => $student->id,
                ]));
            }

            $this->syncClassStatus($classRoom->fresh(['room'])->loadCount('enrollments'));

            return 'Đã ghi danh vào lớp cố định thành công.';
        });
    }

    public function submitCustomScheduleRequest(User $student, Subject $subject, array $data): string
    {
        $this->ensureSubjectIsAvailable($subject);

        return DB::transaction(function () use ($student, $subject, $data) {
            $existingEnrollment = $this->findSubjectEnrollment($student->id, $subject->id);

            if ($existingEnrollment && $existingEnrollment->hasCourseAccess()) {
                throw new EnrollmentOperationException('Bạn đã được ghi danh hoặc xếp lớp cho khóa học này. Nếu cần dời buổi, vui lòng liên hệ admin.');
            }

            $payload = [
                'subject_id' => $subject->id,
                'course_id' => null,
                'lop_hoc_id' => null,
                'preferred_schedule' => $data['preferred_schedule'] ?? null,
                'assigned_teacher_id' => null,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'preferred_days' => $data['preferred_days'],
                'note' => null,
                'schedule' => null,
                'is_submitted' => true,
                'submitted_at' => now(),
            ];

            if ($existingEnrollment) {
                if (in_array($existingEnrollment->normalizedStatus(), [Enrollment::STATUS_REJECTED, Enrollment::STATUS_PENDING], true)) {
                    $payload['status'] = Enrollment::STATUS_PENDING;
                    $payload['reviewed_by'] = null;
                    $payload['reviewed_at'] = null;
                } elseif ($existingEnrollment->normalizedStatus() === Enrollment::STATUS_APPROVED) {
                    $payload['status'] = Enrollment::STATUS_APPROVED;
                } else {
                    $payload['status'] = $existingEnrollment->status;
                }

                $existingEnrollment->update($payload);

                return 'Yêu cầu đăng ký của bạn đã được cập nhật. Admin sẽ xem lại và xếp lớp phù hợp.';
            }

            Enrollment::create(array_merge($payload, [
                'user_id' => $student->id,
                'status' => Enrollment::STATUS_PENDING,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]));

            return 'Đã gửi yêu cầu đăng ký khóa học. Admin sẽ xem lịch mong muốn và xếp lớp phù hợp.';
        });
    }

    public function paginateStudentEnrollments(User $student): LengthAwarePaginator
    {
        $paginator = Enrollment::query()
            ->with([
                'subject.category',
                'course',
                'assignedTeacher',
                'classRoom.schedules',
                'classRoom.room',
                'classRoom.teacher',
            ])
            ->where('user_id', $student->id)
            ->latest()
            ->paginate(10);

        $collection = $paginator->getCollection();
        Enrollment::syncDisplayStatusesByClass($collection);
        $paginator->setCollection($collection);

        return $paginator;
    }

    protected function openClassesForSubject(Subject $subject)
    {
        return ClassRoom::query()
            ->with(['subject', 'course', 'room', 'teacher', 'schedules'])
            ->withCount('enrollments')
            ->where('subject_id', $subject->id)
            ->where('status', ClassRoom::STATUS_OPEN)
            ->get()
            ->sortBy(fn (ClassRoom $classRoom) => $classRoom->enrollmentAvailabilitySortKey())
            ->values();
    }

    protected function ensureSubjectIsAvailable(Subject $subject): void
    {
        if (! $subject->isOpenForEnrollment()) {
            throw new EnrollmentOperationException('Khóa học này hiện chưa mở đăng ký.');
        }
    }

    protected function findSubjectEnrollment(int $userId, int $subjectId, array $with = []): ?Enrollment
    {
        return Enrollment::query()
            ->with($with)
            ->forUserSubject($userId, $subjectId)
            ->latest('id')
            ->first();
    }

    protected function syncClassStatus(ClassRoom $classRoom): void
    {
        if (! $classRoom->room) {
            return;
        }

        $classRoom->update([
            'status' => $classRoom->isFull() ? ClassRoom::STATUS_FULL : ClassRoom::STATUS_OPEN,
        ]);
    }

    protected function ensureStudentHasNoScheduleConflict(User $student, ClassRoom $targetClassRoom, ?int $ignoreEnrollmentId = null): void
    {
        $conflict = Enrollment::query()
            ->with([
                'classRoom.course',
                'classRoom.schedules',
                'course.classRooms.schedules',
            ])
            ->where('user_id', $student->id)
            ->when($ignoreEnrollmentId, fn ($query) => $query->whereKeyNot($ignoreEnrollmentId))
            ->whereIn('status', [
                Enrollment::LEGACY_STATUS_CONFIRMED,
                Enrollment::STATUS_ENROLLED,
                Enrollment::STATUS_SCHEDULED,
                Enrollment::STATUS_ACTIVE,
            ])
            ->get()
            ->first(function (Enrollment $enrollment) use ($targetClassRoom): bool {
                $existingClassRoom = $enrollment->currentClassRoom();

                return $existingClassRoom !== null
                    && (int) $existingClassRoom->id !== (int) $targetClassRoom->id
                    && $existingClassRoom->conflictsWith($targetClassRoom);
            });

        if ($conflict) {
            throw new EnrollmentOperationException('Học viên đã có lớp khác trùng lịch trong cùng khung giờ. Vui lòng chọn lớp khác.');
        }
    }

    protected function buildScheduleConflictMap(User $student, Collection $classes): array
    {
        $activeEnrollments = Enrollment::query()
            ->with([
                'classRoom.course',
                'classRoom.schedules',
            ])
            ->where('user_id', $student->id)
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->whereNotNull('lop_hoc_id')
            ->get();

        $conflictMap = [];

        foreach ($classes as $classRoom) {
            foreach ($activeEnrollments as $enrollment) {
                $existingClassRoom = $enrollment->currentClassRoom();

                if (! $existingClassRoom || (int) $existingClassRoom->id === (int) $classRoom->id) {
                    continue;
                }

                $conflict = $existingClassRoom->firstScheduleConflictWith($classRoom);

                if (! $conflict) {
                    continue;
                }

                $conflictMap[$classRoom->id] = [
                    'existing_enrollment_id' => $enrollment->id,
                    'existing_class_room_id' => $existingClassRoom->id,
                    'existing_class_name' => $existingClassRoom->displayName(),
                    'existing_day_label' => $conflict['day_label'] ?? 'Chưa rõ ngày',
                    'existing_time_label' => $conflict['existing_time_label'] ?? '',
                    'candidate_time_label' => $conflict['candidate_time_label'] ?? '',
                    'message' => sprintf(
                        'Trùng với lớp %s, %s, %s.',
                        $existingClassRoom->displayName(),
                        $conflict['day_label'] ?? 'chưa rõ ngày',
                        $conflict['existing_time_label'] ?? ''
                    ),
                ];

                break;
            }
        }

        return $conflictMap;
    }
}
