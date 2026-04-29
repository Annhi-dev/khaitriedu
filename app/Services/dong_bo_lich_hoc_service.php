<?php

namespace App\Services;

use App\Helpers\ScheduleHelper;
use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\PhongHoc;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseScheduleSyncService
{
    public function syncCourses(Collection|array $courses): array
    {
        $courses = collect($courses)->values();

        $report = [
            'courses_processed' => 0,
            'courses_updated' => 0,
            'classrooms_created' => 0,
            'classrooms_updated' => 0,
            'schedules_created' => 0,
            'schedules_updated' => 0,
        ];

        if ($courses->isEmpty()) {
            return $report;
        }

        $rooms = PhongHoc::query()
            ->where('status', PhongHoc::STATUS_ACTIVE)
            ->orderBy('id')
            ->get();

        foreach ($courses as $index => $courseItem) {
            $course = $courseItem instanceof KhoaHoc
                ? $courseItem->loadMissing(['subject.category', 'classRooms.schedules'])
                : KhoaHoc::query()->with(['subject.category', 'classRooms.schedules'])->findOrFail($courseItem);

            if (! $course instanceof KhoaHoc) {
                continue;
            }

            $result = DB::transaction(fn () => $this->syncCourse($course, $rooms, (int) $index));

            foreach ($result as $key => $value) {
                $report[$key] += $value;
            }
        }

        return $report;
    }

    protected function syncCourse(KhoaHoc $course, Collection $rooms, int $index): array
    {
        $courseUpdated = false;
        $classRoom = $course->classRooms()
            ->whereNotIn('status', [LopHoc::STATUS_CLOSED, LopHoc::STATUS_COMPLETED])
            ->orderByDesc('id')
            ->first();

        $placement = $this->resolvePlacement($course, $rooms, $index, $classRoom?->id);
        $template = $placement['template'];
        $startDate = $placement['startDate'];
        $endDate = $placement['endDate'];
        $room = $placement['room'];

        $course->fill([
            'day_of_week' => $template['days'][0],
            'meeting_days' => $template['days'],
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'start_time' => $template['start_time'],
            'end_time' => $template['end_time'],
            'schedule' => $this->buildScheduleLabel($template, $startDate, $endDate),
        ]);

        if ($course->isDirty()) {
            $course->save();
            $courseUpdated = true;
        }
        $classRoomCreated = false;

        $classRoomPayload = [
            'subject_id' => $course->subject_id,
            'course_id' => $course->id,
            'name' => $course->title,
            'room_id' => $room?->id,
            'teacher_id' => $course->teacher_id,
            'start_date' => $startDate->toDateString(),
            'duration' => $course->subject?->duration,
            'status' => LopHoc::STATUS_OPEN,
        ];

        if (! $classRoom) {
            $classRoom = $course->classRooms()->create($classRoomPayload);
            $classRoomCreated = true;
        } else {
            $classRoom->fill($classRoomPayload);

            if ($classRoom->isDirty()) {
                $classRoom->save();
            }
        }

        [$schedulesCreated, $schedulesUpdated] = $this->syncClassSchedules(
            $classRoom,
            $template['days'],
            (string) $template['start_time'],
            (string) $template['end_time'],
            $course->teacher_id ? (int) $course->teacher_id : null,
            $room?->id,
        );

        return [
            'courses_processed' => 1,
            'courses_updated' => $courseUpdated ? 1 : 0,
            'classrooms_created' => $classRoomCreated ? 1 : 0,
            'classrooms_updated' => $classRoomCreated ? 0 : 1,
            'schedules_created' => $schedulesCreated,
            'schedules_updated' => $schedulesUpdated,
        ];
    }

    protected function syncClassSchedules(
        LopHoc $classRoom,
        array $days,
        string $startTime,
        string $endTime,
        ?int $teacherId,
        ?int $roomId
    ): array {
        $schedulesCreated = 0;
        $schedulesUpdated = 0;

        $existingSchedules = $classRoom->schedules()
            ->lockForUpdate()
            ->get()
            ->keyBy('day_of_week');

        $normalizedDays = array_values(array_unique($days));

        foreach ($normalizedDays as $dayOfWeek) {
            $schedule = $existingSchedules->get($dayOfWeek);

            if ($schedule) {
                $schedule->fill([
                    'teacher_id' => $teacherId,
                    'room_id' => $roomId,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);

                if ($schedule->isDirty()) {
                    $schedule->save();
                    $schedulesUpdated++;
                }

                continue;
            }

            LichHoc::query()->create([
                'lop_hoc_id' => $classRoom->id,
                'teacher_id' => $teacherId,
                'room_id' => $roomId,
                'day_of_week' => $dayOfWeek,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);

            $schedulesCreated++;
        }

        $classRoom->schedules()
            ->whereNotIn('day_of_week', $normalizedDays)
            ->delete();

        return [$schedulesCreated, $schedulesUpdated];
    }

    protected function resolvePlacement(KhoaHoc $course, Collection $rooms, int $index, ?int $excludeClassRoomId = null): array
    {
        $templateCandidates = $this->resolveTemplateCandidates($course);
        $roomCandidates = $this->resolveRoomCandidates($rooms, $course, $index);
        $weekOffsets = collect(range(0, 16))
            ->merge(range(18, 52, 2))
            ->values()
            ->all();

        foreach ($weekOffsets as $weekOffset) {
            foreach ($templateCandidates as $template) {
                [$startDate, $endDate] = $this->resolveDateRange($course, $template, $index, $weekOffset);

                foreach ($roomCandidates as $room) {
                    if ($this->placementHasConflict(
                        $course,
                        $template['days'],
                        (string) $template['start_time'],
                        (string) $template['end_time'],
                        $room?->id,
                        $startDate,
                        $endDate,
                        $excludeClassRoomId
                    )) {
                        continue;
                    }

                    return [
                        'template' => $template,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'room' => $room,
                    ];
                }
            }
        }

        $fallbackTemplate = $templateCandidates[0] ?? $this->resolveTemplate($course);
        [$startDate, $endDate] = $this->resolveDateRange($course, $fallbackTemplate, $index);
        $room = $roomCandidates->first() ?: $this->resolveRoom($rooms, $course, $index);

        return [
            'template' => $fallbackTemplate,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'room' => $room,
        ];
    }

    protected function resolveTemplateCandidates(KhoaHoc $course): array
    {
        $templates = array_merge(
            [$this->resolveTemplate($course)],
            $this->templatePool()
        );

        return collect($templates)
            ->filter(function (array $template) {
                return isset($template['days'], $template['start_time'], $template['end_time'])
                    && is_array($template['days'])
                    && $template['days'] !== [];
            })
            ->unique(function (array $template) {
                return implode('|', [
                    $template['prefix'] ?? '',
                    implode(',', $template['days']),
                    $template['start_time'],
                    $template['end_time'],
                ]);
            })
            ->values()
            ->all();
    }

    protected function templatePool(): array
    {
        return [
            [
                'prefix' => 'Tối',
                'days' => ['Monday', 'Wednesday', 'Friday'],
                'start_time' => '18:00',
                'end_time' => ScheduleHelper::normalizeEndTime('18:00'),
            ],
            [
                'prefix' => 'Tối',
                'days' => ['Tuesday', 'Thursday', 'Saturday'],
                'start_time' => '18:00',
                'end_time' => ScheduleHelper::normalizeEndTime('18:00'),
            ],
            [
                'prefix' => 'Tối',
                'days' => ['Monday', 'Thursday', 'Saturday'],
                'start_time' => '19:00',
                'end_time' => ScheduleHelper::normalizeEndTime('19:00'),
            ],
            [
                'prefix' => 'Tối',
                'days' => ['Tuesday', 'Friday', 'Sunday'],
                'start_time' => '19:00',
                'end_time' => ScheduleHelper::normalizeEndTime('19:00'),
            ],
            [
                'prefix' => 'Tối',
                'days' => ['Wednesday', 'Friday'],
                'start_time' => '19:00',
                'end_time' => ScheduleHelper::normalizeEndTime('19:00'),
            ],
            [
                'prefix' => 'Tối',
                'days' => ['Tuesday', 'Thursday'],
                'start_time' => '19:00',
                'end_time' => ScheduleHelper::normalizeEndTime('19:00'),
            ],
            [
                'prefix' => 'Chiều',
                'days' => ['Saturday'],
                'start_time' => '13:30',
                'end_time' => ScheduleHelper::normalizeEndTime('13:30'),
            ],
            [
                'prefix' => 'Sáng',
                'days' => ['Sunday'],
                'start_time' => '08:00',
                'end_time' => ScheduleHelper::normalizeEndTime('08:00'),
            ],
        ];
    }

    protected function resolveRoomCandidates(Collection $rooms, KhoaHoc $course, int $index): Collection
    {
        if ($rooms->isEmpty()) {
            return collect([null]);
        }

        $ordered = collect();

        if ($course->classRooms->isNotEmpty()) {
            $existingRoomId = $course->classRooms->first()?->room_id;

            if ($existingRoomId) {
                $existingRoom = $rooms->firstWhere('id', (int) $existingRoomId);
                if ($existingRoom) {
                    $ordered->push($existingRoom);
                }
            }
        }

        $offset = $rooms->count() > 0 ? $index % $rooms->count() : 0;
        $rotatedRooms = $rooms->slice($offset)->concat($rooms->take($offset))->values();

        foreach ($rotatedRooms as $room) {
            $ordered->push($room);
        }

        return $ordered
            ->filter(fn ($room) => $room !== null)
            ->unique('id')
            ->values();
    }

    protected function placementHasConflict(
        KhoaHoc $course,
        array $days,
        string $startTime,
        string $endTime,
        ?int $roomId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeClassRoomId = null
    ): bool {
        $teacherId = $course->teacher_id ? (int) $course->teacher_id : null;

        if ($teacherId && LopHoc::teacherHasConflict(
            $teacherId,
            $days,
            $startTime,
            $endTime,
            $startDate->toDateString(),
            $endDate->toDateString(),
            $excludeClassRoomId
        )) {
            return true;
        }

        if ($roomId && LopHoc::roomHasConflict(
            $roomId,
            $days,
            $startTime,
            $endTime,
            $startDate->toDateString(),
            $endDate->toDateString(),
            $excludeClassRoomId
        )) {
            return true;
        }

        return false;
    }

    protected function resolveRoom(Collection $rooms, KhoaHoc $course, int $index): ?PhongHoc
    {
        if ($course->classRooms->isNotEmpty()) {
            $existingRoomId = $course->classRooms->first()?->room_id;

            if ($existingRoomId) {
                $existingRoom = $rooms->firstWhere('id', (int) $existingRoomId);
                if ($existingRoom) {
                    return $existingRoom;
                }
            }
        }

        if ($rooms->isEmpty()) {
            return null;
        }

        return $rooms->values()->get($index % $rooms->count());
    }

    protected function resolveDateRange(KhoaHoc $course, array $template, int $index, int $weekOffset = 0): array
    {
        $firstDayIndex = array_search($template['days'][0], array_keys(LichHoc::$dayOptions), true);
        $baseStart = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->subWeeks(2 + intdiv($index, 4));
        $baseStart = $baseStart->addWeeks($weekOffset);

        if ($firstDayIndex !== false) {
            $baseStart = $baseStart->addDays($firstDayIndex);
        }

        $durationMonths = max(1, (int) ($course->subject?->duration ?? 3));
        $endDate = $baseStart->copy()->addMonths($durationMonths);

        return [$baseStart, $endDate];
    }

    protected function buildScheduleLabel(array $template, Carbon $startDate, Carbon $endDate): string
    {
        $dayLabels = collect($template['days'])
            ->map(fn (string $day) => LichHoc::$dayOptions[$day] ?? $day)
            ->implode(', ');

        return trim(
            ($template['prefix'] ? $template['prefix'] . ' ' : '')
            . $dayLabels
            . ', ' . $template['start_time'] . ' - ' . $template['end_time']
            . ' | Từ ' . $startDate->format('d/m/Y')
            . ' đến ' . $endDate->format('d/m/Y')
        );
    }

    protected function resolveTemplate(KhoaHoc $course): array
    {
        $title = Str::upper((string) $course->title);
        $categoryName = Str::upper((string) ($course->subject?->category?->name ?? ''));

        if (Str::contains($title, ['ANH VĂN', 'TIẾNG ANH', 'TIN HỌC', 'CÔNG NGHỆ THÔNG TIN']) || Str::contains($categoryName, 'NGOẠI NGỮ')) {
            return [
                'prefix' => 'Tối',
                'days' => ['Monday', 'Wednesday', 'Friday'],
                'start_time' => '18:00',
                'end_time' => ScheduleHelper::normalizeEndTime('18:00'),
            ];
        }

        if (Str::contains($categoryName, 'ĐÀO TẠO DÀI HẠN') || Str::contains($title, ['TRUNG CẤP', 'CAO ĐẲNG', 'ĐẠI HỌC', 'THẠC SĨ'])) {
            return [
                'prefix' => 'Tối',
                'days' => ['Tuesday', 'Thursday'],
                'start_time' => '19:00',
                'end_time' => ScheduleHelper::normalizeEndTime('19:00'),
            ];
        }

        if (Str::contains($categoryName, 'ĐÀO TẠO NGHỀ') || Str::contains($title, ['ĐIỆN', 'MÁY', 'KỸ THUẬT', 'PHA CHẾ', 'CHẾ BIẾN', 'CHĂM SÓC', 'THIẾT KẾ'])) {
            return [
                'prefix' => 'Tối',
                'days' => ['Wednesday', 'Friday', 'Sunday'],
                'start_time' => '19:00',
                'end_time' => ScheduleHelper::normalizeEndTime('19:00'),
            ];
        }

        return [
            'prefix' => 'Tối',
            'days' => ['Tuesday', 'Thursday', 'Saturday'],
            'start_time' => '19:00',
            'end_time' => ScheduleHelper::normalizeEndTime('19:00'),
        ];
    }
}
