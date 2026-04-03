<?php

namespace App\Services;

use App\Exceptions\EnrollmentOperationException;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentService
{
    public function paginateAvailableSubjects(User $student): LengthAwarePaginator
    {
        return Subject::query()
            ->with([
                'category',
                'classRooms' => fn (Builder $query) => $query
                    ->with(['room', 'teacher', 'schedules'])
                    ->withCount('enrollments')
                    ->where('status', ClassRoom::STATUS_OPEN),
                'enrollments' => fn (Builder $query) => $query
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

        return [
            'subject' => $subject,
            'classes' => $this->openClassesForSubject($subject),
            'existingEnrollment' => $this->findSubjectEnrollment($student->id, $subject->id, [
                'classRoom.room',
                'classRoom.teacher',
                'classRoom.schedules',
                'assignedTeacher',
                'course',
            ]),
        ];
    }

    public function getCustomRequestContext(User $student, Subject $subject): array
    {
        $this->ensureSubjectIsAvailable($subject);

        $subject->load([
            'category',
            'classRooms' => fn (Builder $query) => $query
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
                throw new EnrollmentOperationException('Lop hoc khong thuoc khoa hoc nay.');
            }

            if ($classRoom->status !== ClassRoom::STATUS_OPEN) {
                throw new EnrollmentOperationException('Lop nay hien khong con mo dang ky.');
            }

            if ($classRoom->isFull()) {
                $this->syncClassStatus($classRoom);

                throw new EnrollmentOperationException('Lop nay da du cho. Vui long chon lop khac hoac gui yeu cau lich hoc rieng.');
            }

            $existingEnrollment = $this->findSubjectEnrollment($student->id, $subject->id);

            if ($existingEnrollment && $existingEnrollment->hasCourseAccess() && (int) $existingEnrollment->lop_hoc_id === (int) $classRoom->id) {
                return 'Ban da ghi danh vao lop co dinh nay roi.';
            }

            if ($existingEnrollment && $existingEnrollment->hasCourseAccess()) {
                throw new EnrollmentOperationException('Ban da co lop cho khoa hoc nay. Neu muon doi lop, vui long lien he admin.');
            }

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

            if ($existingEnrollment) {
                $existingEnrollment->fill($payload)->save();
            } else {
                Enrollment::create(array_merge($payload, [
                    'user_id' => $student->id,
                ]));
            }

            $this->syncClassStatus($classRoom->fresh(['room'])->loadCount('enrollments'));

            return 'Da ghi danh vao lop co dinh thanh cong.';
        });
    }

    public function submitCustomScheduleRequest(User $student, Subject $subject, array $data): string
    {
        $this->ensureSubjectIsAvailable($subject);

        return DB::transaction(function () use ($student, $subject, $data) {
            $existingEnrollment = $this->findSubjectEnrollment($student->id, $subject->id);

            if ($existingEnrollment && $existingEnrollment->hasCourseAccess()) {
                throw new EnrollmentOperationException('Ban da duoc ghi danh hoac xep lop cho khoa hoc nay. Neu can doi lich, vui long lien he admin.');
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

                return 'Yeu cau dang ky cua ban da duoc cap nhat. Admin se xem lai va xep lop phu hop.';
            }

            Enrollment::create(array_merge($payload, [
                'user_id' => $student->id,
                'status' => Enrollment::STATUS_PENDING,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]));

            return 'Da gui yeu cau dang ky khoa hoc. Admin se xem lich mong muon va xep lop phu hop.';
        });
    }

    public function paginateStudentEnrollments(User $student): LengthAwarePaginator
    {
        return Enrollment::query()
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
    }

    protected function openClassesForSubject(Subject $subject)
    {
        return ClassRoom::query()
            ->with(['subject', 'room', 'teacher', 'schedules'])
            ->withCount('enrollments')
            ->where('subject_id', $subject->id)
            ->where('status', ClassRoom::STATUS_OPEN)
            ->get()
            ->filter(fn (ClassRoom $classRoom) => ! $classRoom->isFull())
            ->values();
    }

    protected function ensureSubjectIsAvailable(Subject $subject): void
    {
        if (! $subject->isOpenForEnrollment()) {
            throw new EnrollmentOperationException('Khoa hoc nay hien chua mo dang ky.');
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
}
