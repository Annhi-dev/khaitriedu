<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use App\Services\CourseScheduleSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class dong_bo_lich_hoc_test extends TestCase
{
    use RefreshDatabase;

    public function test_sync_service_fills_missing_demo_schedule_and_is_idempotent(): void
    {
        $teacher = User::factory()->teacher()->create();
        Room::create([
            'code' => 'PH001',
            'name' => 'Phòng demo 1',
            'type' => 'offline',
            'location' => 'Tầng 2',
            'capacity' => 30,
            'status' => Room::STATUS_ACTIVE,
        ]);
        Room::create([
            'code' => 'PH002',
            'name' => 'Phòng demo 2',
            'type' => 'offline',
            'location' => 'Tầng 3',
            'capacity' => 30,
            'status' => Room::STATUS_ACTIVE,
        ]);

        $englishCategory = Category::create([
            'name' => 'Ngoại ngữ - Tin học',
            'slug' => 'ngoai-ngu-tin-hoc',
            'status' => Category::STATUS_ACTIVE,
        ]);
        $longTermCategory = Category::create([
            'name' => 'Đào tạo dài hạn',
            'slug' => 'dao-tao-dai-han',
            'status' => Category::STATUS_ACTIVE,
        ]);

        $englishSubject = Subject::create([
            'name' => 'ANH VĂN KHUNG 6 BẬC',
            'category_id' => $englishCategory->id,
            'status' => Subject::STATUS_OPEN,
            'duration' => 3,
            'price' => 2000000,
        ]);
        $longTermSubject = Subject::create([
            'name' => 'THẠC SĨ QTKD',
            'category_id' => $longTermCategory->id,
            'status' => Subject::STATUS_OPEN,
            'duration' => 6,
            'price' => 6000000,
        ]);

        $englishCourse = Course::create([
            'subject_id' => $englishSubject->id,
            'title' => 'Khóa 26 - ANH VĂN KHUNG 6 BẬC',
            'description' => 'Khóa demo tiếng Anh.',
            'teacher_id' => $teacher->id,
            'status' => Course::STATUS_ACTIVE,
        ]);
        $longTermCourse = Course::create([
            'subject_id' => $longTermSubject->id,
            'title' => 'Khóa 26 - THẠC SĨ QTKD',
            'description' => 'Khóa demo dài hạn.',
            'teacher_id' => $teacher->id,
            'status' => Course::STATUS_ACTIVE,
        ]);

        $service = app(CourseScheduleSyncService::class);
        $report = $service->syncCourses(collect([$englishCourse, $longTermCourse]));

        $this->assertSame(2, $report['courses_processed']);
        $this->assertSame(2, $report['courses_updated']);
        $this->assertSame(2, $report['classrooms_created']);
        $this->assertSame(0, $report['classrooms_updated']);
        $this->assertSame(5, $report['schedules_created']);
        $this->assertSame(0, $report['schedules_updated']);

        $englishCourse->refresh()->load('classRooms.schedules');
        $longTermCourse->refresh()->load('classRooms.schedules');

        $this->assertSame(['Monday', 'Wednesday', 'Friday'], $englishCourse->meeting_days);
        $this->assertSame('18:00', $englishCourse->start_time);
        $this->assertSame('20:15', $englishCourse->end_time);
        $this->assertStringContainsString('Từ', $englishCourse->schedule);
        $this->assertCount(1, $englishCourse->classRooms);
        $this->assertCount(3, $englishCourse->classRooms->first()->schedules);

        $this->assertSame(['Tuesday', 'Thursday'], $longTermCourse->meeting_days);
        $this->assertSame('19:00', $longTermCourse->start_time);
        $this->assertSame('21:15', $longTermCourse->end_time);
        $this->assertStringContainsString('Từ', $longTermCourse->schedule);
        $this->assertCount(1, $longTermCourse->classRooms);
        $this->assertCount(2, $longTermCourse->classRooms->first()->schedules);

        $this->assertNoClassRoomConflicts(
            ClassRoom::query()->with(['schedules', 'course.subject'])->get()
        );

        $classRoomCount = ClassRoom::count();
        $scheduleCount = ClassSchedule::count();

        $service->syncCourses(collect([$englishCourse->fresh(['subject.category', 'classRooms.schedules']), $longTermCourse->fresh(['subject.category', 'classRooms.schedules'])]));

        $this->assertSame($classRoomCount, ClassRoom::count());
        $this->assertSame($scheduleCount, ClassSchedule::count());
    }

    private function assertNoClassRoomConflicts(Collection $classRooms): void
    {
        $classRooms = $classRooms->values();

        for ($i = 0; $i < $classRooms->count(); $i++) {
            for ($j = $i + 1; $j < $classRooms->count(); $j++) {
                $first = $classRooms->get($i);
                $second = $classRooms->get($j);

                $this->assertFalse(
                    $this->classRoomsConflict($first, $second),
                    sprintf('Lớp %d và lớp %d đang trùng lịch, phòng hoặc giảng viên.', $first->id, $second->id)
                );
            }
        }
    }

    private function classRoomsConflict(ClassRoom $first, ClassRoom $second): bool
    {
        if (! $this->weeklySchedulesOverlap($first, $second)) {
            return false;
        }

        if (! $this->dateRangesOverlap($first, $second)) {
            return false;
        }

        return $first->teacher_id === $second->teacher_id
            || $first->room_id === $second->room_id;
    }

    private function weeklySchedulesOverlap(ClassRoom $first, ClassRoom $second): bool
    {
        return $first->schedules->contains(function (ClassSchedule $firstSchedule) use ($second): bool {
            return $second->schedules->contains(function (ClassSchedule $secondSchedule) use ($firstSchedule): bool {
                if ($firstSchedule->day_of_week !== $secondSchedule->day_of_week) {
                    return false;
                }

                return $firstSchedule->start_time < $secondSchedule->end_time
                    && $firstSchedule->end_time > $secondSchedule->start_time;
            });
        });
    }

    private function dateRangesOverlap(ClassRoom $first, ClassRoom $second): bool
    {
        $this->assertNotNull($first->start_date);
        $this->assertNotNull($second->start_date);

        $firstStart = $first->start_date->copy()->startOfDay();
        $firstEnd = $firstStart->copy()->addMonths(max(1, (int) ($first->duration ?? $first->course?->subject?->duration ?? 1)));
        $secondStart = $second->start_date->copy()->startOfDay();
        $secondEnd = $secondStart->copy()->addMonths(max(1, (int) ($second->duration ?? $second->course?->subject?->duration ?? 1)));

        return $firstStart->lte($secondEnd) && $firstEnd->gte($secondStart);
    }
}

