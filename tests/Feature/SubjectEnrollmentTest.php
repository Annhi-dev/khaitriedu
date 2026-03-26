<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
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
            'role' => 'hoc_vien',
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
            'role' => 'hoc_vien',
        ]);

        [, $subject] = $this->createCatalogSubject();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->post(route('khoa-hoc.enroll', $subject->id), [
                'start_time' => '18:00',
                'end_time' => '20:00',
                'preferred_days' => ['Monday', 'Wednesday', 'Friday'],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('dang_ky', [
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => null,
            'status' => 'pending',
        ]);
    }

    public function test_student_without_confirmed_class_is_redirected_from_internal_class_page(): void
    {
        $student = User::factory()->create([
            'role' => 'hoc_vien',
        ]);

        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('courses.show', $course->id));

        $response->assertRedirect(route('khoa-hoc.show', $subject->id));
        $response->assertSessionHas('error');
    }

    public function test_confirmed_student_can_open_internal_class_page(): void
    {
        $student = User::factory()->create([
            'role' => 'hoc_vien',
        ]);

        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject);

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'status' => 'confirmed',
            'is_submitted' => true,
        ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('courses.show', $course->id));

        $response->assertOk();
        $response->assertSee($course->title);
        $response->assertSee('Lộ trình trong lớp học');
    }

    public function test_admin_must_choose_class_before_confirming_enrollment(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $student = User::factory()->create([
            'role' => 'hoc_vien',
        ]);

        [, $subject] = $this->createCatalogSubject();

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => 'pending',
            'is_submitted' => true,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.enrollments.update', $enrollment->id), [
                'status' => 'confirmed',
                'course_id' => '',
                'assigned_teacher_id' => '',
                'schedule' => '',
                'note' => '',
            ]);

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('dang_ky', [
            'id' => $enrollment->id,
            'status' => 'pending',
            'course_id' => null,
        ]);
    }

    public function test_admin_confirmation_defaults_teacher_and_schedule_from_selected_class(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $student = User::factory()->create([
            'role' => 'hoc_vien',
        ]);
        $teacher = User::factory()->create([
            'role' => 'giang_vien',
        ]);

        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject, $teacher, 'T2-T4-T6, 18:00-20:00');

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => 'pending',
            'is_submitted' => true,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.enrollments.update', $enrollment->id), [
                'status' => 'confirmed',
                'course_id' => $course->id,
                'assigned_teacher_id' => '',
                'schedule' => '',
                'note' => '',
            ]);

        $response->assertRedirect(route('admin.enrollments'));

        $this->assertDatabaseHas('dang_ky', [
            'id' => $enrollment->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'assigned_teacher_id' => $teacher->id,
            'schedule' => 'T2-T4-T6, 18:00-20:00',
            'status' => 'confirmed',
        ]);
    }

    private function createCatalogSubject(): array
    {
        $category = Category::create([
            'name' => 'Ngoại ngữ - Tin học',
            'slug' => 'ngoai-ngu-tin-hoc',
            'order' => 1,
        ]);

        $subject = Subject::create([
            'name' => 'Tin học văn phòng',
            'category_id' => $category->id,
            'price' => 1500000,
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
