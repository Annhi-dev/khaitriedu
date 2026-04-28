<?php

namespace App\Services;

use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class AdminEnrollmentService
{
    public function paginateEnrollments(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $requestSource = trim((string) ($filters['request_source'] ?? ''));
        $studentId = ! empty($filters['student_id']) ? (int) $filters['student_id'] : null;
        $classRoomId = ! empty($filters['class_room_id']) ? (int) $filters['class_room_id'] : null;

        return Enrollment::query()
            ->with([
                'user',
                'subject.category',
                'course.subject.category',
                'course.classRooms.room',
                'course.classRooms.teacher',
                'course.classRooms.schedules',
                'classRoom.course',
                'classRoom.room',
                'classRoom.teacher',
                'classRoom.schedules',
                'assignedTeacher',
                'reviewer',
            ])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->whereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where(function (Builder $userFilter) use ($search) {
                            $userFilter->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                    })->orWhereHas('subject', function (Builder $subjectQuery) use ($search) {
                        $subjectQuery->where('name', 'like', '%' . $search . '%');
                    })->orWhereHas('course', function (Builder $courseQuery) use ($search) {
                        $courseQuery->where('title', 'like', '%' . $search . '%')
                            ->orWhereHas('subject', function (Builder $subjectQuery) use ($search) {
                                $subjectQuery->where('name', 'like', '%' . $search . '%');
                            });
                    });
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($studentId !== null, fn (Builder $query) => $query->where('user_id', $studentId))
            ->when($classRoomId !== null, fn (Builder $query) => $query->where('lop_hoc_id', $classRoomId))
            ->when($requestSource !== '', function (Builder $query) use ($requestSource) {
                if ($requestSource === Enrollment::REQUEST_SOURCE_FIXED_CLASS) {
                    $query->where(function (Builder $builder) {
                        $builder->whereNotNull('lop_hoc_id')
                            ->orWhere('status', Enrollment::STATUS_ENROLLED);
                    });

                    return;
                }

                if ($requestSource === Enrollment::REQUEST_SOURCE_CUSTOM_SCHEDULE) {
                    $query->whereNull('lop_hoc_id')
                        ->where('status', '!=', Enrollment::STATUS_ENROLLED);
                }
            })
            ->orderByRaw("case when status = '" . Enrollment::STATUS_PENDING . "' then 0 else 1 end")
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function getEnrollmentDetail(Enrollment $enrollment): array
    {
        $enrollment->load([
            'user',
            'subject.category',
            'course.subject.category',
            'course.teacher',
            'course.classRooms.room',
            'course.classRooms.teacher',
            'course.classRooms.schedules',
            'classRoom.course',
            'assignedTeacher',
            'classRoom.room',
            'classRoom.teacher',
            'classRoom.schedules',
            'reviewer',
        ]);

        return [
            'enrollment' => $enrollment,
        ];
    }

    public function reviewEnrollment(Enrollment $enrollment, array $data, User $admin): string
    {
        $action = (string) $data['action'];
        $enrollment->loadMissing(['user']);

        if ($action === 'schedule' && $this->shouldCreateNewClassInPhase9($enrollment)) {
            throw ValidationException::withMessages([
                'action' => 'Hồ sơ yêu cầu lịch học riêng cần xử lý ở phase 9 để tạo lớp mới phù hợp.',
            ]);
        }

        $selectedCourse = $this->resolveSelectedCourse($enrollment, $data['course_id'] ?? null);
        $selectedClassRoom = $this->resolveSelectedClassRoom($enrollment, $data['class_room_id'] ?? null, $selectedCourse);
        $assignedTeacherId = $data['assigned_teacher_id'] ?? null;
        $note = $data['note'] ?? null;
        $finalSchedule = $data['schedule'] ?? null;

        if ($assignedTeacherId && ! User::query()->teachers()->whereKey($assignedTeacherId)->exists()) {
            throw ValidationException::withMessages([
                'assigned_teacher_id' => 'Giảng viên được chọn không hợp lệ.',
            ]);
        }

        if (! $assignedTeacherId && $selectedCourse?->teacher_id) {
            $assignedTeacherId = $selectedCourse->teacher_id;
        }

        if (($finalSchedule === null || $finalSchedule === '') && $selectedCourse?->schedule) {
            $finalSchedule = $selectedCourse->schedule;
        }

        if (($finalSchedule === null || $finalSchedule === '') && $enrollment->schedule) {
            $finalSchedule = $enrollment->schedule;
        }

        $finalSchedule = $finalSchedule !== '' ? $finalSchedule : null;

        if ($action === 'approve' && $selectedClassRoom) {
            $this->ensureStudentHasNoScheduleConflict($enrollment->user, $selectedClassRoom, $enrollment->id);
        }

        switch ($action) {
            case 'approve':
                $enrollment->status = Enrollment::STATUS_APPROVED;
                $enrollment->note = $note;
                if (! $enrollment->isFixedClassEnrollment()) {
                    $enrollment->course_id = null;
                    $enrollment->assigned_teacher_id = null;
                    $enrollment->schedule = null;
                    $message = 'Đăng ký học đã được duyệt và đang chờ xếp lớp.';
                } else {
                    if (! $selectedClassRoom) {
                        throw ValidationException::withMessages([
                            'class_room_id' => 'Khóa này chưa có lớp hiện hành. Vui lòng mở lớp trước khi duyệt ghi danh.',
                        ]);
                    }

                    $enrollment->lop_hoc_id = $selectedClassRoom->id;
                    $enrollment->course_id = $selectedClassRoom->course_id ?? $selectedCourse?->id ?? $enrollment->course_id;
                    $enrollment->subject_id = $selectedClassRoom->subject_id ?? $selectedCourse?->subject_id ?? $enrollment->subject_id;
                    $enrollment->assigned_teacher_id = $selectedClassRoom->teacher_id ?? $assignedTeacherId ?? $enrollment->assigned_teacher_id;
                    $enrollment->schedule = $selectedClassRoom->schedules->isNotEmpty()
                        ? $selectedClassRoom->scheduleSummary()
                        : ($finalSchedule ?? $enrollment->schedule);
                    $message = 'Ghi danh lớp cố định đã được duyệt và gắn vào lớp hiện hành.';
                }
                break;

            case 'reject':
                $enrollment->status = Enrollment::STATUS_REJECTED;
                $enrollment->course_id = null;
                $enrollment->assigned_teacher_id = null;
                $enrollment->schedule = null;
                $enrollment->note = $note;
                $message = 'Đăng ký học đã bị từ chối.';
                break;

            case 'request_update':
                $enrollment->status = Enrollment::STATUS_PENDING;
                $enrollment->course_id = null;
                $enrollment->assigned_teacher_id = null;
                $enrollment->schedule = null;
                $enrollment->note = $note;
                $message = 'Đã gửi yêu cầu học viên bổ sung lại thông tin đăng ký.';
                break;

            case 'schedule':
                if (! $selectedCourse) {
                    throw ValidationException::withMessages([
                        'course_id' => 'Vui lòng chọn lớp học trước khi xếp lịch.',
                    ]);
                }

                if (! $finalSchedule) {
                    throw ValidationException::withMessages([
                        'schedule' => 'Vui lòng nhập lịch học chính thức hoặc chọn lớp đã có lịch.',
                    ]);
                }

                $enrollment->status = Enrollment::STATUS_SCHEDULED;
                $enrollment->course_id = $selectedCourse->id;
                $enrollment->subject_id = $selectedCourse->subject_id;
                $enrollment->assigned_teacher_id = $assignedTeacherId;
                $enrollment->schedule = $finalSchedule;
                $enrollment->note = $note;
                $message = 'Đăng ký học đã được xếp lớp và chốt lịch sơ bộ.';
                break;

            case 'activate':
                if (! $selectedCourse) {
                    throw ValidationException::withMessages([
                        'course_id' => 'Vui lòng chọn lớp học trước khi chuyển sang đang học.',
                    ]);
                }

                $enrollment->status = Enrollment::STATUS_ACTIVE;
                $enrollment->course_id = $selectedCourse->id;
                $enrollment->subject_id = $selectedCourse->subject_id;
                $enrollment->assigned_teacher_id = $assignedTeacherId;
                $enrollment->schedule = $finalSchedule;
                $enrollment->note = $note;
                $message = 'Đăng ký học đã được chuyển sang trạng thái đang học.';
                break;

            case 'complete':
                if (! $selectedCourse) {
                    throw ValidationException::withMessages([
                        'course_id' => 'Vui lòng chọn lớp học trước khi đánh dấu hoàn thành.',
                    ]);
                }

                $enrollment->status = Enrollment::STATUS_COMPLETED;
                $enrollment->course_id = $selectedCourse->id;
                $enrollment->subject_id = $selectedCourse->subject_id;
                $enrollment->assigned_teacher_id = $assignedTeacherId;
                $enrollment->schedule = $finalSchedule;
                $enrollment->note = $note;
                $message = 'Đăng ký học đã được đánh dấu hoàn thành.';
                break;

            default:
                throw ValidationException::withMessages([
                    'action' => 'Hành động xử lý đăng ký không hợp lệ.',
                ]);
        }

        $enrollment->reviewed_by = $admin->id;
        $enrollment->reviewed_at = now();
        $enrollment->save();

        if (in_array($action, ['schedule', 'activate', 'complete'], true)) {
            $this->syncClassEnrollmentStatuses($enrollment, $admin);
        }

        return $message;
    }

    protected function shouldCreateNewClassInPhase9(Enrollment $enrollment): bool
    {
        return $enrollment->isCustomScheduleRequest()
            && in_array($enrollment->normalizedStatus(), [
                Enrollment::STATUS_PENDING,
                Enrollment::STATUS_APPROVED,
            ], true);
    }

    protected function resolveSelectedCourse(Enrollment $enrollment, ?int $courseId): ?Course
    {
        $course = null;

        if ($courseId) {
            $course = Course::query()->with('teacher')->find($courseId);
        } elseif ($enrollment->course_id) {
            $course = Course::query()->with('teacher')->find($enrollment->course_id);
        }

        if (! $course) {
            return null;
        }

        if ($enrollment->subject_id && (int) $course->subject_id !== (int) $enrollment->subject_id) {
            throw ValidationException::withMessages([
                'course_id' => 'Lớp học phải thuộc đúng khóa học mà học viên đã đăng ký.',
            ]);
        }

        return $course;
    }

    protected function resolveSelectedClassRoom(Enrollment $enrollment, ?int $classRoomId, ?Course $selectedCourse = null): ?ClassRoom
    {
        $classRoom = null;

        if ($classRoomId) {
            $classRoom = ClassRoom::query()
                ->with(['course.teacher', 'room', 'teacher', 'schedules'])
                ->find($classRoomId);
        } elseif ($enrollment->classRoom) {
            $classRoom = $enrollment->classRoom;
        } elseif ($selectedCourse) {
            $classRoom = $selectedCourse->currentClassRoom();
        } elseif ($enrollment->course_id) {
            $classRoom = Course::query()
                ->with(['classRooms.room', 'classRooms.teacher', 'classRooms.schedules'])
                ->find($enrollment->course_id)
                ?->currentClassRoom();
        }

        if (! $classRoom) {
            return null;
        }

        if ($selectedCourse && (int) $classRoom->course_id !== (int) $selectedCourse->id) {
            throw ValidationException::withMessages([
                'class_room_id' => 'Lớp học phải thuộc đúng khóa học đã chọn.',
            ]);
        }

        if ($enrollment->subject_id && (int) $classRoom->subject_id !== (int) $enrollment->subject_id) {
            throw ValidationException::withMessages([
                'class_room_id' => 'Lớp học phải thuộc đúng môn mà học viên đã đăng ký.',
            ]);
        }

        return $classRoom;
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
            ->when($ignoreEnrollmentId, fn (Builder $query) => $query->whereKeyNot($ignoreEnrollmentId))
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->get()
            ->first(function (Enrollment $enrollment) use ($targetClassRoom): bool {
                $existingClassRoom = $enrollment->conflictReferenceClassRoom();

                return $existingClassRoom !== null
                    && (int) $existingClassRoom->id !== (int) $targetClassRoom->id
                    && $existingClassRoom->conflictsWith($targetClassRoom);
            });

        if ($conflict) {
            throw ValidationException::withMessages([
                'class_room_id' => 'Học viên đã có lớp khác trùng lịch trong cùng khung giờ. Vui lòng chọn lớp khác.',
            ]);
        }
    }

    protected function syncClassEnrollmentStatuses(Enrollment $sourceEnrollment, User $admin): void
    {
        if (! $sourceEnrollment->lop_hoc_id) {
            return;
        }

        if (! in_array($sourceEnrollment->normalizedStatus(), [
            Enrollment::STATUS_SCHEDULED,
            Enrollment::STATUS_ACTIVE,
            Enrollment::STATUS_COMPLETED,
        ], true)) {
            return;
        }

        Enrollment::query()
            ->where('lop_hoc_id', $sourceEnrollment->lop_hoc_id)
            ->whereKeyNot($sourceEnrollment->id)
            ->whereIn('status', [
                Enrollment::STATUS_APPROVED,
                Enrollment::STATUS_ENROLLED,
                Enrollment::STATUS_SCHEDULED,
                Enrollment::STATUS_ACTIVE,
                Enrollment::STATUS_COMPLETED,
                Enrollment::LEGACY_STATUS_CONFIRMED,
            ])
            ->update([
                'status' => $sourceEnrollment->normalizedStatus(),
                'course_id' => $sourceEnrollment->course_id,
                'subject_id' => $sourceEnrollment->subject_id,
                'assigned_teacher_id' => $sourceEnrollment->assigned_teacher_id,
                'schedule' => $sourceEnrollment->schedule,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);
    }
}
