<?php

namespace Tests\Feature;

use App\Models\KhoaHoc;
use App\Models\PhongBan;
use App\Models\GhiDanh;
use App\Models\YeuCauDoiLich;
use App\Models\MonHoc;
use App\Models\DonUngTuyenGiaoVien;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class quan_ly_giao_vien_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_teacher_list(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $teacherA = NguoiDung::factory()->teacher()->create([
            'name' => 'Giang Vien A (Phát triển Cá nhân)',
            'email' => 'teacher-a@example.com',
        ]);
        $subject = MonHoc::create([
            'name' => 'Tieng Anh giao tiep',
            'price' => 1800000,
        ]);
        KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => 'Tieng Anh giao tiep - Lop sang',
            'teacher_id' => $teacherA->id,
            'schedule' => 'Thu 2 - Thu 4, 18:00 - 20:00',
        ]);
        $teacherB = NguoiDung::factory()->teacher()->inactive()->create([
            'name' => 'Giang Vien B',
            'email' => 'teacher-b@example.com',
        ]);
        $student = NguoiDung::factory()->student()->create([
            'name' => 'Student Hidden',
            'email' => 'student-hidden@example.com',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.teachers.index'));

        $response->assertOk();
        $response->assertSee('Quản lý giảng viên');
        $response->assertSee('Chuyên môn');
        $response->assertDontSee('Giang Vien A (Phát triển Cá nhân)');
        $response->assertSee('Giang Vien A');
        $response->assertSee('Phát triển Cá nhân');
        $response->assertSee($teacherA->email);
        $response->assertSee($teacherB->email);
        $response->assertSee('0 yêu cầu');
        $response->assertDontSee('Tieng Anh giao tiep');
        $response->assertDontSee($student->email);
    }

    public function test_admin_can_search_and_filter_teachers(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $targetDepartment = PhongBan::create([
            'code' => 'PH-DT-01',
            'name' => 'Phong Dao tao 01',
            'status' => PhongBan::STATUS_ACTIVE,
        ]);
        $otherDepartment = PhongBan::create([
            'code' => 'PH-DT-02',
            'name' => 'Phong Dao tao 02',
            'status' => PhongBan::STATUS_ACTIVE,
        ]);

        $target = NguoiDung::factory()->teacher()->locked()->create([
            'name' => 'Tran Minh Locked',
            'email' => 'teacher-locked@example.com',
            'phone' => '0911000111',
            'department_id' => $targetDepartment->id,
        ]);
        $other = NguoiDung::factory()->teacher()->create([
            'name' => 'Tran Minh Active',
            'email' => 'teacher-active@example.com',
            'phone' => '0911000222',
            'department_id' => $otherDepartment->id,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.teachers.index', [
                'search' => 'Tran Minh',
                'status' => NguoiDung::STATUS_LOCKED,
                'department_id' => $targetDepartment->id,
            ]));

        $response->assertOk();
        $response->assertSee($target->email);
        $response->assertDontSee($other->email);
    }

    public function test_admin_can_create_teacher(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $department = PhongBan::create([
            'code' => 'PH-GV-01',
            'name' => 'Phong Giang Vien 01',
            'status' => PhongBan::STATUS_ACTIVE,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teachers.store'), [
                'name' => 'Giang Vien Moi',
                'username' => 'giangvienmoi',
                'email' => 'giangvienmoi@example.com',
                'phone' => '0912345678',
                'department_id' => $department->id,
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'status' => NguoiDung::STATUS_ACTIVE,
            ]);

        $teacher = NguoiDung::where('email', 'giangvienmoi@example.com')->first();

        $response->assertRedirect(route('admin.teachers.show', $teacher));
        $this->assertDatabaseHas('nguoi_dung', [
            'email' => 'giangvienmoi@example.com',
            'role_id' => \App\Models\VaiTro::idByName(NguoiDung::ROLE_TEACHER),
            'department_id' => $department->id,
            'status' => NguoiDung::STATUS_ACTIVE,
        ]);
    }

    public function test_admin_can_update_teacher(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $departmentA = PhongBan::create([
            'code' => 'PH-SUA-01',
            'name' => 'Phong Sua 01',
            'status' => PhongBan::STATUS_ACTIVE,
        ]);
        $departmentB = PhongBan::create([
            'code' => 'PH-SUA-02',
            'name' => 'Phong Sua 02',
            'status' => PhongBan::STATUS_ACTIVE,
        ]);
        $teacher = NguoiDung::factory()->teacher()->create([
            'name' => 'Giang Vien Cu',
            'username' => 'giangviencu',
            'email' => 'giangviencu@example.com',
            'phone' => '0911111000',
            'department_id' => $departmentA->id,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teachers.update', $teacher), [
                'name' => 'Giang Vien Da Sua',
                'username' => 'giangviendasua',
                'email' => 'giangviendasua@example.com',
                'phone' => '0912222333',
                'department_id' => $departmentB->id,
                'password' => '',
                'password_confirmation' => '',
                'status' => NguoiDung::STATUS_INACTIVE,
            ]);

        $response->assertRedirect(route('admin.teachers.show', $teacher));
        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $teacher->id,
            'name' => 'Giang Vien Da Sua',
            'username' => 'giangviendasua',
            'email' => 'giangviendasua@example.com',
            'phone' => '0912222333',
            'department_id' => $departmentB->id,
            'status' => NguoiDung::STATUS_INACTIVE,
        ]);
    }

    public function test_admin_can_view_teacher_detail_with_teaching_data(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $teacher = NguoiDung::factory()->teacher()->create([
            'name' => 'Giang Vien Chi Tiet (Phát triển Cá nhân)',
            'email' => 'giangvienchitiet@example.com',
        ]);
        $student = NguoiDung::factory()->student()->create([
            'name' => 'Hoc Vien A',
        ]);
        $subject = MonHoc::create([
            'name' => 'Tieng Anh giao tiep',
            'price' => 1800000,
        ]);
        $course = KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => 'Tieng Anh giao tiep - Lop toi',
            'teacher_id' => $teacher->id,
            'schedule' => 'Thu 3 - Thu 5, 18:30 - 20:30',
        ]);

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => 'confirmed',
            'schedule' => 'Thu 3 - Thu 5, 18:30 - 20:30',
            'is_submitted' => true,
        ]);

        DonUngTuyenGiaoVien::create([
            'name' => $teacher->name,
            'email' => $teacher->email,
            'experience' => '5 nam day giao tiep',
            'message' => 'Chuyen day giao tiep va luyen phan xa',
            'status' => 'approved',
        ]);

        YeuCauDoiLich::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'requested_date' => '2026-04-01',
            'requested_start_time' => '19:00',
            'requested_end_time' => '21:00',
            'reason' => 'Cần dời buổi do công tác',
            'status' => YeuCauDoiLich::STATUS_PENDING,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.teachers.show', $teacher));

        $response->assertOk();
        $response->assertSee('Giang Vien Chi Tiet');
        $response->assertSee('Phát triển Cá nhân');
        $response->assertSee('Chuyên môn giảng dạy');
        $response->assertSee('Tieng Anh giao tiep');
        $response->assertDontSee('Tieng Anh giao tiep - Lop toi');
        $response->assertSee('Thu 3 - Thu 5, 18:30 - 20:30');
        $response->assertSee('5 nam day giao tiep');
        $response->assertSee('Cần dời buổi do công tác');
    }

    public function test_admin_can_lock_and_unlock_teacher(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $teacher = NguoiDung::factory()->teacher()->create();

        $lockResponse = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teachers.lock', $teacher));

        $lockResponse->assertRedirect(route('admin.teachers.show', $teacher));
        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $teacher->id,
            'status' => NguoiDung::STATUS_LOCKED,
        ]);

        $unlockResponse = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teachers.unlock', $teacher));

        $unlockResponse->assertRedirect(route('admin.teachers.show', $teacher));
        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $teacher->id,
            'status' => NguoiDung::STATUS_ACTIVE,
        ]);
    }

    public function test_student_is_blocked_from_teacher_management(): void
    {
        $student = NguoiDung::factory()->student()->create();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('admin.teachers.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_teacher_is_blocked_from_admin_teacher_management(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create();

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('admin.teachers.index'));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_admin_cannot_create_teacher_with_duplicate_email_username_or_phone(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $department = PhongBan::create([
            'code' => 'PH-DUP-01',
            'name' => 'Phong Duplicate',
            'status' => PhongBan::STATUS_ACTIVE,
        ]);
        NguoiDung::factory()->teacher()->create([
            'username' => 'duplicate-teacher',
            'email' => 'duplicate-teacher@example.com',
            'phone' => '0913333444',
            'department_id' => $department->id,
        ]);

        $response = $this
            ->from(route('admin.teachers.create'))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teachers.store'), [
                'name' => 'Giang Vien Trung',
                'username' => 'duplicate-teacher',
                'email' => 'duplicate-teacher@example.com',
                'phone' => '0913333444',
                'department_id' => $department->id,
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'status' => NguoiDung::STATUS_ACTIVE,
            ]);

        $response->assertRedirect(route('admin.teachers.create'));
        $response->assertSessionHasErrors(['email', 'username', 'phone']);
    }
}

