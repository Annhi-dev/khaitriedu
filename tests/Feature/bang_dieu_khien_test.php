<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
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
use App\Services\AdminScheduleConflictService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class bang_dieu_khien_test extends TestCase
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
        $response->assertSee('Xem chi tiết');
        $response->assertSee('Xem điểm số');
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

    public function test_admin_dashboard_shows_schedule_conflict_warning_when_conflicts_exist(): void
    {
        $admin = User::factory()->create([
            'role_id' => \App\Models\Role::idByName(User::ROLE_ADMIN),
        ]);
        $student = User::factory()->create([
            'role_id' => \App\Models\Role::idByName(User::ROLE_STUDENT),
            'name' => 'Hoc vien trung lich',
        ]);
        $teacherA = User::factory()->create([
            'role_id' => \App\Models\Role::idByName(User::ROLE_TEACHER),
        ]);
        $teacherB = User::factory()->create([
            'role_id' => \App\Models\Role::idByName(User::ROLE_TEACHER),
        ]);

        $category = Category::create([
            'name' => 'Ngoại ngữ - Tin học',
            'slug' => 'ngoai-ngu-tin-hoc-conflict',
            'status' => Category::STATUS_ACTIVE,
        ]);

        $subjectA = Subject::create([
            'name' => 'Tin học văn phòng',
            'price' => 1500000,
            'category_id' => $category->id,
        ]);
        $subjectB = Subject::create([
            'name' => 'Tiếng Anh giao tiếp',
            'price' => 1800000,
            'category_id' => $category->id,
        ]);

        $roomA = Room::create([
            'code' => 'P201',
            'name' => 'Phòng 201',
            'capacity' => 30,
            'status' => Room::STATUS_ACTIVE,
        ]);
        $roomB = Room::create([
            'code' => 'P202',
            'name' => 'Phòng 202',
            'capacity' => 30,
            'status' => Room::STATUS_ACTIVE,
        ]);

        $courseA = Course::create([
            'subject_id' => $subjectA->id,
            'title' => 'Tin học văn phòng - Ca tối',
            'teacher_id' => $teacherA->id,
            'start_date' => '2026-05-01',
            'end_date' => '2026-07-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'status' => Course::STATUS_ACTIVE,
        ]);
        $courseB = Course::create([
            'subject_id' => $subjectB->id,
            'title' => 'Tiếng Anh giao tiếp - Ca tối',
            'teacher_id' => $teacherB->id,
            'start_date' => '2026-05-01',
            'end_date' => '2026-07-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'status' => Course::STATUS_ACTIVE,
        ]);

        $classRoomA = ClassRoom::create([
            'subject_id' => $subjectA->id,
            'course_id' => $courseA->id,
            'name' => $courseA->title,
            'room_id' => $roomA->id,
            'teacher_id' => $teacherA->id,
            'start_date' => '2026-05-01',
            'duration' => 3,
            'status' => ClassRoom::STATUS_OPEN,
        ]);
        $classRoomB = ClassRoom::create([
            'subject_id' => $subjectB->id,
            'course_id' => $courseB->id,
            'name' => $courseB->title,
            'room_id' => $roomB->id,
            'teacher_id' => $teacherB->id,
            'start_date' => '2026-05-01',
            'duration' => 3,
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        foreach ([[$classRoomA, $teacherA, $roomA], [$classRoomB, $teacherB, $roomB]] as [$classRoom, $teacher, $room]) {
            ClassSchedule::create([
                'lop_hoc_id' => $classRoom->id,
                'teacher_id' => $teacher->id,
                'room_id' => $room->id,
                'day_of_week' => 'Monday',
                'start_time' => '18:00',
                'end_time' => '20:00',
            ]);
        }

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subjectA->id,
            'course_id' => $courseA->id,
            'lop_hoc_id' => $classRoomA->id,
            'assigned_teacher_id' => $teacherA->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'schedule' => $courseA->formattedSchedule(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subjectB->id,
            'course_id' => $courseB->id,
            'lop_hoc_id' => $classRoomB->id,
            'assigned_teacher_id' => $teacherB->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'schedule' => $courseB->formattedSchedule(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Cảnh báo xung đột lịch');
        $response->assertSee('Học viên đang bị trùng lịch');
        $response->assertSee('Kiểm tra xung đột');
        $response->assertSee(route('admin.schedules.conflicts'), false);
        $response->assertViewHas('studentConflictStudentCount', 1);
        $response->assertViewHas('studentConflictPairCount', 1);
        $this->assertSame(1, app(AdminScheduleConflictService::class)->studentConflictPairCount());
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

