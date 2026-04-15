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

class kiem_tra_xung_dot_lich_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_detect_teacher_and_room_conflicts_for_manual_schedule(): void
    {
        $admin = User::factory()->admin()->create();
        $teacherOne = User::factory()->teacher()->create();
        $teacherTwo = User::factory()->teacher()->create();
        $roomOne = $this->createRoom('PH001');
        $roomTwo = $this->createRoom('PH002');
        [, $subject] = $this->createSubject();

        $this->createPendingOpenCourse($subject, $teacherOne);
        $roomConflictClass = $this->createClassRoom($subject, $teacherTwo, $roomOne);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.schedules.conflicts', [
                'teacher_id' => $teacherOne->id,
                'room_id' => $roomOne->id,
                'day_of_week' => ['Monday'],
                'start_date' => '2026-05-10',
                'end_date' => '2026-06-10',
                'start_time' => '18:00',
                'end_time' => '20:00',
            ]));

        $response->assertOk();
        $response->assertSee('Có xung đột');
        $response->assertSee('Xung đột giảng viên');
        $response->assertSee('Xung đột phòng học');
        $response->assertSee('Khóa chờ mở trùng giờ');
        $response->assertSee($roomConflictClass->displayName());
    }

    public function test_admin_can_open_conflict_checker_from_existing_class_without_self_conflict(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $room = $this->createRoom('PH010');
        [, $subject] = $this->createSubject('Tin hoc van phong', 'tin-hoc-van-phong');
        $classRoom = $this->createClassRoom($subject, $teacher, $room, 'Khóa hiện hành');

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.schedules.conflicts', [
                'class_room_id' => $classRoom->id,
            ]));

        $response->assertOk();
        $response->assertSee('Không phát hiện xung đột');
        $response->assertSee('Lớp: ' . $classRoom->displayName());
        $response->assertDontSee('Có xung đột');
    }

    private function createSubject(string $name = 'ANH VĂN KHUNG 6 BẬC', string $slug = 'anh-van-khung-6-bac'): array
    {
        $category = Category::create([
            'name' => 'Ngoại ngữ - Tin học',
            'slug' => $slug . '-group-' . fake()->unique()->numberBetween(100, 999),
            'status' => Category::STATUS_ACTIVE,
        ]);

        $subject = Subject::create([
            'name' => $name,
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'price' => 2500000,
            'duration' => 3,
        ]);

        return [$category, $subject];
    }

    private function createPendingOpenCourse(Subject $subject, User $teacher): Course
    {
        return Course::create([
            'subject_id' => $subject->id,
            'title' => 'Khóa chờ mở trùng giờ',
            'description' => 'Khóa demo chờ mở để test xung đột giảng viên.',
            'teacher_id' => $teacher->id,
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday'],
            'start_time' => '18:00',
            'end_time' => '20:00',
            'status' => Course::STATUS_PENDING_OPEN,
        ]);
    }

    private function createClassRoom(Subject $subject, User $teacher, Room $room, string $title = 'Khóa đã mở trùng phòng'): ClassRoom
    {
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => $title,
            'description' => 'Khóa demo lớp hiện hành.',
            'teacher_id' => $teacher->id,
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday'],
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'status' => Course::STATUS_ACTIVE,
        ]);

        $classRoom = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'name' => $course->title,
            'room_id' => $room->id,
            'teacher_id' => $teacher->id,
            'start_date' => '2026-05-01',
            'duration' => 3,
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        ClassSchedule::create([
            'lop_hoc_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);

        return $classRoom;
    }

    private function createRoom(string $code): Room
    {
        return Room::create([
            'code' => $code,
            'name' => 'Phòng ' . $code,
            'type' => 'offline',
            'location' => 'Tầng 2',
            'capacity' => 30,
            'status' => Room::STATUS_ACTIVE,
        ]);
    }
}

