<?php

namespace App\Services;

use App\Exceptions\EnrollmentOperationException;
use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\GhiDanh;
use App\Models\ThongBao;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentService
{
    public function paginateAvailableSubjects(NguoiDung $student): LengthAwarePaginator
    {
        return MonHoc::query()
            ->with([
                'category',
                'classRooms' => fn ($query) => $query
                    ->with(['room', 'teacher', 'schedules'])
                    ->withCount('enrollments')
                    ->where('status', LopHoc::STATUS_OPEN),
                'enrollments' => fn ($query) => $query
                    ->where('user_id', $student->id)
                    ->latest('id'),
            ])
            ->publiclyAvailable()
            ->orderBy('name')
            ->paginate(12);
    }

    public function getFixedClassSelectionContext(NguoiDung $student, MonHoc $subject): array
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

    public function getCustomRequestContext(NguoiDung $student, MonHoc $subject): array
    {
        $this->ensureSubjectIsAvailable($subject);

        $subject->load([
            'category',
            'classRooms' => fn ($query) => $query
                ->with(['room', 'teacher', 'schedules'])
                ->withCount('enrollments')
                ->where('status', LopHoc::STATUS_OPEN),
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
                ->filter(fn (LopHoc $classRoom) => ! $classRoom->isFull())
                ->values(),
            'dayOptions' => LichHoc::$dayOptions,
        ];
    }

    public function submitFixedClassEnrollment(NguoiDung $student, MonHoc $subject, int $classRoomId): string
    {
        $this->ensureSubjectIsAvailable($subject);

        return DB::transaction(function () use ($student, $subject, $classRoomId) {
            $classRoom = LopHoc::query()
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
            $existingClassEnrollment = GhiDanh::query()
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
                'status' => GhiDanh::STATUS_ENROLLED,
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
                GhiDanh::create(array_merge($payload, [
                    'user_id' => $student->id,
                ]));
            }

            $savedEnrollment = $targetEnrollment?->fresh() ?? GhiDanh::query()
                ->where('user_id', $student->id)
                ->where('subject_id', $subject->id)
                ->latest('id')
                ->first();

            $this->syncClassStatus($classRoom->fresh(['room'])->loadCount('enrollments'));
            $this->notifyAdmins(
                'Học viên đăng ký lớp cố định',
                sprintf(
                    '%s vừa đăng ký lớp cố định %s.',
                    $student->displayName(),
                    $classRoom->displayName()
                ),
                route('admin.enrollments.fixed.show', $savedEnrollment)
            );

            return 'Đã ghi danh vào lớp cố định thành công.';
        });
    }

    public function submitCustomScheduleRequest(NguoiDung $student, MonHoc $subject, array $data): string
    {
        $this->ensureSubjectIsAvailable($subject);

        return DB::transaction(function () use ($student, $subject, $data) {
            $existingEnrollment = $this->findSubjectEnrollment($student->id, $subject->id);

            if ($existingEnrollment && $existingEnrollment->hasCourseAccess()) {
                throw new EnrollmentOperationException('Bạn đã được ghi danh hoặc xếp lớp cho khóa học này. Nếu cần dời buổi, vui lòng liên hệ admin.');
            }

            $this->ensureStudentHasNoRequestedScheduleConflict(
                $student->id,
                $data['preferred_days'],
                (string) $data['start_time'],
                (string) $data['end_time'],
                $existingEnrollment?->id
            );

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
                if (in_array($existingEnrollment->normalizedStatus(), [GhiDanh::STATUS_REJECTED, GhiDanh::STATUS_PENDING], true)) {
                    $payload['status'] = GhiDanh::STATUS_PENDING;
                    $payload['reviewed_by'] = null;
                    $payload['reviewed_at'] = null;
                } elseif ($existingEnrollment->normalizedStatus() === GhiDanh::STATUS_APPROVED) {
                    $payload['status'] = GhiDanh::STATUS_APPROVED;
                } else {
                    $payload['status'] = $existingEnrollment->status;
                }

                $existingEnrollment->update($payload);
                $savedEnrollment = $existingEnrollment->fresh();
                $this->notifyAdmins(
                    'Học viên gửi yêu cầu lịch học',
                    sprintf(
                        '%s vừa cập nhật yêu cầu lịch học riêng cho môn %s.',
                        $student->displayName(),
                        $subject->name
                    ),
                    route('admin.enrollments.custom.show', $savedEnrollment)
                );

                return 'Yêu cầu đăng ký của bạn đã được cập nhật. Admin sẽ xem lại và xếp lớp phù hợp.';
            }

            $savedEnrollment = GhiDanh::create(array_merge($payload, [
                'user_id' => $student->id,
                'status' => GhiDanh::STATUS_PENDING,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]));

            $this->notifyAdmins(
                'Học viên gửi yêu cầu lịch học',
                sprintf(
                    '%s vừa gửi yêu cầu lịch học riêng cho môn %s.',
                    $student->displayName(),
                    $subject->name
                ),
                route('admin.enrollments.custom.show', $savedEnrollment)
            );

            return 'Đã gửi yêu cầu đăng ký khóa học. Admin sẽ xem lịch mong muốn và xếp lớp phù hợp.';
        });
    }

    public function paginateStudentEnrollments(NguoiDung $student): LengthAwarePaginator
    {
        $paginator = GhiDanh::query()
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
        GhiDanh::syncDisplayStatusesByClass($collection);
        $paginator->setCollection($collection);

        return $paginator;
    }

    protected function openClassesForSubject(MonHoc $subject)
    {
        return LopHoc::query()
            ->with(['subject', 'course', 'room', 'teacher', 'schedules'])
            ->withCount('enrollments')
            ->where('subject_id', $subject->id)
            ->where('status', LopHoc::STATUS_OPEN)
            ->get()
            ->sortBy(fn (LopHoc $classRoom) => $classRoom->enrollmentAvailabilitySortKey())
            ->values();
    }

    protected function ensureSubjectIsAvailable(MonHoc $subject): void
    {
        if (! $subject->isOpenForEnrollment()) {
            throw new EnrollmentOperationException('Khóa học này hiện chưa mở đăng ký.');
        }
    }

    protected function findSubjectEnrollment(int $userId, int $subjectId, array $with = []): ?GhiDanh
    {
        return GhiDanh::query()
            ->with($with)
            ->forUserSubject($userId, $subjectId)
            ->latest('id')
            ->first();
    }

    protected function syncClassStatus(LopHoc $classRoom): void
    {
        if (! $classRoom->room) {
            return;
        }

        $classRoom->update([
            'status' => $classRoom->isFull() ? LopHoc::STATUS_FULL : LopHoc::STATUS_OPEN,
        ]);
    }

    protected function notifyAdmins(string $title, string $message, string $link): void
    {
        NguoiDung::query()
            ->whereHas('role', fn ($query) => $query->where('name', NguoiDung::ROLE_ADMIN))
            ->where('status', NguoiDung::STATUS_ACTIVE)
            ->get()
            ->each(function (NguoiDung $admin) use ($title, $message, $link): void {
                ThongBao::create([
                    'user_id' => $admin->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => 'enrollment',
                    'link' => $link,
                ]);
            });
    }

    protected function ensureStudentHasNoScheduleConflict(NguoiDung $student, LopHoc $targetClassRoom, ?int $ignoreEnrollmentId = null): void
    {
        $conflict = GhiDanh::query()
            ->with([
                'classRoom.course',
                'classRoom.schedules',
                'course.classRooms.schedules',
            ])
            ->where('user_id', $student->id)
            ->when($ignoreEnrollmentId, fn ($query) => $query->whereKeyNot($ignoreEnrollmentId))
            ->whereIn('status', [
                GhiDanh::LEGACY_STATUS_CONFIRMED,
                GhiDanh::STATUS_ENROLLED,
                GhiDanh::STATUS_SCHEDULED,
                GhiDanh::STATUS_ACTIVE,
                GhiDanh::STATUS_COMPLETED,
            ])
            ->get()
            ->first(function (GhiDanh $enrollment) use ($targetClassRoom): bool {
                $existingClassRoom = $enrollment->conflictReferenceClassRoom();

                return $existingClassRoom !== null
                    && (int) $existingClassRoom->id !== (int) $targetClassRoom->id
                    && $existingClassRoom->conflictsWith($targetClassRoom);
            });

        if ($conflict) {
            throw new EnrollmentOperationException('Học viên đã có lớp khác trùng lịch trong cùng khung giờ. Vui lòng chọn lớp khác.');
        }
    }

    protected function ensureStudentHasNoRequestedScheduleConflict(
        int $studentId,
        array $meetingDays,
        string $startTime,
        string $endTime,
        ?int $ignoreEnrollmentId = null
    ): void {
        $conflict = GhiDanh::query()
            ->with([
                'classRoom.course',
                'classRoom.schedules',
                'course.classRooms.schedules',
            ])
            ->where('user_id', $studentId)
            ->when($ignoreEnrollmentId, fn ($query) => $query->whereKeyNot($ignoreEnrollmentId))
            ->whereIn('status', GhiDanh::scheduleBlockingStatuses())
            ->get()
            ->first(function (GhiDanh $enrollment) use ($meetingDays, $startTime, $endTime): bool {
                $existingClassRoom = $enrollment->conflictReferenceClassRoom();

                if (! $existingClassRoom) {
                    return false;
                }

                foreach ($existingClassRoom->scheduleRows() as $schedule) {
                    if (! in_array((string) ($schedule['day_of_week'] ?? ''), $meetingDays, true)) {
                        continue;
                    }

                    if ($this->timeRangesOverlap(
                        (string) ($schedule['start_time'] ?? ''),
                        (string) ($schedule['end_time'] ?? ''),
                        $startTime,
                        $endTime
                    )) {
                        return true;
                    }
                }

                return false;
            });

        if ($conflict) {
            throw new EnrollmentOperationException('Học viên đã có lớp khác trùng lịch trong cùng khung giờ. Vui lòng chọn lịch khác.');
        }
    }

    protected function timeRangesOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        $startA = substr($startA, 0, 5);
        $endA = substr($endA, 0, 5);
        $startB = substr($startB, 0, 5);
        $endB = substr($endB, 0, 5);

        if ($startA === '' || $endA === '' || $startB === '' || $endB === '') {
            return false;
        }

        return $startA < $endB && $endA > $startB;
    }

    protected function buildScheduleConflictMap(NguoiDung $student, Collection $classes): array
    {
        $activeEnrollments = GhiDanh::query()
            ->with([
                'classRoom.course',
                'classRoom.schedules',
            ])
            ->where('user_id', $student->id)
            ->whereIn('status', GhiDanh::courseAccessStatuses())
            ->whereNotNull('lop_hoc_id')
            ->get();

        $conflictMap = [];

        foreach ($classes as $classRoom) {
            foreach ($activeEnrollments as $enrollment) {
                $existingClassRoom = $enrollment->conflictReferenceClassRoom();

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
