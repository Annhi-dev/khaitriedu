<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\Subject;
use App\Models\User;
use App\Services\StudentClassService;
use App\Services\TeacherTestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class teacher_bai_kiem_tra_test extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_quiz_for_assigned_class(): void
    {
        [$teacher, $classRoom, $course] = $this->makeTeachingContext();
        $service = app(TeacherTestService::class);
        $quiz = $service->saveQuiz($teacher, null, $this->quizPayload($classRoom));

        $this->assertDatabaseHas('bai_kiem_tra', [
            'id' => $quiz->id,
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $course->subject_id,
            'lop_hoc_id' => $classRoom->id,
            'title' => 'Kiểm tra chương 1',
            'status' => Quiz::STATUS_PUBLISHED,
        ]);
        $this->assertDatabaseHas('cau_hoi', [
            'quiz_id' => $quiz->id,
            'question' => '2 + 2 = ?',
        ]);
        $this->assertDatabaseCount('lua_chon', 4);
    }

    public function test_teacher_can_update_created_quiz(): void
    {
        [$teacher, $classRoom] = $this->makeTeachingContext();
        $service = app(TeacherTestService::class);
        $quiz = $service->saveQuiz($teacher, null, $this->quizPayload($classRoom));
        $quiz->load('questions.options');
        $question = $quiz->questions->firstOrFail();
        $payload = $this->quizPayload($classRoom, [
            'title' => 'Kiểm tra chương 1 - cập nhật',
            'status' => Quiz::STATUS_DRAFT,
            'questions' => [
                [
                    'id' => $question->id,
                    'question' => '3 + 3 = ?',
                    'description' => 'Câu hỏi cập nhật',
                    'points' => 8,
                    'correct_option' => 'C',
                    'option_ids' => [
                        'A' => $question->options[0]->id,
                        'B' => $question->options[1]->id,
                        'C' => $question->options[2]->id,
                        'D' => $question->options[3]->id,
                    ],
                    'options' => [
                        'A' => '4',
                        'B' => '5',
                        'C' => '6',
                        'D' => '7',
                    ],
                ],
            ],
        ]);

        $updatedQuiz = $service->saveQuiz($teacher, $quiz, $payload);
        $this->assertDatabaseHas('bai_kiem_tra', [
            'id' => $updatedQuiz->id,
            'title' => 'Kiểm tra chương 1 - cập nhật',
            'status' => Quiz::STATUS_DRAFT,
        ]);
        $this->assertDatabaseHas('cau_hoi', [
            'id' => $question->id,
            'question' => '3 + 3 = ?',
        ]);
        $this->assertDatabaseHas('lua_chon', [
            'question_id' => $question->id,
            'option_text' => '6',
            'is_correct' => 1,
        ]);
    }

    public function test_teacher_cannot_delete_quiz_with_student_answers(): void
    {
        [$teacher, $classRoom] = $this->makeTeachingContext();
        $student = User::factory()->student()->create();
        $service = app(TeacherTestService::class);
        $quiz = $service->saveQuiz($teacher, null, $this->quizPayload($classRoom));
        $quiz->load('questions.options');
        $question = $quiz->questions->firstOrFail();
        $option = $question->options->firstWhere('is_correct', true) ?? $question->options->first();

        QuizAnswer::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'question_id' => $question->id,
            'option_id' => $option?->id,
            'is_correct' => true,
            'attempt' => 1,
        ]);

        try {
            $service->deleteQuiz($teacher, $quiz);
            $this->fail('Expected quiz deletion to be blocked.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('quiz', $e->errors());
        }

        $this->assertDatabaseHas('bai_kiem_tra', ['id' => $quiz->id]);
    }

    public function test_student_class_service_shows_published_teacher_quiz(): void
    {
        [$teacher, $classRoom, $course] = $this->makeTeachingContext();
        $student = User::factory()->student()->create();
        $service = app(TeacherTestService::class);
        $quiz = $service->saveQuiz($teacher, null, $this->quizPayload($classRoom));
        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $course->subject_id,
            'course_id' => $course->id,
            'lop_hoc_id' => $classRoom->id,
            'assigned_teacher_id' => $teacher->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'is_submitted' => true,
        ]);

        $service = app(StudentClassService::class);
        $quizzes = $service->getAvailableQuizzes($enrollment);

        $this->assertTrue($quizzes->contains(fn (Quiz $item) => (int) $item->id === (int) $quiz->id));
    }

    protected function makeTeachingContext(): array
    {
        $teacher = User::factory()->teacher()->create();
        $subject = Subject::create([
            'name' => 'Tiếng Anh giao tiếp',
            'status' => Subject::STATUS_OPEN,
            'duration' => 12,
        ]);
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Tiếng Anh A1',
            'teacher_id' => $teacher->id,
            'status' => Course::STATUS_ACTIVE,
        ]);
        $classRoom = ClassRoom::create([
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'name' => 'Lớp A1 - Sáng',
            'teacher_id' => $teacher->id,
            'status' => ClassRoom::STATUS_OPEN,
        ]);
        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Chương 1',
            'content' => 'Giới thiệu',
            'status' => Module::STATUS_PUBLISHED,
            'position' => 1,
        ]);
        Lesson::create([
            'module_id' => $module->id,
            'title' => 'Bài 1',
            'description' => 'Khởi động',
            'content' => 'Nội dung khởi động',
            'order' => 1,
        ]);

        return [$teacher, $classRoom, $course];
    }

    protected function quizPayload(ClassRoom $classRoom, array $overrides = []): array
    {
        return array_replace_recursive([
            'title' => 'Kiểm tra chương 1',
            'description' => 'Bài kiểm tra trắc nghiệm đơn giản',
            'lop_hoc_id' => $classRoom->id,
            'course_id' => '',
            'subject_id' => '',
            'duration_minutes' => 30,
            'total_score' => 10,
            'status' => Quiz::STATUS_PUBLISHED,
            'questions' => [
                [
                    'question' => '2 + 2 = ?',
                    'description' => '',
                    'points' => 10,
                    'correct_option' => 'B',
                    'options' => [
                        'A' => '3',
                        'B' => '4',
                        'C' => '5',
                        'D' => '6',
                    ],
                ],
            ],
        ], $overrides);
    }
}
