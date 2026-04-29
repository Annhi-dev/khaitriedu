<?php

namespace Tests\Feature;

use App\Models\NhomHoc;
use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\PhongHoc;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class quan_ly_ghi_danh_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_filter_enrollments(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        [, $subjectA] = $this->createCatalogSubject('Tin hoc van phong');
        [, $subjectB] = $this->createCatalogSubject('Tieng Anh giao tiep', 'ngoai-ngu');
        $studentA = NguoiDung::factory()->student()->create([
            'name' => 'Nguyen Van Lan',
            'email' => 'lan@example.com',
        ]);
        $studentB = NguoiDung::factory()->student()->create([
            'name' => 'Tran Minh',
            'email' => 'minh@example.com',
        ]);

        $enrollmentA = $this->createPendingEnrollment($studentA, $subjectA);
        $enrollmentB = GhiDanh::create([
            'user_id' => $studentB->id,
            'subject_id' => $subjectB->id,
            'status' => GhiDanh::STATUS_REJECTED,
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
                'status' => GhiDanh::STATUS_PENDING,
            ]));

        $response->assertOk();
        $response->assertSee(route('admin.enrollments.custom.show', $enrollmentA), false);
        $response->assertDontSee(route('admin.enrollments.custom.show', $enrollmentB), false);
    }

    public function test_admin_can_distinguish_custom_schedule_requests_from_fixed_class_enrollments(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $studentCustom = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien Linh Hoat']);
        $studentFixed = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien Lop Co Dinh']);
        [, $subjectCustom] = $this->createCatalogSubject('Tin hoc van phong', 'tin-hoc-van-phong');
        [, $subjectFixed] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');

        $customEnrollment = $this->createPendingEnrollment($studentCustom, $subjectCustom);
        [, , $fixedEnrollment] = $this->createFixedClassEnrollment($studentFixed, $subjectFixed);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments'));

        $response->assertOk();
        $response->assertSee('Yêu cầu lịch học riêng');
        $response->assertSee('Ghi danh lớp cố định');
        $response->assertSee($studentCustom->name);
        $response->assertSee($studentFixed->name);
        $response->assertSee(route('admin.enrollments.custom.show', $customEnrollment), false);
        $response->assertSee(route('admin.enrollments.fixed.show', $fixedEnrollment), false);
    }

    public function test_admin_can_filter_enrollments_by_request_source(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $studentCustom = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien Theo Yeu Cau']);
        $studentFixed = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien Lop Co Dinh']);
        [, $subjectCustom] = $this->createCatalogSubject('Tin hoc van phong', 'tin-hoc-van-phong');
        [, $subjectFixed] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');

        $customEnrollment = $this->createPendingEnrollment($studentCustom, $subjectCustom);
        [, , $fixedEnrollment] = $this->createFixedClassEnrollment($studentFixed, $subjectFixed);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments', [
                'request_source' => GhiDanh::REQUEST_SOURCE_FIXED_CLASS,
            ]));

        $response->assertOk();
        $response->assertSee('Loại hồ sơ');
        $response->assertSee(route('admin.enrollments.fixed.show', $fixedEnrollment), false);
        $response->assertDontSee(route('admin.enrollments.custom.show', $customEnrollment), false);
    }

    public function test_admin_can_filter_enrollments_by_student(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $targetStudent = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien Can Loc']);
        $otherStudent = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien Khac']);
        [, $subjectA] = $this->createCatalogSubject('Tin hoc van phong', 'tin-hoc-van-phong');
        [, $subjectB] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');

        $targetPendingEnrollment = $this->createPendingEnrollment($targetStudent, $subjectA);
        [, , $targetFixedEnrollment] = $this->createFixedClassEnrollment($targetStudent, $subjectB);
        $otherEnrollment = $this->createPendingEnrollment($otherStudent, $subjectA);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments', [
                'student_id' => $targetStudent->id,
            ]));

        $response->assertOk();
        $response->assertSee(route('admin.enrollments.custom.show', $targetPendingEnrollment), false);
        $response->assertSee(route('admin.enrollments.fixed.show', $targetFixedEnrollment), false);
        $response->assertDontSee(route('admin.enrollments.custom.show', $otherEnrollment), false);
    }

    public function test_admin_can_filter_enrollments_by_class_room(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $studentA = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien Lop A']);
        $studentB = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien Lop B']);
        [, $subjectA] = $this->createCatalogSubject('Tin hoc van phong', 'tin-hoc-van-phong');
        [, $subjectB] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');

        [, $classRoomA, $enrollmentA] = $this->createFixedClassEnrollment($studentA, $subjectA);
        [, $classRoomB, $enrollmentB] = $this->createFixedClassEnrollment($studentB, $subjectB);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments', [
                'class_room_id' => $classRoomA->id,
            ]));

        $response->assertOk();
        $response->assertSee(route('admin.enrollments.fixed.show', $enrollmentA), false);
        $response->assertDontSee(route('admin.enrollments.fixed.show', $enrollmentB), false);
    }

    public function test_admin_can_approve_enrollment(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $student = NguoiDung::factory()->student()->create();
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
            'status' => GhiDanh::STATUS_APPROVED,
            'reviewed_by' => $admin->id,
            'note' => 'Ho so hop le',
        ]);
        $this->assertNotNull($enrollment->fresh()->reviewed_at);
    }

    public function test_custom_schedule_request_cannot_be_scheduled_directly_from_detail_form(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $student = NguoiDung::factory()->student()->create();
        $teacher = NguoiDung::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $course = $this->createInternalCourse($subject, $teacher, 'T2-T4-T6, 18:00-20:00');
        $enrollment = $this->createPendingEnrollment($student, $subject);

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
            'status' => GhiDanh::STATUS_PENDING,
            'course_id' => null,
            'assigned_teacher_id' => null,
        ]);
    }

    public function test_admin_can_reject_enrollment_with_reason(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $student = NguoiDung::factory()->student()->create();
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
            'status' => GhiDanh::STATUS_REJECTED,
            'note' => 'Lich dang ky chua phu hop voi khoa hoc nay',
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_admin_can_request_enrollment_update_and_keep_it_pending(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $student = NguoiDung::factory()->student()->create();
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
            'status' => GhiDanh::STATUS_PENDING,
            'note' => 'Vui long bo sung them khung gio hoc trong tuan',
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_custom_schedule_request_detail_guides_admin_to_create_new_class_in_phase_9(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $student = NguoiDung::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();
        $enrollment = $this->createPendingEnrollment($student, $subject);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments.custom.show', $enrollment));

        $response->assertOk();
        $response->assertSee('Mở màn xếp lịch');
        $response->assertSee('Hồ sơ chưa gắn lớp cố định.');
        $response->assertDontSee('Giảng viên phụ trách');
        $response->assertDontSee('Lịch học chính thức');
        $response->assertDontSee('Xếp lớp và chốt lịch');
    }

    public function test_admin_can_view_custom_schedule_request_detail_with_request_source_label(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $student = NguoiDung::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();
        $enrollment = $this->createPendingEnrollment($student, $subject);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments.custom.show', $enrollment));

        $response->assertOk();
        $response->assertSee('Yêu cầu lịch học riêng');
        $response->assertSee('Thứ 2');
        $response->assertSee('Thứ 4');
        $response->assertSee('Thứ 6');
    }

    public function test_admin_can_view_fixed_class_enrollment_detail_in_dedicated_screen(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $student = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien Lop Co Dinh']);
        [, $subject] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');
        [, $classRoom, $enrollment] = $this->createFixedClassEnrollment($student, $subject);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments.fixed.show', $enrollment));

        $response->assertOk();
        $response->assertSee('Chi tiết lớp cố định');
        $response->assertSee('Duyệt ghi danh');
        $response->assertSee($student->name);
        $response->assertSee($classRoom->displayName());
        $response->assertSee($classRoom->scheduleSummary());
        $response->assertSee('Mở trang lớp học');
        $response->assertDontSee('Mở màn xếp lịch');
        $response->assertDontSee('Yêu cầu lịch học riêng');
    }

    public function test_admin_can_view_pending_fixed_class_enrollment_with_approval_action(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $student = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien Cho Duyet']);
        [, $subject] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');
        [, , $enrollment] = $this->createFixedClassEnrollment($student, $subject);

        $enrollment->update([
            'status' => GhiDanh::STATUS_PENDING,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments.fixed.show', $enrollment));

        $response->assertOk();
        $response->assertSee('Duyệt ghi danh');
        $response->assertSee('Chi tiết lớp cố định');
        $response->assertSee($student->name);
    }

    public function test_admin_can_approve_fixed_class_enrollment_without_losing_class_data(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $student = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien Lop Co Dinh']);
        [, $subject] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');
        [, $classRoom, $enrollment] = $this->createFixedClassEnrollment($student, $subject);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.enrollments.review', $enrollment), [
                'action' => 'approve',
                'course_id' => $classRoom->course_id,
                'assigned_teacher_id' => '',
                'schedule' => '',
                'note' => 'Da duyet ghi danh lop co dinh',
            ]);

        $response->assertRedirect(route('admin.enrollments.show', $enrollment));

        $updatedEnrollment = $enrollment->fresh();
        $this->assertSame(GhiDanh::STATUS_APPROVED, $updatedEnrollment->status);
        $this->assertSame($classRoom->id, $updatedEnrollment->lop_hoc_id);
        $this->assertSame($classRoom->course_id, $updatedEnrollment->course_id);
        $this->assertSame($classRoom->teacher_id, $updatedEnrollment->assigned_teacher_id);
        $this->assertNotNull($updatedEnrollment->reviewed_at);
        $this->assertSame($admin->id, $updatedEnrollment->reviewed_by);
    }

    public function test_activating_fixed_class_enrollment_syncs_status_for_all_students_in_same_class(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $studentA = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien A']);
        $studentB = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien B']);
        [, $subject] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');
        [$course, $classRoom, $enrollmentA] = $this->createFixedClassEnrollment($studentA, $subject);

        $enrollmentB = GhiDanh::create([
            'user_id' => $studentB->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $classRoom->teacher_id,
            'status' => GhiDanh::STATUS_ENROLLED,
            'schedule' => $course->schedule,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.enrollments.review', $enrollmentA), [
                'action' => 'activate',
                'course_id' => $course->id,
                'class_room_id' => $classRoom->id,
                'assigned_teacher_id' => '',
                'schedule' => $course->schedule,
                'note' => 'Bat dau hoc dong loat cho ca lop',
            ]);

        $response->assertRedirect(route('admin.enrollments.show', $enrollmentA));

        $this->assertDatabaseHas('dang_ky', [
            'id' => $enrollmentA->id,
            'status' => GhiDanh::STATUS_ACTIVE,
            'reviewed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('dang_ky', [
            'id' => $enrollmentB->id,
            'status' => GhiDanh::STATUS_ACTIVE,
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_admin_can_approve_fixed_class_enrollment_and_restore_missing_course_from_class(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $student = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien Lop Co Dinh']);
        [, $subject] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');
        [, $classRoom, $enrollment] = $this->createFixedClassEnrollment($student, $subject);

        $enrollment->update([
            'course_id' => null,
            'assigned_teacher_id' => null,
            'schedule' => null,
            'status' => GhiDanh::STATUS_PENDING,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.enrollments.review', $enrollment), [
                'action' => 'approve',
                'course_id' => '',
                'class_room_id' => $classRoom->id,
                'assigned_teacher_id' => '',
                'schedule' => '',
                'note' => 'Da duyet ghi danh lop co dinh',
            ]);

        $response->assertRedirect(route('admin.enrollments.show', $enrollment));

        $updatedEnrollment = $enrollment->fresh();
        $this->assertSame(GhiDanh::STATUS_APPROVED, $updatedEnrollment->status);
        $this->assertSame($classRoom->id, $updatedEnrollment->lop_hoc_id);
        $this->assertSame($classRoom->course_id, $updatedEnrollment->course_id);
        $this->assertSame($classRoom->teacher_id, $updatedEnrollment->assigned_teacher_id);
        $this->assertNotNull($updatedEnrollment->reviewed_at);
        $this->assertSame($admin->id, $updatedEnrollment->reviewed_by);
    }

    public function test_admin_list_shows_current_class_label_for_fixed_class_enrollment_without_course_id(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $student = NguoiDung::factory()->student()->create(['name' => 'Hoc Vien Lop Co Dinh']);
        [, $subject] = $this->createCatalogSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep');
        [, $classRoom, $enrollment] = $this->createFixedClassEnrollment($student, $subject);

        $enrollment->update([
            'course_id' => null,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.enrollments'));

        $response->assertOk();
        $response->assertSee($classRoom->displayName());
    }

    public function test_student_is_blocked_from_admin_enrollments(): void
    {
        $student = NguoiDung::factory()->student()->create();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('admin.enrollments'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_teacher_is_blocked_from_admin_enrollments(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create();

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('admin.enrollments'));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }

    private function createCatalogSubject(string $subjectName = 'Tin hoc van phong', string $slug = 'tin-hoc-van-phong'): array
    {
        $category = NhomHoc::create([
            'name' => 'Nhom hoc ' . $slug,
            'slug' => 'ngoai-ngu-tin-hoc-' . $slug,
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        $subject = MonHoc::create([
            'name' => $subjectName,
            'category_id' => $category->id,
            'status' => MonHoc::STATUS_OPEN,
            'price' => 1500000,
        ]);

        return [$category, $subject];
    }

    private function createPendingEnrollment(NguoiDung $student, MonHoc $subject): GhiDanh
    {
        return GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => GhiDanh::STATUS_PENDING,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'preferred_days' => json_encode(['Monday', 'Wednesday', 'Friday']),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);
    }

    private function createInternalCourse(MonHoc $subject, ?NguoiDung $teacher = null, ?string $schedule = null): KhoaHoc
    {
        return KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => $subject->name . ' - Lop toi',
            'description' => 'Lop hoc noi bo danh cho hoc vien da duoc xep lop.',
            'teacher_id' => $teacher?->id,
            'schedule' => $schedule,
        ]);
    }

    private function createFixedClassEnrollment(NguoiDung $student, MonHoc $subject): array
    {
        $teacher = NguoiDung::factory()->teacher()->create([
            'name' => 'Giang vien co dinh',
        ]);
        $room = PhongHoc::create([
            'code' => 'PH' . fake()->unique()->numberBetween(100, 999),
            'name' => 'Phong hoc co dinh ' . fake()->unique()->numberBetween(1, 99),
            'type' => 'offline',
            'location' => 'Tang 3',
            'capacity' => 20,
            'status' => PhongHoc::STATUS_ACTIVE,
        ]);
        $course = $this->createInternalCourse($subject, $teacher, 'T3-T5-T7, 18:00 - 20:00');
        $classRoom = LopHoc::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'name' => $subject->name . ' - Lop co dinh',
            'room_id' => $room->id,
            'teacher_id' => $teacher->id,
            'status' => LopHoc::STATUS_OPEN,
            'duration' => 3,
        ]);

        foreach (['Tuesday', 'Thursday', 'Saturday'] as $dayOfWeek) {
            LichHoc::create([
                'lop_hoc_id' => $classRoom->id,
                'teacher_id' => $teacher->id,
                'room_id' => $room->id,
                'day_of_week' => $dayOfWeek,
                'start_time' => '18:00',
                'end_time' => '20:00',
            ]);
        }

        $enrollment = GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => GhiDanh::STATUS_ENROLLED,
            'schedule' => $course->schedule,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        return [$course, $classRoom, $enrollment];
    }
}

