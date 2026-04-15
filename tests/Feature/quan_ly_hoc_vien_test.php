<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Review;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class quan_ly_hoc_vien_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_student_list(): void
    {
        $admin = User::factory()->admin()->create();
        $studentA = User::factory()->student()->create([
            'name' => 'Hoc Vien A',
            'email' => 'student-a@example.com',
        ]);
        $studentB = User::factory()->student()->inactive()->create([
            'name' => 'Hoc Vien B',
            'email' => 'student-b@example.com',
        ]);
        $teacher = User::factory()->teacher()->create([
            'name' => 'Teacher Hidden',
            'email' => 'teacher-hidden@example.com',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.students.index'));

        $response->assertOk();
        $response->assertSee('Quản lý học viên');
        $response->assertSee($studentA->email);
        $response->assertSee($studentB->email);
        $response->assertDontSee($teacher->email);
    }

    public function test_admin_can_search_and_filter_students(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->student()->locked()->create([
            'name' => 'Nguyen Lan Locked',
            'email' => 'lan-locked@example.com',
            'phone' => '0909000111',
        ]);
        $other = User::factory()->student()->create([
            'name' => 'Nguyen Lan Active',
            'email' => 'lan-active@example.com',
            'phone' => '0909000222',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.students.index', [
                'search' => 'Lan',
                'status' => User::STATUS_LOCKED,
            ]));

        $response->assertOk();
        $response->assertSee($target->email);
        $response->assertDontSee($other->email);
    }

    public function test_admin_can_create_student(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.students.store'), [
                'name' => 'Hoc Vien Moi',
                'username' => 'hocvienmoi',
                'email' => 'hocvienmoi@example.com',
                'phone' => '0901234567',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'status' => User::STATUS_ACTIVE,
            ]);

        $student = User::where('email', 'hocvienmoi@example.com')->first();

        $response->assertRedirect(route('admin.students.show', $student));
        $this->assertDatabaseHas('nguoi_dung', [
            'email' => 'hocvienmoi@example.com',
            'role_id' => \App\Models\Role::idByName(User::ROLE_STUDENT),
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_admin_can_update_student(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create([
            'name' => 'Hoc Vien Cu',
            'username' => 'hocviencu',
            'email' => 'hocviencu@example.com',
            'phone' => '0901111000',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.students.update', $student), [
                'name' => 'Hoc Vien Da Sua',
                'username' => 'hocviendasua',
                'email' => 'hocviendasua@example.com',
                'phone' => '0902222333',
                'password' => '',
                'password_confirmation' => '',
                'status' => User::STATUS_INACTIVE,
            ]);

        $response->assertRedirect(route('admin.students.show', $student));
        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $student->id,
            'name' => 'Hoc Vien Da Sua',
            'username' => 'hocviendasua',
            'email' => 'hocviendasua@example.com',
            'phone' => '0902222333',
            'status' => User::STATUS_INACTIVE,
        ]);
    }

    public function test_admin_can_view_student_detail_with_study_data(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create([
            'name' => 'Giang Vien A',
        ]);
        $student = User::factory()->student()->create([
            'name' => 'Hoc Vien Chi Tiet',
        ]);
        $subject = Subject::create([
            'name' => 'Tin hoc van phong',
            'price' => 1500000,
        ]);
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Tin hoc van phong - Ca toi',
            'teacher_id' => $teacher->id,
            'schedule' => 'Thu 2 - Thu 4, 18:00 - 20:00',
        ]);
        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => 'confirmed',
            'schedule' => 'Thu 2 - Thu 4, 18:00 - 20:00',
            'is_submitted' => true,
        ]);

        Grade::create([
            'enrollment_id' => $enrollment->id,
            'score' => 88,
            'grade' => 'A',
            'feedback' => 'Tien bo tot',
        ]);

        Review::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'rating' => 5,
            'comment' => 'Rat huu ich',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.students.show', $student));

        $response->assertOk();
        $response->assertSee('Hoc Vien Chi Tiet');
        $response->assertSee('Tin hoc van phong - Ca toi');
        $response->assertSee('Thu 2 - Thu 4, 18:00 - 20:00');
        $response->assertSee('88');
        $response->assertSee('Rat huu ich');
    }

    public function test_admin_can_lock_and_unlock_student(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();

        $lockResponse = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.students.lock', $student));

        $lockResponse->assertRedirect(route('admin.students.show', $student));
        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $student->id,
            'status' => User::STATUS_LOCKED,
        ]);

        $unlockResponse = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.students.unlock', $student));

        $unlockResponse->assertRedirect(route('admin.students.show', $student));
        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $student->id,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_student_is_blocked_from_student_management(): void
    {
        $student = User::factory()->student()->create();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('admin.students.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_teacher_is_blocked_from_student_management(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('admin.students.index'));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_admin_cannot_create_student_with_duplicate_email_username_or_phone(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->student()->create([
            'username' => 'duplicate-user',
            'email' => 'duplicate@example.com',
            'phone' => '0903333444',
        ]);

        $response = $this
            ->from(route('admin.students.create'))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.students.store'), [
                'name' => 'Hoc Vien Trung',
                'username' => 'duplicate-user',
                'email' => 'duplicate@example.com',
                'phone' => '0903333444',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'status' => User::STATUS_ACTIVE,
            ]);

        $response->assertRedirect(route('admin.students.create'));
        $response->assertSessionHasErrors(['email', 'username', 'phone']);
    }
}

