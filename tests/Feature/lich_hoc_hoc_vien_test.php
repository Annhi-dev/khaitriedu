<?php

namespace Tests\Feature;

use App\Models\DiemDanh;
use App\Models\NhomHoc;
use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\PhongHoc;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class lich_hoc_hoc_vien_test extends TestCase
{
    use RefreshDatabase;

    public function test_student_schedule_displays_attendance_summary_and_classmates(): void
    {
        $student = NguoiDung::factory()->student()->create(['name' => 'Hoc vien chinh']);
        $classmate = NguoiDung::factory()->student()->create(['name' => 'Ban cung lop']);
        $teacher = NguoiDung::factory()->teacher()->create(['name' => 'Giang vien A']);

        ['subject' => $subject, 'course' => $course, 'classRoom' => $classRoom, 'schedule' => $schedule] = $this->createClassBundle($teacher);

        $studentEnrollment = GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => GhiDanh::STATUS_ACTIVE,
            'schedule' => $course->formattedSchedule(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        GhiDanh::create([
            'user_id' => $classmate->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => GhiDanh::STATUS_ACTIVE,
            'schedule' => $course->formattedSchedule(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        DiemDanh::create([
            'course_id' => $course->id,
            'class_room_id' => $classRoom->id,
            'class_schedule_id' => $schedule->id,
            'enrollment_id' => $studentEnrollment->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'attendance_date' => '2026-04-02',
            'status' => DiemDanh::STATUS_PRESENT,
            'recorded_at' => now(),
        ]);

        DiemDanh::create([
            'course_id' => $course->id,
            'class_room_id' => $classRoom->id,
            'class_schedule_id' => $schedule->id,
            'enrollment_id' => $studentEnrollment->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'attendance_date' => '2026-04-09',
            'status' => DiemDanh::STATUS_LATE,
            'recorded_at' => now(),
        ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('student.schedule'));

        $response->assertOk();
        $response->assertSee($course->title);
        $response->assertSee('Thống kê điểm danh');
        $response->assertSee('Tỷ lệ có mặt');
        $response->assertSee('Lần điểm danh gần đây');
        $response->assertSee('Bạn học cùng lớp');
        $response->assertSee($classmate->name);
        $response->assertSee('Khung giờ');
        $response->assertSee('Lịch học theo tuần');
        $response->assertSee('Xem chi tiết');
        $response->assertSee('Xem khoa hoc');
    }

    public function test_student_schedule_flags_overlapping_classes_as_conflicts(): void
    {
        $student = NguoiDung::factory()->student()->create(['name' => 'Hoc vien co xung dot']);
        $teacherA = NguoiDung::factory()->teacher()->create(['name' => 'Giang vien A']);
        $teacherB = NguoiDung::factory()->teacher()->create(['name' => 'Giang vien B']);

        ['subject' => $subjectA, 'course' => $courseA, 'classRoom' => $classRoomA] = $this->createClassBundle($teacherA, 'Wednesday', '18:00', '22:15');
        ['subject' => $subjectB, 'course' => $courseB, 'classRoom' => $classRoomB] = $this->createClassBundle($teacherB, 'Wednesday', '18:30', '20:45');

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
            ->withSession(['user_id' => $student->id])
            ->get(route('student.schedule'));

        $response->assertOk();
        $response->assertSee('Phát hiện 1 buổi học bị trùng lịch trong dữ liệu hiện tại.');
        $response->assertSee('Trùng lịch');
        $response->assertSee('Trùng với');
        $response->assertSee($classRoomA->displayName());
        $response->assertSee($classRoomB->displayName());
    }

    public function test_student_schedule_ignores_completed_classes_when_marking_weekly_conflicts(): void
    {
        $student = NguoiDung::factory()->student()->create(['name' => 'Hoc vien da hoan thanh']);
        $teacherA = NguoiDung::factory()->teacher()->create(['name' => 'Giang vien A']);
        $teacherB = NguoiDung::factory()->teacher()->create(['name' => 'Giang vien B']);

        ['subject' => $subjectA, 'course' => $courseA, 'classRoom' => $classRoomA] = $this->createClassBundle($teacherA, 'Wednesday', '18:00', '20:15');
        ['subject' => $subjectB, 'course' => $courseB, 'classRoom' => $classRoomB] = $this->createClassBundle($teacherB, 'Wednesday', '18:30', '20:45');

        $classRoomA->update(['status' => LopHoc::STATUS_COMPLETED]);

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subjectA->id,
            'course_id' => $courseA->id,
            'lop_hoc_id' => $classRoomA->id,
            'assigned_teacher_id' => $teacherA->id,
            'status' => GhiDanh::STATUS_COMPLETED,
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
            ->withSession(['user_id' => $student->id])
            ->get(route('student.schedule'));

        $response->assertOk();
        $response->assertDontSee('Phát hiện 1 buổi học bị trùng lịch trong dữ liệu hiện tại.');
        $response->assertDontSee('Trùng lịch');
    }

    private function createClassBundle(
        NguoiDung $teacher,
        string $dayOfWeek = 'Thursday',
        string $startTime = '18:00',
        string $endTime = '20:00'
    ): array
    {
        return $this->createClassBundleWithSchedule($teacher, $dayOfWeek, $startTime, $endTime);
    }

    private function createClassBundleWithSchedule(
        NguoiDung $teacher,
        string $dayOfWeek = 'Thursday',
        string $startTime = '18:00',
        string $endTime = '20:00'
    ): array
    {
        $slug = 'student-schedule-' . str()->lower(str()->random(6));

        $category = NhomHoc::create([
            'name' => 'Nhom hoc ' . $slug,
            'slug' => 'nhom-' . $slug,
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        $subject = MonHoc::create([
            'name' => 'Mon hoc ' . $slug,
            'category_id' => $category->id,
            'status' => MonHoc::STATUS_OPEN,
            'price' => 1200000,
            'duration' => 3,
        ]);

        $course = KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => 'Lop hoc ' . $slug,
            'teacher_id' => $teacher->id,
            'day_of_week' => $dayOfWeek,
            'meeting_days' => [$dayOfWeek],
            'start_date' => '2026-04-01',
            'end_date' => '2026-06-01',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => KhoaHoc::STATUS_SCHEDULED,
            'schedule' => 'Thu 5, ' . $startTime . ' - ' . $endTime . ' | Tu 01/04/2026 den 01/06/2026',
        ]);

        $room = PhongHoc::create([
            'code' => 'P' . fake()->unique()->numberBetween(100, 999),
            'name' => 'Phong hoc test',
            'type' => 'offline',
            'location' => 'Tang 3',
            'capacity' => 20,
            'status' => PhongHoc::STATUS_ACTIVE,
        ]);

        $classRoom = LopHoc::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'name' => $course->title,
            'room_id' => $room->id,
            'teacher_id' => $teacher->id,
            'start_date' => '2026-04-01',
            'duration' => 3,
            'status' => LopHoc::STATUS_OPEN,
        ]);

        $schedule = LichHoc::create([
            'lop_hoc_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        return compact('category', 'subject', 'course', 'classRoom', 'schedule');
    }
}

