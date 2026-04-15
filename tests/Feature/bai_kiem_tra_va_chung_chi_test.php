<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class bai_kiem_tra_va_chung_chi_test extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_quiz_and_receive_single_certificate(): void
    {
        $fixture = $this->createQuizFixture(questionCount: 2, maxAttempts: 3, passingScore: 100);

        $response = $this
            ->withSession(['user_id' => $fixture['student']->id])
            ->post(route('courses.quiz.submit', [$fixture['course']->id, $fixture['quiz']->id]), [
                'answers' => $fixture['correctAnswers'],
            ]);

        $response->assertRedirect(route('courses.show', $fixture['course']->id));
        $response->assertSessionHas('status');

        $this->assertSame(2, QuizAnswer::where('quiz_id', $fixture['quiz']->id)->count());
        $this->assertSame(
            [1],
            QuizAnswer::where('quiz_id', $fixture['quiz']->id)->pluck('attempt')->unique()->values()->all()
        );

        $certificate = Certificate::where('user_id', $fixture['student']->id)
            ->where('course_id', $fixture['course']->id)
            ->firstOrFail();

        $this->assertSame(100.0, (float) $certificate->score);

        $repeatResponse = $this
            ->withSession(['user_id' => $fixture['student']->id])
            ->post(route('courses.quiz.submit', [$fixture['course']->id, $fixture['quiz']->id]), [
                'answers' => $fixture['correctAnswers'],
            ]);

        $repeatResponse->assertRedirect(route('courses.show', $fixture['course']->id));
        $repeatResponse->assertSessionHas('status');

        $this->assertSame(4, QuizAnswer::where('quiz_id', $fixture['quiz']->id)->count());
        $this->assertSame(
            [1, 2],
            QuizAnswer::where('quiz_id', $fixture['quiz']->id)->pluck('attempt')->unique()->sort()->values()->all()
        );
        $this->assertSame(1, Certificate::count());
        $this->assertSame(100.0, (float) $certificate->fresh()->score);
    }

    public function test_student_cannot_exceed_quiz_max_attempts(): void
    {
        $fixture = $this->createQuizFixture(questionCount: 1, maxAttempts: 1, passingScore: 100);

        $firstResponse = $this
            ->from(route('courses.quiz.show', [$fixture['course']->id, $fixture['quiz']->id]))
            ->withSession(['user_id' => $fixture['student']->id])
            ->post(route('courses.quiz.submit', [$fixture['course']->id, $fixture['quiz']->id]), [
                'answers' => $fixture['wrongAnswers'],
            ]);

        $firstResponse->assertRedirect(route('courses.show', $fixture['course']->id));
        $firstResponse->assertSessionHas('status');
        $this->assertSame(1, QuizAnswer::where('quiz_id', $fixture['quiz']->id)->count());
        $this->assertSame(0, Certificate::count());

        $secondResponse = $this
            ->from(route('courses.quiz.show', [$fixture['course']->id, $fixture['quiz']->id]))
            ->withSession(['user_id' => $fixture['student']->id])
            ->post(route('courses.quiz.submit', [$fixture['course']->id, $fixture['quiz']->id]), [
                'answers' => $fixture['wrongAnswers'],
            ]);

        $secondResponse->assertRedirect(route('courses.quiz.show', [$fixture['course']->id, $fixture['quiz']->id]));
        $secondResponse->assertSessionHasErrors('answers');
        $this->assertSame(1, QuizAnswer::where('quiz_id', $fixture['quiz']->id)->count());
        $this->assertSame(0, Certificate::count());
    }

    public function test_student_can_view_only_their_own_certificate(): void
    {
        $fixture = $this->createQuizFixture(questionCount: 2, maxAttempts: 3, passingScore: 100);

        $this
            ->withSession(['user_id' => $fixture['student']->id])
            ->post(route('courses.quiz.submit', [$fixture['course']->id, $fixture['quiz']->id]), [
                'answers' => $fixture['correctAnswers'],
            ])
            ->assertRedirect(route('courses.show', $fixture['course']->id));

        $certificate = Certificate::where('user_id', $fixture['student']->id)
            ->where('course_id', $fixture['course']->id)
            ->firstOrFail();

        $ownerResponse = $this
            ->withSession(['user_id' => $fixture['student']->id])
            ->get(route('certificates.show', $certificate));

        $ownerResponse->assertOk();
        $ownerResponse->assertSee($certificate->certificate_number);
        $ownerResponse->assertSee($fixture['course']->title);

        $otherStudent = User::factory()->student()->create();

        $otherResponse = $this
            ->withSession(['user_id' => $otherStudent->id])
            ->get(route('certificates.show', $certificate));

        $otherResponse->assertRedirect(route('certificates.index'));
        $otherResponse->assertSessionHas('error');
    }

    private function createQuizFixture(int $questionCount = 2, int $maxAttempts = 3, int $passingScore = 100): array
    {
        $teacher = User::factory()->teacher()->create([
            'name' => 'Giang vien quiz',
        ]);
        $student = User::factory()->student()->create([
            'name' => 'Hoc vien quiz',
        ]);
        $category = Category::create([
            'name' => 'Nhom hoc quiz',
            'slug' => 'nhom-hoc-quiz-' . fake()->unique()->numberBetween(100, 999),
            'status' => Category::STATUS_ACTIVE,
        ]);
        $subject = Subject::create([
            'name' => 'Tin hoc van phong quiz',
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'price' => 1500000,
        ]);
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Tin hoc van phong - Lop quiz',
            'description' => 'Khoa hoc phuc vu test quiz va chung chi.',
            'teacher_id' => $teacher->id,
            'status' => Course::STATUS_ACTIVE,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_ENROLLED,
            'schedule' => 'T2-T4-T6, 18:00 - 20:00',
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Module 1',
            'content' => 'Noi dung module quiz',
            'position' => 1,
            'status' => Module::STATUS_PUBLISHED,
        ]);

        $lesson = Lesson::create([
            'module_id' => $module->id,
            'title' => 'Bai hoc quiz',
            'description' => 'Bai hoc co quiz',
            'content' => 'Noi dung bai hoc',
            'order' => 1,
            'duration' => 45,
        ]);

        $quiz = Quiz::create([
            'lesson_id' => $lesson->id,
            'title' => 'Quiz Kiem tra',
            'description' => 'Kiem tra hieu biet co ban',
            'passing_score' => $passingScore,
            'is_required' => true,
            'max_attempts' => $maxAttempts,
        ]);

        $correctAnswers = [];
        $wrongAnswers = [];

        $questionOne = Question::create([
            'quiz_id' => $quiz->id,
            'question' => 'Cau hoi 1',
            'type' => 'multiple_choice',
            'order' => 1,
            'points' => 5,
        ]);

        $questionOneWrong = Option::create([
            'question_id' => $questionOne->id,
            'option_text' => 'Sai',
            'is_correct' => false,
            'order' => 1,
        ]);

        $questionOneRight = Option::create([
            'question_id' => $questionOne->id,
            'option_text' => 'Dung',
            'is_correct' => true,
            'order' => 2,
        ]);

        $correctAnswers[$questionOne->id] = $questionOneRight->id;
        $wrongAnswers[$questionOne->id] = $questionOneWrong->id;

        if ($questionCount > 1) {
            $questionTwo = Question::create([
                'quiz_id' => $quiz->id,
                'question' => 'Cau hoi 2',
                'type' => 'true_false',
                'order' => 2,
                'points' => 5,
            ]);

            $questionTwoWrong = Option::create([
                'question_id' => $questionTwo->id,
                'option_text' => 'Sai',
                'is_correct' => false,
                'order' => 1,
            ]);

            $questionTwoRight = Option::create([
                'question_id' => $questionTwo->id,
                'option_text' => 'Dung',
                'is_correct' => true,
                'order' => 2,
            ]);

            $correctAnswers[$questionTwo->id] = $questionTwoRight->id;
            $wrongAnswers[$questionTwo->id] = $questionTwoWrong->id;
        }

        return [
            'teacher' => $teacher,
            'student' => $student,
            'category' => $category,
            'subject' => $subject,
            'course' => $course,
            'quiz' => $quiz,
            'correctAnswers' => $correctAnswers,
            'wrongAnswers' => $wrongAnswers,
        ];
    }
}

