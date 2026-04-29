<?php

namespace Tests\Feature;

use App\Models\DiemDanh;
use App\Models\NhomHoc;
use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\ThongBao;
use App\Models\PhongHoc;
use App\Models\YeuCauDoiLich;
use App\Models\MonHoc;
use App\Models\DanhGiaGiaoVien;
use App\Models\NguoiDung;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class hoc_phan_giao_vien_test extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_dashboard_displays_current_schedule_and_notifications(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create(['name' => 'Teacher Dashboard']);
        ['classRoom' => $classRoom, 'schedule' => $schedule] = $this->createClassroomBundle($teacher, [
            'day' => now()->englishDayOfWeek,
        ]);

        ThongBao::create([
            'user_id' => $teacher->id,
            'title' => 'Yêu cầu dạy bù đã được duyệt',
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
        $response->assertSee('Xem chi tiết');
        $response->assertSee('Yêu cầu dạy bù đã được duyệt');
    }

    public function test_student_dashboard_displays_notifications(): void
    {
        $student = NguoiDung::factory()->student()->create(['name' => 'Student Dashboard']);

        ThongBao::create([
            'user_id' => $student->id,
            'title' => 'Lịch học đã thay đổi',
            'message' => 'Lịch học của bạn đã được cập nhật. Vui lòng kiểm tra lại thời khóa biểu của bạn.',
            'type' => 'info',
            'link' => route('student.schedule'),
        ]);

        $response = $this
            ->actingAs($student)
            ->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee('Thông báo gần đây');
        $response->assertSee('Lịch học đã thay đổi');
        $response->assertSee('Vui lòng kiểm tra lại thời khóa biểu của bạn.');
    }

    public function test_teacher_can_manage_attendance_grades_and_evaluations_for_assigned_class(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create();
        $studentA = NguoiDung::factory()->student()->create(['name' => 'Hoc vien A']);
        $studentB = NguoiDung::factory()->student()->create(['name' => 'Hoc vien B']);

        [
            'subject' => $subject,
            'course' => $course,
            'classRoom' => $classRoom,
            'schedule' => $schedule,
        ] = $this->createClassroomBundle($teacher);

        GhiDanh::create([
            'user_id' => $studentA->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => GhiDanh::STATUS_ACTIVE,
            'schedule' => $schedule->label(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        GhiDanh::create([
            'user_id' => $studentB->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => GhiDanh::STATUS_ENROLLED,
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
        $showResponse->assertSee('Đang học');
        $showResponse->assertDontSee('Đã ghi danh');

        $attendanceResponse = $this
            ->actingAs($teacher)
            ->post(route('teacher.classes.attendance.store', $classRoom), [
                'class_schedule_id' => $schedule->id,
                'attendance_date' => now()->toDateString(),
                'attendance' => [
                    $studentA->id => ['status' => DiemDanh::STATUS_PRESENT, 'note' => 'On time'],
                    $studentB->id => ['status' => DiemDanh::STATUS_LATE, 'note' => 'Traffic'],
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
            'status' => DiemDanh::STATUS_PRESENT,
        ]);

        $gradeResponse = $this
            ->actingAs($teacher)
            ->post(route('teacher.classes.grades.store', $classRoom), [
                'scores' => [
                    $studentA->id => [1 => 78, 2 => 76],
                    $studentB->id => [1 => 77],
                ],
            ]);

        $gradeResponse->assertRedirect(route('teacher.classes.show', [
            'classRoom' => $classRoom->id,
            'tab' => 'grades',
        ]));

        $this->assertDatabaseHas('diem', [
            'class_room_id' => $classRoom->id,
            'student_id' => $studentA->id,
            'test_name' => 'Kiểm tra 1',
            'score' => 78.00,
            'weight' => 1,
            'grade' => 'C',
        ]);

        $this->assertDatabaseHas('diem', [
            'class_room_id' => $classRoom->id,
            'student_id' => $studentA->id,
            'test_name' => 'Kiểm tra 2',
            'score' => 76.00,
            'weight' => 1,
            'grade' => 'C',
        ]);

        $detailResponse = $this
            ->actingAs($teacher)
            ->get(route('teacher.classes.show', [
                'classRoom' => $classRoom->id,
                'tab' => 'grades',
            ]));

        $detailResponse->assertOk();
        $detailResponse->assertSee('51.33');
        $detailResponse->assertSee('Chỉ admin mới được chỉnh');
        $detailResponse->assertSee('TB được tính theo công thức');

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

    public function test_admin_can_update_grade_weights_on_class(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $teacher = NguoiDung::factory()->teacher()->create();
        ['classRoom' => $classRoom] = $this->createClassroomBundle($teacher);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.classes.grade-weights.update', $classRoom), [
                'weights' => [
                    1 => 2,
                    2 => 1,
                    3 => 3,
                ],
            ]);

        $response->assertRedirect(route('admin.classes.show', $classRoom));

        $this->assertDatabaseHas('lop_hoc', [
            'id' => $classRoom->id,
        ]);

        $classRoom->refresh();

        $this->assertSame([1 => 2, 2 => 1, 3 => 3], $classRoom->grade_weights);
    }

    public function test_teacher_can_edit_historical_evaluation_even_when_student_not_in_active_enrollment(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create();
        $student = NguoiDung::factory()->student()->create(['name' => 'Hoc vien cu']);
        ['classRoom' => $classRoom] = $this->createClassroomBundle($teacher);

        // Simulate an old enrollment that is no longer in the active status list.
        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $classRoom->subject_id,
            'course_id' => $classRoom->course_id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => GhiDanh::STATUS_REJECTED,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        DanhGiaGiaoVien::create([
            'class_room_id' => $classRoom->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'rating' => 3,
            'comments' => 'Nhan xet cu',
        ]);

        $response = $this
            ->actingAs($teacher)
            ->post(route('teacher.classes.evaluations.store', $classRoom), [
                'student_id' => $student->id,
                'rating' => 4,
                'comments' => 'Da cap nhat lai danh gia',
            ]);

        $response->assertRedirect(route('teacher.classes.show', [
            'classRoom' => $classRoom->id,
            'tab' => 'evaluations',
            'student_id' => $student->id,
        ]));

        $this->assertDatabaseHas('teacher_evaluations', [
            'class_room_id' => $classRoom->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'rating' => 4,
            'comments' => 'Da cap nhat lai danh gia',
        ]);
    }

    public function test_teacher_can_submit_schedule_change_request_from_schedule_slot(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create();
        $currentRoom = $this->createRoom(['name' => 'Phong hien tai']);
        $requestedRoom = $this->createRoom(['name' => 'Phong de xuat']);
        ['classRoom' => $classRoom, 'schedule' => $schedule] = $this->createClassroomBundle($teacher, [
            'room_id' => $currentRoom->id,
        ]);

        $startAt = Carbon::parse('next friday 19:00');
        $endAt = Carbon::parse('next friday 21:00');

        $response = $this
            ->actingAs($teacher)
            ->post(route('teacher.schedules.change-requests.store', $schedule), [
                'requested_start_at' => $startAt->format('Y-m-d H:i:s'),
                'requested_end_at' => $endAt->format('Y-m-d H:i:s'),
                'requested_room_id' => $requestedRoom->id,
                'reason' => 'Cần dời buổi để phù hợp lịch dạy mới.',
            ]);

        $response->assertRedirect(route('teacher.schedules.index'));

        $this->assertDatabaseHas('schedule_change_requests', [
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'class_schedule_id' => $schedule->id,
            'requested_room_id' => $requestedRoom->id,
            'status' => YeuCauDoiLich::STATUS_PENDING,
            'requested_day_of_week' => 'Friday',
        ]);
    }

    public function test_admin_can_approve_class_schedule_change_request_and_notify_teacher_and_students(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $teacher = NguoiDung::factory()->teacher()->create();
        $student = NguoiDung::factory()->student()->create();
        $currentRoom = $this->createRoom(['name' => 'Phong 1']);
        $requestedRoom = $this->createRoom(['name' => 'Phong 2']);

        ['subject' => $subject, 'course' => $course, 'classRoom' => $classRoom, 'schedule' => $schedule] = $this->createClassroomBundle($teacher, [
            'day' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'room_id' => $currentRoom->id,
        ]);

        $enrollment = GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => GhiDanh::STATUS_ACTIVE,
            'schedule' => $course->schedule,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $request = YeuCauDoiLich::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'class_room_id' => $classRoom->id,
            'class_schedule_id' => $schedule->id,
            'requested_room_id' => $requestedRoom->id,
            'current_schedule' => $schedule->label(),
            'requested_day_of_week' => 'Wednesday',
            'requested_date' => now()->addWeek()->toDateString(),
            'requested_end_date' => now()->addWeek()->toDateString(),
            'requested_start_time' => '19:00',
            'requested_end_time' => '21:00',
            'reason' => 'De phu hop lich cong tac moi.',
            'status' => YeuCauDoiLich::STATUS_PENDING,
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
            'room_id' => $requestedRoom->id,
        ]);

        $this->assertDatabaseHas('khoa_hoc', [
            'id' => $course->id,
            'day_of_week' => 'Wednesday',
            'start_time' => '19:00',
            'end_time' => '21:00',
        ]);

        $this->assertStringContainsString('19:00 - 21:00', (string) $course->fresh()->schedule);
        $this->assertSame($course->fresh()->schedule, (string) $enrollment->fresh()->schedule);

        $this->assertDatabaseHas('thong_bao', [
            'user_id' => $teacher->id,
            'title' => 'Yêu cầu dời lịch đã được duyệt',
            'type' => 'success',
            'link' => route('teacher.schedule-change-requests.index'),
        ]);

        $this->assertDatabaseHas('thong_bao', [
            'user_id' => $student->id,
            'title' => 'Lịch học đã thay đổi',
            'type' => 'info',
            'link' => route('student.schedule'),
        ]);

        $teacherNotification = ThongBao::query()->where('user_id', $teacher->id)->firstOrFail();
        $studentNotification = ThongBao::query()->where('user_id', $student->id)->firstOrFail();

        $this->assertStringContainsString($request->targetTitle(), $teacherNotification->message);
        $this->assertStringContainsString($request->currentScheduleLabel(), $teacherNotification->message);
        $this->assertStringContainsString($request->requestedScheduleLabel(), $teacherNotification->message);
        $this->assertStringContainsString('Hiệu lực từ', $teacherNotification->message);

        $this->assertStringContainsString($request->targetTitle(), $studentNotification->message);
        $this->assertStringContainsString($request->currentScheduleLabel(), $studentNotification->message);
        $this->assertStringContainsString($request->requestedScheduleLabel(), $studentNotification->message);
        $this->assertStringContainsString('Vui lòng kiểm tra lại thời khóa biểu của bạn.', $studentNotification->message);
    }

    private function createClassroomBundle(NguoiDung $teacher, array $overrides = []): array
    {
        $slug = 'lop-' . str()->random(6);

        $category = NhomHoc::create([
            'name' => 'Nhom hoc ' . $slug,
            'slug' => 'nhom-' . $slug,
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        $subject = MonHoc::create([
            'name' => 'Tin hoc ung dung ' . $slug,
            'category_id' => $category->id,
            'status' => MonHoc::STATUS_OPEN,
            'price' => 1800000,
            'duration' => 3,
        ]);

        $course = KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => 'Lop hoc noi bo ' . $slug,
            'description' => 'KhoaHoc phuc vu test teacher module.',
            'teacher_id' => $teacher->id,
            'day_of_week' => $overrides['day'] ?? 'Tuesday',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'start_time' => $overrides['start_time'] ?? '18:00',
            'end_time' => $overrides['end_time'] ?? '20:00',
            'capacity' => 25,
            'status' => KhoaHoc::STATUS_SCHEDULED,
            'schedule' => 'Lich test',
        ]);

        $classRoom = LopHoc::create([
            'subject_id' => $subject->id,
            'course_id' => $overrides['course_id'] ?? $course->id,
            'teacher_id' => $teacher->id,
            'room_id' => $overrides['room_id'] ?? null,
            'start_date' => now()->toDateString(),
            'duration' => 3,
            'status' => LopHoc::STATUS_OPEN,
            'note' => 'Lop hoc noi bo phuc vu test.',
        ]);

        $schedule = LichHoc::create([
            'lop_hoc_id' => $classRoom->id,
            'room_id' => $overrides['schedule_room_id'] ?? ($overrides['room_id'] ?? null),
            'day_of_week' => $overrides['day'] ?? 'Tuesday',
            'start_time' => $overrides['start_time'] ?? '18:00',
            'end_time' => $overrides['end_time'] ?? '20:00',
        ]);

        return compact('category', 'subject', 'course', 'classRoom', 'schedule');
    }

    private function createRoom(array $overrides = []): PhongHoc
    {
        $code = $overrides['code'] ?? 'PH-' . strtoupper(str()->random(6));

        return PhongHoc::create(array_merge([
            'code' => $code,
            'name' => 'Phong ' . $code,
            'type' => 'theory',
            'location' => 'Tang 1',
            'capacity' => 30,
            'status' => PhongHoc::STATUS_ACTIVE,
        ], $overrides));
    }
}

