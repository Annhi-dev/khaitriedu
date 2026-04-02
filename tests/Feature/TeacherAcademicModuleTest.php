<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Category;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\ScheduleChangeRequest;
use App\Models\Subject;
use App\Models\TeacherEvaluation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAcademicModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_dashboard_displays_current_schedule_and_notifications(): void
    {
        $teacher = User::factory()->teacher()->create(['name' => 'Teacher Dashboard']);
        ['classRoom' => $classRoom, 'schedule' => $schedule] = $this->createClassroomBundle($teacher, [
            'day' => now()->englishDayOfWeek,
        ]);

        Notification::create([
            'user_id' => $teacher->id,
            'title' => 'Yêu cầu đổi lịch đã được duyệt',
            'message' => 'Admin đã cập nhật lịch giảng mới cho lớp của bạn.',
            'type' => 'success',
            'link' => route('teacher.schedule-change-requests.index'),
        ]);

        $response = $this
            ->actingAs($teacher)
            ->get(route('teacher.dashboard'));

        $response->assertOk();
        $response->assertSee('Lịch giảng trong ngày');
        $response->assertSee($classRoom->displayName());
        $response->assertSee($schedule->timeRangeLabel());
        $response->assertSee('Yêu cầu đổi lịch đã được duyệt');
    }

    public function test_teacher_can_manage_attendance_grades_and_evaluations_for_assigned_class(): void
    {
        $teacher = User::factory()->teacher()->create();
        $studentA = User::factory()->student()->create(['name' => 'Hoc vien A']);
        $studentB = User::factory()->student()->create(['name' => 'Hoc vien B']);

        [
            'subject' => $subject,
            'course' => $course,
            'classRoom' => $classRoom,
            'schedule' => $schedule,
        ] = $this->createClassroomBundle($teacher);

        Enrollment::create([
            'user_id' => $studentA->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'schedule' => $schedule->label(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        Enrollment::create([
            'user_id' => $studentB->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'schedule' => $schedule->label(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $showResponse = $this
            ->actingAs($teacher)
            ->get(route('teacher.classes.show', $classRoom));

        $showResponse->assertOk();
        $showResponse->assertSee('Danh sách học viên');
        $showResponse->assertSee($studentA->name);
        $showResponse->assertSee($studentB->name);

        $attendanceResponse = $this
            ->actingAs($teacher)
            ->post(route('teacher.classes.attendance.store', $classRoom), [
                'class_schedule_id' => $schedule->id,
                'attendance_date' => now()->toDateString(),
                'attendance' => [
                    $studentA->id => ['status' => AttendanceRecord::STATUS_PRESENT, 'note' => 'On time'],
                    $studentB->id => ['status' => AttendanceRecord::STATUS_LATE, 'note' => 'Traffic'],
                ],
            ]);

        $attendanceResponse->assertRedirect(route('teacher.classes.show', [
            'classRoom' => $classRoom->id,
            'tab' => 'attendance',
            'schedule_id' => $schedule->id,
            'date' => now()->toDateString(),
        ]));

        $this->assertDatabaseHas('attendance_records', [
            'class_room_id' => $classRoom->id,
            'class_schedule_id' => $schedule->id,
            'student_id' => $studentA->id,
            'status' => AttendanceRecord::STATUS_PRESENT,
        ]);

        $gradeResponse = $this
            ->actingAs($teacher)
            ->post(route('teacher.classes.grades.store', $classRoom), [
                'test_name' => 'Kiem tra giua ky',
                'grades' => [
                    $studentA->id => ['score' => 91, 'feedback' => 'Lam bai tot'],
                    $studentB->id => ['score' => 77, 'feedback' => 'Can on them'],
                ],
            ]);

        $gradeResponse->assertRedirect(route('teacher.classes.show', [
            'classRoom' => $classRoom->id,
            'tab' => 'grades',
            'test_name' => 'Kiem tra giua ky',
        ]));

        $this->assertDatabaseHas('diem', [
            'class_room_id' => $classRoom->id,
            'student_id' => $studentA->id,
            'test_name' => 'Kiem tra giua ky',
            'score' => 91.00,
            'grade' => 'A',
        ]);

        $evaluationResponse = $this
            ->actingAs($teacher)
            ->post(route('teacher.classes.evaluations.store', $classRoom), [
                'student_id' => $studentA->id,
                'rating' => 5,
                'comments' => 'Thai do hoc tap rat tich cuc.',
            ]);

        $evaluationResponse->assertRedirect(route('teacher.classes.show', [
            'classRoom' => $classRoom->id,
            'tab' => 'evaluations',
            'student_id' => $studentA->id,
        ]));

        $this->assertDatabaseHas('teacher_evaluations', [
            'class_room_id' => $classRoom->id,
            'student_id' => $studentA->id,
            'teacher_id' => $teacher->id,
            'rating' => 5,
        ]);
    }

    public function test_teacher_can_submit_schedule_change_request_from_schedule_slot(): void
    {
        $teacher = User::factory()->teacher()->create();
        ['classRoom' => $classRoom, 'schedule' => $schedule] = $this->createClassroomBundle($teacher);

        $startAt = Carbon::parse('next friday 19:00');
        $endAt = Carbon::parse('next friday 21:00');

        $response = $this
            ->actingAs($teacher)
            ->post(route('teacher.schedules.change-requests.store', $schedule), [
                'requested_start_at' => $startAt->format('Y-m-d H:i:s'),
                'requested_end_at' => $endAt->format('Y-m-d H:i:s'),
                'reason' => 'Can doi lich de phu hop lich day moi.',
            ]);

        $response->assertRedirect(route('teacher.schedules.index'));

        $this->assertDatabaseHas('schedule_change_requests', [
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'class_schedule_id' => $schedule->id,
            'status' => ScheduleChangeRequest::STATUS_PENDING,
            'requested_day_of_week' => 'Friday',
        ]);
    }

    public function test_admin_can_approve_class_schedule_change_request_and_notify_teacher(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();

        ['classRoom' => $classRoom, 'schedule' => $schedule] = $this->createClassroomBundle($teacher, [
            'day' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);

        $request = ScheduleChangeRequest::create([
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'class_schedule_id' => $schedule->id,
            'current_schedule' => $schedule->label(),
            'requested_day_of_week' => 'Wednesday',
            'requested_date' => now()->addWeek()->toDateString(),
            'requested_end_date' => now()->addWeek()->toDateString(),
            'requested_start_time' => '19:00',
            'requested_end_time' => '21:00',
            'reason' => 'De phu hop lich cong tac moi.',
            'status' => ScheduleChangeRequest::STATUS_PENDING,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.schedule-change-requests.review', $request), [
                'action' => 'approve',
                'admin_note' => 'Duyet lich moi cho giang vien.',
            ]);

        $response->assertRedirect(route('admin.schedule-change-requests.show', $request));

        $this->assertDatabaseHas('lich_hoc', [
            'id' => $schedule->id,
            'day_of_week' => 'Wednesday',
            'start_time' => '19:00',
            'end_time' => '21:00',
        ]);

        $this->assertDatabaseHas('thong_bao', [
            'user_id' => $teacher->id,
            'title' => 'Yêu cầu đổi lịch đã được duyệt',
            'type' => 'success',
        ]);
    }

    private function createClassroomBundle(User $teacher, array $overrides = []): array
    {
        $slug = 'lop-' . str()->random(6);

        $category = Category::create([
            'name' => 'Nhom hoc ' . $slug,
            'slug' => 'nhom-' . $slug,
            'status' => Category::STATUS_ACTIVE,
        ]);

        $subject = Subject::create([
            'name' => 'Tin hoc ung dung ' . $slug,
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'price' => 1800000,
            'duration' => 3,
        ]);

        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Lop hoc noi bo ' . $slug,
            'description' => 'Course phuc vu test teacher module.',
            'teacher_id' => $teacher->id,
            'day_of_week' => $overrides['day'] ?? 'Tuesday',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'start_time' => $overrides['start_time'] ?? '18:00',
            'end_time' => $overrides['end_time'] ?? '20:00',
            'capacity' => 25,
            'status' => Course::STATUS_SCHEDULED,
            'schedule' => 'Lich test',
        ]);

        $classRoom = ClassRoom::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'start_date' => now()->toDateString(),
            'duration' => 3,
            'status' => ClassRoom::STATUS_OPEN,
            'note' => 'Lop hoc noi bo phuc vu test.',
        ]);

        $schedule = ClassSchedule::create([
            'lop_hoc_id' => $classRoom->id,
            'day_of_week' => $overrides['day'] ?? 'Tuesday',
            'start_time' => $overrides['start_time'] ?? '18:00',
            'end_time' => $overrides['end_time'] ?? '20:00',
        ]);

        return compact('category', 'subject', 'course', 'classRoom', 'schedule');
    }
}
