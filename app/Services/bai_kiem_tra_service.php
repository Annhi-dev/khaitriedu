<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CourseQuizService
{
    public function submit(User $student, Course $course, Quiz $quiz, array $answers): array
    {
        return DB::transaction(function () use ($student, $course, $quiz, $answers): array {
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
