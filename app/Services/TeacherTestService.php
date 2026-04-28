<?php

namespace App\Services;

use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class TeacherTestService
{
    public function getDashboardSummary(User $teacher, array $filters = []): array
    {
        $tests = $this->getTeacherTests($teacher, $filters);

        return [
            'tests' => $tests,
            'summary' => [
                'total' => $tests->count(),
                'published' => $tests->where('status', Quiz::STATUS_PUBLISHED)->count(),
                'draft' => $tests->where('status', Quiz::STATUS_DRAFT)->count(),
                'questions' => (int) $tests->sum('questions_count'),
            ],
        ];
    }

    public function getTeacherTests(User $teacher, array $filters = []): Collection
    {
        $status = trim((string) ($filters['status'] ?? 'all'));
        $search = trim((string) ($filters['search'] ?? ''));

        return Quiz::query()
            ->ownedByTeacher($teacher)
            ->with([
                'teacher',
                'course.subject',
                'subject',
                'classRoom.course.subject',
                'lesson.module.course.subject',
            ])
            ->withCount(['questions', 'answers'])
            ->when(
                $status === Quiz::STATUS_DRAFT && Schema::hasColumn((new Quiz())->getTable(), 'status'),
                fn (Builder $query) => $query->where('status', Quiz::STATUS_DRAFT)
            )
            ->when(
                $status === Quiz::STATUS_PUBLISHED,
                fn (Builder $query) => $query->published()
            )
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $quizTable = (new Quiz())->getTable();

                    $builder->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');

                    if (Schema::hasColumn($quizTable, 'course_id')) {
                        $builder->orWhereHas('course', fn (Builder $courseQuery) => $courseQuery->where('title', 'like', '%' . $search . '%'));
                    }

                    if (Schema::hasColumn($quizTable, 'subject_id')) {
                        $builder->orWhereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', '%' . $search . '%'));
                    }

                    if (Schema::hasColumn($quizTable, 'lop_hoc_id')) {
                        $builder->orWhereHas('classRoom', fn (Builder $classRoomQuery) => $classRoomQuery->where('name', 'like', '%' . $search . '%'));
                    }

                    if (Schema::hasColumn($quizTable, 'lesson_id')) {
                        $builder->orWhereHas('lesson', fn (Builder $lessonQuery) => $lessonQuery->where('title', 'like', '%' . $search . '%'));
                    }
                });
            })
            ->orderByDesc('id')
            ->get()
            ->sortByDesc(fn (Quiz $quiz) => $quiz->published_at?->timestamp ?? $quiz->created_at?->timestamp ?? 0)
            ->values();
    }

    public function getFormOptions(User $teacher): array
    {
        $courses = Course::query()
            ->where('teacher_id', $teacher->id)
            ->with(['subject', 'modules.lessons'])
            ->orderByDesc('id')
            ->get();

        $classRooms = ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->with(['course.subject'])
            ->orderByDesc('id')
            ->get();

        $subjects = Subject::query()
            ->whereHas('courses', fn (Builder $query) => $query->where('teacher_id', $teacher->id))
            ->with('category')
            ->orderBy('name')
            ->get();

        $lessonOptions = $courses->flatMap(function (Course $course): Collection {
            return $course->modules->flatMap(function ($module) use ($course): Collection {
                return $module->lessons->map(function ($lesson) use ($course, $module): array {
                    return [
                        'id' => $lesson->id,
                        'course_id' => $course->id,
                        'label' => $course->title . ' / ' . $module->title . ' / ' . $lesson->title,
                    ];
                });
            });
        })->values();

        return compact('courses', 'classRooms', 'subjects', 'lessonOptions');
    }

    public function getQuizFormRows(?Quiz $quiz = null): array
    {
        if (! $quiz) {
            return [
                [
                    'id' => null,
                    'question' => '',
                    'description' => '',
                    'points' => 1,
                    'options' => [
                        'A' => '',
                        'B' => '',
                        'C' => '',
                        'D' => '',
                    ],
                    'correct_option' => 'A',
                ],
            ];
        }

        $quiz->loadMissing('questions.options');

        return $quiz->questions->map(function (Question $question): array {
            $options = $question->options->keyBy(fn (Option $option) => $this->optionLetter($option->order));
            $correctOption = $question->options->firstWhere('is_correct', true);

            return [
                'id' => $question->id,
                'question' => $question->question,
                'description' => $question->description,
                'points' => $question->points,
                'option_ids' => [
                    'A' => $options->get('A')?->id,
                    'B' => $options->get('B')?->id,
                    'C' => $options->get('C')?->id,
                    'D' => $options->get('D')?->id,
                ],
                'options' => [
                    'A' => $options->get('A')?->option_text ?? '',
                    'B' => $options->get('B')?->option_text ?? '',
                    'C' => $options->get('C')?->option_text ?? '',
                    'D' => $options->get('D')?->option_text ?? '',
                ],
                'correct_option' => $correctOption ? $this->optionLetter($correctOption->order) : 'A',
            ];
        })->values()->all();
    }

    public function resolveOwnedQuiz(User $teacher, Quiz $quiz): Quiz
    {
        $ownedQuiz = Quiz::query()
            ->ownedByTeacher($teacher)
            ->with([
                'teacher',
                'course.subject',
                'subject',
                'classRoom.course.subject',
                'classRoom.teacher',
                'lesson.module.course.subject',
                'questions.options',
                'answers',
            ])
            ->find($quiz->id);

        if (! $ownedQuiz) {
            abort(403, 'Bạn không có quyền truy cập bài kiểm tra này.');
        }

        return $ownedQuiz;
    }

    public function saveQuiz(User $teacher, ?Quiz $quiz, array $data): Quiz
    {
        $data = $this->normalizeQuizPayload($data);
        $this->validateQuizPayload($data);

        return DB::transaction(function () use ($teacher, $quiz, $data): Quiz {
            $target = $this->resolveTarget($teacher, $data);

            $payload = $this->buildQuizPayload($teacher, $quiz, $target, $data);

            if ($quiz) {
                $quiz->fill($payload)->save();
            } else {
                $quiz = Quiz::create($payload);
            }

            $this->syncQuestions($quiz, $data['questions'] ?? []);

            return $quiz->fresh(['teacher', 'course.subject', 'subject', 'classRoom.course.subject', 'lesson.module.course.subject', 'questions.options']);
        });
    }

    public function deleteQuiz(User $teacher, Quiz $quiz): void
    {
        $ownedQuiz = $this->resolveOwnedQuiz($teacher, $quiz);

        if (QuizAnswer::query()->where('quiz_id', $ownedQuiz->id)->exists()) {
            throw ValidationException::withMessages([
                'quiz' => 'Bài kiểm tra đã có học viên làm nên không thể xóa.',
            ]);
        }

        DB::transaction(fn () => $ownedQuiz->delete());
    }

    protected function resolveTarget(User $teacher, array $data): array
    {
        $course = null;
        $classRoom = null;
        $subject = null;

        if (! empty($data['lop_hoc_id'])) {
            $classRoom = ClassRoom::query()
                ->where('teacher_id', $teacher->id)
                ->with(['course.subject', 'course.modules.lessons', 'subject'])
                ->find((int) $data['lop_hoc_id']);

            if (! $classRoom) {
                throw ValidationException::withMessages([
                    'lop_hoc_id' => 'Lớp học được chọn không thuộc giảng viên này.',
                ]);
            }

            $course = $classRoom->course;
        }

        if (! $course && ! empty($data['course_id'])) {
            $course = Course::query()
                ->where('teacher_id', $teacher->id)
                ->with(['subject', 'modules.lessons'])
                ->find((int) $data['course_id']);

            if (! $course) {
                throw ValidationException::withMessages([
                    'course_id' => 'Khóa học được chọn không thuộc giảng viên này.',
                ]);
            }
        }

        if (! $course && ! empty($data['subject_id'])) {
            $subject = Subject::query()
                ->whereHas('courses', fn (Builder $query) => $query->where('teacher_id', $teacher->id))
                ->with(['courses.modules.lessons'])
                ->find((int) $data['subject_id']);

            if (! $subject) {
                throw ValidationException::withMessages([
                    'subject_id' => 'Môn học được chọn không thuộc giảng viên này.',
                ]);
            }

            $course = $subject->courses
                ->sortByDesc('id')
                ->first(fn (Course $course) => (int) $course->teacher_id === (int) $teacher->id)
                ?? $subject->courses->sortByDesc('id')->first();

            if (! $course) {
                throw ValidationException::withMessages([
                    'subject_id' => 'Môn học này chưa có khóa học phù hợp để gán bài kiểm tra.',
                ]);
            }
        }

        if (! $course) {
            throw ValidationException::withMessages([
                'course_id' => 'Vui lòng chọn lớp học, khóa học hoặc môn học.',
            ]);
        }

        if ($classRoom && (int) $classRoom->course_id !== (int) $course->id) {
            throw ValidationException::withMessages([
                'lop_hoc_id' => 'Lớp học phải thuộc khóa học đã chọn.',
            ]);
        }

        if ($subject && (int) $course->subject_id !== (int) $subject->id) {
            throw ValidationException::withMessages([
                'subject_id' => 'Khóa học được chọn không thuộc môn học này.',
            ]);
        }

        $lesson = $this->resolveLessonForCourse($course, $data['lesson_id'] ?? null);

        if (! $lesson) {
            throw ValidationException::withMessages([
                'lesson_id' => 'Khóa học này cần có ít nhất một bài học để gán bài kiểm tra.',
            ]);
        }

        return compact('course', 'classRoom', 'lesson');
    }

    protected function resolveLessonForCourse(Course $course, mixed $lessonId = null): ?Lesson
    {
        $course->loadMissing(['modules.lessons']);

        if ($lessonId) {
            $lesson = Lesson::query()
                ->whereHas('module', fn (Builder $query) => $query->where('course_id', $course->id))
                ->find((int) $lessonId);

            if ($lesson) {
                return $lesson;
            }
        }

        return $course->modules
            ->flatMap(fn ($module) => $module->lessons)
            ->first();
    }

    protected function validateQuizPayload(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
            'total_score' => ['nullable', 'numeric', 'min:1', 'max:1000'],
            'status' => ['required', 'in:' . implode(',', [Quiz::STATUS_DRAFT, Quiz::STATUS_PUBLISHED])],
            'course_id' => ['nullable', 'integer'],
            'subject_id' => ['nullable', 'integer'],
            'lop_hoc_id' => ['nullable', 'integer'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'string', 'max:1000'],
            'questions.*.description' => ['nullable', 'string', 'max:2000'],
            'questions.*.points' => ['nullable', 'numeric', 'min:0.1', 'max:1000'],
            'questions.*.correct_option' => ['required', 'in:A,B,C,D'],
            'questions.*.options.A' => ['required', 'string', 'max:1000'],
            'questions.*.options.B' => ['required', 'string', 'max:1000'],
            'questions.*.options.C' => ['required', 'string', 'max:1000'],
            'questions.*.options.D' => ['required', 'string', 'max:1000'],
        ], [
            'title.required' => 'Tên bài kiểm tra không được để trống.',
            'questions.required' => 'Vui lòng thêm ít nhất một câu hỏi.',
            'questions.*.question.required' => 'Mỗi câu hỏi cần có nội dung.',
            'questions.*.correct_option.required' => 'Mỗi câu hỏi phải có đáp án đúng.',
        ]);

        if (! ($data['lop_hoc_id'] ?? null) && ! ($data['course_id'] ?? null) && ! ($data['subject_id'] ?? null)) {
            $validator->errors()->add('course_id', 'Vui lòng chọn lớp học, khóa học hoặc môn học.');
        }

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }
    }

    protected function normalizeQuizPayload(array $data): array
    {
        foreach (['course_id', 'subject_id', 'lop_hoc_id'] as $field) {
            if (array_key_exists($field, $data) && in_array($data[$field], ['', null], true)) {
                $data[$field] = null;
            }
        }

        return $data;
    }

    protected function quizPayloadKey(string $column): ?string
    {
        return Schema::hasColumn((new Quiz())->getTable(), $column) ? $column : null;
    }

    protected function buildQuizPayload(User $teacher, ?Quiz $quiz, array $target, array $data): array
    {
        $source = [
            'teacher_id' => $teacher->id,
            'course_id' => $target['course']->id,
            'subject_id' => $target['course']->subject_id,
            'lop_hoc_id' => $target['classRoom']?->id,
            'lesson_id' => $target['lesson']->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? 15,
            'total_score' => $data['total_score'] ?? 10,
            'status' => $data['status'],
            'published_at' => $data['status'] === Quiz::STATUS_PUBLISHED ? ($quiz?->published_at ?? now()) : null,
            'passing_score' => $data['passing_score'] ?? 70,
            'is_required' => true,
            'max_attempts' => $data['max_attempts'] ?? 3,
        ];

        $payload = [];

        foreach ($source as $column => $value) {
            if ($value === null) {
                continue;
            }

            if ($this->quizPayloadKey($column) === null) {
                continue;
            }

            $payload[$column] = $value;
        }

        return $payload;
    }

    protected function syncQuestions(Quiz $quiz, array $questions): void
    {
        if ($questions === []) {
            throw ValidationException::withMessages([
                'questions' => 'Vui lòng thêm ít nhất một câu hỏi.',
            ]);
        }

        $quiz->loadMissing('questions.options');
        $keptQuestionIds = [];

        foreach (array_values($questions) as $index => $questionData) {
            $question = null;

            if (! empty($questionData['id'])) {
                $question = $quiz->questions->firstWhere('id', (int) $questionData['id']);
            }

            if (! $question) {
                $question = new Question(['quiz_id' => $quiz->id]);
            }

            $question->fill([
                'question' => trim((string) ($questionData['question'] ?? '')),
                'description' => trim((string) ($questionData['description'] ?? '')) ?: null,
                'type' => 'multiple_choice',
                'order' => $index + 1,
                'points' => (float) ($questionData['points'] ?? 1),
            ])->save();

            $keptQuestionIds[] = $question->id;
            $existingOptions = $question->options->keyBy('id');
            $keptOptionIds = [];
            $filledOptions = 0;
            $correctOption = trim((string) ($questionData['correct_option'] ?? 'A'));

            foreach (['A', 'B', 'C', 'D'] as $position => $letter) {
                $optionId = (int) ($questionData['option_ids'][$letter] ?? 0);
                $option = $optionId > 0 ? $existingOptions->get($optionId) : null;

                if (! $option) {
                    $option = new Option(['question_id' => $question->id]);
                }

                $option->fill([
                    'option_text' => trim((string) ($questionData['options'][$letter] ?? '')),
                    'is_correct' => $correctOption === $letter,
                    'order' => $position + 1,
                ])->save();

                if ($option->option_text !== '') {
                    $filledOptions++;
                }

                $keptOptionIds[] = $option->id;
            }

            if ($filledOptions < 2) {
                throw ValidationException::withMessages([
                    'questions' => 'Mỗi câu hỏi phải có ít nhất 2 đáp án hợp lệ.',
                ]);
            }

            if (! $question->options()->where('is_correct', true)->exists()) {
                throw ValidationException::withMessages([
                    'questions' => 'Mỗi câu hỏi phải có 1 đáp án đúng.',
                ]);
            }

            $removedOptions = $question->options()->whereNotIn('id', $keptOptionIds)->get();

            foreach ($removedOptions as $removedOption) {
                if (QuizAnswer::query()->where('option_id', $removedOption->id)->exists()) {
                    throw ValidationException::withMessages([
                        'questions' => 'Không thể xóa lựa chọn đã có học viên sử dụng.',
                    ]);
                }
            }

            $question->options()->whereNotIn('id', $keptOptionIds)->delete();
        }

        $removedQuestions = $quiz->questions()->whereNotIn('id', $keptQuestionIds)->get();

        foreach ($removedQuestions as $removedQuestion) {
            if (QuizAnswer::query()->where('question_id', $removedQuestion->id)->exists()) {
                throw ValidationException::withMessages([
                    'questions' => 'Không thể xóa câu hỏi đã có học viên làm.',
                ]);
            }
        }

        $quiz->questions()->whereNotIn('id', $keptQuestionIds)->delete();
    }

    protected function optionLetter(int $order): string
    {
        return match ($order) {
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            default => 'A',
        };
    }
}
