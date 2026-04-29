<?php

namespace Tests\Feature;

use App\Models\NhomHoc;
use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\PhongHoc;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use App\Services\AdminScheduleConflictService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class kiem_tra_xung_dot_lich_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_detect_teacher_and_room_conflicts_for_manual_schedule(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $teacherOne = NguoiDung::factory()->teacher()->create();
        $teacherTwo = NguoiDung::factory()->teacher()->create();
        $roomOne = $this->createRoom('PH001');
        $roomTwo = $this->createRoom('PH002');
        [, $subject] = $this->createSubject();

        $pendingCourse = $this->createPendingOpenCourse($subject, $teacherOne);
        $roomConflictClass = $this->createClassRoom($subject, $teacherTwo, $roomOne);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.schedules.conflicts', [
                'teacher_id' => $teacherOne->id,
                'room_id' => $roomOne->id,
                'day_of_week' => ['Monday'],
                'start_date' => '2026-05-10',
                'end_date' => '2026-06-10',
                'start_time' => '18:00',
                'end_time' => '20:00',
            ]));

        $response->assertOk();
        $response->assertSee('Có xung đột');
        $response->assertSee('Xung đột giảng viên');
        $response->assertSee('Xung đột phòng học');
        $response->assertSee('Ô sửa nhanh');
        $response->assertSee('Sửa nhanh');
        $response->assertSee('Khóa chờ mở trùng giờ');
        $response->assertSee($roomConflictClass->displayName());
        $response->assertSee(route('admin.course.show', $pendingCourse), false);
        $response->assertSee(route('admin.course.show', $roomConflictClass->course), false);
    }

    public function test_admin_can_open_conflict_checker_from_existing_class_without_self_conflict(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $teacher = NguoiDung::factory()->teacher()->create();
        $room = $this->createRoom('PH010');
        [, $subject] = $this->createSubject('Tin hoc van phong', 'tin-hoc-van-phong');
        $classRoom = $this->createClassRoom($subject, $teacher, $room, 'Khóa hiện hành');

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.schedules.conflicts', [
                'class_room_id' => $classRoom->id,
            ]));

        $response->assertOk();
        $response->assertSee('Không phát hiện xung đột');
        $response->assertSee('Lớp: ' . $classRoom->displayName());
        $response->assertDontSee('Có xung đột');
        $response->assertDontSee('Báo cáo dọn dữ liệu bẩn');
    }

    public function test_admin_can_review_student_schedule_conflicts_for_cleanup(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $student = NguoiDung::factory()->student()->create(['name' => 'Hoc vien trung lich']);
        $teacherA = NguoiDung::factory()->teacher()->create(['name' => 'Giang vien A']);
        $teacherB = NguoiDung::factory()->teacher()->create(['name' => 'Giang vien B']);
        $roomA = $this->createRoom('PH100');
        $roomB = $this->createRoom('PH101');
        [, $subjectA] = $this->createSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep', 'Ngoai ngu');
        [, $subjectB] = $this->createSubject('Lap trinh Python co ban', 'lap-trinh-python', 'Tin hoc');

        $classRoomA = $this->createClassRoom($subjectA, $teacherA, $roomA, 'Lop trung lich A');
        $classRoomB = $this->createClassRoom($subjectB, $teacherB, $roomB, 'Lop trung lich B');

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subjectA->id,
            'course_id' => $classRoomA->course_id,
            'lop_hoc_id' => $classRoomA->id,
            'assigned_teacher_id' => $teacherA->id,
            'status' => GhiDanh::STATUS_ACTIVE,
            'schedule' => $classRoomA->scheduleSummary(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subjectB->id,
            'course_id' => $classRoomB->course_id,
            'lop_hoc_id' => $classRoomB->id,
            'assigned_teacher_id' => $teacherB->id,
            'status' => GhiDanh::STATUS_ACTIVE,
            'schedule' => $classRoomB->scheduleSummary(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.schedules.conflicts'));

        $response->assertOk();
        $response->assertSee('Xung đột học viên toàn hệ thống');
        $response->assertSee('Tự động rà soát');
        $response->assertSee('Danh sách học viên cần rà soát');
        $response->assertSee('Ô sửa nhanh');
        $response->assertSee('Sửa nhanh');
        $response->assertSee($student->name);
        $response->assertSee($classRoomA->displayName());
        $response->assertSee($classRoomB->displayName());
        $response->assertSee('Trùng vào');
        $response->assertSee(route('admin.course.show', $classRoomA->course), false);
        $response->assertSee(route('admin.course.show', $classRoomB->course), false);
    }

    public function test_admin_student_conflicts_are_grouped_by_class_pair(): void
    {
        $teacherA = NguoiDung::factory()->teacher()->create(['name' => 'Giang vien A']);
        $teacherB = NguoiDung::factory()->teacher()->create(['name' => 'Giang vien B']);
        $roomA = $this->createRoom('PH200');
        $roomB = $this->createRoom('PH201');
        [, $subjectA] = $this->createSubject('Tieng Anh giao tiep', 'tieng-anh-giao-tiep', 'Ngoai ngu');
        [, $subjectB] = $this->createSubject('Lap trinh Python co ban', 'lap-trinh-python', 'Tin hoc');

        $classRoomA = $this->createClassRoom($subjectA, $teacherA, $roomA, 'Lop trung lich A');
        $classRoomB = $this->createClassRoom($subjectB, $teacherB, $roomB, 'Lop trung lich B');

        $studentOne = NguoiDung::factory()->student()->create(['name' => 'Hoc vien 1']);
        $studentTwo = NguoiDung::factory()->student()->create(['name' => 'Hoc vien 2']);

        foreach ([$studentOne, $studentTwo] as $student) {
            GhiDanh::create([
                'user_id' => $student->id,
                'subject_id' => $subjectA->id,
                'course_id' => $classRoomA->course_id,
                'lop_hoc_id' => $classRoomA->id,
                'assigned_teacher_id' => $teacherA->id,
                'status' => GhiDanh::STATUS_ACTIVE,
                'schedule' => $classRoomA->scheduleSummary(),
                'is_submitted' => true,
                'submitted_at' => now(),
            ]);

            GhiDanh::create([
                'user_id' => $student->id,
                'subject_id' => $subjectB->id,
                'course_id' => $classRoomB->course_id,
                'lop_hoc_id' => $classRoomB->id,
                'assigned_teacher_id' => $teacherB->id,
                'status' => GhiDanh::STATUS_ACTIVE,
                'schedule' => $classRoomB->scheduleSummary(),
                'is_submitted' => true,
                'submitted_at' => now(),
            ]);
        }

        $conflicts = app(AdminScheduleConflictService::class)->studentConflicts();

        $this->assertCount(1, $conflicts);
        $this->assertSame(2, $conflicts->first()['student_count']);
        $this->assertSame(2, collect($conflicts->first()['students'] ?? [])->pluck('student_name')->unique()->count());
        $this->assertSame(1, app(AdminScheduleConflictService::class)->studentConflictPairCount());
    }

    private function createSubject(
        string $name = 'ANH VĂN KHUNG 6 BẬC',
        string $slug = 'anh-van-khung-6-bac',
        string $categoryName = 'Ngoại ngữ - Tin học'
    ): array
    {
        $category = NhomHoc::create([
            'name' => $categoryName,
            'slug' => $slug . '-group-' . fake()->unique()->numberBetween(100, 999),
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        $subject = MonHoc::create([
            'name' => $name,
            'category_id' => $category->id,
            'status' => MonHoc::STATUS_OPEN,
            'price' => 2500000,
            'duration' => 3,
        ]);

        return [$category, $subject];
    }

    private function createPendingOpenCourse(MonHoc $subject, NguoiDung $teacher): KhoaHoc
    {
        return KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => 'Khóa chờ mở trùng giờ',
            'description' => 'Khóa demo chờ mở để test xung đột giảng viên.',
            'teacher_id' => $teacher->id,
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday'],
            'start_time' => '18:00',
            'end_time' => '20:00',
            'status' => KhoaHoc::STATUS_PENDING_OPEN,
        ]);
    }

    private function createClassRoom(MonHoc $subject, NguoiDung $teacher, PhongHoc $room, string $title = 'Khóa đã mở trùng phòng'): LopHoc
    {
        $course = KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => $title,
            'description' => 'Khóa demo lớp hiện hành.',
            'teacher_id' => $teacher->id,
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday'],
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-01',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'status' => KhoaHoc::STATUS_ACTIVE,
        ]);

        $classRoom = LopHoc::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'name' => $course->title,
            'room_id' => $room->id,
            'teacher_id' => $teacher->id,
            'start_date' => '2026-05-01',
            'duration' => 3,
            'status' => LopHoc::STATUS_OPEN,
        ]);

        LichHoc::create([
            'lop_hoc_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);

        return $classRoom;
    }

    private function createRoom(string $code): PhongHoc
    {
        return PhongHoc::create([
            'code' => $code,
            'name' => 'Phòng ' . $code,
            'type' => 'offline',
            'location' => 'Tầng 2',
            'capacity' => 30,
            'status' => PhongHoc::STATUS_ACTIVE,
        ]);
    }
}

