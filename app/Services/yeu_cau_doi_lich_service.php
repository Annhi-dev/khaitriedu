<?php

namespace App\Services;

use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\ThongBao;
use App\Models\PhongHoc;
use App\Models\YeuCauDoiLich;
use App\Models\NguoiDung;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminScheduleChangeRequestService
{
    public function paginateRequests(array $filters): LengthAwarePaginator
    {
        $status = $filters['status'] ?? null;
        $search = trim((string) ($filters['search'] ?? ''));

        return YeuCauDoiLich::query()
            ->with([
                'teacher',
                'course.subject.category',
                'classRoom.subject.category',
                'classRoom.room',
                'classSchedule.room',
                'requestedRoom',
                'reviewer',
            ])
            ->when(in_array($status, YeuCauDoiLich::filterableStatuses(), true), fn (Builder $query) => $query->where('status', $status))
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
            ->orderByRaw("case when status = '" . YeuCauDoiLich::STATUS_PENDING . "' then 0 else 1 end")
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();
    }

    public function review(YeuCauDoiLich $scheduleChangeRequest, array $data, NguoiDung $admin): string
    {
        return DB::transaction(function () use ($scheduleChangeRequest, $data, $admin): string {
            $scheduleChangeRequest = YeuCauDoiLich::query()
                ->lockForUpdate()
                ->with([
                    'teacher',
                    'course.enrollments.user',
                    'classRoom.subject.category',
                    'classRoom.room',
                    'classRoom.course.enrollments',
                    'classRoom.enrollments.user',
                    'classSchedule.room',
                    'requestedRoom',
                ])
                ->findOrFail($scheduleChangeRequest->id);

            if (! $scheduleChangeRequest->isPending()) {
                throw ValidationException::withMessages([
                    'action' => 'Yêu cầu này đã được xử lý trước đó.',
                ]);
            }

            if (! $scheduleChangeRequest->course && ! $scheduleChangeRequest->classSchedule) {
                throw ValidationException::withMessages([
                    'action' => 'Lịch gắn với yêu cầu dời buổi không còn tồn tại.',
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
                    'status' => YeuCauDoiLich::STATUS_APPROVED,
                    'admin_note' => $data['admin_note'] ?? null,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now(),
                ]);

                $this->notifyApproval($scheduleChangeRequest);

                return 'Đã duyệt yêu cầu dời buổi và cập nhật lịch mới: ' . $newSchedule;
            }

            $scheduleChangeRequest->update([
                'status' => YeuCauDoiLich::STATUS_REJECTED,
                'admin_note' => $data['admin_note'],
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $this->notifyTeacher($scheduleChangeRequest, false);

            return 'Đã từ chối yêu cầu dời buổi của giảng viên.';
        });
    }

    protected function applyApprovedSchedule(YeuCauDoiLich $scheduleChangeRequest): string
    {
        $course = KhoaHoc::query()
            ->lockForUpdate()
            ->findOrFail($scheduleChangeRequest->course_id);

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
                GhiDanh::LEGACY_STATUS_CONFIRMED,
                GhiDanh::STATUS_SCHEDULED,
                GhiDanh::STATUS_ACTIVE,
            ])
            ->update(['schedule' => $newSchedule]);

        return $newSchedule;
    }

    protected function ensureTeacherAvailability(YeuCauDoiLich $scheduleChangeRequest): void
    {
        $course = $scheduleChangeRequest->course;
        $targetStartDate = optional($scheduleChangeRequest->requested_date)->format('Y-m-d');
        $targetEndDate = optional($scheduleChangeRequest->requested_end_date)->format('Y-m-d') ?: $targetStartDate;

        $conflict = KhoaHoc::query()
            ->where('teacher_id', $course->teacher_id)
            ->whereKeyNot($course->id)
            ->where('day_of_week', $scheduleChangeRequest->requested_day_of_week)
            ->where('start_time', '<', $scheduleChangeRequest->requested_end_time)
            ->where('end_time', '>', $scheduleChangeRequest->requested_start_time)
            ->whereIn('status', KhoaHoc::schedulingStatuses())
            ->whereDate('start_date', '<=', $targetEndDate)
            ->where(function (Builder $query) use ($targetStartDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $targetStartDate);
            })
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                    'action' => 'Buổi dạy bù đề xuất đang trùng với lớp khác mà giảng viên này phụ trách.',
            ]);
        }
    }

    protected function ensureStudentsAvailability(YeuCauDoiLich $scheduleChangeRequest): void
    {
        $course = $scheduleChangeRequest->course;
        $studentIds = $course->enrollments()
            ->whereIn('status', [
                GhiDanh::LEGACY_STATUS_CONFIRMED,
                GhiDanh::STATUS_SCHEDULED,
                GhiDanh::STATUS_ACTIVE,
            ])
            ->pluck('user_id');

        if ($studentIds->isEmpty()) {
            return;
        }

        $targetStartDate = optional($scheduleChangeRequest->requested_date)->format('Y-m-d');
        $targetEndDate = optional($scheduleChangeRequest->requested_end_date)->format('Y-m-d') ?: $targetStartDate;

        $conflict = GhiDanh::query()
            ->with('user')
            ->whereIn('user_id', $studentIds)
            ->whereIn('status', [
                GhiDanh::LEGACY_STATUS_CONFIRMED,
                GhiDanh::STATUS_SCHEDULED,
                GhiDanh::STATUS_ACTIVE,
            ])
            ->whereHas('course', function (Builder $query) use ($course, $scheduleChangeRequest, $targetStartDate, $targetEndDate) {
                $query->whereKeyNot($course->id)
                    ->where('day_of_week', $scheduleChangeRequest->requested_day_of_week)
                    ->where('start_time', '<', $scheduleChangeRequest->requested_end_time)
                    ->where('end_time', '>', $scheduleChangeRequest->requested_start_time)
                    ->whereIn('status', KhoaHoc::schedulingStatuses())
                    ->whereDate('start_date', '<=', $targetEndDate)
                    ->where(function (Builder $builder) use ($targetStartDate) {
                        $builder->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $targetStartDate);
                    });
            })
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'action' => 'Buổi dạy bù đề xuất sẽ làm học viên ' . ($conflict->user?->name ?? 'trong lớp') . ' bị trùng lịch với lớp khác.',
            ]);
        }
    }

    protected function applyApprovedClassSchedule(YeuCauDoiLich $scheduleChangeRequest): string
    {
        $classSchedule = LichHoc::query()
            ->lockForUpdate()
            ->findOrFail($scheduleChangeRequest->class_schedule_id);

        $classRoom = null;

        if ($scheduleChangeRequest->class_room_id) {
            $classRoom = LopHoc::query()
                ->lockForUpdate()
                ->find($scheduleChangeRequest->class_room_id);
        }

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

    protected function ensureTeacherAvailabilityForClassSchedule(YeuCauDoiLich $scheduleChangeRequest): void
    {
        $requestedRange = $this->requestedDateRange($scheduleChangeRequest);

        $conflict = LichHoc::query()
            ->whereKeyNot($scheduleChangeRequest->class_schedule_id)
            ->where('day_of_week', $scheduleChangeRequest->requested_day_of_week)
            ->where('start_time', '<', $scheduleChangeRequest->requested_end_time)
            ->where('end_time', '>', $scheduleChangeRequest->requested_start_time)
            ->whereHas('classRoom', function (Builder $query) use ($scheduleChangeRequest) {
                $query->where('teacher_id', $scheduleChangeRequest->teacher_id)
                    ->whereNotIn('status', [LopHoc::STATUS_CLOSED, LopHoc::STATUS_COMPLETED]);
            })
            ->with(['classRoom.course.subject'])
            ->get()
            ->first(function (LichHoc $classSchedule) use ($requestedRange) {
                if ($requestedRange === null) {
                    return true;
                }

                return $classSchedule->classRoom?->overlapsDateRange($requestedRange[0], $requestedRange[1]);
            });

        if ($conflict) {
            throw ValidationException::withMessages([
                'action' => 'Buổi dạy bù đề xuất đang trùng với một buổi học khác của giảng viên này.',
            ]);
        }
    }

    protected function ensureStudentsAvailabilityForClassSchedule(YeuCauDoiLich $scheduleChangeRequest): void
    {
        $classRoom = $scheduleChangeRequest->classRoom;
        $requestedRange = $this->requestedDateRange($scheduleChangeRequest);

        if (! $classRoom) {
            return;
        }

        $studentIds = $classRoom->enrollments()
            ->whereIn('status', GhiDanh::courseAccessStatuses())
            ->pluck('user_id');

        if ($studentIds->isEmpty()) {
            return;
        }

        $conflict = LichHoc::query()
            ->whereKeyNot($scheduleChangeRequest->class_schedule_id)
            ->where('day_of_week', $scheduleChangeRequest->requested_day_of_week)
            ->where('start_time', '<', $scheduleChangeRequest->requested_end_time)
            ->where('end_time', '>', $scheduleChangeRequest->requested_start_time)
            ->whereHas('classRoom', function (Builder $query) use ($classRoom, $studentIds) {
                $query->whereKeyNot($classRoom->id)
                    ->whereNotIn('status', [LopHoc::STATUS_CLOSED, LopHoc::STATUS_COMPLETED])
                    ->whereHas('enrollments', function (Builder $enrollmentQuery) use ($studentIds) {
                        $enrollmentQuery->whereIn('user_id', $studentIds)
                            ->whereIn('status', GhiDanh::courseAccessStatuses());
                    });
            })
            ->with(['classRoom.enrollments' => function ($query) use ($studentIds) {
                $query->whereIn('user_id', $studentIds)->with('user');
            }, 'classRoom.course.subject'])
            ->get()
            ->first(function (LichHoc $classSchedule) use ($requestedRange) {
                if ($requestedRange === null) {
                    return true;
                }

                return $classSchedule->classRoom?->overlapsDateRange($requestedRange[0], $requestedRange[1]);
            });

        if ($conflict) {
            $conflictingEnrollment = $conflict->classRoom?->enrollments->first();

            throw ValidationException::withMessages([
                'action' => 'Buổi dạy bù đề xuất sẽ làm học viên '
                    . ($conflictingEnrollment?->user?->name ?? 'trong lớp')
                    . ' bị trùng với buổi học khác.',
            ]);
        }
    }

    protected function ensureRequestedRoomAvailabilityForClassSchedule(YeuCauDoiLich $scheduleChangeRequest): void
    {
        $requestedRoomId = (int) ($scheduleChangeRequest->requested_room_id ?? 0);
        $currentRoomId = (int) ($scheduleChangeRequest->classSchedule?->room_id ?: $scheduleChangeRequest->classRoom?->room_id);
        $requestedRange = $this->requestedDateRange($scheduleChangeRequest);

        if ($requestedRoomId === 0 || $requestedRoomId === $currentRoomId) {
            return;
        }

        $requestedRoom = PhongHoc::query()->find($requestedRoomId);

        if (! $requestedRoom || $requestedRoom->status !== PhongHoc::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'action' => 'Phong hoc de xuat khong hop le hoac dang tam ngung.',
            ]);
        }

        $conflict = LichHoc::query()
            ->whereKeyNot($scheduleChangeRequest->class_schedule_id)
            ->where('room_id', $requestedRoomId)
            ->where('day_of_week', $scheduleChangeRequest->requested_day_of_week)
            ->where('start_time', '<', $scheduleChangeRequest->requested_end_time)
            ->where('end_time', '>', $scheduleChangeRequest->requested_start_time)
            ->whereHas('classRoom', function (Builder $query) {
                $query->whereNotIn('status', [LopHoc::STATUS_CLOSED, LopHoc::STATUS_COMPLETED]);
            })
            ->with(['classRoom.course.subject'])
            ->get()
            ->first(function (LichHoc $classSchedule) use ($requestedRange) {
                if ($requestedRange === null) {
                    return true;
                }

                return $classSchedule->classRoom?->overlapsDateRange($requestedRange[0], $requestedRange[1]);
            });

        if ($conflict) {
            throw ValidationException::withMessages([
                'action' => 'Phong hoc de xuat dang trung lich voi mot lop khac.',
            ]);
        }
    }

    protected function requestedDateRange(YeuCauDoiLich $scheduleChangeRequest): ?array
    {
        if (! $scheduleChangeRequest->requested_date) {
            return null;
        }

        $startDate = $scheduleChangeRequest->requested_date->copy()->startOfDay();
        $endDateSource = $scheduleChangeRequest->requested_end_date ?: $scheduleChangeRequest->requested_date;
        $endDate = $endDateSource->copy()->endOfDay();

        return [$startDate, $endDate];
    }

    protected function syncLinkedCourseScheduleForClassRoom(?LopHoc $classRoom, YeuCauDoiLich $scheduleChangeRequest): void
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
                GhiDanh::LEGACY_STATUS_CONFIRMED,
                GhiDanh::STATUS_SCHEDULED,
                GhiDanh::STATUS_ACTIVE,
            ])
            ->update(['schedule' => $course->schedule]);
    }

    protected function buildLinkedCourseSchedule(KhoaHoc $course): string
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

    protected function buildScheduleLabel(YeuCauDoiLich $scheduleChangeRequest): string
    {
        $preview = new KhoaHoc([
            'day_of_week' => $scheduleChangeRequest->requested_day_of_week,
            'start_date' => optional($scheduleChangeRequest->requested_date)->format('Y-m-d'),
            'end_date' => optional($scheduleChangeRequest->requested_end_date)->format('Y-m-d'),
            'start_time' => $scheduleChangeRequest->requested_start_time,
            'end_time' => $scheduleChangeRequest->requested_end_time,
        ]);

        return $preview->formattedSchedule();
    }

    protected function notifyApproval(YeuCauDoiLich $scheduleChangeRequest): void
    {
        $title = 'Yêu cầu dời lịch đã được duyệt';
        $teacherMessage = $this->buildTeacherNotificationMessage($scheduleChangeRequest);
        $studentMessage = $this->buildStudentNotificationMessage($scheduleChangeRequest);

        ThongBao::create([
            'user_id' => $scheduleChangeRequest->teacher_id,
            'title' => $title,
            'message' => $teacherMessage,
            'type' => 'success',
            'link' => route('teacher.schedule-change-requests.index'),
        ]);

        foreach ($this->affectedStudentIds($scheduleChangeRequest) as $studentId) {
            ThongBao::create([
                'user_id' => $studentId,
                'title' => 'Lịch học đã thay đổi',
                'message' => $studentMessage,
                'type' => 'info',
                'link' => route('student.schedule'),
            ]);
        }
    }

    protected function notifyTeacher(YeuCauDoiLich $scheduleChangeRequest, bool $approved): void
    {
        ThongBao::create([
            'user_id' => $scheduleChangeRequest->teacher_id,
            'title' => $approved ? 'Yêu cầu dời lịch đã được duyệt' : 'Yêu cầu dời lịch bị từ chối',
            'message' => $approved
                ? $this->buildTeacherNotificationMessage($scheduleChangeRequest)
                : 'Admin đã từ chối yêu cầu dời lịch cho ' . $scheduleChangeRequest->targetTitle() . '.',
            'type' => $approved ? 'success' : 'warning',
            'link' => route('teacher.schedule-change-requests.index'),
        ]);
    }

    protected function affectedStudentIds(YeuCauDoiLich $scheduleChangeRequest): Collection
    {
        $query = $scheduleChangeRequest->classRoom
            ? $scheduleChangeRequest->classRoom->enrollments()
            : $scheduleChangeRequest->course?->enrollments();

        if (! $query) {
            return collect();
        }

        return $query
            ->whereIn('status', GhiDanh::courseAccessStatuses())
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();
    }

    protected function buildTeacherNotificationMessage(YeuCauDoiLich $scheduleChangeRequest): string
    {
        return $this->buildScheduleChangeNotificationMessage($scheduleChangeRequest, true);
    }

    protected function buildStudentNotificationMessage(YeuCauDoiLich $scheduleChangeRequest): string
    {
        return $this->buildScheduleChangeNotificationMessage($scheduleChangeRequest, false);
    }

    protected function buildScheduleChangeNotificationMessage(YeuCauDoiLich $scheduleChangeRequest, bool $isTeacher): string
    {
        $segments = [];

        $segments[] = ($isTeacher ? 'Yêu cầu dời lịch cho ' : 'Lịch học của bạn tại ')
            . $scheduleChangeRequest->targetTitle()
            . ' đã được admin duyệt.';
        $segments[] = 'Lịch cũ: ' . $scheduleChangeRequest->currentScheduleLabel();
        $segments[] = 'Lịch mới: ' . $scheduleChangeRequest->requestedScheduleLabel();

        $currentRoom = $scheduleChangeRequest->currentRoomLabel();
        $requestedRoom = $scheduleChangeRequest->requestedRoomLabel();

        if ($requestedRoom !== $currentRoom) {
            $segments[] = 'Phòng học: ' . $currentRoom . ' -> ' . $requestedRoom;
        } elseif ($requestedRoom !== 'Chưa phân phòng') {
            $segments[] = 'Phòng học: ' . $requestedRoom;
        }

        if ($scheduleChangeRequest->requested_date) {
            $segments[] = 'Hiệu lực từ ' . $scheduleChangeRequest->requested_date->format('d/m/Y');
        }

        if (! $isTeacher) {
            $segments[] = 'Vui lòng kiểm tra lại thời khóa biểu của bạn.';
        }

        return implode(' ', $segments);
    }
}
