<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_subject_show_page_loads_for_students(): void
    {
        $student = User::factory()->create([
            'role_id' => Role::idByName('student'),
        ]);

        [$category, $subject] = $this->createCatalogSubject();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('khoa-hoc.show', $subject->id));

        $response->assertOk();
        $response->assertSee('Đăng ký khóa học');
        $response->assertSee($subject->name);
    }

    public function test_student_can_submit_subject_enrollment_without_preassigned_course(): void
    {
        $student = User::factory()->create([
            'role_id' => Role::idByName('student'),
        ]);

        [, $subject] = $this->createCatalogSubject();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->post(route('khoa-hoc.enroll', $subject->id), [
                'start_time' => '18:00',
                'end_time' => '20:15',
                'preferred_days' => ['Monday', 'Wednesday', 'Friday'],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('dang_ky', [
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => null,
            'status' => Enrollment::STATUS_PENDING,
        ]);
    }

    public function test_student_without_scheduled_class_is_redirected_from_internal_class_page(): void
    {
        $student = User::factory()->create([
            'role_id' => Role::idByName('student'),
        ]);

        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('courses.show', $course->id));

        $response->assertRedirect(route('khoa-hoc.show', $subject->id));
        $response->assertSessionHas('error');
    }

    public function test_scheduled_student_can_open_internal_class_page(): void
    {
        $student = User::factory()->create([
            'role_id' => Role::idByName('student'),
        ]);

        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject);

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_SCHEDULED,
            'is_submitted' => true,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Listening',
            'content' => 'Nghe hội thoại cơ bản và bắt ý chính.',
            'session_count' => 5,
            'position' => 1,
            'status' => Module::STATUS_PUBLISHED,
        ]);

        Lesson::create([
            'module_id' => $module->id,
            'title' => 'Buổi 1',
            'description' => 'Làm quen với chủ đề và từ khóa.',
            'content' => 'Nội dung buổi 1.',
            'order' => 1,
            'duration' => 45,
        ]);

        Lesson::create([
            'module_id' => $module->id,
            'title' => 'Buổi 2',
            'description' => 'Luyện nghe chi tiết.',
            'content' => 'Nội dung buổi 2.',
            'order' => 2,
            'duration' => 45,
        ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('courses.show', $course->id));

        $response->assertOk();
        $response->assertSee($course->title);
        $response->assertSee('Lộ trình trong lớp học');
        $response->assertSee('Listening');
        $response->assertSee('5 buổi');
        $response->assertSee('Nghe hội thoại cơ bản và bắt ý chính.');
    }

    public function test_admin_must_use_phase_9_to_schedule_custom_request(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::idByName('admin'),
        ]);
        $student = User::factory()->create([
            'role_id' => Role::idByName('student'),
        ]);

        [, $subject] = $this->createCatalogSubject();

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => Enrollment::STATUS_PENDING,
            'is_submitted' => true,
        ]);

        $response = $this
            ->from(route('admin.enrollments.custom.show', $enrollment))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.enrollments.review', $enrollment), [
                'action' => 'schedule',
                'course_id' => '',
                'assigned_teacher_id' => '',
                'schedule' => '',
                'note' => '',
            ]);

        $response->assertRedirect(route('admin.enrollments.custom.show', $enrollment));
        $response->assertSessionHasErrors('action');

        $this->assertDatabaseHas('dang_ky', [
            'id' => $enrollment->id,
            'status' => Enrollment::STATUS_PENDING,
            'course_id' => null,
        ]);
    }

    public function test_admin_cannot_pick_existing_class_from_phase_8_for_custom_request(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::idByName('admin'),
        ]);
        $student = User::factory()->create([
            'role_id' => Role::idByName('student'),
        ]);
        $teacher = User::factory()->create([
            'role_id' => Role::idByName('teacher'),
        ]);

        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject, $teacher, 'T2-T4-T6, 18:00-20:15');

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => Enrollment::STATUS_PENDING,
            'is_submitted' => true,
        ]);

        $response = $this
            ->from(route('admin.enrollments.custom.show', $enrollment))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.enrollments.review', $enrollment), [
                'action' => 'schedule',
                'course_id' => $course->id,
                'assigned_teacher_id' => '',
                'schedule' => '',
                'note' => '',
            ]);

        $response->assertRedirect(route('admin.enrollments.custom.show', $enrollment));
        $response->assertSessionHasErrors('action');

        $this->assertDatabaseHas('dang_ky', [
            'id' => $enrollment->id,
            'subject_id' => $subject->id,
            'course_id' => null,
            'assigned_teacher_id' => null,
            'status' => Enrollment::STATUS_PENDING,
        ]);
    }

    public function test_subject_enrollment_update_keeps_approved_status(): void
    {
        $student = User::factory()->create([
            'role_id' => Role::idByName('student'),
        ]);
        $admin = User::factory()->create([
            'role_id' => Role::idByName('admin'),
        ]);

        [, $subject] = $this->createCatalogSubject();

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => Enrollment::STATUS_APPROVED,
            'start_time' => '17:30',
            'end_time' => '19:30',
            'preferred_days' => ['Monday', 'Wednesday'],
            'note' => 'Can cap nhat lai lich.',
            'is_submitted' => true,
            'submitted_at' => now()->subDay(),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subHours(2),
        ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->post(route('khoa-hoc.enroll', $subject->id), [
                'start_time' => '18:00',
                'end_time' => '20:15',
                'preferred_days' => ['Tuesday', 'Thursday'],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $updatedEnrollment = $enrollment->fresh();
        $this->assertSame(Enrollment::STATUS_APPROVED, $updatedEnrollment->status);
        $this->assertSame('18:00', $updatedEnrollment->start_time);
        $this->assertSame('20:15', $updatedEnrollment->end_time);
        $this->assertSame(['Tuesday', 'Thursday'], $updatedEnrollment->preferred_days);
        $this->assertNull($updatedEnrollment->note);
        $this->assertSame($admin->id, $updatedEnrollment->reviewed_by);
        $this->assertNotNull($updatedEnrollment->reviewed_at);
    }

    private function createCatalogSubject(): array
    {
        $category = Category::create([
            'name' => 'Ngoại ngữ - Tin học',
            'slug' => 'ngoai-ngu-tin-hoc',
            'order' => 1,
            'status' => Category::STATUS_ACTIVE,
        ]);

        $subject = Subject::create([
            'name' => 'Tin học văn phòng',
            'category_id' => $category->id,
            'price' => 1500000,
            'status' => Subject::STATUS_OPEN,
        ]);

        return [$category, $subject];
    }

    private function createInternalCourse(Subject $subject, ?User $teacher = null, ?string $schedule = null): Course
    {
        return Course::create([
            'subject_id' => $subject->id,
            'title' => $subject->name . ' - Ca tối',
            'description' => 'Lớp học nội bộ dành cho học viên đã được admin xếp lớp.',
            'teacher_id' => $teacher?->id,
            'schedule' => $schedule,
        ]);
    }
}
