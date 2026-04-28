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
use App\Services\CourseQuizService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class bai_kiem_tra_va_chung_chi_test extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_quiz_and_receive_single_certificate(): void
    {
        $fixture = $this->createQuizFixture(questionCount: 2, maxAttempts: 3, passingScore: 100);
        $quizUrl = route('courses.quiz.show', ['course' => $fixture['course']->id, 'quiz' => $fixture['quiz']->id]);
        $submitUrl = route('courses.quiz.submit', ['course' => $fixture['course']->id, 'quiz' => $fixture['quiz']->id]);

        $response = $this
            ->withSession(['user_id' => $fixture['student']->id])
            ->post($submitUrl, [
                '_token' => csrf_token(),
                'answers' => $fixture['correctAnswers'],
            ]);

        $response->assertRedirect($quizUrl);
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
            ->post($submitUrl, [
                '_token' => csrf_token(),
                'answers' => $fixture['correctAnswers'],
            ]);

        $repeatResponse->assertRedirect($quizUrl);
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
        $quizUrl = route('courses.quiz.show', ['course' => $fixture['course']->id, 'quiz' => $fixture['quiz']->id]);
        $submitUrl = route('courses.quiz.submit', ['course' => $fixture['course']->id, 'quiz' => $fixture['quiz']->id]);

        $firstResponse = $this
            ->from($quizUrl)
            ->withSession(['user_id' => $fixture['student']->id])
            ->post($submitUrl, [
                '_token' => csrf_token(),
                'answers' => $fixture['wrongAnswers'],
            ]);

        $firstResponse->assertRedirect($quizUrl);
        $firstResponse->assertSessionHas('status');
        $this->assertSame(1, QuizAnswer::where('quiz_id', $fixture['quiz']->id)->count());
        $this->assertSame(0, Certificate::count());

        $secondResponse = $this
            ->from($quizUrl)
            ->withSession(['user_id' => $fixture['student']->id])
            ->post($submitUrl, [
                '_token' => csrf_token(),
                'answers' => $fixture['wrongAnswers'],
            ]);

        $secondResponse->assertRedirect($quizUrl);
        $secondResponse->assertSessionHasErrors('answers');
        $this->assertSame(1, QuizAnswer::where('quiz_id', $fixture['quiz']->id)->count());
        $this->assertSame(0, Certificate::count());
    }

    public function test_quiz_service_requires_course_access_before_grading(): void
    {
        $fixture = $this->createQuizFixture(questionCount: 1, maxAttempts: 3, passingScore: 100);
        $otherStudent = User::factory()->student()->create();

        try {
            app(CourseQuizService::class)->submit($otherStudent, $fixture['course'], $fixture['quiz'], $fixture['correctAnswers']);
            $this->fail('Quiz service should reject students without course access.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('course_id', $exception->errors());
            $this->assertSame('Bạn chưa được xếp vào lớp học này.', $exception->errors()['course_id'][0]);
        }

        $this->assertSame(0, QuizAnswer::count());
        $this->assertSame(0, Certificate::count());
    }

    public function test_teacher_cannot_submit_quiz_even_when_assigned_to_course(): void
    {
        $fixture = $this->createQuizFixture(questionCount: 1, maxAttempts: 3, passingScore: 100);
        $quizUrl = route('courses.quiz.show', ['course' => $fixture['course']->id, 'quiz' => $fixture['quiz']->id]);
        $submitUrl = route('courses.quiz.submit', ['course' => $fixture['course']->id, 'quiz' => $fixture['quiz']->id]);

        $response = $this
            ->withSession(['user_id' => $fixture['teacher']->id])
            ->post($submitUrl, [
                '_token' => csrf_token(),
                'answers' => $fixture['correctAnswers'],
            ]);

        $response->assertRedirect(route('courses.show', $fixture['course']->id));
        $response->assertSessionHas('error', 'Chi hoc vien da duoc xep lop moi co the nop quiz.');

        $this->assertSame(0, QuizAnswer::count());
        $this->assertSame(0, Certificate::count());
    }

    public function test_quiz_page_is_scoped_to_the_selected_course(): void
    {
        $firstFixture = $this->createQuizFixture(questionCount: 1, maxAttempts: 3, passingScore: 100, suffix: '-a');
        $secondFixture = $this->createQuizFixture(questionCount: 1, maxAttempts: 3, passingScore: 100, suffix: '-b');
        $quizUrl = route('courses.quiz.show', ['course' => $firstFixture['course']->id, 'quiz' => $secondFixture['quiz']->id]);

        $response = $this
            ->withSession(['user_id' => $firstFixture['student']->id])
            ->get($quizUrl);

        $response->assertRedirect(route('courses.show', $firstFixture['course']->id));
        $response->assertSessionHas('error', 'Quiz khong ton tai.');
    }

    public function test_guest_is_redirected_from_certificate_index(): void
    {
        $response = $this->get(route('certificates.index'));

        $response->assertRedirect('/login');
    }

    public function test_student_can_view_only_their_own_certificate(): void
    {
        $fixture = $this->createQuizFixture(questionCount: 2, maxAttempts: 3, passingScore: 100);
        $quizUrl = route('courses.quiz.show', ['course' => $fixture['course']->id, 'quiz' => $fixture['quiz']->id]);
        $submitUrl = route('courses.quiz.submit', ['course' => $fixture['course']->id, 'quiz' => $fixture['quiz']->id]);

        $this
            ->withSession(['user_id' => $fixture['student']->id])
            ->post($submitUrl, [
                '_token' => csrf_token(),
                'answers' => $fixture['correctAnswers'],
            ])
            ->assertRedirect($quizUrl);

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

        $otherResponse->assertRedirect('/certificates');
        $otherResponse->assertSessionHas('error');
    }

    public function test_guest_is_redirected_from_certificate_detail(): void
    {
        $fixture = $this->createQuizFixture(questionCount: 1, maxAttempts: 3, passingScore: 100, suffix: '-guest');

        $certificate = Certificate::create([
            'user_id' => $fixture['student']->id,
            'course_id' => $fixture['course']->id,
            'certificate_number' => 'CERT-' . strtoupper((string) fake()->unique()->bothify('??????')),
            'file_path' => null,
            'score' => 100,
            'issued_at' => now(),
            'status' => 'issued',
        ]);

        $detailResponse = $this->get(route('certificates.show', $certificate));

        $detailResponse->assertRedirect('/certificates');
        $detailResponse->assertSessionHas('error', 'Chứng chỉ không tồn tại.');
    }

    private function createQuizFixture(int $questionCount = 2, int $maxAttempts = 3, int $passingScore = 100, string $suffix = ''): array
    {
        $suffix = $suffix !== '' ? ' ' . $suffix : '';
        $slugSuffix = $suffix !== '' ? strtolower(str_replace(' ', '-', trim($suffix))) : '-' . fake()->unique()->numberBetween(100, 999);

        $teacher = User::factory()->teacher()->create([
            'name' => 'Giang vien quiz' . $suffix,
        ]);
        $student = User::factory()->student()->create([
            'name' => 'Hoc vien quiz' . $suffix,
        ]);
        $category = Category::create([
            'name' => 'Nhom hoc quiz' . $suffix,
            'slug' => 'nhom-hoc-quiz' . $slugSuffix,
            'status' => Category::STATUS_ACTIVE,
        ]);
        $subject = Subject::create([
            'name' => 'Tin hoc van phong quiz' . $suffix,
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'price' => 1500000,
        ]);
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Tin hoc van phong - Lop quiz' . $suffix,
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
            'title' => 'Module 1' . $suffix,
            'content' => 'Noi dung module quiz',
            'position' => 1,
            'status' => Module::STATUS_PUBLISHED,
        ]);

        $lesson = Lesson::create([
            'module_id' => $module->id,
            'title' => 'Bai hoc quiz' . $suffix,
            'description' => 'Bai hoc co quiz',
            'content' => 'Noi dung bai hoc',
            'order' => 1,
            'duration' => 45,
        ]);

        $quiz = Quiz::create([
            'lesson_id' => $lesson->id,
            'title' => 'Quiz Kiem tra' . $suffix,
            'description' => 'Kiem tra hieu biet co ban',
            'passing_score' => $passingScore,
            'is_required' => true,
            'max_attempts' => $maxAttempts,
            'status' => Quiz::STATUS_PUBLISHED,
        ]);

        $correctAnswers = [];
        $wrongAnswers = [];

        $questionOne = Question::create([
            'quiz_id' => $quiz->id,
            'question' => 'Cau hoi 1' . $suffix,
            'type' => 'multiple_choice',
            'order' => 1,
            'points' => 5,
        ]);

        $questionOneWrong = Option::create([
            'question_id' => $questionOne->id,
            'option_text' => 'Sai' . $suffix,
            'is_correct' => false,
            'order' => 1,
        ]);

        $questionOneRight = Option::create([
            'question_id' => $questionOne->id,
            'option_text' => 'Dung' . $suffix,
            'is_correct' => true,
            'order' => 2,
        ]);

        $correctAnswers[$questionOne->id] = $questionOneRight->id;
        $wrongAnswers[$questionOne->id] = $questionOneWrong->id;

        if ($questionCount > 1) {
            $questionTwo = Question::create([
                'quiz_id' => $quiz->id,
                'question' => 'Cau hoi 2' . $suffix,
                'type' => 'true_false',
                'order' => 2,
                'points' => 5,
            ]);

            $questionTwoWrong = Option::create([
                'question_id' => $questionTwo->id,
                'option_text' => 'Sai' . $suffix,
                'is_correct' => false,
                'order' => 1,
            ]);

            $questionTwoRight = Option::create([
                'question_id' => $questionTwo->id,
                'option_text' => 'Dung' . $suffix,
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

