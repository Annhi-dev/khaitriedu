<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminClassRoomManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_class_with_required_course_teacher_room_and_schedule(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $room = $this->createRoom();
        [, $subject] = $this->createCatalogSubject();
        $course = $this->createCourse($subject, $teacher, [
            'title' => 'Tin hoc van phong - Khóa học 2',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.classes.store'), [
                'subject_id' => $subject->id,
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
                'room_id' => $room->id,
                'start_date' => '2026-05-01',
                'note' => 'Lop moi tao tu Phase 2',
                'schedules' => [
                    [
                        'day' => 'Monday',
                        'start' => '18:00',
                        'end' => '20:15',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.classes.index'));
        $response->assertSessionHas('status');

        $classRoom = ClassRoom::query()->first();

        $this->assertNotNull($classRoom);
        $this->assertDatabaseHas('lop_hoc', [
            'id' => $classRoom->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        $this->assertDatabaseHas('lich_hoc', [
            'lop_hoc_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:15',
        ]);
    }

    public function test_class_creation_requires_course_teacher_room_and_schedule(): void
    {
        $admin = User::factory()->admin()->create();
        [, $subject] = $this->createCatalogSubject();

        $response = $this
            ->from(route('admin.classes.create'))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.classes.store'), [
                'subject_id' => $subject->id,
                'course_id' => '',
                'teacher_id' => '',
                'room_id' => '',
                'schedules' => [],
            ]);

        $response->assertRedirect(route('admin.classes.create'));
        $response->assertSessionHasErrors(['course_id', 'teacher_id', 'room_id', 'schedules']);
    }

    public function test_admin_cannot_create_class_when_course_does_not_belong_to_subject(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $room = $this->createRoom();
        [, $subjectA] = $this->createCatalogSubject('Tin hoc A', 'tin-hoc-a');
        [, $subjectB] = $this->createCatalogSubject('Tin hoc B', 'tin-hoc-b');
        $courseA = $this->createCourse($subjectA, $teacher);

        $response = $this
            ->from(route('admin.classes.create'))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.classes.store'), [
                'subject_id' => $subjectB->id,
                'course_id' => $courseA->id,
                'teacher_id' => $teacher->id,
                'room_id' => $room->id,
                'schedules' => [
                    [
                        'day' => 'Tuesday',
                        'start' => '17:00',
                        'end' => '19:00',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.classes.create'));
        $response->assertSessionHasErrors('course_id');
        $this->assertDatabaseCount('lop_hoc', 0);
    }

    public function test_admin_cannot_create_class_with_inactive_room(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $course = $this->createCourse($subject, $teacher);
        $room = $this->createRoom([
            'status' => Room::STATUS_MAINTENANCE,
        ]);

        $response = $this
            ->from(route('admin.classes.create'))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.classes.store'), [
                'subject_id' => $subject->id,
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
                'room_id' => $room->id,
                'schedules' => [
                    [
                        'day' => 'Wednesday',
                        'start' => '18:00',
                        'end' => '20:00',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.classes.create'));
        $response->assertSessionHasErrors('room_id');
        $this->assertDatabaseCount('lop_hoc', 0);
    }

    public function test_admin_cannot_create_class_with_non_teacher_account(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();
        $course = $this->createCourse($subject, null);
        $room = $this->createRoom();

        $response = $this
            ->from(route('admin.classes.create'))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.classes.store'), [
                'subject_id' => $subject->id,
                'course_id' => $course->id,
                'teacher_id' => $student->id,
                'room_id' => $room->id,
                'schedules' => [
                    [
                        'day' => 'Thursday',
                        'start' => '18:00',
                        'end' => '20:00',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.classes.create'));
        $response->assertSessionHasErrors('teacher_id');
        $this->assertDatabaseCount('lop_hoc', 0);
    }

    public function test_admin_cannot_create_class_when_teacher_has_schedule_conflict(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $course = $this->createCourse($subject, $teacher, ['title' => 'Khóa mới']);
        $roomA = $this->createRoom(['code' => 'PA01']);
        $roomB = $this->createRoom(['code' => 'PB01']);

        $existingClass = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $this->createCourse($subject, $teacher, ['title' => 'Khóa hiện hữu'])->id,
            'room_id' => $roomA->id,
            'teacher_id' => $teacher->id,
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        ClassSchedule::create([
            'lop_hoc_id' => $existingClass->id,
            'teacher_id' => $teacher->id,
            'room_id' => $roomA->id,
            'day_of_week' => 'Friday',
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);

        $response = $this
            ->from(route('admin.classes.create'))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.classes.store'), [
                'subject_id' => $subject->id,
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
                'room_id' => $roomB->id,
                'schedules' => [
                    [
                        'day' => 'Friday',
                        'start' => '19:00',
                        'end' => '21:00',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.classes.create'));
        $response->assertSessionHasErrors('teacher_id');
        $this->assertDatabaseCount('lop_hoc', 1);
    }

    public function test_admin_can_create_class_when_weekly_slot_matches_but_date_ranges_do_not_overlap(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $room = $this->createRoom();
        [, $subject] = $this->createCatalogSubject('Tin hoc van phong', 'tin-hoc-van-phong', 1);
        $firstCourse = $this->createCourse($subject, $teacher, ['title' => 'Khóa hiện hữu']);
        $secondCourse = $this->createCourse($subject, $teacher, ['title' => 'Khóa kế tiếp']);

        $existingClassRoom = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $firstCourse->id,
            'room_id' => $room->id,
            'teacher_id' => $teacher->id,
            'start_date' => '2026-05-01',
            'duration' => 1,
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        ClassSchedule::create([
            'lop_hoc_id' => $existingClassRoom->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);

        $response = $this
            ->from(route('admin.classes.create'))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.classes.store'), [
                'subject_id' => $subject->id,
                'course_id' => $secondCourse->id,
                'teacher_id' => $teacher->id,
                'room_id' => $room->id,
                'start_date' => '2026-07-01',
                'schedules' => [
                    [
                        'day' => 'Monday',
                        'start' => '18:00',
                        'end' => '20:00',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.classes.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseCount('lop_hoc', 2);
    }

    private function createCatalogSubject(string $subjectName = 'Tin hoc van phong', string $slug = 'tin-hoc-van-phong', int $duration = 24): array
    {
        $category = Category::create([
            'name' => 'Nhom hoc ' . $slug,
            'slug' => 'nhom-hoc-' . $slug . '-' . fake()->unique()->numberBetween(100, 999),
            'status' => Category::STATUS_ACTIVE,
        ]);

        $subject = Subject::create([
            'name' => $subjectName,
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'price' => 1500000,
            'duration' => $duration,
        ]);

        return [$category, $subject];
    }

    private function createCourse(Subject $subject, ?User $teacher, array $overrides = []): Course
    {
        return Course::create(array_merge([
            'subject_id' => $subject->id,
            'title' => $subject->name . ' - Khóa học',
            'description' => 'Khóa học phục vụ test tạo lớp.',
            'teacher_id' => $teacher?->id,
            'status' => Course::STATUS_DRAFT,
        ], $overrides));
    }

    private function createRoom(array $overrides = []): Room
    {
        $code = $overrides['code'] ?? 'P' . fake()->unique()->numberBetween(100, 999);

        return Room::create(array_merge([
            'code' => $code,
            'name' => 'Phong ' . $code,
            'type' => 'offline',
            'location' => 'Tang 2',
            'capacity' => 20,
            'status' => Room::STATUS_ACTIVE,
        ], $overrides));
    }
}
