<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseTimeSlot;
use App\Models\Enrollment;
use App\Models\Room;
use App\Models\ScheduleChangeRequest;
use App\Models\SlotRegistration;
use App\Models\SlotRegistrationChoice;
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
            'role_id' => \App\Models\Role::idByName(User::ROLE_ADMIN),
        ]);
        $teacher = User::factory()->create([
            'role_id' => \App\Models\Role::idByName(User::ROLE_TEACHER),
        ]);
        $student = User::factory()->create([
            'role_id' => \App\Models\Role::idByName(User::ROLE_STUDENT),
        ]);
        $category = Category::create([
            'name' => 'Ngoại ngữ - Tin học',
            'slug' => 'ngoai-ngu-tin-hoc',
            'status' => Category::STATUS_ACTIVE,
        ]);
        $subject = Subject::create([
            'name' => 'Tin học văn phòng',
            'price' => 1500000,
            'category_id' => $category->id,
        ]);
        $room = Room::create([
            'code' => 'P101',
            'name' => 'Phòng 101',
            'capacity' => 30,
            'status' => Room::STATUS_ACTIVE,
        ]);
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Tin học văn phòng - Ca tối',
            'teacher_id' => $teacher->id,
            'status' => Course::STATUS_ACTIVE,
        ]);

        TeacherApplication::create([
            'name' => 'Ứng viên A',
            'email' => 'ungvien@example.com',
            'status' => TeacherApplication::STATUS_PENDING,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_PENDING,
            'is_submitted' => true,
        ]);

        CourseTimeSlot::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'min_students' => 10,
            'max_students' => 20,
            'status' => CourseTimeSlot::STATUS_OPEN_FOR_REGISTRATION,
        ]);

        SlotRegistration::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => SlotRegistration::STATUS_PENDING,
        ]);

        $slotRegistration = SlotRegistration::first();

        SlotRegistrationChoice::create([
            'slot_registration_id' => $slotRegistration->id,
            'course_time_slot_id' => CourseTimeSlot::first()->id,
            'priority' => 1,
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
        $response->assertSee('Khung giờ mở đăng ký');
        $response->assertViewHas('studentCount', 1);
        $response->assertViewHas('teacherCount', 1);
        $response->assertViewHas('pendingTeacherApplications', 1);
        $response->assertViewHas('subjectCount', 1);
        $response->assertViewHas('groupCount', 1);
        $response->assertViewHas('roomCount', 1);
        $response->assertViewHas('openTimeSlotCount', 1);
        $response->assertViewHas('pendingSlotRegistrationCount', 1);
        $response->assertViewHas('configuredTimeSlotCount', 1);
        $response->assertViewHas('slotChoiceCount', 1);
        $response->assertViewHas('activeClassCount', 1);
        $response->assertViewHas('pendingScheduleChangeRequests', 1);
        $response->assertViewHas('slotDemandSummary', function ($slotDemandSummary) use ($subject) {
            return $slotDemandSummary->count() === 1
                && $slotDemandSummary->first()->subject?->is($subject)
                && (int) $slotDemandSummary->first()->registrations_count === 1;
        });
        $response->assertViewHas('pendingSlotRegistrationsList', function ($pendingSlotRegistrationsList) use ($student) {
            return $pendingSlotRegistrationsList->count() === 1
                && $pendingSlotRegistrationsList->first()->student?->is($student)
                && (int) $pendingSlotRegistrationsList->first()->choices_count === 1;
        });
    }

    public function test_student_is_blocked_from_admin_dashboard(): void
    {
        $student = User::factory()->create([
            'role_id' => \App\Models\Role::idByName(User::ROLE_STUDENT),
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
            'role_id' => \App\Models\Role::idByName(User::ROLE_TEACHER),
        ]);

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get('/admin/dashboard');

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }
}
