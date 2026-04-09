<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentEnrollmentPortalTest extends TestCase
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
                'end_time' => '20:00',
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
            'end_time' => '20:00',
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
                'end_time' => '20:30',
                'preferred_days' => ['Tuesday', 'Thursday'],
                'preferred_schedule' => 'Cap nhat sang lich toi.',
            ]);

        $response->assertRedirect(route('student.enroll.my-classes'));
        $response->assertSessionHas('status');

        $updatedEnrollment = $enrollment->fresh();
        $this->assertSame(Enrollment::STATUS_APPROVED, $updatedEnrollment->status);
        $this->assertSame('18:30', $updatedEnrollment->start_time);
        $this->assertSame('20:30', $updatedEnrollment->end_time);
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

    private function createCatalogSubject(string $name = 'Tieng Anh giao tiep'): array
    {
        $category = Category::create([
            'name' => 'Ngoai ngu',
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

    private function createOpenClassRoom(Subject $subject, User $teacher): ClassRoom
    {
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => $subject->name . ' - Lop co dinh',
            'description' => 'Lop co dinh do admin mo.',
            'teacher_id' => $teacher->id,
            'schedule' => 'T2-T4-T6, 18:00 - 20:00',
        ]);

        $room = Room::create([
            'code' => 'A' . fake()->unique()->numberBetween(100, 999),
            'name' => 'Phong hoc ' . fake()->unique()->numberBetween(1, 99),
            'type' => 'offline',
            'location' => 'Tang 2',
            'capacity' => 20,
            'status' => Room::STATUS_ACTIVE,
        ]);

        return ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'name' => $subject->name . ' - Lop toi',
            'room_id' => $room->id,
            'teacher_id' => $teacher->id,
            'status' => ClassRoom::STATUS_OPEN,
            'duration' => 3,
        ]);
    }
}
