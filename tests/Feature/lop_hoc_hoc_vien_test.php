<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Category;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Question;
use App\Models\Room;
use App\Models\Subject;
use App\Models\TeacherEvaluation;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Lesson;
use App\Models\Module as CourseModule;
use App\Models\QuizAnswer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class lop_hoc_hoc_vien_test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array']);
    }

    public function test_student_class_index_shows_only_accessible_classes_and_summary(): void
    {
        $student = User::factory()->student()->create(['name' => 'Hoc vien A']);
        $teacher = User::factory()->teacher()->create(['name' => 'Giang vien A']);
        $completedTeacher = User::factory()->teacher()->create(['name' => 'Giang vien B']);

        $activeBundle = $this->createStudentClassBundle($student, $teacher, [
            'subject_name' => 'Tin hoc van phong',
            'class_name' => 'Tin hoc van phong - Lop A',
            'class_status' => ClassRoom::STATUS_OPEN,
            'enrollment_status' => Enrollment::STATUS_ACTIVE,
            'grade_score' => 88.5,
        ]);

        $completedBundle = $this->createStudentClassBundle($student, $completedTeacher, [
            'subject_name' => 'Tieng Anh giao tiep',
            'class_name' => 'Tieng Anh giao tiep - Lop B',
            'class_status' => ClassRoom::STATUS_COMPLETED,
            'enrollment_status' => Enrollment::STATUS_COMPLETED,
            'grade_score' => 92.0,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $completedBundle['subject']->id,
            'status' => Enrollment::STATUS_PENDING,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $response = $this
            ->actingAs($student)
            ->withSession(['user_id' => $student->id])
            ->get(route('student.classes.index'));
        $response->assertOk();
        $response->assertSee('Lớp học của tôi');
        $response->assertSee('Tin học văn phòng - Lớp A');
        $response->assertSee('Tiếng Anh giao tiếp - Lớp B');
        $response->assertSee('Tổng lớp');
        $response->assertSee('Lớp đang học');
        $response->assertSee('Lớp đã hoàn thành');
        $response->assertSee('Điểm trung bình');
        $response->assertDontSee('Chờ duyệt');
        $response->assertViewHas('summary', function (array $summary) use ($activeBundle, $completedBundle) {
            return $summary['totalClasses'] === 2
                && $summary['ongoingClasses'] === 1
                && $summary['completedClasses'] === 1
                && (float) $summary['averageGrade'] > 0;
        });
    }

    public function test_student_can_view_class_detail_and_sections(): void
    {
        $student = User::factory()->student()->create(['name' => 'Hoc vien detail']);
        $teacher = User::factory()->teacher()->create(['name' => 'Giang vien detail']);

        $bundle = $this->createStudentClassBundle($student, $teacher, [
            'subject_name' => 'Nhap mon lap trinh',
            'class_name' => 'Nhap mon lap trinh - Lop 1',
            'class_status' => ClassRoom::STATUS_OPEN,
            'enrollment_status' => Enrollment::STATUS_ACTIVE,
            'grade_score' => 76.25,
            'attendance_status' => AttendanceRecord::STATUS_LATE,
            'evaluation_rating' => 5,
            'evaluation_comments' => 'Rõ ràng, dễ hiểu.',
        ]);

        $response = $this
            ->actingAs($student)
            ->withSession(['user_id' => $student->id])
            ->get(route('student.classes.show', $bundle['enrollment']));

        $response->assertOk();
        $response->assertSee('Tổng quan');
        $response->assertSee('Lịch học');
        $response->assertSee('Điểm số');
        $response->assertSee('Bài kiểm tra');
        $response->assertSee('Danh sách lớp');
        $response->assertSee('Điểm danh');
        $response->assertSee('Đánh giá');
        $response->assertSee('Nhap mon lap trinh - Lop 1');
        $response->assertSee('76.25');
        $response->assertSee('Làm bài');
        $response->assertSee('Rõ ràng, dễ hiểu.');
    }

    public function test_student_cannot_access_other_student_enrollment_and_pending_enrollment_redirects(): void
    {
        $student = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();

        $bundle = $this->createStudentClassBundle($student, $teacher, [
            'subject_name' => 'Quan tri co so du lieu',
            'class_name' => 'Quan tri co so du lieu - Lop 1',
            'class_status' => ClassRoom::STATUS_OPEN,
            'enrollment_status' => Enrollment::STATUS_ACTIVE,
        ]);

        $otherResponse = $this
            ->actingAs($otherStudent)
            ->withSession(['user_id' => $otherStudent->id])
            ->get(route('student.classes.show', $bundle['enrollment']));

        $otherResponse->assertForbidden();

        $pendingEnrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $bundle['subject']->id,
            'status' => Enrollment::STATUS_PENDING,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $pendingResponse = $this
            ->actingAs($student)
            ->withSession(['user_id' => $student->id])
            ->get(route('student.classes.show', $pendingEnrollment));

        $pendingResponse->assertRedirect(route('student.classes.index'));
        $pendingResponse->assertSessionHas('error');
    }

    public function test_student_can_create_and_update_class_evaluation(): void
    {
        $student = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();

        $bundle = $this->createStudentClassBundle($student, $teacher, [
            'subject_name' => 'Thiet ke web',
            'class_name' => 'Thiet ke web - Lop 1',
            'class_status' => ClassRoom::STATUS_OPEN,
            'enrollment_status' => Enrollment::STATUS_ACTIVE,
            'evaluation_rating' => 3,
            'evaluation_comments' => 'Nhan xet cu.',
        ]);

        $response = $this
            ->actingAs($student)
            ->withSession(['user_id' => $student->id])
            ->post(route('student.classes.evaluation.store', $bundle['enrollment']), [
                'rating' => 5,
                'comments' => 'Da cap nhat lai.',
            ]);

        $response->assertRedirect(route('student.classes.evaluation', $bundle['enrollment']));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('teacher_evaluations', [
            'class_room_id' => $bundle['classRoom']->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'rating' => 5,
            'comments' => 'Da cap nhat lai.',
        ]);
    }

    public function test_legacy_my_classes_route_redirects_to_new_class_index(): void
    {
        $student = User::factory()->student()->create();

        $response = $this
            ->actingAs($student)
            ->withSession(['user_id' => $student->id])
            ->get(route('student.enroll.my-classes'));

        $response->assertRedirect(route('student.classes.index'));
    }

    protected function createStudentClassBundle(User $student, User $teacher, array $overrides = []): array
    {
        $slug = str()->slug(($overrides['subject_name'] ?? 'Mon hoc') . '-' . str()->random(6));

        $category = Category::create([
            'name' => 'Nhom hoc ' . $slug,
            'slug' => 'nhom-hoc-' . $slug,
            'status' => Category::STATUS_ACTIVE,
        ]);

        $subject = Subject::create([
            'name' => $overrides['subject_name'] ?? 'Mon hoc',
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'price' => 1500000,
            'duration' => 3,
        ]);

        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => $overrides['class_name'] ?? ($subject->name . ' - Lop hoc'),
            'description' => 'Khoa hoc phuc vu test hoc vien.',
            'teacher_id' => $teacher->id,
            'day_of_week' => 'Monday',
            'meeting_days' => ['Monday', 'Wednesday'],
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => now()->addMonths(1)->toDateString(),
            'start_time' => '18:00',
            'end_time' => '20:00',
            'capacity' => 20,
            'status' => Course::STATUS_ACTIVE,
        ]);

        $room = Room::create([
            'code' => 'P' . fake()->unique()->numberBetween(100, 999),
            'name' => 'Phong hoc ' . fake()->unique()->numberBetween(1, 99),
            'type' => 'offline',
            'location' => 'Tang 2',
            'capacity' => 20,
            'status' => Room::STATUS_ACTIVE,
        ]);

        $classRoom = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'name' => $overrides['class_name'] ?? ($subject->name . ' - Lop hoc'),
            'room_id' => $room->id,
            'teacher_id' => $teacher->id,
            'start_date' => now()->subDays(3)->toDateString(),
            'duration' => 3,
            'status' => $overrides['class_status'] ?? ClassRoom::STATUS_OPEN,
        ]);

        $schedule = ClassSchedule::create([
            'lop_hoc_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);

        $module = CourseModule::create([
            'course_id' => $course->id,
            'title' => 'Chuong 1',
            'content' => 'Noi dung chuong 1',
            'status' => CourseModule::STATUS_PUBLISHED,
            'position' => 1,
        ]);

        $lesson = Lesson::create([
            'module_id' => $module->id,
            'title' => 'Bai 1',
            'description' => 'Mo dau',
            'content' => 'Noi dung bai hoc',
            'order' => 1,
            'duration' => 45,
        ]);

        $quiz = Quiz::create([
            'lesson_id' => $lesson->id,
            'title' => 'Quiz 1',
            'description' => 'Kiem tra chuong 1',
            'passing_score' => 70,
            'is_required' => true,
            'max_attempts' => 3,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'question' => 'Cau hoi 1',
            'description' => 'Mo ta cau hoi',
            'type' => 'short_answer',
            'order' => 1,
            'points' => 10,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => $overrides['enrollment_status'] ?? Enrollment::STATUS_ACTIVE,
            'schedule' => $course->formattedSchedule(),
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        Grade::create([
            'enrollment_id' => $enrollment->id,
            'class_room_id' => $classRoom->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'test_name' => 'Kiểm tra 1',
            'score' => $overrides['grade_score'] ?? 80,
            'grade' => 'B',
            'feedback' => 'Ghi chu bai lam.',
        ]);

        AttendanceRecord::create([
            'course_id' => $course->id,
            'class_room_id' => $classRoom->id,
            'class_schedule_id' => $schedule->id,
            'enrollment_id' => $enrollment->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'attendance_date' => now()->toDateString(),
            'status' => $overrides['attendance_status'] ?? AttendanceRecord::STATUS_PRESENT,
            'note' => 'Diem danh test',
            'recorded_at' => now(),
        ]);

        TeacherEvaluation::create([
            'class_room_id' => $classRoom->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'rating' => $overrides['evaluation_rating'] ?? 4,
            'comments' => $overrides['evaluation_comments'] ?? 'Nhan xet de test.',
        ]);

        if (array_key_exists('quiz_attempt_score', $overrides) && $overrides['quiz_attempt_score'] !== null) {
            QuizAnswer::create([
                'user_id' => $student->id,
                'quiz_id' => $quiz->id,
                'question_id' => $question->id,
                'answer_text' => 'Da lam',
                'is_correct' => true,
                'attempt' => $overrides['quiz_attempt_score'],
            ]);
        }

        return compact('category', 'subject', 'course', 'room', 'classRoom', 'schedule', 'module', 'lesson', 'quiz', 'question', 'enrollment');
    }

}
