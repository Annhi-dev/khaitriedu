<?php

namespace App\Services;

use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\Room;
use App\Models\ScheduleChangeRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class AdminScheduleChangeRequestService
{
    public function paginateRequests(array $filters): LengthAwarePaginator
    {
        $status = $filters['status'] ?? null;
        $search = trim((string) ($filters['search'] ?? ''));

        return ScheduleChangeRequest::query()
            ->with([
                'teacher',
                'course.subject.category',
                'classRoom.subject.category',
                'classRoom.room',
                'classSchedule.room',
                'requestedRoom',
                'reviewer',
            ])
            ->when(in_array($status, ScheduleChangeRequest::filterableStatuses(), true), fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->whereHas('teacher', function (Builder $teacherQuery) use ($search) {
                        $teacherQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })->orWhereHas('course', function (Builder $courseQuery) use ($search) {
                        $courseQuery->where('title', 'like', '%' . $search . '%')
                            ->orWhereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', '%' . $search . '%'));
                    })->orWhereHas('classRoom', function (Builder $classQuery) use ($search) {
                        $classQuery->whereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', '%' . $search . '%'))
                            ->orWhere('id', 'like', '%' . $search . '%');
                    })->orWhere('reason', 'like', '%' . $search . '%');
                });
            })
            ->orderByRaw("case when status = '" . ScheduleChangeRequest::STATUS_PENDING . "' then 0 else 1 end")
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();
    }

    public function review(ScheduleChangeRequest $scheduleChangeRequest, array $data, User $admin): string
    {
        $scheduleChangeRequest->loadMissing([
            'teacher',
            'course.enrollments.user',
            'classRoom.subject.category',
            'classRoom.room',
            'classRoom.course.enrollments',
            'classRoom.enrollments.user',
            'classSchedule.room',
            'requestedRoom',
        ]);

        if (! $scheduleChangeRequest->isPending()) {
            throw ValidationException::withMessages([
                'action' => 'Yêu cầu này đã được xử lý trước đó.',
            ]);
        }

        if (! $scheduleChangeRequest->course && ! $scheduleChangeRequest->classSchedule) {
            throw ValidationException::withMessages([
                'action' => 'Lịch gắn với yêu cầu đổi lịch không còn tồn tại.',
            ]);
        }

        if ($data['action'] === 'approve') {
            if ($scheduleChangeRequest->classSchedule) {
                $this->ensureTeacherAvailabilityForClassSchedule($scheduleChangeRequest);
                $this->ensureStudentsAvailabilityForClassSchedule($scheduleChangeRequest);
                $this->ensureRequestedRoomAvailabilityForClassSchedule($scheduleChangeRequest);
                $newSchedule = $this->applyApprovedClassSchedule($scheduleChangeRequest);
            } else {
                $this->ensureTeacherAvailability($scheduleChangeRequest);
                $this->ensureStudentsAvailability($scheduleChangeRequest);
                $newSchedule = $this->applyApprovedSchedule($scheduleChangeRequest);
            }

            $scheduleChangeRequest->update([
                'status' => ScheduleChangeRequest::STATUS_APPROVED,
                'admin_note' => $data['admin_note'] ?? null,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $this->notifyTeacher($scheduleChangeRequest, true);

            return 'Đã duyệt yêu cầu đổi lịch và cập nhật lịch mới: ' . $newSchedule;
        }

        $scheduleChangeRequest->update([
            'status' => ScheduleChangeRequest::STATUS_REJECTED,
            'admin_note' => $data['admin_note'],
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->notifyTeacher($scheduleChangeRequest, false);

        return 'Đã từ chối yêu cầu đổi lịch của giảng viên.';
    }

    protected function applyApprovedSchedule(ScheduleChangeRequest $scheduleChangeRequest): string
    {
        $course = $scheduleChangeRequest->course;
        $newSchedule = $this->buildScheduleLabel($scheduleChangeRequest);

        $course->fill([
            'day_of_week' => $scheduleChangeRequest->requested_day_of_week,
            'start_date' => optional($scheduleChangeRequest->requested_date)->format('Y-m-d'),
            'end_date' => optional($scheduleChangeRequest->requested_end_date)->format('Y-m-d'),
            'start_time' => $scheduleChangeRequest->requested_start_time,
            'end_time' => $scheduleChangeRequest->requested_end_time,
            'schedule' => $newSchedule,
        ]);
        $course->save();

        $course->enrollments()
            ->whereIn('status', [
                Enrollment::LEGACY_STATUS_CONFIRMED,
                Enrollment::STATUS_SCHEDULED,
                Enrollment::STATUS_ACTIVE,
            ])
            ->update(['schedule' => $newSchedule]);

        return $newSchedule;
    }

    protected function ensureTeacherAvailability(ScheduleChangeRequest $scheduleChangeRequest): void
    {
        $course = $scheduleChangeRequest->course;
        $targetStartDate = optional($scheduleChangeRequest->requested_date)->format('Y-m-d');
        $targetEndDate = optional($scheduleChangeRequest->requested_end_date)->format('Y-m-d') ?: $targetStartDate;

        $conflict = Course::query()
            ->where('teacher_id', $course->teacher_id)
            ->whereKeyNot($course->id)
            ->where('day_of_week', $scheduleChangeRequest->requested_day_of_week)
            ->where('start_time', '<', $scheduleChangeRequest->requested_end_time)
            ->where('end_time', '>', $scheduleChangeRequest->requested_start_time)
            ->whereIn('status', Course::schedulingStatuses())
            ->whereDate('start_date', '<=', $targetEndDate)
            ->where(function (Builder $query) use ($targetStartDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $targetStartDate);
            })
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'action' => 'Lịch đề xuất đang trùng với lớp khác mà giảng viên này phụ trách.',
            ]);
        }
    }

    protected function ensureStudentsAvailability(ScheduleChangeRequest $scheduleChangeRequest): void
    {
        $course = $scheduleChangeRequest->course;
        $studentIds = $course->enrollments()
            ->whereIn('status', [
                Enrollment::LEGACY_STATUS_CONFIRMED,
                Enrollment::STATUS_SCHEDULED,
                Enrollment::STATUS_ACTIVE,
            ])
            ->pluck('user_id');

        if ($studentIds->isEmpty()) {
            return;
        }

        $targetStartDate = optional($scheduleChangeRequest->requested_date)->format('Y-m-d');
        $targetEndDate = optional($scheduleChangeRequest->requested_end_date)->format('Y-m-d') ?: $targetStartDate;

        $conflict = Enrollment::query()
            ->with('user')
            ->whereIn('user_id', $studentIds)
            ->whereIn('status', [
                Enrollment::LEGACY_STATUS_CONFIRMED,
                Enrollment::STATUS_SCHEDULED,
                Enrollment::STATUS_ACTIVE,
            ])
            ->whereHas('course', function (Builder $query) use ($course, $scheduleChangeRequest, $targetStartDate, $targetEndDate) {
                $query->whereKeyNot($course->id)
                    ->where('day_of_week', $scheduleChangeRequest->requested_day_of_week)
                    ->where('start_time', '<', $scheduleChangeRequest->requested_end_time)
                    ->where('end_time', '>', $scheduleChangeRequest->requested_start_time)
                    ->whereIn('status', Course::schedulingStatuses())
                    ->whereDate('start_date', '<=', $targetEndDate)
                    ->where(function (Builder $builder) use ($targetStartDate) {
                        $builder->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $targetStartDate);
                    });
            })
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'action' => 'Lịch đề xuất sẽ làm học viên ' . ($conflict->user?->name ?? 'trong lớp') . ' bị trùng lịch với lớp khác.',
            ]);
        }
    }

    protected function applyApprovedClassSchedule(ScheduleChangeRequest $scheduleChangeRequest): string
    {
        $classSchedule = $scheduleChangeRequest->classSchedule;
        $classRoom = $scheduleChangeRequest->classRoom;

        $attributes = [
            'day_of_week' => $scheduleChangeRequest->requested_day_of_week,
            'start_time' => $scheduleChangeRequest->requested_start_time,
            'end_time' => $scheduleChangeRequest->requested_end_time,
        ];

        if ($scheduleChangeRequest->requested_room_id !== null) {
            $attributes['room_id'] = $scheduleChangeRequest->requested_room_id;
        }

        $classSchedule->fill($attributes)->save();

        if ($classRoom && $scheduleChangeRequest->requested_date) {
            if (! $classRoom->start_date || $classRoom->start_date->gt($scheduleChangeRequest->requested_date)) {
                $classRoom->forceFill([
                    'start_date' => $scheduleChangeRequest->requested_date,
                ])->save();
            }
        }

        $this->syncLinkedCourseScheduleForClassRoom($classRoom, $scheduleChangeRequest);

        return $classSchedule->fresh()->label();
    }

    protected function ensureTeacherAvailabilityForClassSchedule(ScheduleChangeRequest $scheduleChangeRequest): void
    {
        $conflict = ClassSchedule::query()
            ->whereKeyNot($scheduleChangeRequest->class_schedule_id)
            ->where('day_of_week', $scheduleChangeRequest->requested_day_of_week)
            ->where('start_time', '<', $scheduleChangeRequest->requested_end_time)
            ->where('end_time', '>', $scheduleChangeRequest->requested_start_time)
            ->whereHas('classRoom', function (Builder $query) use ($scheduleChangeRequest) {
                $query->where('teacher_id', $scheduleChangeRequest->teacher_id)
                    ->whereNotIn('status', [ClassRoom::STATUS_CLOSED, ClassRoom::STATUS_COMPLETED]);
            })
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'action' => 'Lịch đề xuất đang trùng với một buổi học khác của giảng viên này.',
            ]);
        }
    }

    protected function ensureStudentsAvailabilityForClassSchedule(ScheduleChangeRequest $scheduleChangeRequest): void
    {
        $classRoom = $scheduleChangeRequest->classRoom;

        if (! $classRoom) {
            return;
        }

        $studentIds = $classRoom->enrollments()
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->pluck('user_id');

        if ($studentIds->isEmpty()) {
            return;
        }

        $conflict = ClassSchedule::query()
            ->whereKeyNot($scheduleChangeRequest->class_schedule_id)
            ->where('day_of_week', $scheduleChangeRequest->requested_day_of_week)
            ->where('start_time', '<', $scheduleChangeRequest->requested_end_time)
            ->where('end_time', '>', $scheduleChangeRequest->requested_start_time)
            ->whereHas('classRoom', function (Builder $query) use ($classRoom, $studentIds) {
                $query->whereKeyNot($classRoom->id)
                    ->whereNotIn('status', [ClassRoom::STATUS_CLOSED, ClassRoom::STATUS_COMPLETED])
                    ->whereHas('enrollments', function (Builder $enrollmentQuery) use ($studentIds) {
                        $enrollmentQuery->whereIn('user_id', $studentIds)
                            ->whereIn('status', Enrollment::courseAccessStatuses());
                    });
            })
            ->with(['classRoom.enrollments' => function ($query) use ($studentIds) {
                $query->whereIn('user_id', $studentIds)->with('user');
            }])
            ->first();

        if ($conflict) {
            $conflictingEnrollment = $conflict->classRoom?->enrollments->first();

            throw ValidationException::withMessages([
                'action' => 'Lịch đề xuất sẽ làm học viên '
                    . ($conflictingEnrollment?->user?->name ?? 'trong lớp')
                    . ' bị trùng với buổi học khác.',
            ]);
        }
    }

    protected function ensureRequestedRoomAvailabilityForClassSchedule(ScheduleChangeRequest $scheduleChangeRequest): void
    {
        $requestedRoomId = (int) ($scheduleChangeRequest->requested_room_id ?? 0);
        $currentRoomId = (int) ($scheduleChangeRequest->classSchedule?->room_id ?: $scheduleChangeRequest->classRoom?->room_id);

        if ($requestedRoomId === 0 || $requestedRoomId === $currentRoomId) {
            return;
        }

        $requestedRoom = Room::query()->find($requestedRoomId);

        if (! $requestedRoom || $requestedRoom->status !== Room::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'action' => 'Phong hoc de xuat khong hop le hoac dang tam ngung.',
            ]);
        }

        $conflict = ClassSchedule::query()
            ->whereKeyNot($scheduleChangeRequest->class_schedule_id)
            ->where('room_id', $requestedRoomId)
            ->where('day_of_week', $scheduleChangeRequest->requested_day_of_week)
            ->where('start_time', '<', $scheduleChangeRequest->requested_end_time)
            ->where('end_time', '>', $scheduleChangeRequest->requested_start_time)
            ->whereHas('classRoom', function (Builder $query) {
                $query->whereNotIn('status', [ClassRoom::STATUS_CLOSED, ClassRoom::STATUS_COMPLETED]);
            })
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'action' => 'Phong hoc de xuat dang trung lich voi mot lop khac.',
            ]);
        }
    }

    protected function syncLinkedCourseScheduleForClassRoom(?ClassRoom $classRoom, ScheduleChangeRequest $scheduleChangeRequest): void
    {
        if (! $classRoom || ! $classRoom->course_id) {
            return;
        }

        $classRoom->loadMissing(['course', 'schedules']);
        $course = $classRoom->course;

        if (! $course) {
            return;
        }

        $meetingDays = $classRoom->schedules
            ->pluck('day_of_week')
            ->filter(fn ($day) => is_string($day) && $day !== '')
            ->unique()
            ->values()
            ->all();

        if ($meetingDays === []) {
            $meetingDays = [(string) $scheduleChangeRequest->requested_day_of_week];
        }

        $course->fill([
            'day_of_week' => $meetingDays[0] ?? $course->day_of_week,
            'meeting_days' => $meetingDays,
            'start_time' => $scheduleChangeRequest->requested_start_time ?: $course->start_time,
            'end_time' => $scheduleChangeRequest->requested_end_time ?: $course->end_time,
        ]);

        if ($classRoom->start_date && (! $course->start_date || $course->start_date->gt($classRoom->start_date))) {
            $course->start_date = $classRoom->start_date->format('Y-m-d');
        }

        $course->schedule = $this->buildLinkedCourseSchedule($course);
        $course->save();

        $course->enrollments()
            ->whereIn('status', [
                Enrollment::LEGACY_STATUS_CONFIRMED,
                Enrollment::STATUS_SCHEDULED,
                Enrollment::STATUS_ACTIVE,
            ])
            ->update(['schedule' => $course->schedule]);
    }

    protected function buildLinkedCourseSchedule(Course $course): string
    {
        $segments = [];

        if ($course->meetingDayValues() !== []) {
            $segments[] = $course->meetingDaysLabel();
        }

        if ($course->start_time && $course->end_time) {
            $segments[] = $course->start_time . ' - ' . $course->end_time;
        }

        if ($course->start_date) {
            $segments[] = 'Từ ' . $course->start_date->format('d/m/Y')
                . ($course->end_date ? ' đến ' . $course->end_date->format('d/m/Y') : '');
        }

        return $segments !== [] ? implode(' | ', $segments) : 'Chưa có lịch cụ thể';
    }

    protected function buildScheduleLabel(ScheduleChangeRequest $scheduleChangeRequest): string
    {
        $preview = new Course([
            'day_of_week' => $scheduleChangeRequest->requested_day_of_week,
            'start_date' => optional($scheduleChangeRequest->requested_date)->format('Y-m-d'),
            'end_date' => optional($scheduleChangeRequest->requested_end_date)->format('Y-m-d'),
            'start_time' => $scheduleChangeRequest->requested_start_time,
            'end_time' => $scheduleChangeRequest->requested_end_time,
        ]);

        return $preview->formattedSchedule();
    }

    protected function notifyTeacher(ScheduleChangeRequest $scheduleChangeRequest, bool $approved): void
    {
        Notification::create([
            'user_id' => $scheduleChangeRequest->teacher_id,
            'title' => $approved ? 'Yêu cầu đổi lịch đã được duyệt' : 'Yêu cầu đổi lịch bị từ chối',
            'message' => ($approved ? 'Admin đã duyệt lịch mới cho ' : 'Admin đã từ chối yêu cầu đổi lịch của ')
                . $scheduleChangeRequest->targetTitle()
                . '.',
            'type' => $approved ? 'success' : 'warning',
            'link' => route('teacher.schedule-change-requests.index'),
        ]);
    }
}
