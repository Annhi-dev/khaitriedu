<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CourseQuizService
{
    public function getStudentQuizProgress(User $student, Quiz $quiz): array
    {
        $quiz->loadMissing('questions.options');

        $answers = QuizAnswer::query()
            ->where('user_id', $student->id)
            ->where('quiz_id', $quiz->id)
            ->with(['question', 'option'])
            ->orderBy('attempt')
            ->orderBy('id')
            ->get();

        $attempts = $answers
            ->groupBy('attempt')
            ->map(fn (Collection $attemptAnswers, int $attempt) => $this->summarizeAttempt((int) $attempt, $attemptAnswers, $quiz))
            ->sortByDesc('attempt')
            ->values();

        $attemptCount = $attempts->count();
        $maxAttempts = (int) ($quiz->max_attempts ?: 0);
        $remainingAttempts = $maxAttempts > 0 ? max(0, $maxAttempts - $attemptCount) : null;
        $latestAttempt = $attempts->first();
        $bestScore = $attempts->isNotEmpty() ? (float) $attempts->max('score') : null;

        return [
            'attempts' => $attempts,
            'attemptCount' => $attemptCount,
            'latestAttempt' => $latestAttempt,
            'bestScore' => $bestScore,
            'maxAttempts' => $maxAttempts,
            'remainingAttempts' => $remainingAttempts,
            'canAttempt' => $maxAttempts === 0 || $remainingAttempts > 0,
        ];
    }

    public function getTeacherQuizReport(Quiz $quiz): array
    {
        $quiz->loadMissing('questions.options');

        $answers = QuizAnswer::query()
            ->where('quiz_id', $quiz->id)
            ->with(['student', 'question', 'option'])
            ->orderBy('user_id')
            ->orderBy('attempt')
            ->orderBy('id')
            ->get();

        $studentAttempts = $answers
            ->groupBy('user_id')
            ->map(function (Collection $studentAnswers) use ($quiz): array {
                $attempts = $studentAnswers
                    ->groupBy('attempt')
                    ->map(fn (Collection $attemptAnswers, int $attempt) => $this->summarizeAttempt((int) $attempt, $attemptAnswers, $quiz))
                    ->sortByDesc('attempt')
                    ->values();

                $latestAttempt = $attempts->first();
                $student = $studentAnswers->first()?->student;

                return [
                    'student' => $student,
                    'attemptCount' => $attempts->count(),
                    'latestAttempt' => $latestAttempt,
                    'bestScore' => $attempts->isNotEmpty() ? (float) $attempts->max('score') : null,
                    'passed' => $latestAttempt ? (bool) $latestAttempt['passed'] : false,
                ];
            })
            ->sortByDesc(fn (array $row) => $row['latestAttempt']['submitted_at_raw'] ?? 0)
            ->values();

        $totalAttempts = $answers
            ->map(fn (QuizAnswer $answer) => $answer->user_id . '|' . $answer->attempt)
            ->unique()
            ->count();
        $studentCount = $studentAttempts->count();
        $averageScore = $studentAttempts->isNotEmpty()
            ? round((float) $studentAttempts->avg(fn (array $row) => $row['latestAttempt']['score'] ?? 0), 2)
            : null;
        $passRate = $studentAttempts->isNotEmpty()
            ? round(($studentAttempts->filter(fn (array $row) => $row['passed'])->count() / $studentAttempts->count()) * 100, 1)
            : null;

        return [
            'totalAttempts' => $totalAttempts,
            'studentCount' => $studentCount,
            'averageScore' => $averageScore,
            'passRate' => $passRate,
            'recentAttempts' => $studentAttempts->take(10)->values(),
        ];
    }

    public function submit(User $student, Course $course, Quiz $quiz, array $answers): array
    {
        return DB::transaction(function () use ($student, $course, $quiz, $answers): array {
            $hasCourseAccess = Enrollment::query()
                ->where('user_id', $student->id)
                ->where('course_id', $course->id)
                ->whereIn('status', Enrollment::courseAccessStatuses())
                ->exists();

            if (! $hasCourseAccess) {
                throw ValidationException::withMessages([
                    'course_id' => 'Bạn chưa được xếp vào lớp học này.',
                ]);
            }

            $quiz->loadMissing('questions.options');

            $attempt = $this->nextAttemptNumber($student, $quiz);
            $maxAttempts = (int) ($quiz->max_attempts ?: 0);

            if ($maxAttempts > 0 && $attempt > $maxAttempts) {
                throw ValidationException::withMessages([
                    'answers' => 'Bạn đã đạt số lần làm quiz tối đa cho bài kiểm tra này.',
                ]);
            }

            $totalPoints = 0;
            $earnedPoints = 0;

            foreach ($quiz->questions as $question) {
                $totalPoints += (int) $question->points;
                $selected = $answers[$question->id] ?? null;
                [$optionId, $answerText, $isCorrect] = $this->resolveAnswer($question->type, $question->options, $selected);

                if ($isCorrect) {
                    $earnedPoints += (int) $question->points;
                }

                QuizAnswer::create([
                    'user_id' => $student->id,
                    'quiz_id' => $quiz->id,
                    'question_id' => $question->id,
                    'option_id' => $optionId,
                    'answer_text' => $answerText,
                    'is_correct' => $isCorrect,
                    'attempt' => $attempt,
                ]);
            }

            $score = $totalPoints > 0
                ? round(($earnedPoints / $totalPoints) * 100, 2)
                : 0;
            $passed = $score >= (int) ($quiz->passing_score ?: 70);
            $certificate = null;

            if ($passed) {
                $certificate = $this->issueCertificate($student, $course, $score);
            }

            return [
                'attempt' => $attempt,
                'score' => $score,
                'passed' => $passed,
                'certificate' => $certificate,
            ];
        });
    }

    protected function resolveAnswer(string $questionType, $options, mixed $selected): array
    {
        if ($questionType === 'short_answer') {
            $answerText = is_scalar($selected) ? trim((string) $selected) : null;

            return [null, $answerText !== '' ? $answerText : null, false];
        }

        $selectedOptionId = is_scalar($selected) && $selected !== ''
            ? (int) $selected
            : null;
        $option = $selectedOptionId !== null
            ? $options->firstWhere('id', $selectedOptionId)
            : null;

        if (! $option) {
            $answerText = is_scalar($selected) ? trim((string) $selected) : null;

            return [null, $answerText !== '' ? $answerText : null, false];
        }

        return [
            (int) $option->id,
            $option->option_text,
            (bool) $option->is_correct,
        ];
    }

    protected function nextAttemptNumber(User $student, Quiz $quiz): int
    {
        $lastAttempt = QuizAnswer::query()
            ->where('user_id', $student->id)
            ->where('quiz_id', $quiz->id)
            ->max('attempt');

        return ((int) $lastAttempt) + 1;
    }

    protected function summarizeAttempt(int $attempt, Collection $attemptAnswers, Quiz $quiz): array
    {
        $attemptAnswers = $attemptAnswers->sortBy('question_id')->values();
        $answerMap = $attemptAnswers->keyBy('question_id');
        $totalPoints = 0;
        $earnedPoints = 0;
        $correctCount = 0;
        $answeredCount = 0;
        $questions = collect();

        foreach ($quiz->questions as $question) {
            $totalPoints += (int) $question->points;
            $answer = $answerMap->get($question->id);

            if ($answer) {
                $answeredCount++;
                if ($answer->is_correct) {
                    $earnedPoints += (int) $question->points;
                    $correctCount++;
                }
            }

            $questions->push([
                'id' => $question->id,
                'question' => $question->question,
                'selected' => $answer?->option?->option_text ?? $answer?->answer_text,
                'correct' => $answer?->is_correct ?? false,
                'correct_answer' => $question->options->firstWhere('is_correct', true)?->option_text,
            ]);
        }

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

        return [
            'attempt' => $attempt,
            'score' => $score,
            'passed' => $score >= (int) ($quiz->passing_score ?: 70),
            'answeredCount' => $answeredCount,
            'correctCount' => $correctCount,
            'totalQuestions' => $quiz->questions->count(),
            'totalPoints' => $totalPoints,
            'earnedPoints' => $earnedPoints,
            'submitted_at_raw' => optional($attemptAnswers->last()?->created_at)?->timestamp ?? 0,
            'submitted_at' => optional($attemptAnswers->last()?->created_at)->format('d/m/Y H:i'),
            'questions' => $questions->all(),
        ];
    }

    protected function issueCertificate(User $student, Course $course, float $score): Certificate
    {
        $certificate = Certificate::firstOrNew([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        if (! $certificate->exists || ! $certificate->certificate_number) {
            $certificate->certificate_number = $this->generateCertificateNumber();
        }

        $certificate->score = max((float) ($certificate->score ?? 0), round($score, 2));
        $certificate->issued_at = now();
        $certificate->status = 'issued';
        $certificate->save();

        return $certificate;
    }

    protected function generateCertificateNumber(): string
    {
        do {
            $number = 'KT-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
        } while (Certificate::where('certificate_number', $number)->exists());

        return $number;
    }
}
