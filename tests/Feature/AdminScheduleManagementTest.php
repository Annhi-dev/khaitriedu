<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_schedule_queue(): void
    {
        $admin = User::factory()->admin()->create();
        [, $subject] = $this->createCatalogSubject();
        $student = User::factory()->student()->create([
            'name' => 'Hoc Vien Xep Lich',
            'email' => 'queue-student@example.com',
        ]);
        $pendingEnrollment = $this->createEnrollment($student, $subject, [
            'status' => Enrollment::STATUS_PENDING,
        ]);
        $this->createEnrollment(User::factory()->student()->create(), $subject, [
            'status' => Enrollment::STATUS_REJECTED,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.schedules.queue', [
                'search' => 'Hoc Vien Xep Lich',
            ]));

        $response->assertOk();
        $response->assertSee('Xếp lịch');
        $response->assertSee($pendingEnrollment->user->name);
        $response->assertSee($subject->name);
    }

    public function test_admin_can_open_schedule_screen_for_custom_schedule_request(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();
        $enrollment = $this->createEnrollment($student, $subject, [
            'status' => Enrollment::STATUS_PENDING,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.schedules.enrollments.show', $enrollment));

        $response->assertOk();
        $response->assertSee($subject->name);
        $response->assertSee('Luu lop cho mo');
        $response->assertSee('Luu y kiem tra');
        $response->assertDontSee('Chon lop hoc co san');
    }

    public function test_admin_can_view_and_filter_system_schedule_list(): void
    {
        $admin = User::factory()->admin()->create();
        $teacherA = User::factory()->teacher()->create(['name' => 'Teacher Alpha']);
        $teacherB = User::factory()->teacher()->create(['name' => 'Teacher Beta']);
        [, $subject] = $this->createCatalogSubject();

        $courseA = $this->createInternalCourse($subject, $teacherA, [
            'title' => 'Lop Teacher Alpha',
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday'],
            'start_date' => '2026-04-01',
            'end_date' => '2026-05-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'schedule' => 'Thu 2, 18:00 - 20:00 | Tu 01/04/2026 den 01/05/2026',
            'status' => Course::STATUS_SCHEDULED,
        ]);
        $courseB = $this->createInternalCourse($subject, $teacherB, [
            'title' => 'Lop Teacher Beta',
            'day_of_week' => 'Tuesday',
            'meeting_days' => ['Tuesday'],
            'start_date' => '2026-04-02',
            'end_date' => '2026-05-02',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'schedule' => 'Thu 3, 08:00 - 10:00 | Tu 02/04/2026 den 02/05/2026',
            'status' => Course::STATUS_SCHEDULED,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.schedules.index', [
                'teacher_id' => $teacherA->id,
            ]));

        $response->assertOk();
        $response->assertSee('Xếp lịch');
        $response->assertViewHas('schedules', function ($schedules) use ($courseA, $courseB) {
            $ids = $schedules->getCollection()->pluck('id');

            return $ids->contains($courseA->id) && ! $ids->contains($courseB->id);
        });
    }

    public function test_admin_can_view_active_seed_courses_on_schedule_overview(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create(['name' => 'Teacher Active']);
        [, $subject] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');
        $room = $this->createRoom();
        $course = $this->createInternalCourse($subject, $teacher, [
            'title' => 'Khóa 26 - ANH VĂN KHUNG 6 BẬC',
            'schedule' => 'Tối T2-T4-T6, 18:00 - 20:15',
            'status' => Course::STATUS_ACTIVE,
        ]);
        $classRoom = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'name' => $course->title,
            'room_id' => $room->id,
            'teacher_id' => $teacher->id,
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
            'end_time' => '20:15',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.schedules.index'));

        $response->assertOk();
        $response->assertSee($course->title);
        $response->assertSee($course->schedule);
        $response->assertSee($teacher->name);
        $response->assertSee(route('admin.classes.show', $classRoom));
        $response->assertSee(route('admin.schedules.courses.show', $course));
        $response->assertSee('Xem lịch chi tiết');
    }

    public function test_admin_can_view_schedule_detail_page_with_classroom_sessions_and_students(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create(['name' => 'Teacher Detail']);
        $student = User::factory()->student()->create(['name' => 'Student Detail']);
        $room = $this->createRoom(['name' => 'Phong 201', 'code' => 'P201']);
        [, $subject] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');

        $course = $this->createInternalCourse($subject, $teacher, [
            'title' => 'Khóa 26 - ANH VĂN THIẾU NHI',
            'schedule' => 'Tối T2-T4-T6, 18:00 - 20:15 | Từ 23/03/2026 đến 23/04/2026',
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday', 'Wednesday', 'Friday'],
            'start_date' => '2026-03-23',
            'end_date' => '2026-04-23',
            'start_time' => '18:00',
            'end_time' => '20:15',
            'status' => Course::STATUS_ACTIVE,
        ]);

        $classRoom = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'name' => $course->title,
            'room_id' => $room->id,
            'teacher_id' => $teacher->id,
            'start_date' => '2026-03-23',
            'duration' => 3,
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        ClassSchedule::create([
            'lop_hoc_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:15',
        ]);

        ClassSchedule::create([
            'lop_hoc_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => 'Wednesday',
            'start_time' => '18:00',
            'end_time' => '20:15',
        ]);

        $this->createEnrollment($student, $subject, [
            'course_id' => $course->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => Enrollment::STATUS_SCHEDULED,
            'schedule' => $course->schedule,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.schedules.courses.show', $course));

        $response->assertOk();
        $response->assertSee('Chi tiết lịch học');
        $response->assertSee($course->title);
        $response->assertSee($teacher->name);
        $response->assertSee($room->name);
        $response->assertSee('Thứ 2');
        $response->assertSee('18:00 - 20:15');
        $response->assertSee($student->name);
        $response->assertSee(route('admin.classes.show', $classRoom));
    }

    public function test_custom_schedule_request_cannot_use_existing_open_class_in_phase_9(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject, $teacher, [
            'status' => Course::STATUS_SCHEDULED,
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday'],
            'start_date' => '2026-04-01',
            'end_date' => '2026-05-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);
        $enrollment = $this->createEnrollment($student, $subject, [
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        $response = $this
            ->from(route('admin.schedules.enrollments.show', $enrollment))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.enrollments.store', $enrollment), [
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
                'day_of_week' => ['Monday'],
                'start_time' => '18:00',
                'end_time' => '20:00',
                'capacity' => 20,
                'note' => 'Thu nghiem',
            ]);

        $response->assertRedirect(route('admin.schedules.enrollments.show', $enrollment));
        $response->assertSessionHasErrors('course_id');

        $enrollment->refresh();
        $this->assertSame(Enrollment::STATUS_APPROVED, $enrollment->status);
        $this->assertNull($enrollment->course_id);
    }

    public function test_admin_can_save_custom_request_into_waiting_class_and_notify_student(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');
        $enrollment = $this->createEnrollment($student, $subject, [
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.enrollments.store', $enrollment), [
                'course_id' => '',
                'teacher_id' => $teacher->id,
                'new_course_title' => '',
                'new_course_description' => 'Lop moi dang gom hoc vien',
                'day_of_week' => ['Wednesday', 'Friday'],
                'start_time' => '19:00',
                'end_time' => '21:00',
                'capacity' => 15,
                'note' => 'Luu lop cho mo',
            ]);

        $response->assertRedirect(route('admin.schedules.index'));

        $course = Course::where('subject_id', $subject->id)->latest('id')->first();

        $this->assertNotNull($course);
        $this->assertSame($subject->id, $course->subject_id);
        $this->assertSame('Tieng Anh giao tiep - Khóa học 1', $course->title);
        $this->assertSame($teacher->id, $course->teacher_id);
        $this->assertSame(Course::STATUS_PENDING_OPEN, $course->status);
        $this->assertSame(15, $course->capacity);
        $this->assertSame(['Wednesday', 'Friday'], $course->meeting_days);
        $this->assertNull($course->start_date);
        $this->assertNull($course->end_date);

        $enrollment->refresh();
        $this->assertSame($course->id, $enrollment->course_id);
        $this->assertSame(Enrollment::STATUS_SCHEDULED, $enrollment->status);
        $this->assertSame($teacher->id, $enrollment->assigned_teacher_id);

        $this->assertSame(1, Notification::query()->where('user_id', $student->id)->count());
        $this->assertDatabaseHas('thong_bao', [
            'user_id' => $student->id,
            'type' => 'info',
            'link' => route('student.enroll.my-classes'),
        ]);
    }

    public function test_custom_schedule_request_can_join_existing_waiting_class(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $studentA = User::factory()->student()->create();
        $studentB = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();

        $waitingCourse = $this->createInternalCourse($subject, $teacher, [
            'title' => 'Tin hoc van phong - Khóa học 1',
            'day_of_week' => 'Thursday',
            'meeting_days' => ['Thursday'],
            'start_time' => '18:30',
            'end_time' => '20:30',
            'capacity' => 12,
            'status' => Course::STATUS_PENDING_OPEN,
            'schedule' => 'Thu 5, 18:30 - 20:30 | Cho du 5 hoc vien de mo lop',
        ]);
        $this->createEnrollment($studentA, $subject, [
            'course_id' => $waitingCourse->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => Enrollment::STATUS_SCHEDULED,
            'schedule' => $waitingCourse->schedule,
        ]);

        $enrollment = $this->createEnrollment($studentB, $subject, [
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.enrollments.store', $enrollment), [
                'course_id' => $waitingCourse->id,
                'note' => 'Ghep vao lop cho mo',
            ]);

        $response->assertRedirect(route('admin.schedules.index'));

        $this->assertSame(1, Course::query()->count());
        $this->assertSame($waitingCourse->id, $enrollment->fresh()->course_id);
        $this->assertSame(2, $waitingCourse->fresh()->enrollments()->whereIn('status', Enrollment::courseAccessStatuses())->count());
        $this->assertSame(1, Notification::query()->where('user_id', $studentB->id)->count());
    }

    public function test_generated_course_title_increments_for_same_subject(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();
        $this->createInternalCourse($subject, null, [
            'title' => 'Tin hoc van phong - Khóa học 1',
        ]);
        $enrollment = $this->createEnrollment($student, $subject, [
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        $this->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.enrollments.store', $enrollment), [
                'course_id' => '',
                'teacher_id' => $teacher->id,
                'new_course_title' => '',
                'new_course_description' => 'Lop moi tao tu dong ten',
                'day_of_week' => ['Thursday'],
                'start_time' => '18:30',
                'end_time' => '20:30',
                'capacity' => 12,
                'note' => 'Da luu cho mo',
            ]);

        $this->assertDatabaseHas('khoa_hoc', [
            'subject_id' => $subject->id,
            'title' => 'Tin hoc van phong - Khóa học 2',
        ]);
    }

    public function test_admin_can_open_waiting_class_once_it_has_enough_students_and_notify_students(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $room = $this->createRoom();
        [, $subject] = $this->createCatalogSubject();

        $waitingCourse = $this->createInternalCourse($subject, $teacher, [
            'title' => 'Tin hoc van phong - Khóa học 1',
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday', 'Wednesday'],
            'start_time' => '18:00',
            'end_time' => '20:00',
            'capacity' => 10,
            'status' => Course::STATUS_PENDING_OPEN,
            'schedule' => 'Thu 2, Thu 4, 18:00 - 20:00 | Cho du 5 hoc vien de mo lop',
        ]);

        foreach (range(1, Course::minimumStudentsToOpen()) as $index) {
            $student = User::factory()->student()->create([
                'name' => 'Hoc Vien ' . $index,
            ]);

            $this->createEnrollment($student, $subject, [
                'course_id' => $waitingCourse->id,
                'assigned_teacher_id' => $teacher->id,
                'status' => Enrollment::STATUS_SCHEDULED,
                'schedule' => $waitingCourse->schedule,
            ]);
        }

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.courses.open.store', $waitingCourse), [
                'room_id' => $room->id,
                'start_date' => '2026-04-20',
                'end_date' => '2026-06-20',
            ]);

        $response->assertRedirect(route('admin.schedules.index'));

        $waitingCourse->refresh();
        $this->assertSame(Course::STATUS_SCHEDULED, $waitingCourse->status);
        $this->assertSame('2026-04-20', $waitingCourse->start_date?->format('Y-m-d'));
        $this->assertSame('2026-06-20', $waitingCourse->end_date?->format('Y-m-d'));
        $this->assertStringContainsString('20/04/2026', $waitingCourse->schedule);

        $classRoom = ClassRoom::query()->where('course_id', $waitingCourse->id)->first();
        $this->assertNotNull($classRoom);
        $this->assertSame($room->id, $classRoom->room_id);
        $this->assertSame($teacher->id, $classRoom->teacher_id);
        $this->assertDatabaseHas('lich_hoc', [
            'lop_hoc_id' => $classRoom->id,
            'room_id' => $room->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);

        $this->assertDatabaseHas('dang_ky', [
            'course_id' => $waitingCourse->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
        ]);

        $this->assertSame(Course::minimumStudentsToOpen(), Notification::query()->where('type', 'success')->count());
        $this->assertDatabaseHas('thong_bao', [
            'type' => 'success',
            'link' => route('student.schedule'),
        ]);
    }

    public function test_admin_cannot_open_waiting_class_before_minimum_students(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $room = $this->createRoom();
        [, $subject] = $this->createCatalogSubject();

        $waitingCourse = $this->createInternalCourse($subject, $teacher, [
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday'],
            'start_time' => '18:00',
            'end_time' => '20:00',
            'status' => Course::STATUS_PENDING_OPEN,
        ]);

        foreach (range(1, Course::minimumStudentsToOpen() - 1) as $index) {
            $student = User::factory()->student()->create([
                'name' => 'Hoc Vien Cho ' . $index,
            ]);

            $this->createEnrollment($student, $subject, [
                'course_id' => $waitingCourse->id,
                'assigned_teacher_id' => $teacher->id,
                'status' => Enrollment::STATUS_SCHEDULED,
            ]);
        }

        $response = $this
            ->from(route('admin.schedules.courses.open', $waitingCourse))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.courses.open.store', $waitingCourse), [
                'room_id' => $room->id,
                'start_date' => '2026-04-20',
                'end_date' => '2026-06-20',
            ]);

        $response->assertRedirect(route('admin.schedules.courses.open', $waitingCourse));
        $response->assertSessionHasErrors('course');
        $this->assertSame(Course::STATUS_PENDING_OPEN, $waitingCourse->fresh()->status);
    }

    public function test_admin_cannot_open_waiting_class_when_teacher_has_conflicting_time_slot(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $room = $this->createRoom();
        [, $subject] = $this->createCatalogSubject();

        $this->createInternalCourse($subject, $teacher, [
            'title' => 'Lop dang day',
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday'],
            'start_date' => '2026-04-01',
            'end_date' => '2026-05-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'schedule' => 'Thu 2, 18:00 - 20:00 | Tu 01/04/2026 den 01/05/2026',
            'status' => Course::STATUS_SCHEDULED,
        ]);

        $waitingCourse = $this->createInternalCourse($subject, $teacher, [
            'title' => 'Lop cho mo',
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday'],
            'start_time' => '19:00',
            'end_time' => '21:00',
            'status' => Course::STATUS_PENDING_OPEN,
        ]);

        foreach (range(1, Course::minimumStudentsToOpen()) as $index) {
            $student = User::factory()->student()->create();

            $this->createEnrollment($student, $subject, [
                'course_id' => $waitingCourse->id,
                'assigned_teacher_id' => $teacher->id,
                'status' => Enrollment::STATUS_SCHEDULED,
            ]);
        }

        $response = $this
            ->from(route('admin.schedules.courses.open', $waitingCourse))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.courses.open.store', $waitingCourse), [
                'room_id' => $room->id,
                'start_date' => '2026-04-15',
                'end_date' => '2026-05-15',
            ]);

        $response->assertRedirect(route('admin.schedules.courses.open', $waitingCourse));
        $response->assertSessionHasErrors('teacher_id');
        $this->assertSame(Course::STATUS_PENDING_OPEN, $waitingCourse->fresh()->status);
    }

    public function test_admin_cannot_open_waiting_class_when_room_has_conflicting_time_slot(): void
    {
        $admin = User::factory()->admin()->create();
        $teacherA = User::factory()->teacher()->create();
        $teacherB = User::factory()->teacher()->create();
        $room = $this->createRoom();
        [, $subject] = $this->createCatalogSubject();

        $existingCourse = $this->createInternalCourse($subject, $teacherA, [
            'title' => 'Lop dang su dung phong',
            'day_of_week' => 'Wednesday',
            'meeting_days' => ['Wednesday'],
            'start_date' => '2026-04-01',
            'end_date' => '2026-05-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'status' => Course::STATUS_SCHEDULED,
        ]);

        $existingClass = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $existingCourse->id,
            'room_id' => $room->id,
            'teacher_id' => $teacherA->id,
            'start_date' => '2026-04-01',
            'duration' => 3,
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        ClassSchedule::create([
            'lop_hoc_id' => $existingClass->id,
            'teacher_id' => $teacherA->id,
            'room_id' => $room->id,
            'day_of_week' => 'Wednesday',
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);

        $waitingCourse = $this->createInternalCourse($subject, $teacherB, [
            'title' => 'Lop cho mo',
            'day_of_week' => 'Wednesday',
            'meeting_days' => ['Wednesday'],
            'start_time' => '19:00',
            'end_time' => '21:00',
            'status' => Course::STATUS_PENDING_OPEN,
        ]);

        foreach (range(1, Course::minimumStudentsToOpen()) as $index) {
            $student = User::factory()->student()->create();

            $this->createEnrollment($student, $subject, [
                'course_id' => $waitingCourse->id,
                'assigned_teacher_id' => $teacherB->id,
                'status' => Enrollment::STATUS_SCHEDULED,
            ]);
        }

        $response = $this
            ->from(route('admin.schedules.courses.open', $waitingCourse))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.courses.open.store', $waitingCourse), [
                'room_id' => $room->id,
                'start_date' => '2026-04-15',
                'end_date' => '2026-05-15',
            ]);

        $response->assertRedirect(route('admin.schedules.courses.open', $waitingCourse));
        $response->assertSessionHasErrors('room_id');
        $this->assertSame(Course::STATUS_PENDING_OPEN, $waitingCourse->fresh()->status);
    }

    public function test_admin_cannot_open_waiting_class_when_student_has_conflicting_time_slot(): void
    {
        $admin = User::factory()->admin()->create();
        $teacherA = User::factory()->teacher()->create();
        $teacherB = User::factory()->teacher()->create();
        $room = $this->createRoom();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();

        $existingCourse = $this->createInternalCourse($subject, $teacherA, [
            'title' => 'Lop hien tai cua hoc vien',
            'day_of_week' => 'Tuesday',
            'meeting_days' => ['Tuesday'],
            'start_date' => '2026-04-02',
            'end_date' => '2026-05-02',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'schedule' => 'Thu 3, 18:00 - 20:00 | Tu 02/04/2026 den 02/05/2026',
            'status' => Course::STATUS_SCHEDULED,
        ]);
        $this->createEnrollment($student, $subject, [
            'course_id' => $existingCourse->id,
            'assigned_teacher_id' => $teacherA->id,
            'status' => Enrollment::STATUS_SCHEDULED,
            'schedule' => $existingCourse->schedule,
        ]);

        $waitingCourse = $this->createInternalCourse($subject, $teacherB, [
            'title' => 'Lop cho mo cua hoc vien',
            'day_of_week' => 'Tuesday',
            'meeting_days' => ['Tuesday'],
            'start_time' => '19:00',
            'end_time' => '21:00',
            'status' => Course::STATUS_PENDING_OPEN,
        ]);

        $this->createEnrollment($student, $subject, [
            'course_id' => $waitingCourse->id,
            'assigned_teacher_id' => $teacherB->id,
            'status' => Enrollment::STATUS_SCHEDULED,
        ]);

        foreach (range(1, Course::minimumStudentsToOpen() - 1) as $index) {
            $otherStudent = User::factory()->student()->create();

            $this->createEnrollment($otherStudent, $subject, [
                'course_id' => $waitingCourse->id,
                'assigned_teacher_id' => $teacherB->id,
                'status' => Enrollment::STATUS_SCHEDULED,
            ]);
        }

        $response = $this
            ->from(route('admin.schedules.courses.open', $waitingCourse))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.courses.open.store', $waitingCourse), [
                'room_id' => $room->id,
                'start_date' => '2026-04-16',
                'end_date' => '2026-05-16',
            ]);

        $response->assertRedirect(route('admin.schedules.courses.open', $waitingCourse));
        $response->assertSessionHasErrors('start_date');
        $this->assertSame(Course::STATUS_PENDING_OPEN, $waitingCourse->fresh()->status);
    }

    public function test_teacher_can_see_waiting_class_after_admin_saves_it(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create(['name' => 'Giang Vien Lich Day']);
        $student = User::factory()->student()->create(['name' => 'Hoc Vien Da Xep']);
        [, $subject] = $this->createCatalogSubject();
        $enrollment = $this->createEnrollment($student, $subject, [
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        $this->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.enrollments.store', $enrollment), [
                'course_id' => '',
                'teacher_id' => $teacher->id,
                'new_course_title' => '',
                'new_course_description' => 'Lop moi cho hoc vien yeu cau lich rieng',
                'day_of_week' => ['Thursday'],
                'start_time' => '18:30',
                'end_time' => '20:45',
                'capacity' => 12,
                'note' => 'Da luu cho teacher',
            ]);

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('teacher.courses'));

        $response->assertOk();
        $response->assertSee('Tin hoc van phong - Khóa học 1');
        $response->assertSee('18:30 - 20:45');
        $response->assertSee('Hoc Vien Da Xep');
    }

    public function test_student_can_see_waiting_class_after_admin_saves_it(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create(['name' => 'Teacher Schedule']);
        $student = User::factory()->student()->create(['name' => 'Student Schedule']);
        [, $subject] = $this->createCatalogSubject();
        $enrollment = $this->createEnrollment($student, $subject, [
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        $this->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.enrollments.store', $enrollment), [
                'course_id' => '',
                'teacher_id' => $teacher->id,
                'new_course_title' => '',
                'new_course_description' => 'Lop moi cho hoc vien yeu cau lich rieng',
                'day_of_week' => ['Friday'],
                'start_time' => '17:00',
                'end_time' => '19:15',
                'capacity' => 25,
                'note' => 'Da luu cho student',
            ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('student.schedule'));

        $response->assertOk();
        $response->assertSee($subject->name);
        $response->assertSee('17:00 - 19:15');
        $response->assertSee('Teacher Schedule');
        $response->assertSee('Dang cho mo lop');
        $response->assertDontSee('Vao lop hoc');
    }

    public function test_admin_course_teacher_update_syncs_teacher_schedule_pages(): void
    {
        $admin = User::factory()->admin()->create();
        $teacherA = User::factory()->teacher()->create(['name' => 'Teacher Alpha']);
        $teacherB = User::factory()->teacher()->create(['name' => 'Teacher Beta']);
        [, $subject] = $this->createCatalogSubject('Tin hoc van phong', 'tin-hoc-van-phong');

        $course = $this->createInternalCourse($subject, $teacherA, [
            'title' => 'Lop Teacher Alpha',
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday'],
            'start_date' => '2026-04-01',
            'end_date' => '2026-05-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'schedule' => 'Thu 2, 18:00 - 20:00 | Tu 01/04/2026 den 01/05/2026',
            'status' => Course::STATUS_SCHEDULED,
        ]);

        $classRoom = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'teacher_id' => $teacherA->id,
            'start_date' => '2026-04-01',
            'duration' => 3,
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        $schedule = ClassSchedule::create([
            'lop_hoc_id' => $classRoom->id,
            'teacher_id' => $teacherA->id,
            'day_of_week' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.update', $course), [
                'title' => $course->title,
                'description' => $course->description,
                'price' => 0,
                'subject_id' => $subject->id,
                'teacher_id' => $teacherB->id,
                'schedule' => $course->schedule,
            ]);

        $response->assertRedirect(route('admin.course.show', $course));

        $course->refresh();
        $classRoom->refresh();
        $schedule->refresh();

        $this->assertSame($teacherB->id, $course->teacher_id);
        $this->assertSame($teacherB->id, $classRoom->teacher_id);
        $this->assertSame($teacherB->id, $schedule->teacher_id);

        $this->assertDatabaseHas('lop_hoc', [
            'id' => $classRoom->id,
            'teacher_id' => $teacherB->id,
        ]);

        $this->assertDatabaseHas('lich_hoc', [
            'id' => $schedule->id,
            'teacher_id' => $teacherB->id,
        ]);

        $oldTeacherResponse = $this
            ->withSession(['user_id' => $teacherA->id])
            ->get(route('teacher.schedules.index'));

        $oldTeacherResponse->assertOk();
        $oldTeacherResponse->assertDontSee('Lop Teacher Alpha');
        $oldTeacherResponse->assertSee('Khung gio');

        $newTeacherResponse = $this
            ->withSession(['user_id' => $teacherB->id])
            ->get(route('teacher.schedules.index'));

        $newTeacherResponse->assertOk();
        $newTeacherResponse->assertSee('Lop Teacher Alpha');
        $newTeacherResponse->assertSee('Khung gio');
        $newTeacherResponse->assertSee('Xem chi tiet');
    }

    private function createCatalogSubject(string $subjectName = 'Tin hoc van phong', string $slug = 'tin-hoc-van-phong'): array
    {
        $category = Category::create([
            'name' => 'Nhom hoc ' . $slug,
            'slug' => 'nhom-hoc-' . $slug,
            'status' => Category::STATUS_ACTIVE,
        ]);

        $subject = Subject::create([
            'name' => $subjectName,
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'price' => 1500000,
            'duration' => 24,
        ]);

        return [$category, $subject];
    }

    private function createEnrollment(User $student, Subject $subject, array $overrides = []): Enrollment
    {
        return Enrollment::create(array_merge([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => Enrollment::STATUS_PENDING,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'preferred_days' => json_encode(['Monday', 'Wednesday', 'Friday']),
            'is_submitted' => true,
            'submitted_at' => now(),
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

    private function createRoom(array $overrides = []): Room
    {
        $code = $overrides['code'] ?? 'P' . fake()->unique()->numberBetween(100, 999);

        return Room::create(array_merge([
            'code' => $code,
            'name' => 'Phong ' . $code,
            'type' => 'offline',
            'location' => 'Tang 2',
            'capacity' => 25,
            'status' => Room::STATUS_ACTIVE,
        ], $overrides));
    }
}
