<?php

namespace Tests\Feature;

use App\Models\NhomHoc;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\BaiHoc;
use App\Models\HocPhan;
use App\Models\VaiTro;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ghi_danh_mon_hoc_test extends TestCase
{
    use RefreshDatabase;

    public function test_subject_show_page_loads_for_students(): void
    {
        $student = NguoiDung::factory()->create([
            'role_id' => VaiTro::idByName('student'),
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
        $student = NguoiDung::factory()->create([
            'role_id' => VaiTro::idByName('student'),
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
            'status' => GhiDanh::STATUS_PENDING,
        ]);
    }

    public function test_student_without_scheduled_class_is_redirected_from_internal_class_page(): void
    {
        $student = NguoiDung::factory()->create([
            'role_id' => VaiTro::idByName('student'),
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
        $student = NguoiDung::factory()->create([
            'role_id' => VaiTro::idByName('student'),
        ]);

        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject);

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'status' => GhiDanh::STATUS_SCHEDULED,
            'is_submitted' => true,
        ]);

        $module = HocPhan::create([
            'course_id' => $course->id,
            'title' => 'Listening',
            'content' => 'Nghe hội thoại cơ bản và bắt ý chính.',
            'session_count' => 5,
            'position' => 1,
            'status' => HocPhan::STATUS_PUBLISHED,
        ]);

        BaiHoc::create([
            'module_id' => $module->id,
            'title' => 'Buổi 1',
            'description' => 'Làm quen với chủ đề và từ khóa.',
            'content' => 'Nội dung buổi 1.',
            'order' => 1,
            'duration' => 45,
        ]);

        BaiHoc::create([
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
        $admin = NguoiDung::factory()->create([
            'role_id' => VaiTro::idByName('admin'),
        ]);
        $student = NguoiDung::factory()->create([
            'role_id' => VaiTro::idByName('student'),
        ]);

        [, $subject] = $this->createCatalogSubject();

        $enrollment = GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => GhiDanh::STATUS_PENDING,
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
            'status' => GhiDanh::STATUS_PENDING,
            'course_id' => null,
        ]);
    }

    public function test_admin_cannot_pick_existing_class_from_phase_8_for_custom_request(): void
    {
        $admin = NguoiDung::factory()->create([
            'role_id' => VaiTro::idByName('admin'),
        ]);
        $student = NguoiDung::factory()->create([
            'role_id' => VaiTro::idByName('student'),
        ]);
        $teacher = NguoiDung::factory()->create([
            'role_id' => VaiTro::idByName('teacher'),
        ]);

        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject, $teacher, 'T2-T4-T6, 18:00-20:15');

        $enrollment = GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => GhiDanh::STATUS_PENDING,
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
            'status' => GhiDanh::STATUS_PENDING,
        ]);
    }

    public function test_subject_enrollment_update_keeps_approved_status(): void
    {
        $student = NguoiDung::factory()->create([
            'role_id' => VaiTro::idByName('student'),
        ]);
        $admin = NguoiDung::factory()->create([
            'role_id' => VaiTro::idByName('admin'),
        ]);

        [, $subject] = $this->createCatalogSubject();

        $enrollment = GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => GhiDanh::STATUS_APPROVED,
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
        $this->assertSame(GhiDanh::STATUS_APPROVED, $updatedEnrollment->status);
        $this->assertSame('18:00', $updatedEnrollment->start_time);
        $this->assertSame('20:15', $updatedEnrollment->end_time);
        $this->assertSame(['Tuesday', 'Thursday'], $updatedEnrollment->preferred_days);
        $this->assertNull($updatedEnrollment->note);
        $this->assertSame($admin->id, $updatedEnrollment->reviewed_by);
        $this->assertNotNull($updatedEnrollment->reviewed_at);
    }

    private function createCatalogSubject(): array
    {
        $category = NhomHoc::create([
            'name' => 'Ngoại ngữ - Tin học',
            'slug' => 'ngoai-ngu-tin-hoc',
            'order' => 1,
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        $subject = MonHoc::create([
            'name' => 'Tin học văn phòng',
            'category_id' => $category->id,
            'price' => 1500000,
            'status' => MonHoc::STATUS_OPEN,
        ]);

        return [$category, $subject];
    }

    private function createInternalCourse(MonHoc $subject, ?NguoiDung $teacher = null, ?string $schedule = null): KhoaHoc
    {
        return KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => $subject->name . ' - Ca tối',
            'description' => 'Lớp học nội bộ dành cho học viên đã được admin xếp lớp.',
            'teacher_id' => $teacher?->id,
            'schedule' => $schedule,
        ]);
    }
}

