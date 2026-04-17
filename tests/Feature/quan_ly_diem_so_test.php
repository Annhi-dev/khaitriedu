<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class quan_ly_diem_so_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_browse_and_filter_student_grades(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $studentA = User::factory()->student()->create(['name' => 'Hoc vien A']);
        $studentB = User::factory()->student()->create(['name' => 'Hoc vien B']);

        ['subject' => $subjectA, 'course' => $courseA, 'classRoom' => $classRoomA, 'enrollment' => $enrollmentA] = $this->createGradeFixture(
            $teacher,
            $studentA,
            'Tin hoc van phong A',
            'Tin hoc van phong - Lop A',
            'Lop A',
            87.5,
            'B',
        );

        $this->createGradeFixture(
            $teacher,
            $studentB,
            'Tieng Anh giao tiep B',
            'Tieng Anh giao tiep - Lop B',
            'Lop B',
            65.0,
            'C',
        );

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.grades.index', [
                'student_id' => $studentA->id,
                'subject_id' => $subjectA->id,
                'course_id' => $courseA->id,
                'class_room_id' => $classRoomA->id,
            ]));

        $response->assertOk();
        $response->assertSee('Điểm số toàn hệ thống');
        $response->assertSee($studentA->name);
        $response->assertSee($classRoomA->displayName());
        $response->assertSee($subjectA->name);
        $response->assertViewHas('summary', function (array $summary) {
            return $summary['totalGrades'] === 1
                && $summary['uniqueStudents'] === 1
                && $summary['uniqueClasses'] === 1
                && (float) $summary['averageScore'] === 87.5;
        });
    }

    public function test_non_admin_cannot_access_admin_grade_page(): void
    {
        $student = User::factory()->student()->create();

        $response = $this
            ->actingAs($student)
            ->get(route('admin.grades.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    private function createGradeFixture(
        User $teacher,
        User $student,
        string $subjectName,
        string $courseTitle,
        string $className,
        float $score,
        string $gradeLabel
    ): array {
        $slug = str()->slug($subjectName . ' ' . $courseTitle . ' ' . $className) . '-' . str()->random(4);

        $category = Category::create([
            'name' => 'Nhom hoc ' . $slug,
            'slug' => 'nhom-hoc-' . $slug,
            'status' => Category::STATUS_ACTIVE,
        ]);

        $subject = Subject::create([
            'name' => $subjectName,
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'price' => 1500000,
            'duration' => 3,
        ]);

        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => $courseTitle,
            'teacher_id' => $teacher->id,
            'status' => Course::STATUS_SCHEDULED,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);

        $classRoom = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'name' => $className,
            'teacher_id' => $teacher->id,
            'start_date' => now()->toDateString(),
            'duration' => 3,
            'status' => ClassRoom::STATUS_OPEN,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'schedule' => $course->formattedSchedule(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        Grade::create([
            'enrollment_id' => $enrollment->id,
            'class_room_id' => $classRoom->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'test_name' => 'Bai kiem tra 1',
            'score' => $score,
            'weight' => 1,
            'grade' => $gradeLabel,
            'feedback' => 'Nhan xet cho ' . $student->name,
        ]);

        return compact('category', 'subject', 'course', 'classRoom', 'enrollment');
    }
}
