<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
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
        $response->assertSee('Phase 9');
        $response->assertSee($pendingEnrollment->user->name);
        $response->assertSee($subject->name);
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
        $response->assertSee('Phase 9');
        $response->assertViewHas('schedules', function ($schedules) use ($courseA, $courseB) {
            $ids = $schedules->getCollection()->pluck('id');

            return $ids->contains($courseA->id) && ! $ids->contains($courseB->id);
        });
    }

    public function test_admin_can_schedule_approved_enrollment_into_existing_class(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject);
        $enrollment = $this->createEnrollment($student, $subject, [
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.enrollments.store', $enrollment), [
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
                'new_course_title' => '',
                'new_course_description' => '',
                'day_of_week' => 'Monday',
                'start_date' => '2026-04-01',
                'end_date' => '2026-05-01',
                'start_time' => '18:00',
                'end_time' => '20:00',
                'capacity' => 20,
                'note' => 'Xep lop toi cho hoc vien',
            ]);

        $response->assertRedirect(route('admin.schedules.index'));

        $enrollment->refresh();
        $course->refresh();

        $this->assertSame(Enrollment::STATUS_SCHEDULED, $enrollment->status);
        $this->assertSame($course->id, $enrollment->course_id);
        $this->assertSame($teacher->id, $enrollment->assigned_teacher_id);
        $this->assertSame($admin->id, $enrollment->reviewed_by);
        $this->assertSame(Course::STATUS_SCHEDULED, $course->status);
        $this->assertSame('Monday', $course->day_of_week);
        $this->assertSame('18:00', $course->start_time);
        $this->assertSame('20:00', $course->end_time);
        $this->assertNotNull($enrollment->reviewed_at);
        $this->assertNotNull($enrollment->schedule);
        $this->assertStringContainsString('18:00 - 20:00', $enrollment->schedule);
        $this->assertStringContainsString('01/04/2026', $enrollment->schedule);
    }

    public function test_admin_can_create_new_class_while_scheduling_enrollment(): void
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
                'new_course_title' => 'Tieng Anh giao tiep - Ca toi',
                'new_course_description' => 'Lop moi tao trong phase 9',
                'day_of_week' => 'Wednesday',
                'start_date' => '2026-04-08',
                'end_date' => '2026-06-08',
                'start_time' => '19:00',
                'end_time' => '21:00',
                'capacity' => 15,
                'note' => 'Tao lop moi cho hoc vien',
            ]);

        $response->assertRedirect(route('admin.schedules.index'));

        $course = Course::where('title', 'Tieng Anh giao tiep - Ca toi')->first();

        $this->assertNotNull($course);
        $this->assertSame($subject->id, $course->subject_id);
        $this->assertSame($teacher->id, $course->teacher_id);
        $this->assertSame(Course::STATUS_SCHEDULED, $course->status);
        $this->assertSame(15, $course->capacity);

        $enrollment->refresh();
        $this->assertSame($course->id, $enrollment->course_id);
        $this->assertSame(Enrollment::STATUS_SCHEDULED, $enrollment->status);
    }

    public function test_teacher_can_see_schedule_after_admin_arranges_it(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create(['name' => 'Giang Vien Lich Day']);
        $student = User::factory()->student()->create(['name' => 'Hoc Vien Da Xep']);
        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject, null, [
            'title' => 'Tin hoc van phong - Lop toi',
        ]);
        $enrollment = $this->createEnrollment($student, $subject, [
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        $this->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.enrollments.store', $enrollment), [
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
                'new_course_title' => '',
                'new_course_description' => '',
                'day_of_week' => 'Thursday',
                'start_date' => '2026-04-09',
                'end_date' => '2026-06-09',
                'start_time' => '18:30',
                'end_time' => '20:30',
                'capacity' => 12,
                'note' => 'Da xep cho teacher',
            ]);

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('teacher.courses'));

        $response->assertOk();
        $response->assertSee('Tin hoc van phong - Lop toi');
        $response->assertSee('18:30 - 20:30');
        $response->assertSee('Hoc Vien Da Xep');
    }

    public function test_student_can_see_schedule_after_admin_arranges_it(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create(['name' => 'Teacher Schedule']);
        $student = User::factory()->student()->create(['name' => 'Student Schedule']);
        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject);
        $enrollment = $this->createEnrollment($student, $subject, [
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        $this->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.enrollments.store', $enrollment), [
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
                'new_course_title' => '',
                'new_course_description' => '',
                'day_of_week' => 'Friday',
                'start_date' => '2026-04-10',
                'end_date' => '2026-06-10',
                'start_time' => '17:00',
                'end_time' => '19:00',
                'capacity' => 25,
                'note' => 'Da xep cho student',
            ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('student.schedule'));

        $response->assertOk();
        $response->assertSee($subject->name);
        $response->assertSee('17:00 - 19:00');
        $response->assertSee('Teacher Schedule');
    }

    public function test_admin_cannot_schedule_teacher_into_conflicting_time_slot(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();

        $this->createInternalCourse($subject, $teacher, [
            'title' => 'Lop dang day',
            'day_of_week' => 'Monday',
            'start_date' => '2026-04-01',
            'end_date' => '2026-05-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'schedule' => 'Thu 2, 18:00 - 20:00 | Tu 01/04/2026 den 01/05/2026',
            'status' => Course::STATUS_SCHEDULED,
        ]);
        $candidateCourse = $this->createInternalCourse($subject);
        $enrollment = $this->createEnrollment($student, $subject, [
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        $response = $this
            ->from(route('admin.schedules.enrollments.show', $enrollment))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.enrollments.store', $enrollment), [
                'course_id' => $candidateCourse->id,
                'teacher_id' => $teacher->id,
                'new_course_title' => '',
                'new_course_description' => '',
                'day_of_week' => 'Monday',
                'start_date' => '2026-04-15',
                'end_date' => '2026-05-15',
                'start_time' => '19:00',
                'end_time' => '21:00',
                'capacity' => 20,
                'note' => 'Thu nghiem xung dot teacher',
            ]);

        $response->assertRedirect(route('admin.schedules.enrollments.show', $enrollment));
        $response->assertSessionHasErrors('teacher_id');
        $this->assertSame(Enrollment::STATUS_APPROVED, $enrollment->fresh()->status);
        $this->assertNull($enrollment->fresh()->course_id);
    }

    public function test_admin_cannot_schedule_student_into_conflicting_time_slot(): void
    {
        $admin = User::factory()->admin()->create();
        $teacherA = User::factory()->teacher()->create();
        $teacherB = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();

        $existingCourse = $this->createInternalCourse($subject, $teacherA, [
            'title' => 'Lop hien tai cua hoc vien',
            'day_of_week' => 'Tuesday',
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

        $candidateCourse = $this->createInternalCourse($subject);
        $newEnrollment = $this->createEnrollment($student, $subject, [
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        $response = $this
            ->from(route('admin.schedules.enrollments.show', $newEnrollment))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.schedules.enrollments.store', $newEnrollment), [
                'course_id' => $candidateCourse->id,
                'teacher_id' => $teacherB->id,
                'new_course_title' => '',
                'new_course_description' => '',
                'day_of_week' => 'Tuesday',
                'start_date' => '2026-04-16',
                'end_date' => '2026-05-16',
                'start_time' => '19:00',
                'end_time' => '21:00',
                'capacity' => 20,
                'note' => 'Thu nghiem xung dot hoc vien',
            ]);

        $response->assertRedirect(route('admin.schedules.enrollments.show', $newEnrollment));
        $response->assertSessionHasErrors('start_time');
        $this->assertSame(Enrollment::STATUS_APPROVED, $newEnrollment->fresh()->status);
        $this->assertNull($newEnrollment->fresh()->course_id);
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
}