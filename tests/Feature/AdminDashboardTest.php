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

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
        ]);
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);
        $subject = Subject::create([
            'name' => 'Tin học văn phòng',
            'price' => 1500000,
        ]);
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Tin học văn phòng - Ca tối',
        ]);

        TeacherApplication::create([
            'name' => 'Ứng viên A',
            'email' => 'ungvien@example.com',
            'status' => 'pending',
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'status' => 'pending',
            'is_submitted' => true,
        ]);

        ScheduleChangeRequest::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'reason' => 'Bận việc cá nhân',
            'status' => ScheduleChangeRequest::STATUS_PENDING,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard Admin');
        $response->assertSee('1');
    }

    public function test_student_is_blocked_from_admin_dashboard(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get('/admin/dashboard');

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_teacher_is_blocked_from_admin_dashboard(): void
    {
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
        ]);

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get('/admin/dashboard');

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }
}