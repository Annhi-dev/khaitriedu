<?php

namespace App\Services;

use App\Helpers\ScheduleHelper;
use App\Models\ClassRoom;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminCourseScheduleService
{
    public function normalizeMeetingDays(mixed $days): array
    {
        if (is_string($days) && $days !== '') {
            $days = [$days];
        }

        if (! is_array($days)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(function ($day) {
            return is_string($day) ? trim($day) : null;
        }, $days))));
    }

    public function buildStructuredScheduleData(Course $course, Request $request): array
    {
        $course->loadMissing(['subject', 'classRooms.room', 'classRooms.schedules']);

        $currentClassRoom = $course->currentClassRoom();
        $meetingDays = $this->normalizeMeetingDays($request->input('meeting_days', []));
        $startDate = $this->resolveStartDate($course, $currentClassRoom, $request->input('start_date'));
        $endDate = $this->resolveEndDate($course, $currentClassRoom, $request->input('end_date'), $startDate);
        $startTime = (string) ($request->input('start_time') ?? '');
        $endTime = $request->filled('end_time')
            ? (string) $request->input('end_time')
            : ($startTime !== '' ? ScheduleHelper::normalizeEndTime($startTime) : '');
        $teacherId = $request->filled('teacher_id')
            ? (int) $request->input('teacher_id')
            : ($course->teacher_id ? (int) $course->teacher_id : null);
        $roomId = $currentClassRoom?->room_id ? (int) $currentClassRoom->room_id : null;

        return [
            'class_room_id' => $currentClassRoom?->id ? (int) $currentClassRoom->id : null,
            'meeting_days' => $meetingDays,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'teacher_id' => $teacherId,
            'room_id' => $roomId,
        ];
    }

    public function collectConflictLabels(array $preview): array
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

    public function buildConflictMessage(array $conflictLabels): string
    {
        return 'Lịch này đang bị trùng ' . implode(', ', $conflictLabels) . '. Hãy chỉnh lại ngày hoặc giờ rồi kiểm tra lại ngay bên dưới.';
    }

    public function buildConflictRedirectQuery(Course $course, array $courseData): array
    {
        $currentClassRoom = $course->currentClassRoom();

        return array_filter([
            'course_id' => $course->id,
            'class_room_id' => $currentClassRoom?->id,
            'teacher_id' => $courseData['teacher_id'] ?? null,
            'room_id' => $currentClassRoom?->room_id,
            'day_of_week' => $courseData['meeting_days'] ?? [],
            'start_date' => $courseData['start_date'] ?? null,
            'end_date' => $courseData['end_date'] ?? null,
            'start_time' => $courseData['start_time'] ?? null,
            'end_time' => $courseData['end_time'] ?? null,
            'exclude_course_id' => $course->id,
            'exclude_class_room_id' => $currentClassRoom?->id,
        ], static fn ($value) => $value !== null && $value !== '');
    }

    public function formatSchedulePreviewResponse(array $preview): array
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

    public function formatTeacherConflicts($conflicts): array
    {
        return collect($conflicts)
            ->values()
            ->map(function (Course $course): array {
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

    public function formatRoomConflicts($conflicts): array
    {
        return collect($conflicts)
            ->values()
            ->map(function (ClassRoom $classRoom): array {
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

    public function formatStudentConflicts($conflicts): array
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

    public function resolveStartDate(Course $course, ?ClassRoom $classRoom, mixed $startDate): ?string
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

    public function resolveEndDate(Course $course, ?ClassRoom $classRoom, mixed $endDate, ?string $startDate): ?string
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

    public function buildScheduleLabel(array $meetingDays, string $startTime, string $endTime, ?string $startDate, ?string $endDate): string
    {
        $segments = [];

        if ($meetingDays !== []) {
            $segments[] = implode(', ', array_map(function (string $day): string {
                return Course::dayOptions()[$day] ?? $day;
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
}
