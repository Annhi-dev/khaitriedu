<?php

namespace Tests\Feature;

use App\Models\NhomHoc;
use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\KhungGioKhoaHoc;
use App\Models\GhiDanh;
use App\Models\PhongHoc;
use App\Models\YeuCauDoiLich;
use App\Models\NguyenVongKhungGio;
use App\Models\LuaChonNguyenVongKhungGio;
use App\Models\MonHoc;
use App\Models\DonUngTuyenGiaoVien;
use App\Models\NguoiDung;
use App\Services\AdminScheduleConflictService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class bang_dieu_khien_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = NguoiDung::factory()->create([
            'role_id' => \App\Models\VaiTro::idByName(NguoiDung::ROLE_ADMIN),
        ]);
        $teacher = NguoiDung::factory()->create([
            'role_id' => \App\Models\VaiTro::idByName(NguoiDung::ROLE_TEACHER),
        ]);
        $student = NguoiDung::factory()->create([
            'role_id' => \App\Models\VaiTro::idByName(NguoiDung::ROLE_STUDENT),
        ]);
        $category = NhomHoc::create([
            'name' => 'Ngoại ngữ - Tin học',
            'slug' => 'ngoai-ngu-tin-hoc',
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);
        $subject = MonHoc::create([
            'name' => 'Tin học văn phòng',
            'price' => 1500000,
            'category_id' => $category->id,
        ]);
        $room = PhongHoc::create([
            'code' => 'P101',
            'name' => 'Phòng 101',
            'capacity' => 30,
            'status' => PhongHoc::STATUS_ACTIVE,
        ]);
        $course = KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => 'Tin học văn phòng - Ca tối',
            'teacher_id' => $teacher->id,
            'status' => KhoaHoc::STATUS_ACTIVE,
        ]);

        DonUngTuyenGiaoVien::create([
            'name' => 'Ứng viên A',
            'email' => 'ungvien@example.com',
            'status' => DonUngTuyenGiaoVien::STATUS_PENDING,
        ]);

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'status' => GhiDanh::STATUS_PENDING,
            'is_submitted' => true,
        ]);

        KhungGioKhoaHoc::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'min_students' => 10,
            'max_students' => 20,
            'status' => KhungGioKhoaHoc::STATUS_OPEN_FOR_REGISTRATION,
        ]);

        NguyenVongKhungGio::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => NguyenVongKhungGio::STATUS_PENDING,
        ]);

        $slotRegistration = NguyenVongKhungGio::first();

        LuaChonNguyenVongKhungGio::create([
            'slot_registration_id' => $slotRegistration->id,
            'course_time_slot_id' => KhungGioKhoaHoc::first()->id,
            'priority' => 1,
        ]);

        YeuCauDoiLich::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'reason' => 'Bận việc cá nhân',
            'status' => YeuCauDoiLich::STATUS_PENDING,
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
        $admin = NguoiDung::factory()->create([
            'role_id' => \App\Models\VaiTro::idByName(NguoiDung::ROLE_ADMIN),
        ]);
        $student = NguoiDung::factory()->create([
            'role_id' => \App\Models\VaiTro::idByName(NguoiDung::ROLE_STUDENT),
            'name' => 'Hoc vien trung lich',
        ]);
        $teacherA = NguoiDung::factory()->create([
            'role_id' => \App\Models\VaiTro::idByName(NguoiDung::ROLE_TEACHER),
        ]);
        $teacherB = NguoiDung::factory()->create([
            'role_id' => \App\Models\VaiTro::idByName(NguoiDung::ROLE_TEACHER),
        ]);

        $category = NhomHoc::create([
            'name' => 'Ngoại ngữ - Tin học',
            'slug' => 'ngoai-ngu-tin-hoc-conflict',
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        $subjectA = MonHoc::create([
            'name' => 'Tin học văn phòng',
            'price' => 1500000,
            'category_id' => $category->id,
        ]);
        $subjectB = MonHoc::create([
            'name' => 'Tiếng Anh giao tiếp',
            'price' => 1800000,
            'category_id' => $category->id,
        ]);

        $roomA = PhongHoc::create([
            'code' => 'P201',
            'name' => 'Phòng 201',
            'capacity' => 30,
            'status' => PhongHoc::STATUS_ACTIVE,
        ]);
        $roomB = PhongHoc::create([
            'code' => 'P202',
            'name' => 'Phòng 202',
            'capacity' => 30,
            'status' => PhongHoc::STATUS_ACTIVE,
        ]);

        $courseA = KhoaHoc::create([
            'subject_id' => $subjectA->id,
            'title' => 'Tin học văn phòng - Ca tối',
            'teacher_id' => $teacherA->id,
            'start_date' => '2026-05-01',
            'end_date' => '2026-07-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'status' => KhoaHoc::STATUS_ACTIVE,
        ]);
        $courseB = KhoaHoc::create([
            'subject_id' => $subjectB->id,
            'title' => 'Tiếng Anh giao tiếp - Ca tối',
            'teacher_id' => $teacherB->id,
            'start_date' => '2026-05-01',
            'end_date' => '2026-07-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'status' => KhoaHoc::STATUS_ACTIVE,
        ]);

        $classRoomA = LopHoc::create([
            'subject_id' => $subjectA->id,
            'course_id' => $courseA->id,
            'name' => $courseA->title,
            'room_id' => $roomA->id,
            'teacher_id' => $teacherA->id,
            'start_date' => '2026-05-01',
            'duration' => 3,
            'status' => LopHoc::STATUS_OPEN,
        ]);
        $classRoomB = LopHoc::create([
            'subject_id' => $subjectB->id,
            'course_id' => $courseB->id,
            'name' => $courseB->title,
            'room_id' => $roomB->id,
            'teacher_id' => $teacherB->id,
            'start_date' => '2026-05-01',
            'duration' => 3,
            'status' => LopHoc::STATUS_OPEN,
        ]);

        foreach ([[$classRoomA, $teacherA, $roomA], [$classRoomB, $teacherB, $roomB]] as [$classRoom, $teacher, $room]) {
            LichHoc::create([
                'lop_hoc_id' => $classRoom->id,
                'teacher_id' => $teacher->id,
                'room_id' => $room->id,
                'day_of_week' => 'Monday',
                'start_time' => '18:00',
                'end_time' => '20:00',
            ]);
        }

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subjectA->id,
            'course_id' => $courseA->id,
            'lop_hoc_id' => $classRoomA->id,
            'assigned_teacher_id' => $teacherA->id,
            'status' => GhiDanh::STATUS_ACTIVE,
            'schedule' => $courseA->formattedSchedule(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subjectB->id,
            'course_id' => $courseB->id,
            'lop_hoc_id' => $classRoomB->id,
            'assigned_teacher_id' => $teacherB->id,
            'status' => GhiDanh::STATUS_ACTIVE,
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
        $student = NguoiDung::factory()->create([
            'role_id' => \App\Models\VaiTro::idByName(NguoiDung::ROLE_STUDENT),
        ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get('/admin/dashboard');

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_teacher_is_blocked_from_admin_dashboard(): void
    {
        $teacher = NguoiDung::factory()->create([
            'role_id' => \App\Models\VaiTro::idByName(NguoiDung::ROLE_TEACHER),
        ]);

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get('/admin/dashboard');

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }
}

