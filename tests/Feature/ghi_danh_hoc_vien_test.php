<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ghi_danh_hoc_vien_test extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_custom_schedule_request_without_waiting_for_an_open_class(): void
    {
        $student = User::factory()->student()->create();
        [, $subject] = $this->createCatalogSubject();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->post(route('student.enroll.request-store', $subject), [
                'start_time' => '18:00',
                'end_time' => '20:15',
                'preferred_days' => ['Monday', 'Wednesday', 'Friday'],
                'preferred_schedule' => 'Ưu tiên ca tối trong tuần.',
            ]);

        $response->assertRedirect(route('student.enroll.my-classes'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('dang_ky', [
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'lop_hoc_id' => null,
            'status' => Enrollment::STATUS_PENDING,
            'start_time' => '18:00',
            'end_time' => '20:15',
            'preferred_schedule' => 'Ưu tiên ca tối trong tuần.',
        ]);

        $enrollment = Enrollment::where('user_id', $student->id)->where('subject_id', $subject->id)->firstOrFail();
        $this->assertSame(['Monday', 'Wednesday', 'Friday'], $enrollment->preferred_days);
    }

    public function test_student_can_directly_enroll_into_an_open_fixed_class(): void
    {
        $student = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $classRoom = $this->createOpenClassRoom($subject, $teacher);

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
            'status' => Enrollment::STATUS_ENROLLED,
        ]);
    }

    public function test_legacy_confirmed_enrollment_blocks_enrolling_into_a_different_fixed_class(): void
    {
        $student = User::factory()->student()->create();
        $teacherOne = User::factory()->teacher()->create();
        $teacherTwo = User::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject('Ke toan thuc hanh', 'Ke toan');
        $classRoomOne = $this->createOpenClassRoom($subject, $teacherOne);
        $classRoomTwo = $this->createOpenClassRoom($subject, $teacherTwo, 'Wednesday', '18:00', '20:00');

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $classRoomOne->course_id,
            'lop_hoc_id' => $classRoomOne->id,
            'assigned_teacher_id' => $teacherOne->id,
            'status' => Enrollment::LEGACY_STATUS_CONFIRMED,
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

        $this->assertSame(1, Enrollment::count());
        $this->assertDatabaseHas('dang_ky', [
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'lop_hoc_id' => $classRoomOne->id,
            'status' => Enrollment::LEGACY_STATUS_CONFIRMED,
        ]);
    }

    public function test_student_can_switch_from_custom_request_to_fixed_class_without_creating_duplicate_enrollments(): void
    {
        $student = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $classRoom = $this->createOpenClassRoom($subject, $teacher);

        $pendingEnrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => Enrollment::STATUS_PENDING,
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
            'status' => Enrollment::STATUS_ENROLLED,
            'start_time' => null,
            'end_time' => null,
            'preferred_schedule' => null,
        ]);

        $updatedEnrollment = $pendingEnrollment->fresh();
        $this->assertNull($updatedEnrollment->preferred_days);
    }

    public function test_student_fixed_class_enrollment_reuses_existing_record_for_same_class(): void
    {
        $student = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $classRoom = $this->createOpenClassRoom($subject, $teacher);

        $pendingEnrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'lop_hoc_id' => $classRoom->id,
            'status' => Enrollment::STATUS_PENDING,
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
            'status' => Enrollment::STATUS_ENROLLED,
            'start_time' => null,
            'end_time' => null,
            'preferred_schedule' => null,
        ]);

        $this->assertNull($pendingEnrollment->fresh()->preferred_days);
    }

    public function test_student_cannot_enroll_into_two_classes_with_overlapping_schedule(): void
    {
        $student = User::factory()->student()->create();
        $teacherOne = User::factory()->teacher()->create();
        $teacherTwo = User::factory()->teacher()->create();
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

    public function test_student_select_class_screen_shows_schedule_conflict_warning(): void
    {
        $student = User::factory()->student()->create();
        $teacherOne = User::factory()->teacher()->create();
        $teacherTwo = User::factory()->teacher()->create();
        [, $subjectOne] = $this->createCatalogSubject('Tieng Anh giao tiep');
        [, $subjectTwo] = $this->createCatalogSubject('Lap trinh Python co ban', 'Tin hoc');
        $classRoomOne = $this->createOpenClassRoom($subjectOne, $teacherOne, 'Wednesday', '18:00', '22:15');
        $classRoomTwo = $this->createOpenClassRoom($subjectTwo, $teacherTwo, 'Wednesday', '18:30', '20:45');

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subjectOne->id,
            'course_id' => $classRoomOne->course_id,
            'lop_hoc_id' => $classRoomOne->id,
            'assigned_teacher_id' => $teacherOne->id,
            'status' => Enrollment::STATUS_ACTIVE,
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
        $student = User::factory()->student()->create();
        $teacherOne = User::factory()->teacher()->create();
        $teacherTwo = User::factory()->teacher()->create();
        [, $subjectOne] = $this->createCatalogSubject('Tieng Anh giao tiep');
        [, $subjectTwo] = $this->createCatalogSubject('Lap trinh Python co ban', 'Tin hoc');
        $classRoomOne = $this->createOpenClassRoom($subjectOne, $teacherOne, 'Wednesday', '18:00', '20:15');
        $classRoomTwo = $this->createOpenClassRoom($subjectTwo, $teacherTwo, 'Wednesday', '20:15', '22:00');

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subjectOne->id,
            'course_id' => $classRoomOne->course_id,
            'lop_hoc_id' => $classRoomOne->id,
            'assigned_teacher_id' => $teacherOne->id,
            'status' => Enrollment::STATUS_ACTIVE,
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
        $student = User::factory()->student()->create();
        $admin = User::factory()->admin()->create();
        [, $subject] = $this->createCatalogSubject();

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => Enrollment::STATUS_APPROVED,
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
        $this->assertSame(Enrollment::STATUS_APPROVED, $updatedEnrollment->status);
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
        $student = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();
        [, $subject] = $this->createCatalogSubject();
        $classRoom = $this->createOpenClassRoom($subject, $teacher);

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $classRoom->course_id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => Enrollment::STATUS_ENROLLED,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $classRoom->course_id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => Enrollment::STATUS_ENROLLED,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);
    }

    private function createCatalogSubject(string $name = 'Tieng Anh giao tiep', string $categoryName = 'Ngoai ngu'): array
    {
        $category = Category::create([
            'name' => $categoryName,
            'slug' => 'ngoai-ngu-' . str()->slug($name) . '-' . fake()->unique()->numberBetween(100, 999),
            'status' => Category::STATUS_ACTIVE,
        ]);

        $subject = Subject::create([
            'name' => $name,
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'price' => 2500000,
        ]);

        return [$category, $subject];
    }

    private function createOpenClassRoom(
        Subject $subject,
        User $teacher,
        string $dayOfWeek = 'Monday',
        string $startTime = '18:00',
        string $endTime = '20:00'
    ): ClassRoom
    {
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => $subject->name . ' - Lop co dinh',
            'description' => 'Lop co dinh do admin mo.',
            'teacher_id' => $teacher->id,
            'day_of_week' => $dayOfWeek,
            'meeting_days' => [$dayOfWeek],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'schedule' => 'T2-T4-T6, ' . $startTime . ' - ' . $endTime,
        ]);

        $room = Room::create([
            'code' => 'A' . fake()->unique()->numberBetween(100, 999),
            'name' => 'Phong hoc ' . fake()->unique()->numberBetween(1, 99),
            'type' => 'offline',
            'location' => 'Tang 2',
            'capacity' => 20,
            'status' => Room::STATUS_ACTIVE,
        ]);

        $classRoom = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'name' => $subject->name . ' - Lop toi',
            'room_id' => $room->id,
            'teacher_id' => $teacher->id,
            'status' => ClassRoom::STATUS_OPEN,
            'duration' => 3,
        ]);

        ClassSchedule::create([
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

