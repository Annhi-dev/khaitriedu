<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEnrollmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_filter_enrollments(): void
    {
        $admin = User::factory()->admin()->create();
        [, $subjectA] = $this->createCatalogSubject('Tin hoc van phong');
        [, $subjectB] = $this->createCatalogSubject('Tieng Anh giao tiep', 'ngoai-ngu');
        $studentA = User::factory()->student()->create([
            'name' => 'Nguyen Van Lan',
            'email' => 'lan@example.com',
        ]);
        $studentB = User::factory()->student()->create([
            'name' => 'Tran Minh',
            'email' => 'minh@example.com',
        ]);

        $this->createPendingEnrollment($studentA, $subjectA);
        Enrollment::create([
            'user_id' => $studentB->id,
            'subject_id' => $subjectB->id,
            'status' => Enrollment::STATUS_REJECTED,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'preferred_days' => json_encode(['Tuesday', 'Thursday']),
            'is_submitted' => true,
            'submitted_at' => now(),
            'note' => 'Khung gio khong phu hop',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments', [
                'search' => 'Lan',
                'status' => Enrollment::STATUS_PENDING,
            ]));

        $response->assertOk();
        $response->assertSee('Phase 8');
        $response->assertSee($studentA->name);
        $response->assertDontSee($studentB->name);
    }

    public function test_admin_can_distinguish_custom_schedule_requests_from_fixed_class_enrollments(): void
    {
        $admin = User::factory()->admin()->create();
        $studentCustom = User::factory()->student()->create(['name' => 'Hoc Vien Linh Hoat']);
        $studentFixed = User::factory()->student()->create(['name' => 'Hoc Vien Lop Co Dinh']);
        [, $subjectCustom] = $this->createCatalogSubject('Tin hoc van phong', 'tin-hoc-van-phong');
        [, $subjectFixed] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');

        $this->createPendingEnrollment($studentCustom, $subjectCustom);
        $fixedCourse = $this->createInternalCourse($subjectFixed, null, 'T3-T5-T7, 18:00-20:00');

        Enrollment::create([
            'user_id' => $studentFixed->id,
            'subject_id' => $subjectFixed->id,
            'course_id' => $fixedCourse->id,
            'status' => Enrollment::STATUS_ENROLLED,
            'schedule' => $fixedCourse->schedule,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments'));

        $response->assertOk();
        $response->assertSee('Yêu cầu lịch học riêng');
        $response->assertSee('Ghi danh lớp cố định');
        $response->assertSee($studentCustom->name);
        $response->assertSee($studentFixed->name);
    }

    public function test_admin_can_filter_enrollments_by_request_source(): void
    {
        $admin = User::factory()->admin()->create();
        $studentCustom = User::factory()->student()->create(['name' => 'Hoc Vien Theo Yeu Cau']);
        $studentFixed = User::factory()->student()->create(['name' => 'Hoc Vien Lop Co Dinh']);
        [, $subjectCustom] = $this->createCatalogSubject('Tin hoc van phong', 'tin-hoc-van-phong');
        [, $subjectFixed] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');

        $this->createPendingEnrollment($studentCustom, $subjectCustom);
        $fixedCourse = $this->createInternalCourse($subjectFixed, null, 'T3-T5-T7, 18:00-20:00');

        Enrollment::create([
            'user_id' => $studentFixed->id,
            'subject_id' => $subjectFixed->id,
            'course_id' => $fixedCourse->id,
            'status' => Enrollment::STATUS_ENROLLED,
            'schedule' => $fixedCourse->schedule,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments', [
                'request_source' => Enrollment::REQUEST_SOURCE_FIXED_CLASS,
            ]));

        $response->assertOk();
        $response->assertSee('Loại hồ sơ');
        $response->assertSee($studentFixed->name);
        $response->assertDontSee($studentCustom->name);
    }

    public function test_admin_can_approve_enrollment(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();
        $enrollment = $this->createPendingEnrollment($student, $subject);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.enrollments.review', $enrollment), [
                'action' => 'approve',
                'course_id' => '',
                'assigned_teacher_id' => '',
                'schedule' => '',
                'note' => 'Ho so hop le',
            ]);

        $response->assertRedirect(route('admin.enrollments.show', $enrollment));
        $this->assertDatabaseHas('dang_ky', [
            'id' => $enrollment->id,
            'status' => Enrollment::STATUS_APPROVED,
            'reviewed_by' => $admin->id,
            'note' => 'Ho so hop le',
        ]);
        $this->assertNotNull($enrollment->fresh()->reviewed_at);
    }

    public function test_custom_schedule_request_cannot_be_scheduled_directly_from_detail_form(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject, $teacher, 'T2-T4-T6, 18:00-20:00');
        $enrollment = $this->createPendingEnrollment($student, $subject);

        $response = $this
            ->from(route('admin.enrollments.show', $enrollment))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.enrollments.review', $enrollment), [
                'action' => 'schedule',
                'course_id' => $course->id,
                'assigned_teacher_id' => '',
                'schedule' => '',
                'note' => '',
            ]);

        $response->assertRedirect(route('admin.enrollments.show', $enrollment));
        $response->assertSessionHasErrors('action');
        $this->assertDatabaseHas('dang_ky', [
            'id' => $enrollment->id,
            'status' => Enrollment::STATUS_PENDING,
            'course_id' => null,
            'assigned_teacher_id' => null,
        ]);
    }

    public function test_admin_can_reject_enrollment_with_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();
        $enrollment = $this->createPendingEnrollment($student, $subject);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.enrollments.review', $enrollment), [
                'action' => 'reject',
                'course_id' => '',
                'assigned_teacher_id' => '',
                'schedule' => '',
                'note' => 'Lich dang ky chua phu hop voi khoa hoc nay',
            ]);

        $response->assertRedirect(route('admin.enrollments.show', $enrollment));
        $this->assertDatabaseHas('dang_ky', [
            'id' => $enrollment->id,
            'status' => Enrollment::STATUS_REJECTED,
            'note' => 'Lich dang ky chua phu hop voi khoa hoc nay',
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_admin_can_request_enrollment_update_and_keep_it_pending(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();
        $enrollment = $this->createPendingEnrollment($student, $subject);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.enrollments.review', $enrollment), [
                'action' => 'request_update',
                'course_id' => '',
                'assigned_teacher_id' => '',
                'schedule' => '',
                'note' => 'Vui long bo sung them khung gio hoc trong tuan',
            ]);

        $response->assertRedirect(route('admin.enrollments.show', $enrollment));
        $this->assertDatabaseHas('dang_ky', [
            'id' => $enrollment->id,
            'status' => Enrollment::STATUS_PENDING,
            'note' => 'Vui long bo sung them khung gio hoc trong tuan',
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_custom_schedule_request_detail_guides_admin_to_create_new_class_in_phase_9(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();
        $enrollment = $this->createPendingEnrollment($student, $subject);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments.show', $enrollment));

        $response->assertOk();
        $response->assertSee('Mở màn tạo lớp và xếp lịch phase 9');
        $response->assertSee('Hồ sơ này không chọn lớp nội bộ có sẵn ở màn chi tiết');
        $response->assertDontSee('Giảng viên phụ trách');
        $response->assertDontSee('Lịch học chính thức');
        $response->assertDontSee('Xếp lớp và chốt lịch');
    }

    public function test_admin_can_view_custom_schedule_request_detail_with_request_source_label(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();
        $enrollment = $this->createPendingEnrollment($student, $subject);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments.show', $enrollment));

        $response->assertOk();
        $response->assertSee('Yêu cầu lịch học riêng');
        $response->assertSee('Thứ 2');
        $response->assertSee('Thứ 4');
        $response->assertSee('Thứ 6');
    }

    public function test_student_is_blocked_from_admin_enrollments(): void
    {
        $student = User::factory()->student()->create();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('admin.enrollments'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_teacher_is_blocked_from_admin_enrollments(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('admin.enrollments'));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }

    private function createCatalogSubject(string $subjectName = 'Tin hoc van phong', string $slug = 'tin-hoc-van-phong'): array
    {
        $category = Category::create([
            'name' => 'Nhom hoc ' . $slug,
            'slug' => 'ngoai-ngu-tin-hoc-' . $slug,
            'status' => Category::STATUS_ACTIVE,
        ]);

        $subject = Subject::create([
            'name' => $subjectName,
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'price' => 1500000,
        ]);

        return [$category, $subject];
    }

    private function createPendingEnrollment(User $student, Subject $subject): Enrollment
    {
        return Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => Enrollment::STATUS_PENDING,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'preferred_days' => json_encode(['Monday', 'Wednesday', 'Friday']),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);
    }

    private function createInternalCourse(Subject $subject, ?User $teacher = null, ?string $schedule = null): Course
    {
        return Course::create([
            'subject_id' => $subject->id,
            'title' => $subject->name . ' - Lop toi',
            'description' => 'Lop hoc noi bo danh cho hoc vien da duoc xep lop.',
            'teacher_id' => $teacher?->id,
            'schedule' => $schedule,
        ]);
    }
}
