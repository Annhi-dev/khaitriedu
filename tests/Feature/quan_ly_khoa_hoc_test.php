<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use App\Helpers\ScheduleHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class quan_ly_khoa_hoc_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_course_with_existing_subject_id(): void
    {
        $admin = User::factory()->admin()->create();
        $category = $this->createCategory('Tin hoc', 'tin-hoc');
        $subject = $this->createSubject($category, [
            'name' => 'Tin hoc van phong',
            'description' => 'Khung co san',
            'price' => 1500000,
            'duration' => 24,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.create'), [
                'category_id' => $category->id,
                'subject_id' => $subject->id,
                'title' => 'Khóa 27 - Tin hoc van phong',
                'description' => 'Khoa hoc noi bo',
                'price' => 4200000,
                'return_to_category_id' => $category->id,
            ]);

        $response->assertRedirect(route('admin.categories.show', $category));
        $this->assertDatabaseCount('mon_hoc', 1);
        $this->assertDatabaseHas('khoa_hoc', [
            'subject_id' => $subject->id,
            'title' => 'Khóa 27 - Tin hoc van phong',
            'price' => 4200000,
        ]);
    }

    public function test_admin_can_create_course_by_typing_new_subject_name(): void
    {
        $admin = User::factory()->admin()->create();
        $category = $this->createCategory('Ngoai ngu', 'ngoai-ngu');

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.create'), [
                'category_id' => $category->id,
                'subject_name' => 'Lap trinh Python co ban',
                'subject_duration' => 18,
                'title' => 'Khóa 27 - Lap trinh Python co ban',
                'description' => 'Chuong trinh moi',
                'price' => 5500000,
                'return_to_category_id' => $category->id,
            ]);

        $response->assertRedirect(route('admin.categories.show', $category));

        $subject = Subject::query()->where('name', 'Lap trinh Python co ban')->first();

        $this->assertNotNull($subject);
        $this->assertDatabaseHas('mon_hoc', [
            'id' => $subject->id,
            'category_id' => $category->id,
            'price' => 5500000,
            'duration' => 18,
            'status' => Subject::STATUS_OPEN,
        ]);
        $this->assertDatabaseHas('khoa_hoc', [
            'subject_id' => $subject->id,
            'title' => 'Khóa 27 - Lap trinh Python co ban',
        ]);
    }

    public function test_admin_reuses_existing_subject_when_typing_matching_name(): void
    {
        $admin = User::factory()->admin()->create();
        $category = $this->createCategory('Ky nang', 'ky-nang');
        $subject = $this->createSubject($category, [
            'name' => 'Tin hoc van phong',
            'description' => 'Khung da ton tai',
            'price' => 1600000,
            'duration' => 24,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.create'), [
                'category_id' => $category->id,
                'subject_name' => 'Tin hoc van phong',
                'subject_duration' => 24,
                'title' => 'Khóa 28 - Tin hoc van phong',
                'description' => 'Mo ta moi',
                'price' => 6000000,
                'return_to_category_id' => $category->id,
            ]);

        $response->assertRedirect(route('admin.categories.show', $category));
        $this->assertDatabaseCount('mon_hoc', 1);
        $this->assertDatabaseHas('khoa_hoc', [
            'subject_id' => $subject->id,
            'title' => 'Khóa 28 - Tin hoc van phong',
            'price' => 6000000,
        ]);
    }

    public function test_updating_course_syncs_related_subject_references(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $categoryA = $this->createCategory('Tin hoc', 'tin-hoc');
        $categoryB = $this->createCategory('Ngoai ngu', 'ngoai-ngu');
        $subjectA = $this->createSubject($categoryA, ['name' => 'Tin hoc van phong']);
        $subjectB = $this->createSubject($categoryB, ['name' => 'Tieng Anh giao tiep']);

        $course = Course::create([
            'subject_id' => $subjectA->id,
            'title' => 'Khoa 27 - Tin hoc van phong',
            'description' => 'Mo ta cu',
            'price' => 2500000,
        ]);

        $autoNamedClass = ClassRoom::create([
            'subject_id' => $subjectA->id,
            'course_id' => $course->id,
            'name' => 'Khoa 27 - Tin hoc van phong',
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        $customNamedClass = ClassRoom::create([
            'subject_id' => $subjectA->id,
            'course_id' => $course->id,
            'name' => 'Lop doanh nghiep',
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subjectA->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_PENDING,
            'is_submitted' => true,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.update', $course), [
                'title' => 'Khoa 27 - Tieng Anh giao tiep',
                'description' => 'Mo ta moi',
                'price' => 3100000,
                'subject_id' => $subjectB->id,
            ]);

        $response->assertRedirect(route('admin.course.show', $course));

        $this->assertDatabaseHas('khoa_hoc', [
            'id' => $course->id,
            'subject_id' => $subjectB->id,
            'title' => 'Khoa 27 - Tieng Anh giao tiep',
        ]);

        $this->assertDatabaseHas('lop_hoc', [
            'id' => $autoNamedClass->id,
            'subject_id' => $subjectB->id,
            'name' => 'Khoa 27 - Tieng Anh giao tiep',
        ]);

        $this->assertDatabaseHas('lop_hoc', [
            'id' => $customNamedClass->id,
            'subject_id' => $subjectB->id,
            'name' => 'Lop doanh nghiep',
        ]);

        $this->assertDatabaseHas('dang_ky', [
            'id' => $enrollment->id,
            'subject_id' => $subjectB->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_admin_can_update_course_schedule_with_separate_day_and_time_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create(['name' => 'Teacher Schedule']);
        $category = $this->createCategory('Ngoai ngu', 'ngoai-ngu');
        $subject = $this->createSubject($category, ['name' => 'Tieng Anh giao tiep', 'duration' => 12]);
        $room = $this->createRoom(['name' => 'Phong 305', 'code' => 'P305']);

        $course = $this->createInternalCourse($subject, $teacher, [
            'title' => 'Khoa 27 - Tieng Anh giao tiep',
            'schedule' => 'Thứ 2, 18:00 - 20:00 | Từ 01/04/2026 đến 01/05/2026',
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday'],
            'start_date' => '2026-04-01',
            'end_date' => '2026-05-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'status' => Course::STATUS_SCHEDULED,
        ]);

        $classRoom = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'name' => 'Lop Tieng Anh giao tiep',
            'start_date' => '2026-04-01',
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

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.update', $course), [
                'title' => 'Khoa 27 - Tieng Anh giao tiep',
                'description' => 'Mo ta moi',
                'price' => 3100000,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'meeting_days' => ['Wednesday', 'Friday'],
                'start_date' => '2026-04-03',
                'end_date' => '2026-05-03',
                'start_time' => '19:00',
                'end_time' => '21:00',
            ]);

        $response->assertRedirect(route('admin.schedules.conflicts', [
            'course_id' => $course->id,
            'class_room_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => ['Wednesday', 'Friday'],
            'start_date' => '2026-04-03',
            'end_date' => '2026-05-03',
            'start_time' => '19:00',
            'end_time' => '21:00',
            'exclude_course_id' => $course->id,
            'exclude_class_room_id' => $classRoom->id,
        ]));

        $course->refresh();
        $classRoom->refresh();

        $this->assertSame('Thứ 4, Thứ 6 | 19:00 - 21:00 | Từ 03/04/2026 đến 03/05/2026', $course->schedule);
        $this->assertSame('Wednesday', $course->day_of_week);
        $this->assertSame(['Wednesday', 'Friday'], $course->meeting_days);
        $this->assertSame('2026-04-03', optional($course->start_date)->format('Y-m-d'));
        $this->assertSame('2026-05-03', optional($course->end_date)->format('Y-m-d'));
        $this->assertSame('19:00', $course->start_time);
        $this->assertSame('21:00', $course->end_time);
        $this->assertDatabaseHas('lop_hoc', [
            'id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
        ]);
        $this->assertDatabaseHas('lich_hoc', [
            'lop_hoc_id' => $classRoom->id,
            'day_of_week' => 'Wednesday',
            'start_time' => '19:00',
            'end_time' => '21:00',
        ]);
        $this->assertDatabaseHas('lich_hoc', [
            'lop_hoc_id' => $classRoom->id,
            'day_of_week' => 'Friday',
            'start_time' => '19:00',
            'end_time' => '21:00',
        ]);
    }

    public function test_admin_cannot_save_course_schedule_when_it_conflicts(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create(['name' => 'Teacher Conflict']);
        $category = $this->createCategory('Ngoai ngu', 'ngoai-ngu');
        $subject = $this->createSubject($category, ['name' => 'Tieng Anh giao tiep', 'duration' => 12]);

        $currentCourse = $this->createInternalCourse($subject, $teacher, [
            'title' => 'Khoa 27 - Tieng Anh giao tiep',
            'schedule' => 'Thứ 2, 18:00 - 20:00 | Từ 01/04/2026 đến 01/05/2026',
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday'],
            'start_date' => '2026-04-01',
            'end_date' => '2026-05-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'status' => Course::STATUS_SCHEDULED,
        ]);

        $this->createInternalCourse($subject, $teacher, [
            'title' => 'Khoa 28 - Tieng Anh giao tiep',
            'schedule' => 'Thứ 4, 18:30 - 20:30 | Từ 01/04/2026 đến 01/05/2026',
            'day_of_week' => 'Wednesday',
            'meeting_days' => ['Wednesday'],
            'start_date' => '2026-04-01',
            'end_date' => '2026-05-01',
            'start_time' => '18:30',
            'end_time' => '20:30',
            'status' => Course::STATUS_SCHEDULED,
        ]);

        $response = $this
            ->from(route('admin.course.show', $currentCourse))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.update', $currentCourse), [
                'title' => $currentCourse->title,
                'description' => $currentCourse->description,
                'price' => 0,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'meeting_days' => ['Wednesday'],
                'start_date' => '2026-04-01',
                'end_date' => '2026-05-01',
                'start_time' => '19:00',
                'end_time' => '21:00',
            ]);

        $response->assertSessionHasErrors(['meeting_days']);

        $followUp = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.course.show', $currentCourse));

        $followUp->assertSee('Dữ liệu chưa hợp lệ');
        $followUp->assertSee('Lưu chưa thành công');

        $currentCourse->refresh();

        $this->assertSame('Thứ 2, 18:00 - 20:00 | Từ 01/04/2026 đến 01/05/2026', $currentCourse->schedule);
        $this->assertSame(['Monday'], $currentCourse->meeting_days);
        $this->assertSame('18:00', $currentCourse->start_time);
        $this->assertSame('20:00', $currentCourse->end_time);
        $this->assertSame('2026-04-01', optional($currentCourse->start_date)->format('Y-m-d'));
        $this->assertSame('2026-05-01', optional($currentCourse->end_date)->format('Y-m-d'));
    }

    public function test_admin_can_preview_course_schedule_conflicts_live(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create(['name' => 'Teacher Preview']);
        $teacherForRoom = User::factory()->teacher()->create(['name' => 'Teacher Room Conflict']);
        $teacherForStudent = User::factory()->teacher()->create(['name' => 'Teacher Student Conflict']);
        $student = User::factory()->student()->create(['name' => 'Student Preview']);
        $category = $this->createCategory('Ngoai ngu', 'ngoai-ngu');
        $subject = $this->createSubject($category, ['name' => 'Tieng Anh giao tiep', 'duration' => 12]);
        $room = $this->createRoom(['name' => 'Phong 306', 'code' => 'P306']);
        $roomForStudent = $this->createRoom(['name' => 'Phong 307', 'code' => 'P307']);

        $currentCourse = $this->createInternalCourse($subject, $teacher, [
            'title' => 'Khoa 27 - Tieng Anh giao tiep',
            'schedule' => 'Thứ 4, 19:00 - 21:00 | Từ 03/04/2026 đến 03/05/2026',
            'day_of_week' => 'Wednesday',
            'meeting_days' => ['Wednesday'],
            'start_date' => '2026-04-03',
            'end_date' => '2026-05-03',
            'start_time' => '19:00',
            'end_time' => '21:00',
            'status' => Course::STATUS_SCHEDULED,
        ]);

        $currentClassRoom = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $currentCourse->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'name' => 'Lop Tieng Anh giao tiep',
            'start_date' => '2026-04-03',
            'duration' => 3,
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        ClassSchedule::create([
            'lop_hoc_id' => $currentClassRoom->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => 'Wednesday',
            'start_time' => '19:00',
            'end_time' => '21:00',
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $currentCourse->id,
            'lop_hoc_id' => $currentClassRoom->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'is_submitted' => true,
        ]);

        $this->createInternalCourse($subject, $teacher, [
            'title' => 'Khoa 28 - Tieng Anh giao tiep',
            'schedule' => 'Thứ 4, 19:30 - 21:30 | Từ 03/04/2026 đến 03/05/2026',
            'day_of_week' => 'Wednesday',
            'meeting_days' => ['Wednesday'],
            'start_date' => '2026-04-03',
            'end_date' => '2026-05-03',
            'start_time' => '19:30',
            'end_time' => '21:30',
            'status' => Course::STATUS_SCHEDULED,
        ]);

        $roomConflictCourse = $this->createInternalCourse($subject, $teacherForRoom, [
            'title' => 'Khoa 29 - Tieng Anh giao tiep',
            'schedule' => 'Thứ 4, 19:15 - 21:15 | Từ 03/04/2026 đến 03/05/2026',
            'day_of_week' => 'Wednesday',
            'meeting_days' => ['Wednesday'],
            'start_date' => '2026-04-03',
            'end_date' => '2026-05-03',
            'start_time' => '19:15',
            'end_time' => '21:15',
            'status' => Course::STATUS_SCHEDULED,
        ]);

        $roomConflictClassRoom = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $roomConflictCourse->id,
            'teacher_id' => $teacherForRoom->id,
            'room_id' => $room->id,
            'name' => 'Lop trung phong',
            'start_date' => '2026-04-03',
            'duration' => 3,
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        ClassSchedule::create([
            'lop_hoc_id' => $roomConflictClassRoom->id,
            'teacher_id' => $teacherForRoom->id,
            'room_id' => $room->id,
            'day_of_week' => 'Wednesday',
            'start_time' => '19:15',
            'end_time' => '21:15',
        ]);

        $studentConflictCourse = $this->createInternalCourse($subject, $teacherForStudent, [
            'title' => 'Khoa 30 - Tieng Anh giao tiep',
            'schedule' => 'Thứ 4, 19:00 - 21:00 | Từ 03/04/2026 đến 03/05/2026',
            'day_of_week' => 'Wednesday',
            'meeting_days' => ['Wednesday'],
            'start_date' => '2026-04-03',
            'end_date' => '2026-05-03',
            'start_time' => '19:00',
            'end_time' => '21:00',
            'status' => Course::STATUS_SCHEDULED,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $studentConflictCourse->id,
            'lop_hoc_id' => null,
            'status' => Enrollment::STATUS_ACTIVE,
            'is_submitted' => true,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->getJson(route('admin.courses.schedule-preview', [
                'course' => $currentCourse,
                'meeting_days' => ['Wednesday'],
                'start_date' => '2026-04-03',
                'end_date' => '2026-05-03',
                'start_time' => '19:00',
                'end_time' => '21:00',
                'teacher_id' => $teacher->id,
            ]));

        $response->assertOk();
        $response->assertJsonPath('ready', true);
        $response->assertJsonPath('has_conflicts', true);
        $response->assertJsonPath('counts.teacher', 1);
        $response->assertJsonPath('counts.room', 1);
        $response->assertJsonPath('counts.student_groups', 1);
        $response->assertJsonPath('teacher_conflicts.0.title', 'Khoa 28 - Tieng Anh giao tiep');
        $response->assertJsonPath('room_conflicts.0.title', 'Lop trung phong (Phong 306)');
        $response->assertJsonPath('student_conflicts.0.student_name', 'Student Preview');
    }

    public function test_admin_can_autofill_end_time_when_only_start_time_is_provided(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create(['name' => 'Teacher Auto End']);
        $category = $this->createCategory('Ngoai ngu', 'ngoai-ngu');
        $subject = $this->createSubject($category, ['name' => 'Tieng Anh giao tiep', 'duration' => 12]);

        $course = $this->createInternalCourse($subject, $teacher, [
            'title' => 'Khoa 29 - Tieng Anh giao tiep',
            'status' => Course::STATUS_DRAFT,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.update', $course), [
                'title' => $course->title,
                'description' => $course->description,
                'price' => 0,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'meeting_days' => ['Monday'],
                'start_date' => '2026-04-01',
                'end_date' => '2026-05-01',
                'start_time' => '18:00',
            ]);

        $response->assertRedirect(route('admin.schedules.conflicts', [
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => ['Monday'],
            'start_date' => '2026-04-01',
            'end_date' => '2026-05-01',
            'start_time' => '18:00',
            'end_time' => ScheduleHelper::normalizeEndTime('18:00'),
            'exclude_course_id' => $course->id,
        ]));

        $course->refresh();

        $this->assertSame(ScheduleHelper::normalizeEndTime('18:00'), $course->end_time);
        $this->assertSame('18:00', $course->start_time);
        $this->assertSame('Monday', $course->day_of_week);
        $this->assertSame(['Monday'], $course->meeting_days);
    }

    private function createCategory(string $name, string $slug): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => $slug,
            'status' => Category::STATUS_ACTIVE,
        ]);
    }

    private function createSubject(Category $category, array $overrides = []): Subject
    {
        return Subject::create(array_merge([
            'name' => 'Mon hoc mau',
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'price' => 1500000,
            'duration' => 12,
            'description' => 'Mo ta mau',
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
            'capacity' => 30,
            'status' => Room::STATUS_ACTIVE,
        ], $overrides));
    }

    private function createInternalCourse(Subject $subject, ?User $teacher = null, array $overrides = []): Course
    {
        return Course::create(array_merge([
            'subject_id' => $subject->id,
            'title' => $subject->name . ' - Lop noi bo',
            'description' => 'Lop hoc noi bo danh cho hoc vien da duoc admin xep lich.',
            'teacher_id' => $teacher?->id,
            'capacity' => 20,
            'status' => Course::STATUS_DRAFT,
        ], $overrides));
    }
}

