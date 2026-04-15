<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ScheduleChangeRequest;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class quan_ly_yeu_cau_doi_lich_test extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_schedule_change_request_for_assigned_course(): void
    {
        $teacher = User::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $course = $this->createScheduledCourse($subject, $teacher);

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->post(route('teacher.schedule-change-requests.store', $course), [
                'requested_day_of_week' => 'Wednesday',
                'requested_date' => '2026-04-08',
                'requested_end_date' => '2026-06-08',
                'requested_start_time' => '19:00',
                'requested_end_time' => '21:00',
                'reason' => 'Can doi lich vi lich day hien tai bi trung.',
            ]);

        $response->assertRedirect(route('teacher.schedule-change-requests.index'));
        $this->assertDatabaseHas('schedule_change_requests', [
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'status' => ScheduleChangeRequest::STATUS_PENDING,
            'requested_day_of_week' => 'Wednesday',
            'requested_start_time' => '19:00',
        ]);
    }

    public function test_admin_can_view_and_filter_schedule_change_requests(): void
    {
        $admin = User::factory()->admin()->create();
        $teacherA = User::factory()->teacher()->create(['name' => 'Teacher Filter A', 'email' => 'teacher-a@example.com']);
        $teacherB = User::factory()->teacher()->create(['name' => 'Teacher Filter B', 'email' => 'teacher-b@example.com']);
        [, $subject] = $this->createCatalogSubject();

        $courseA = $this->createScheduledCourse($subject, $teacherA, ['title' => 'Lop Filter A']);
        $courseB = $this->createScheduledCourse($subject, $teacherB, ['title' => 'Lop Filter B']);

        $this->createScheduleChangeRequest($teacherA, $courseA, ['status' => ScheduleChangeRequest::STATUS_PENDING]);
        $this->createScheduleChangeRequest($teacherB, $courseB, ['status' => ScheduleChangeRequest::STATUS_REJECTED]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.schedule-change-requests.index', [
                'search' => 'Teacher Filter A',
                'status' => ScheduleChangeRequest::STATUS_PENDING,
            ]));

        $response->assertOk();
        $response->assertDontSee('Phase 10');
        $response->assertSee($teacherA->name);
        $response->assertSee($courseA->title);
        $response->assertDontSee($teacherB->name);
        $response->assertDontSee($courseB->title);
    }

    public function test_admin_can_approve_schedule_change_request_and_update_visible_schedule(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create(['name' => 'Teacher Approve']);
        $student = User::factory()->student()->create(['name' => 'Student Approve']);
        [, $subject] = $this->createCatalogSubject();
        $course = $this->createScheduledCourse($subject, $teacher, [
            'title' => 'Tin hoc van phong - Lop toi',
        ]);
        $enrollment = $this->createEnrollment($student, $subject, $course, $teacher);
        $request = $this->createScheduleChangeRequest($teacher, $course, [
            'requested_day_of_week' => 'Thursday',
            'requested_date' => '2026-04-09',
            'requested_end_date' => '2026-06-09',
            'requested_start_time' => '17:00',
            'requested_end_time' => '19:00',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedule-change-requests.review', $request), [
                'action' => 'approve',
                'admin_note' => 'Da sap xep lai lich theo de xuat hop ly.',
            ]);

        $response->assertRedirect(route('admin.schedule-change-requests.show', $request));

        $request->refresh();
        $course->refresh();
        $enrollment->refresh();

        $this->assertSame(ScheduleChangeRequest::STATUS_APPROVED, $request->status);
        $this->assertSame($admin->id, $request->reviewed_by);
        $this->assertSame('Thursday', $course->day_of_week);
        $this->assertStringStartsWith('17:00', (string) $course->start_time);
        $this->assertStringStartsWith('19:00', (string) $course->end_time);
        $this->assertStringContainsString('17:00 - 19:00', $course->schedule);
        $this->assertSame($course->schedule, $enrollment->schedule);

        $teacherResponse = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('teacher.courses'));

        $teacherResponse->assertOk();
        $teacherResponse->assertSee('17:00 - 19:00');

        $studentResponse = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('student.schedule'));

        $studentResponse->assertOk();
        $studentResponse->assertSee('17:00 - 19:00');
    }

    public function test_admin_rejecting_schedule_change_request_keeps_current_schedule(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $course = $this->createScheduledCourse($subject, $teacher);
        $request = $this->createScheduleChangeRequest($teacher, $course);
        $originalSchedule = $course->schedule;

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedule-change-requests.review', $request), [
                'action' => 'reject',
                'admin_note' => 'Khung gio de xuat hien tai chua phu hop voi lich hoc vien.',
            ]);

        $response->assertRedirect(route('admin.schedule-change-requests.show', $request));
        $this->assertDatabaseHas('schedule_change_requests', [
            'id' => $request->id,
            'status' => ScheduleChangeRequest::STATUS_REJECTED,
            'reviewed_by' => $admin->id,
        ]);
        $this->assertSame($originalSchedule, $course->fresh()->schedule);
    }

    public function test_teacher_is_blocked_from_admin_schedule_change_request_management(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('admin.schedule-change-requests.index'));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_admin_cannot_approve_schedule_change_request_when_teacher_has_conflict(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();

        $course = $this->createScheduledCourse($subject, $teacher, [
            'title' => 'Lop dang xin doi lich',
        ]);
        $this->createScheduledCourse($subject, $teacher, [
            'title' => 'Lop trung lich',
            'day_of_week' => 'Wednesday',
            'start_date' => '2026-04-08',
            'end_date' => '2026-06-08',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'schedule' => 'Thu 4, 18:00 - 20:00 | Tu 08/04/2026 den 08/06/2026',
        ]);
        $request = $this->createScheduleChangeRequest($teacher, $course, [
            'requested_day_of_week' => 'Wednesday',
            'requested_date' => '2026-04-08',
            'requested_end_date' => '2026-06-08',
            'requested_start_time' => '19:00',
            'requested_end_time' => '21:00',
        ]);

        $response = $this
            ->from(route('admin.schedule-change-requests.show', $request))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedule-change-requests.review', $request), [
                'action' => 'approve',
                'admin_note' => 'Thu nghiem xung dot.',
            ]);

        $response->assertRedirect(route('admin.schedule-change-requests.show', $request));
        $response->assertSessionHasErrors('action');
        $this->assertSame(ScheduleChangeRequest::STATUS_PENDING, $request->fresh()->status);
        $this->assertStringContainsString('18:00 - 20:00', $course->fresh()->schedule);
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

    private function createScheduledCourse(Subject $subject, User $teacher, array $overrides = []): Course
    {
        return Course::create(array_merge([
            'subject_id' => $subject->id,
            'title' => $subject->name . ' - Lop noi bo',
            'description' => 'Lop hoc noi bo da co lich chinh thuc.',
            'teacher_id' => $teacher->id,
            'day_of_week' => 'Monday',
            'start_date' => '2026-04-01',
            'end_date' => '2026-06-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'capacity' => 20,
            'status' => Course::STATUS_SCHEDULED,
            'schedule' => 'Thu 2, 18:00 - 20:00 | Tu 01/04/2026 den 01/06/2026',
        ], $overrides));
    }

    private function createEnrollment(User $student, Subject $subject, Course $course, User $teacher, array $overrides = []): Enrollment
    {
        return Enrollment::create(array_merge([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => Enrollment::STATUS_SCHEDULED,
            'schedule' => $course->schedule,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'preferred_days' => json_encode(['Monday', 'Wednesday']),
            'is_submitted' => true,
            'submitted_at' => now(),
        ], $overrides));
    }

    private function createScheduleChangeRequest(User $teacher, Course $course, array $overrides = []): ScheduleChangeRequest
    {
        return ScheduleChangeRequest::create(array_merge([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'current_schedule' => $course->schedule,
            'requested_day_of_week' => 'Wednesday',
            'requested_date' => '2026-04-08',
            'requested_end_date' => '2026-06-08',
            'requested_start_time' => '19:00',
            'requested_end_time' => '21:00',
            'reason' => 'Can doi lich de phu hop cong tac.',
            'status' => ScheduleChangeRequest::STATUS_PENDING,
        ], $overrides));
    }
}

