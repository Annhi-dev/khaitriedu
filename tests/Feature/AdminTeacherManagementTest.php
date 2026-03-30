<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ScheduleChangeRequest;
use App\Models\Subject;
use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_teacher_list(): void
    {
        $admin = User::factory()->admin()->create();
        $teacherA = User::factory()->teacher()->create([
            'name' => 'Giang Vien A',
            'email' => 'teacher-a@example.com',
        ]);
        $teacherB = User::factory()->teacher()->inactive()->create([
            'name' => 'Giang Vien B',
            'email' => 'teacher-b@example.com',
        ]);
        $student = User::factory()->student()->create([
            'name' => 'Student Hidden',
            'email' => 'student-hidden@example.com',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.teachers.index'));

        $response->assertOk();
        $response->assertSee('Quản lý giảng viên');
        $response->assertSee($teacherA->email);
        $response->assertSee($teacherB->email);
        $response->assertDontSee($student->email);
    }

    public function test_admin_can_search_and_filter_teachers(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->teacher()->locked()->create([
            'name' => 'Tran Minh Locked',
            'email' => 'teacher-locked@example.com',
            'phone' => '0911000111',
        ]);
        $other = User::factory()->teacher()->create([
            'name' => 'Tran Minh Active',
            'email' => 'teacher-active@example.com',
            'phone' => '0911000222',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.teachers.index', [
                'search' => 'Tran Minh',
                'status' => User::STATUS_LOCKED,
            ]));

        $response->assertOk();
        $response->assertSee($target->email);
        $response->assertDontSee($other->email);
    }

    public function test_admin_can_create_teacher(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teachers.store'), [
                'name' => 'Giang Vien Moi',
                'username' => 'giangvienmoi',
                'email' => 'giangvienmoi@example.com',
                'phone' => '0912345678',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'status' => User::STATUS_ACTIVE,
            ]);

        $teacher = User::where('email', 'giangvienmoi@example.com')->first();

        $response->assertRedirect(route('admin.teachers.show', $teacher));
        $this->assertDatabaseHas('nguoi_dung', [
            'email' => 'giangvienmoi@example.com',
            'role_id' => \App\Models\Role::idByName(User::ROLE_TEACHER),
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_admin_can_update_teacher(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create([
            'name' => 'Giang Vien Cu',
            'username' => 'giangviencu',
            'email' => 'giangviencu@example.com',
            'phone' => '0911111000',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teachers.update', $teacher), [
                'name' => 'Giang Vien Da Sua',
                'username' => 'giangviendasua',
                'email' => 'giangviendasua@example.com',
                'phone' => '0912222333',
                'password' => '',
                'password_confirmation' => '',
                'status' => User::STATUS_INACTIVE,
            ]);

        $response->assertRedirect(route('admin.teachers.show', $teacher));
        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $teacher->id,
            'name' => 'Giang Vien Da Sua',
            'username' => 'giangviendasua',
            'email' => 'giangviendasua@example.com',
            'phone' => '0912222333',
            'status' => User::STATUS_INACTIVE,
        ]);
    }

    public function test_admin_can_view_teacher_detail_with_teaching_data(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create([
            'name' => 'Giang Vien Chi Tiet',
            'email' => 'giangvienchitiet@example.com',
        ]);
        $student = User::factory()->student()->create([
            'name' => 'Hoc Vien A',
        ]);
        $subject = Subject::create([
            'name' => 'Tieng Anh giao tiep',
            'price' => 1800000,
        ]);
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Tieng Anh giao tiep - Lop toi',
            'teacher_id' => $teacher->id,
            'schedule' => 'Thu 3 - Thu 5, 18:30 - 20:30',
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => 'confirmed',
            'schedule' => 'Thu 3 - Thu 5, 18:30 - 20:30',
            'is_submitted' => true,
        ]);

        TeacherApplication::create([
            'name' => $teacher->name,
            'email' => $teacher->email,
            'experience' => '5 nam day giao tiep',
            'message' => 'Chuyen day giao tiep va luyen phan xa',
            'status' => 'approved',
        ]);

        ScheduleChangeRequest::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'requested_date' => '2026-04-01',
            'requested_start_time' => '19:00',
            'requested_end_time' => '21:00',
            'reason' => 'Can doi lich do cong tac',
            'status' => ScheduleChangeRequest::STATUS_PENDING,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.teachers.show', $teacher));

        $response->assertOk();
        $response->assertSee('Giang Vien Chi Tiet');
        $response->assertSee('Tieng Anh giao tiep - Lop toi');
        $response->assertSee('Thu 3 - Thu 5, 18:30 - 20:30');
        $response->assertSee('5 nam day giao tiep');
        $response->assertSee('Can doi lich do cong tac');
    }

    public function test_admin_can_lock_and_unlock_teacher(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();

        $lockResponse = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teachers.lock', $teacher));

        $lockResponse->assertRedirect(route('admin.teachers.show', $teacher));
        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $teacher->id,
            'status' => User::STATUS_LOCKED,
        ]);

        $unlockResponse = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teachers.unlock', $teacher));

        $unlockResponse->assertRedirect(route('admin.teachers.show', $teacher));
        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $teacher->id,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_student_is_blocked_from_teacher_management(): void
    {
        $student = User::factory()->student()->create();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('admin.teachers.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_teacher_is_blocked_from_admin_teacher_management(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('admin.teachers.index'));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_admin_cannot_create_teacher_with_duplicate_email_username_or_phone(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->teacher()->create([
            'username' => 'duplicate-teacher',
            'email' => 'duplicate-teacher@example.com',
            'phone' => '0913333444',
        ]);

        $response = $this
            ->from(route('admin.teachers.create'))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teachers.store'), [
                'name' => 'Giang Vien Trung',
                'username' => 'duplicate-teacher',
                'email' => 'duplicate-teacher@example.com',
                'phone' => '0913333444',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'status' => User::STATUS_ACTIVE,
            ]);

        $response->assertRedirect(route('admin.teachers.create'));
        $response->assertSessionHasErrors(['email', 'username', 'phone']);
    }
}