<?php

namespace Tests\Feature;

use App\Models\DiemDanh;
use App\Models\NhomHoc;
use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\YeuCauXinPhep;
use App\Models\ThongBao;
use App\Models\PhongHoc;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class xin_phep_nghi_test extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_leave_request_and_view_the_detail_page(): void
    {
        Carbon::setTestNow('2026-04-17 09:00:00');

        try {
            $student = NguoiDung::factory()->student()->create(['name' => 'Hoc vien xin phep']);
            $teacher = NguoiDung::factory()->teacher()->create(['name' => 'Giang vien xin phep']);
            ['classRoom' => $classRoom, 'schedule' => $schedule] = $this->createTeachingBundle($teacher, 'Friday');

            GhiDanh::create([
                'user_id' => $student->id,
                'subject_id' => $classRoom->subject_id,
                'course_id' => $classRoom->course_id,
                'lop_hoc_id' => $classRoom->id,
                'assigned_teacher_id' => $teacher->id,
                'status' => GhiDanh::STATUS_ACTIVE,
                'schedule' => $classRoom->scheduleSummary(),
                'is_submitted' => true,
                'submitted_at' => now(),
            ]);

            $createResponse = $this
                ->actingAs($student)
                ->get(route('student.leave-requests.create'));

            $createResponse->assertOk();
            $createResponse->assertSee($classRoom->displayName());
            $createResponse->assertSee('Gửi xin phép nghỉ');

            $storeResponse = $this
                ->actingAs($student)
                ->post(route('student.leave-requests.store'), [
                    'class_room_id' => $classRoom->id,
                    'attendance_date' => '2026-04-17',
                    'reason' => 'Em bi sot nen xin phep nghi hoc.',
                    'note' => 'Da thong bao cho gia dinh va lop truong.',
                ]);

            $leaveRequest = YeuCauXinPhep::query()->firstOrFail();

            $storeResponse->assertRedirect(route('student.leave-requests.show', $leaveRequest));
            $storeResponse->assertSessionHas('status');

            $this->assertDatabaseHas('yeu_cau_xin_phep', [
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'class_room_id' => $classRoom->id,
                'class_schedule_id' => $schedule->id,
                'attendance_date' => '2026-04-17 00:00:00',
                'status' => YeuCauXinPhep::STATUS_PENDING,
            ]);

            $this->assertDatabaseHas('thong_bao', [
                'user_id' => $teacher->id,
                'title' => 'Có yêu cầu xin phép nghỉ mới',
            ]);

            $showResponse = $this
                ->actingAs($student)
                ->get(route('student.leave-requests.show', $leaveRequest));

            $showResponse->assertOk();
            $showResponse->assertSee('Chờ xử lý');
            $showResponse->assertSee($teacher->displayName());
            $showResponse->assertSee('Em bi sot nen xin phep nghi hoc.');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_teacher_can_approve_leave_request_and_sync_excused_attendance(): void
    {
        Carbon::setTestNow('2026-04-17 09:00:00');

        try {
            $teacher = NguoiDung::factory()->teacher()->create(['name' => 'Giang vien xu ly']);
            $student = NguoiDung::factory()->student()->create(['name' => 'Hoc vien duyet phep']);
            ['classRoom' => $classRoom, 'schedule' => $schedule] = $this->createTeachingBundle($teacher, 'Friday');

            GhiDanh::create([
                'user_id' => $student->id,
                'subject_id' => $classRoom->subject_id,
                'course_id' => $classRoom->course_id,
                'lop_hoc_id' => $classRoom->id,
                'assigned_teacher_id' => $teacher->id,
                'status' => GhiDanh::STATUS_ACTIVE,
                'schedule' => $classRoom->scheduleSummary(),
                'is_submitted' => true,
                'submitted_at' => now(),
            ]);

            $leaveRequest = YeuCauXinPhep::create([
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'enrollment_id' => null,
                'course_id' => $classRoom->course_id,
                'class_room_id' => $classRoom->id,
                'class_schedule_id' => $schedule->id,
                'attendance_date' => '2026-04-17',
                'reason' => 'Bi om nen xin nghi buoi nay.',
                'status' => YeuCauXinPhep::STATUS_PENDING,
            ]);

            DiemDanh::create([
                'course_id' => $classRoom->course_id,
                'class_room_id' => $classRoom->id,
                'class_schedule_id' => $schedule->id,
                'enrollment_id' => null,
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'attendance_date' => '2026-04-17',
                'status' => DiemDanh::STATUS_ABSENT,
                'note' => 'Ban dau ghi nhan vang khong phep.',
                'recorded_at' => now(),
            ]);

            $response = $this
                ->actingAs($teacher)
                ->post(route('teacher.leave-requests.review', $leaveRequest), [
                    'status' => YeuCauXinPhep::STATUS_ACCEPTED,
                    'teacher_note' => 'Da chấp nhận xin phép cho buổi này.',
                ]);

            $response->assertRedirect(route('teacher.leave-requests.show', $leaveRequest));
            $response->assertSessionHas('status');

            $leaveRequest->refresh();

            $this->assertSame(YeuCauXinPhep::STATUS_ACCEPTED, $leaveRequest->status);
            $this->assertSame($teacher->id, $leaveRequest->reviewed_by);
            $this->assertDatabaseHas('attendance_records', [
                'student_id' => $student->id,
                'class_room_id' => $classRoom->id,
                'class_schedule_id' => $schedule->id,
                'attendance_date' => '2026-04-17 00:00:00',
                'status' => DiemDanh::STATUS_EXCUSED,
                'teacher_id' => $teacher->id,
            ]);

            $this->assertDatabaseHas('thong_bao', [
                'user_id' => $student->id,
                'title' => 'Yêu cầu xin phép đã được chấp nhận',
            ]);

            $studentResponse = $this
                ->actingAs($student)
                ->get(route('student.leave-requests.show', $leaveRequest));

            $studentResponse->assertOk();
            $studentResponse->assertSee('Đã chấp nhận');
            $studentResponse->assertSee('Da chấp nhận xin phép cho buổi này.');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_teacher_can_change_processed_leave_request_and_resync_attendance(): void
    {
        Carbon::setTestNow('2026-04-17 09:00:00');

        try {
            $teacher = NguoiDung::factory()->teacher()->create(['name' => 'Giang vien cap nhat']);
            $student = NguoiDung::factory()->student()->create(['name' => 'Hoc vien doi xu ly']);
            ['classRoom' => $classRoom, 'schedule' => $schedule] = $this->createTeachingBundle($teacher, 'Friday');

            GhiDanh::create([
                'user_id' => $student->id,
                'subject_id' => $classRoom->subject_id,
                'course_id' => $classRoom->course_id,
                'lop_hoc_id' => $classRoom->id,
                'assigned_teacher_id' => $teacher->id,
                'status' => GhiDanh::STATUS_ACTIVE,
                'schedule' => $classRoom->scheduleSummary(),
                'is_submitted' => true,
                'submitted_at' => now(),
            ]);

            $leaveRequest = YeuCauXinPhep::create([
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'enrollment_id' => null,
                'course_id' => $classRoom->course_id,
                'class_room_id' => $classRoom->id,
                'class_schedule_id' => $schedule->id,
                'attendance_date' => '2026-04-17',
                'reason' => 'Ban dau xin phep nghi hoc.',
                'status' => YeuCauXinPhep::STATUS_PENDING,
            ]);

            DiemDanh::create([
                'course_id' => $classRoom->course_id,
                'class_room_id' => $classRoom->id,
                'class_schedule_id' => $schedule->id,
                'enrollment_id' => null,
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'attendance_date' => '2026-04-17',
                'status' => DiemDanh::STATUS_ABSENT,
                'note' => 'Ban dau ghi nhan vang khong phep.',
                'recorded_at' => now(),
            ]);

            $acceptResponse = $this
                ->actingAs($teacher)
                ->post(route('teacher.leave-requests.review', $leaveRequest), [
                    'status' => YeuCauXinPhep::STATUS_ACCEPTED,
                    'teacher_note' => 'Da chap nhan lan dau.',
                ]);

            $acceptResponse->assertRedirect(route('teacher.leave-requests.show', $leaveRequest));

            $leaveRequest->refresh();
            $this->assertSame(YeuCauXinPhep::STATUS_ACCEPTED, $leaveRequest->status);
            $this->assertDatabaseHas('attendance_records', [
                'student_id' => $student->id,
                'class_room_id' => $classRoom->id,
                'class_schedule_id' => $schedule->id,
                'attendance_date' => '2026-04-17 00:00:00',
                'status' => DiemDanh::STATUS_EXCUSED,
            ]);

            $rejectResponse = $this
                ->actingAs($teacher)
                ->post(route('teacher.leave-requests.review', $leaveRequest), [
                    'status' => YeuCauXinPhep::STATUS_REJECTED,
                    'teacher_note' => 'Cap nhat lai thanh tu choi.',
                ]);

            $rejectResponse->assertRedirect(route('teacher.leave-requests.show', $leaveRequest));
            $rejectResponse->assertSessionHas('status');

            $leaveRequest->refresh();

            $this->assertSame(YeuCauXinPhep::STATUS_REJECTED, $leaveRequest->status);
            $this->assertSame($teacher->id, $leaveRequest->reviewed_by);
            $this->assertDatabaseHas('attendance_records', [
                'student_id' => $student->id,
                'class_room_id' => $classRoom->id,
                'class_schedule_id' => $schedule->id,
                'attendance_date' => '2026-04-17 00:00:00',
                'status' => DiemDanh::STATUS_ABSENT,
            ]);
            $this->assertDatabaseHas('thong_bao', [
                'user_id' => $student->id,
                'title' => 'Yêu cầu xin phép bị từ chối',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_student_cannot_open_another_students_leave_request(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create();
        $owner = NguoiDung::factory()->student()->create();
        $otherStudent = NguoiDung::factory()->student()->create();
        ['classRoom' => $classRoom, 'schedule' => $schedule] = $this->createTeachingBundle($teacher, 'Friday');

        $leaveRequest = YeuCauXinPhep::create([
            'student_id' => $owner->id,
            'teacher_id' => $teacher->id,
            'course_id' => $classRoom->course_id,
            'class_room_id' => $classRoom->id,
            'class_schedule_id' => $schedule->id,
            'attendance_date' => now()->toDateString(),
            'reason' => 'Ly do rieng tu.',
            'status' => YeuCauXinPhep::STATUS_PENDING,
        ]);

        $response = $this
            ->actingAs($otherStudent)
            ->get(route('student.leave-requests.show', $leaveRequest));

        $response->assertNotFound();
    }

    private function createTeachingBundle(NguoiDung $teacher, string $dayOfWeek = 'Friday'): array
    {
        $slug = 'xin-phep-' . str()->random(6);

        $category = NhomHoc::create([
            'name' => 'Nhom hoc ' . $slug,
            'slug' => 'nhom-hoc-' . $slug,
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        $subject = MonHoc::create([
            'name' => 'Ky nang giao tiep ' . $slug,
            'category_id' => $category->id,
            'status' => MonHoc::STATUS_OPEN,
            'price' => 1750000,
            'duration' => 3,
        ]);

        $course = KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => 'Lop xin phep ' . $slug,
            'description' => 'Lop hoc phuc vu test xin phep.',
            'teacher_id' => $teacher->id,
            'day_of_week' => $dayOfWeek,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'start_time' => '18:00',
            'end_time' => '20:00',
            'capacity' => 20,
            'status' => KhoaHoc::STATUS_SCHEDULED,
            'schedule' => 'Lich test xin phep',
        ]);

        $room = PhongHoc::create([
            'code' => 'P' . fake()->unique()->numberBetween(100, 999),
            'name' => 'Phong xin phep ' . fake()->unique()->numberBetween(1, 99),
            'type' => 'offline',
            'location' => 'Tang 2',
            'capacity' => 25,
            'status' => PhongHoc::STATUS_ACTIVE,
        ]);

        $classRoom = LopHoc::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'name' => 'Lop xin phep ' . $slug,
            'room_id' => $room->id,
            'teacher_id' => $teacher->id,
            'status' => LopHoc::STATUS_OPEN,
            'duration' => 3,
            'start_date' => now()->toDateString(),
        ]);

        $schedule = LichHoc::create([
            'lop_hoc_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);

        return compact('category', 'subject', 'course', 'classRoom', 'schedule');
    }
}
