<?php

namespace Tests\Feature;

use App\Exceptions\EnrollmentOperationException;
use App\Models\NhomHoc;
use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\ThongBao;
use App\Models\PhongHoc;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use App\Helpers\ScheduleHelper;
use App\Services\AdminScheduleService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ghi_danh_hoc_vien_test extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_custom_schedule_request_without_waiting_for_an_open_class(): void
    {
        $student = NguoiDung::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();
        $admin = NguoiDung::factory()->admin()->create();

        $message = app(\App\Services\StudentEnrollmentService::class)->submitCustomScheduleRequest($student, $subject, [
            'start_time' => '18:00',
            'end_time' => '20:15',
            'preferred_days' => ['Monday', 'Wednesday', 'Friday'],
            'preferred_schedule' => 'Ưu tiên ca tối trong tuần.',
        ]);

        $this->assertSame('Đã gửi yêu cầu đăng ký khóa học. Admin sẽ xem lịch mong muốn và xếp lớp phù hợp.', $message);
        $this->assertDatabaseHas('dang_ky', [
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'lop_hoc_id' => null,
            'status' => GhiDanh::STATUS_PENDING,
            'start_time' => '18:00',
            'end_time' => '20:15',
            'preferred_schedule' => 'Ưu tiên ca tối trong tuần.',
        ]);

        $enrollment = GhiDanh::where('user_id', $student->id)->where('subject_id', $subject->id)->firstOrFail();
        $this->assertSame(['Monday', 'Wednesday', 'Friday'], $enrollment->preferred_days);
        $this->assertDatabaseHas('thong_bao', [
            'user_id' => $admin->id,
            'title' => 'Học viên gửi yêu cầu lịch học',
            'type' => 'enrollment',
            'link' => route('admin.enrollments.custom.show', $enrollment),
        ]);
    }

    public function test_admin_schedule_teacher_list_only_shows_matching_specialists(): void
    {
        $service = app(AdminScheduleService::class);

        $matchingTeacher = NguoiDung::factory()->teacher()->create([
            'name' => 'Bui Anh Dung (Ngoai ngu)',
        ]);
        $otherTeacher = NguoiDung::factory()->teacher()->create([
            'name' => 'Huynh Bao Chau (Tin hoc)',
        ]);

        [, $subject] = $this->createCatalogSubject('Tieng Anh giao tiep', 'Ngoai ngu');

        $teachers = $service->teacherOptionsForSubject($subject->fresh(['category']));

        $this->assertTrue($teachers->contains('id', $matchingTeacher->id));
        $this->assertFalse($teachers->contains('id', $otherTeacher->id));
    }

    public function test_schedule_helper_normalizes_am_pm_time_strings(): void
    {
        $this->assertSame('01:02', ScheduleHelper::normalizeTimeValue('01:02 AM'));
        $this->assertSame('13:17', ScheduleHelper::normalizeTimeValue('01:17 PM'));
    }

    public function test_student_can_directly_enroll_into_an_open_fixed_class(): void
    {
        $student = NguoiDung::factory()->student()->create();
        $adminOne = NguoiDung::factory()->admin()->create(['name' => 'Admin 1']);
        $adminTwo = NguoiDung::factory()->admin()->create(['name' => 'Admin 2']);
        $teacher = NguoiDung::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $classRoom = $this->createOpenClassRoom($subject, $teacher);

        $message = app(\App\Services\StudentEnrollmentService::class)->submitFixedClassEnrollment($student, $subject, $classRoom->id);

        $this->assertSame('Đã ghi danh vào lớp cố định thành công.', $message);
        $this->assertDatabaseHas('dang_ky', [
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $classRoom->course_id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => GhiDanh::STATUS_ENROLLED,
        ]);

        $enrollment = GhiDanh::where('user_id', $student->id)->where('subject_id', $subject->id)->firstOrFail();
        $this->assertDatabaseCount('thong_bao', 2);
        $this->assertDatabaseHas('thong_bao', [
            'user_id' => $adminOne->id,
            'title' => 'Học viên đăng ký lớp cố định',
            'type' => 'enrollment',
            'link' => route('admin.enrollments.fixed.show', $enrollment),
        ]);
        $this->assertDatabaseHas('thong_bao', [
            'user_id' => $adminTwo->id,
            'title' => 'Học viên đăng ký lớp cố định',
            'type' => 'enrollment',
            'link' => route('admin.enrollments.fixed.show', $enrollment),
        ]);
    }

    public function test_legacy_confirmed_enrollment_blocks_enrolling_into_a_different_fixed_class(): void
    {
        $student = NguoiDung::factory()->student()->create();
        $teacherOne = NguoiDung::factory()->teacher()->create();
        $teacherTwo = NguoiDung::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject('Ke toan thuc hanh', 'Ke toan');
        $classRoomOne = $this->createOpenClassRoom($subject, $teacherOne);
        $classRoomTwo = $this->createOpenClassRoom($subject, $teacherTwo, 'Wednesday', '18:00', '20:00');

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $classRoomOne->course_id,
            'lop_hoc_id' => $classRoomOne->id,
            'assigned_teacher_id' => $teacherOne->id,
            'status' => GhiDanh::LEGACY_STATUS_CONFIRMED,
            'schedule' => $classRoomOne->scheduleSummary(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $response = $this
            ->from(route('student.enroll.select', $subject))
            ->withSession(['user_id' => $student->id])
            ->post(route('student.enroll.store', $subject), [
                'lop_hoc_id' => $classRoomTwo->id,
            ]);

        $response->assertRedirect(route('student.enroll.select', $subject));
        $response->assertSessionHas('error', 'Bạn đã có lớp cho khóa học này. Nếu muốn đổi lớp, vui lòng liên hệ admin.');

        $this->assertSame(1, GhiDanh::count());
        $this->assertDatabaseHas('dang_ky', [
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'lop_hoc_id' => $classRoomOne->id,
            'status' => GhiDanh::LEGACY_STATUS_CONFIRMED,
        ]);
    }

    public function test_student_can_switch_from_custom_request_to_fixed_class_without_creating_duplicate_enrollments(): void
    {
        $student = NguoiDung::factory()->student()->create();
        $teacher = NguoiDung::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $classRoom = $this->createOpenClassRoom($subject, $teacher);

        $pendingEnrollment = GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => GhiDanh::STATUS_PENDING,
            'start_time' => '17:30',
            'end_time' => '19:30',
            'preferred_days' => ['Tuesday', 'Thursday'],
            'preferred_schedule' => 'Muốn học sau giờ làm.',
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->post(route('student.enroll.store', $subject), [
                'lop_hoc_id' => $classRoom->id,
            ]);

        $response->assertRedirect(route('student.enroll.my-classes'));

        $this->assertDatabaseCount('dang_ky', 1);
        $this->assertDatabaseHas('dang_ky', [
            'id' => $pendingEnrollment->id,
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $classRoom->course_id,
            'lop_hoc_id' => $classRoom->id,
            'status' => GhiDanh::STATUS_ENROLLED,
            'start_time' => null,
            'end_time' => null,
            'preferred_schedule' => null,
        ]);

        $updatedEnrollment = $pendingEnrollment->fresh();
        $this->assertNull($updatedEnrollment->preferred_days);
    }

    public function test_student_cannot_submit_custom_schedule_request_that_overlaps_an_existing_active_class(): void
    {
        $student = NguoiDung::factory()->student()->create();
        $teacherOne = NguoiDung::factory()->teacher()->create();
        [, $subjectOne] = $this->createCatalogSubject('Tieng Anh giao tiep');
        [, $subjectTwo] = $this->createCatalogSubject('Lap trinh Python co ban', 'Tin hoc');
        $classRoomOne = $this->createOpenClassRoom($subjectOne, $teacherOne, 'Monday', '18:00', '20:15');

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subjectOne->id,
            'course_id' => $classRoomOne->course_id,
            'lop_hoc_id' => $classRoomOne->id,
            'assigned_teacher_id' => $teacherOne->id,
            'status' => GhiDanh::STATUS_ACTIVE,
            'schedule' => $classRoomOne->scheduleSummary(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        try {
            app(\App\Services\StudentEnrollmentService::class)->submitCustomScheduleRequest($student, $subjectTwo, [
                'start_time' => '18:30',
                'end_time' => '20:45',
                'preferred_days' => ['Monday'],
                'preferred_schedule' => 'Muon hoc ca toi.',
            ]);

            $this->fail('Expected schedule conflict exception was not thrown.');
        } catch (EnrollmentOperationException $exception) {
            $this->assertSame('Học viên đã có lớp khác trùng lịch trong cùng khung giờ. Vui lòng chọn lịch khác.', $exception->getMessage());
        }

        $this->assertDatabaseCount('dang_ky', 1);
    }

    public function test_student_fixed_class_enrollment_reuses_existing_record_for_same_class(): void
    {
        $student = NguoiDung::factory()->student()->create();
        $teacher = NguoiDung::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $classRoom = $this->createOpenClassRoom($subject, $teacher);

        $pendingEnrollment = GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'lop_hoc_id' => $classRoom->id,
            'status' => GhiDanh::STATUS_PENDING,
            'start_time' => '17:30',
            'end_time' => '19:30',
            'preferred_days' => ['Tuesday', 'Thursday'],
            'preferred_schedule' => 'Muon hoc cung lop nay.',
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->post(route('student.enroll.store', $subject), [
                'lop_hoc_id' => $classRoom->id,
            ]);

        $response->assertRedirect(route('student.enroll.my-classes'));
        $response->assertSessionHas('status');

        $this->assertDatabaseCount('dang_ky', 1);
        $this->assertDatabaseHas('dang_ky', [
            'id' => $pendingEnrollment->id,
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $classRoom->course_id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => GhiDanh::STATUS_ENROLLED,
            'start_time' => null,
            'end_time' => null,
            'preferred_schedule' => null,
        ]);

        $this->assertNull($pendingEnrollment->fresh()->preferred_days);
    }

    public function test_student_cannot_enroll_into_two_classes_with_overlapping_schedule(): void
    {
        $student = NguoiDung::factory()->student()->create();
        $teacherOne = NguoiDung::factory()->teacher()->create();
        $teacherTwo = NguoiDung::factory()->teacher()->create();
        [, $subjectOne] = $this->createCatalogSubject('Tieng Anh giao tiep');
        [, $subjectTwo] = $this->createCatalogSubject('Lap trinh Python co ban', 'Tin hoc');
        $classRoomOne = $this->createOpenClassRoom($subjectOne, $teacherOne);
        $classRoomTwo = $this->createOpenClassRoom($subjectTwo, $teacherTwo, 'Monday', '18:00', '20:00');

        $firstResponse = $this
            ->withSession(['user_id' => $student->id])
            ->post(route('student.enroll.store', $subjectOne), [
                'lop_hoc_id' => $classRoomOne->id,
            ]);

        $firstResponse->assertRedirect(route('student.enroll.my-classes'));
        $firstResponse->assertSessionHas('status');

        $secondResponse = $this
            ->withSession(['user_id' => $student->id])
            ->post(route('student.enroll.store', $subjectTwo), [
                'lop_hoc_id' => $classRoomTwo->id,
            ]);

        $secondResponse->assertRedirect();
        $secondResponse->assertSessionHas('error', 'Học viên đã có lớp khác trùng lịch trong cùng khung giờ. Vui lòng chọn lớp khác.');
        $this->assertDatabaseCount('dang_ky', 1);
    }

    public function test_completed_class_without_class_room_id_still_blocks_overlapping_new_enrollment_when_schedule_matches_stored_data(): void
    {
        $student = NguoiDung::factory()->student()->create();
        $teacherOne = NguoiDung::factory()->teacher()->create();
        $teacherTwo = NguoiDung::factory()->teacher()->create();
        [, $subjectOne] = $this->createCatalogSubject('Lich lop da ket thuc');
        [, $subjectTwo] = $this->createCatalogSubject('Lop moi trung lich', 'Tin hoc');
        $courseOne = KhoaHoc::create([
            'subject_id' => $subjectOne->id,
            'title' => $subjectOne->name . ' - Lop co dinh',
            'description' => 'Lop co dinh da ket thuc.',
            'teacher_id' => $teacherOne->id,
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday'],
            'start_time' => '18:00',
            'end_time' => '20:00',
            'start_date' => '2026-03-01',
            'end_date' => '2026-04-01',
            'schedule' => 'Thứ 2 | 18:00 - 20:00',
        ]);

        $completedRoom = PhongHoc::create([
            'code' => 'C' . fake()->unique()->numberBetween(100, 999),
            'name' => 'Phong hoc ' . fake()->unique()->numberBetween(1, 99),
            'type' => 'offline',
            'location' => 'Tang 3',
            'capacity' => 20,
            'status' => PhongHoc::STATUS_ACTIVE,
        ]);

        $currentRoom = PhongHoc::create([
            'code' => 'D' . fake()->unique()->numberBetween(100, 999),
            'name' => 'Phong hoc ' . fake()->unique()->numberBetween(1, 99),
            'type' => 'offline',
            'location' => 'Tang 4',
            'capacity' => 20,
            'status' => PhongHoc::STATUS_ACTIVE,
        ]);

        $classRoomOne = LopHoc::create([
            'subject_id' => $subjectOne->id,
            'course_id' => $courseOne->id,
            'name' => $subjectOne->name . ' - Lop da ket thuc',
            'room_id' => $completedRoom->id,
            'teacher_id' => $teacherOne->id,
            'status' => LopHoc::STATUS_COMPLETED,
            'duration' => 3,
            'start_date' => '2026-03-01',
        ]);

        LichHoc::create([
            'lop_hoc_id' => $classRoomOne->id,
            'teacher_id' => $teacherOne->id,
            'room_id' => $completedRoom->id,
            'day_of_week' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);

        $classRoomTwo = LopHoc::create([
            'subject_id' => $subjectOne->id,
            'course_id' => $courseOne->id,
            'name' => $subjectOne->name . ' - Lop hien tai',
            'room_id' => $currentRoom->id,
            'teacher_id' => $teacherOne->id,
            'status' => LopHoc::STATUS_OPEN,
            'duration' => 3,
            'start_date' => '2026-05-01',
        ]);

        LichHoc::create([
            'lop_hoc_id' => $classRoomTwo->id,
            'teacher_id' => $teacherOne->id,
            'room_id' => $currentRoom->id,
            'day_of_week' => 'Wednesday',
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subjectOne->id,
            'course_id' => $courseOne->id,
            'lop_hoc_id' => null,
            'assigned_teacher_id' => $teacherOne->id,
            'status' => GhiDanh::STATUS_COMPLETED,
            'schedule' => $classRoomOne->scheduleSummary(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $completedEnrollment = GhiDanh::firstOrFail();
        $this->assertSame($classRoomOne->id, $completedEnrollment->currentClassRoom()?->id);

        try {
            app(\App\Services\StudentEnrollmentService::class)->submitFixedClassEnrollment(
                $student,
                $subjectTwo,
                $this->createOpenClassRoom($subjectTwo, $teacherTwo, 'Monday', '18:00', '20:00')->id
            );

            $this->fail('Expected schedule conflict exception was not thrown.');
        } catch (EnrollmentOperationException $e) {
            $this->assertSame('Học viên đã có lớp khác trùng lịch trong cùng khung giờ. Vui lòng chọn lớp khác.', $e->getMessage());
        }

        $this->assertDatabaseCount('dang_ky', 1);
    }

    public function test_student_cannot_enroll_into_a_class_that_has_already_started(): void
    {
        Carbon::setTestNow('2026-04-17 10:00:00');

        try {
            $student = NguoiDung::factory()->student()->create();
            $teacher = NguoiDung::factory()->teacher()->create();
            [, $subject] = $this->createCatalogSubject('Tin hoc ung dung');
            $classRoom = $this->createOpenClassRoom($subject, $teacher, 'Monday', '18:00', '20:00', [
                'start_date' => '2026-04-10',
            ]);

            $response = $this
                ->from(route('student.enroll.select', $subject))
                ->withSession(['user_id' => $student->id])
                ->post(route('student.enroll.store', $subject), [
                    'lop_hoc_id' => $classRoom->id,
                ]);

            $response->assertRedirect(route('student.enroll.select', $subject));
            $response->assertSessionHas('error', 'Lớp học này đã bắt đầu, không thể đăng ký.');
            $this->assertDatabaseMissing('dang_ky', [
                'user_id' => $student->id,
                'subject_id' => $subject->id,
                'lop_hoc_id' => $classRoom->id,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_student_cannot_enroll_into_a_class_that_has_already_ended(): void
    {
        Carbon::setTestNow('2026-04-17 10:00:00');

        try {
            $student = NguoiDung::factory()->student()->create();
            $teacher = NguoiDung::factory()->teacher()->create();
            [, $subject] = $this->createCatalogSubject('Ke toan thuc hanh');
            $classRoom = $this->createOpenClassRoom($subject, $teacher, 'Tuesday', '18:00', '20:00', [
                'start_date' => '2026-03-01',
                'end_date' => '2026-04-01',
            ]);

            $response = $this
                ->from(route('student.enroll.select', $subject))
                ->withSession(['user_id' => $student->id])
                ->post(route('student.enroll.store', $subject), [
                    'lop_hoc_id' => $classRoom->id,
                ]);

            $response->assertRedirect(route('student.enroll.select', $subject));
            $response->assertSessionHas('error', 'Lớp học này đã kết thúc, vui lòng chờ admin mở lớp mới.');
            $this->assertDatabaseMissing('dang_ky', [
                'user_id' => $student->id,
                'subject_id' => $subject->id,
                'lop_hoc_id' => $classRoom->id,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_student_can_enroll_into_a_future_open_class(): void
    {
        Carbon::setTestNow('2026-04-17 10:00:00');

        try {
            $student = NguoiDung::factory()->student()->create();
            $teacher = NguoiDung::factory()->teacher()->create();
            [, $subject] = $this->createCatalogSubject('Marketing digital');
            $classRoom = $this->createOpenClassRoom($subject, $teacher, 'Thursday', '18:00', '20:00', [
                'start_date' => '2026-04-25',
                'end_date' => '2026-06-25',
            ]);

            $response = $this
                ->withSession(['user_id' => $student->id])
                ->post(route('student.enroll.store', $subject), [
                    'lop_hoc_id' => $classRoom->id,
                ]);

            $response->assertRedirect(route('student.enroll.my-classes'));
            $response->assertSessionHas('status');
            $this->assertDatabaseHas('dang_ky', [
                'user_id' => $student->id,
                'subject_id' => $subject->id,
                'course_id' => $classRoom->course_id,
                'lop_hoc_id' => $classRoom->id,
                'assigned_teacher_id' => $teacher->id,
                'status' => GhiDanh::STATUS_ENROLLED,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_student_select_class_screen_marks_started_class_as_unavailable(): void
    {
        Carbon::setTestNow('2026-04-17 10:00:00');

        try {
            $student = NguoiDung::factory()->student()->create();
            $teacher = NguoiDung::factory()->teacher()->create();
            [, $subject] = $this->createCatalogSubject('Ky nang ban hang');
            $this->createOpenClassRoom($subject, $teacher, 'Monday', '18:00', '20:00', [
                'start_date' => '2026-04-01',
            ]);
            $futureClass = $this->createOpenClassRoom($subject, $teacher, 'Wednesday', '18:00', '20:00', [
                'start_date' => '2026-04-25',
            ]);

            $response = $this
                ->withSession(['user_id' => $student->id])
                ->get(route('student.enroll.select', $subject));

            $response->assertOk();
            $response->assertSee('Đã bắt đầu');
            $response->assertSee('Lớp học này đã bắt đầu, không thể đăng ký.');
            $response->assertSee('25/04/2026');
            $response->assertSee('Đăng ký lớp này');
            $response->assertSee('aria-disabled="true"', false);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_student_select_class_screen_shows_schedule_conflict_warning(): void
    {
        $student = NguoiDung::factory()->student()->create();
        $teacherOne = NguoiDung::factory()->teacher()->create();
        $teacherTwo = NguoiDung::factory()->teacher()->create();
        [, $subjectOne] = $this->createCatalogSubject('Tieng Anh giao tiep');
        [, $subjectTwo] = $this->createCatalogSubject('Lap trinh Python co ban', 'Tin hoc');
        $classRoomOne = $this->createOpenClassRoom($subjectOne, $teacherOne, 'Wednesday', '18:00', '22:15');
        $classRoomTwo = $this->createOpenClassRoom($subjectTwo, $teacherTwo, 'Wednesday', '18:30', '20:45');

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subjectOne->id,
            'course_id' => $classRoomOne->course_id,
            'lop_hoc_id' => $classRoomOne->id,
            'assigned_teacher_id' => $teacherOne->id,
            'status' => GhiDanh::STATUS_ACTIVE,
            'schedule' => $classRoomOne->scheduleSummary(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('student.enroll.select', $subjectTwo));

        $response->assertOk();
        $response->assertSee('Phát hiện 1 lớp bị trùng lịch với thời khóa biểu hiện tại.');
        $response->assertSee('Trùng với lớp ' . $classRoomOne->displayName());
        $response->assertSee('Bị trùng lịch');
        $response->assertSee('Khung giờ này bị trùng với lịch học hiện tại.');
        $response->assertSee($classRoomTwo->subject->name);
    }

    public function test_student_select_class_screen_keeps_non_conflicting_class_available(): void
    {
        $student = NguoiDung::factory()->student()->create();
        $teacherOne = NguoiDung::factory()->teacher()->create();
        $teacherTwo = NguoiDung::factory()->teacher()->create();
        [, $subjectOne] = $this->createCatalogSubject('Tieng Anh giao tiep');
        [, $subjectTwo] = $this->createCatalogSubject('Lap trinh Python co ban', 'Tin hoc');
        $classRoomOne = $this->createOpenClassRoom($subjectOne, $teacherOne, 'Wednesday', '18:00', '20:15');
        $classRoomTwo = $this->createOpenClassRoom($subjectTwo, $teacherTwo, 'Wednesday', '20:15', '22:00');

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subjectOne->id,
            'course_id' => $classRoomOne->course_id,
            'lop_hoc_id' => $classRoomOne->id,
            'assigned_teacher_id' => $teacherOne->id,
            'status' => GhiDanh::STATUS_ACTIVE,
            'schedule' => $classRoomOne->scheduleSummary(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('student.enroll.select', $subjectTwo));

        $response->assertOk();
        $response->assertDontSee('Phát hiện 1 lớp bị trùng lịch với thời khóa biểu hiện tại.');
        $response->assertSee('Đăng ký lớp này');
        $response->assertDontSee('Bị trùng lịch');
        $response->assertSee($classRoomTwo->subject->name);
    }

    public function test_student_portal_keeps_approved_status_when_updating_custom_schedule_request(): void
    {
        $student = NguoiDung::factory()->student()->create();
        $admin = NguoiDung::factory()->admin()->create();
        [, $subject] = $this->createCatalogSubject();

        $enrollment = GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => GhiDanh::STATUS_APPROVED,
            'start_time' => '17:00',
            'end_time' => '19:00',
            'preferred_days' => ['Monday', 'Wednesday'],
            'preferred_schedule' => 'Muon hoc som.',
            'note' => 'Admin da duyet, vui long xac nhan lai lich.',
            'is_submitted' => true,
            'submitted_at' => now()->subDay(),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subHours(2),
        ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->post(route('student.enroll.request-store', $subject), [
                'start_time' => '18:30',
                'end_time' => '20:45',
                'preferred_days' => ['Tuesday', 'Thursday'],
                'preferred_schedule' => 'Cap nhat sang lich toi.',
            ]);

        $response->assertRedirect(route('student.enroll.my-classes'));
        $response->assertSessionHas('status');

        $updatedEnrollment = $enrollment->fresh();
        $this->assertSame(GhiDanh::STATUS_APPROVED, $updatedEnrollment->status);
        $this->assertSame('18:30', $updatedEnrollment->start_time);
        $this->assertSame('20:45', $updatedEnrollment->end_time);
        $this->assertSame(['Tuesday', 'Thursday'], $updatedEnrollment->preferred_days);
        $this->assertSame('Cap nhat sang lich toi.', $updatedEnrollment->preferred_schedule);
        $this->assertNull($updatedEnrollment->note);
        $this->assertSame($admin->id, $updatedEnrollment->reviewed_by);
        $this->assertNotNull($updatedEnrollment->reviewed_at);
    }

    public function test_database_prevents_duplicate_enrollment_for_same_student_and_class(): void
    {
        $student = NguoiDung::factory()->student()->create();
        $teacher = NguoiDung::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $classRoom = $this->createOpenClassRoom($subject, $teacher);

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $classRoom->course_id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => GhiDanh::STATUS_ENROLLED,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $classRoom->course_id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => GhiDanh::STATUS_ENROLLED,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);
    }

    private function createCatalogSubject(string $name = 'Tieng Anh giao tiep', string $categoryName = 'Ngoai ngu'): array
    {
        $category = NhomHoc::create([
            'name' => $categoryName,
            'slug' => 'ngoai-ngu-' . str()->slug($name) . '-' . fake()->unique()->numberBetween(100, 999),
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        $subject = MonHoc::create([
            'name' => $name,
            'category_id' => $category->id,
            'status' => MonHoc::STATUS_OPEN,
            'price' => 2500000,
        ]);

        return [$category, $subject];
    }

    private function createOpenClassRoom(
        MonHoc $subject,
        NguoiDung $teacher,
        string $dayOfWeek = 'Monday',
        string $startTime = '18:00',
        string $endTime = '20:00',
        array $overrides = []
    ): LopHoc
    {
        $startDate = $overrides['start_date'] ?? null;
        $endDate = $overrides['end_date'] ?? null;

        $course = KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => $subject->name . ' - Lop co dinh',
            'description' => 'Lop co dinh do admin mo.',
            'teacher_id' => $teacher->id,
            'day_of_week' => $dayOfWeek,
            'meeting_days' => [$dayOfWeek],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'schedule' => 'T2-T4-T6, ' . $startTime . ' - ' . $endTime,
        ]);

        $room = PhongHoc::create([
            'code' => 'A' . fake()->unique()->numberBetween(100, 999),
            'name' => 'Phong hoc ' . fake()->unique()->numberBetween(1, 99),
            'type' => 'offline',
            'location' => 'Tang 2',
            'capacity' => 20,
            'status' => PhongHoc::STATUS_ACTIVE,
        ]);

        $classRoom = LopHoc::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'name' => $subject->name . ' - Lop toi',
            'room_id' => $room->id,
            'teacher_id' => $teacher->id,
            'status' => LopHoc::STATUS_OPEN,
            'duration' => $overrides['duration'] ?? 3,
            'start_date' => $startDate,
        ]);

        LichHoc::create([
            'lop_hoc_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        return $classRoom;
    }
}

