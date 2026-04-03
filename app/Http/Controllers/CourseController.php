<?php

namespace App\Http\Controllers;

use App\Exceptions\EnrollmentOperationException;
use App\Http\Requests\Student\StoreStudentScheduleRequest;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Option;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\Review;
use App\Models\Subject;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->sessionUser();
        $categorySlug = $request->query('category');
        $query = Subject::with('category')
            ->withCount(['courses', 'enrollments'])
            ->visibleOnCatalog()
            ->orderBy('id', 'desc');

        if ($categorySlug) {
            $query->whereHas('category', function ($categoryQuery) use ($categorySlug) {
                $categoryQuery->active()->where('slug', $categorySlug);
            });
        }

        $courses = $query->paginate(12)->withQueryString();
        $categories = Category::active()->orderBy('order')->get();

        return view('pages.courses', compact('courses', 'user', 'categories'));
    }

    public function show($id)
    {
        $user = $this->sessionUser();
        [$course, $enrollment, $redirect] = $this->resolveInternalClassAccess((int) $id, $user, ['subject.category', 'teacher', 'modules.lessons.quiz', 'reviews.user']);

        if ($redirect) {
            return $redirect;
        }

        $course->setRelation(
            'modules',
            $course->modules->where('status', Module::STATUS_PUBLISHED)->values()
        );

        $reviews = $course->reviews->sortByDesc('created_at')->values();
        $review = $user && $user->isStudent() ? $reviews->firstWhere('user_id', $user->id) : null;

        $completedLessonIds = [];
        $moduleProgress = [];
        $courseProgress = null;

        if ($user && $user->isStudent() && $enrollment) {
            $lessonIds = $course->modules
                ->flatMap(fn ($module) => $module->lessons->pluck('id'))
                ->filter()
                ->values();

            if ($lessonIds->isNotEmpty()) {
                $completedLessonIds = LessonProgress::query()
                    ->where('user_id', $user->id)
                    ->whereIn('lesson_id', $lessonIds)
                    ->where('is_completed', true)
                    ->pluck('lesson_id')
                    ->all();
            }

            $completedLessonLookup = array_flip($completedLessonIds);

            foreach ($course->modules as $module) {
                $totalLessons = $module->lessons->count();
                $completedLessons = $module->lessons
                    ->filter(fn ($lesson) => isset($completedLessonLookup[$lesson->id]))
                    ->count();

                $moduleProgress[$module->id] = [
                    'completed' => $completedLessons,
                    'total' => $totalLessons,
                    'percent' => $totalLessons > 0
                        ? (int) round(($completedLessons / $totalLessons) * 100)
                        : 0,
                ];
            }

            $totalCourseLessons = (int) $course->modules->sum(fn ($module) => $module->lessons->count());
            $completedCourseLessons = count($completedLessonIds);

            $courseProgress = [
                'completed' => $completedCourseLessons,
                'total' => $totalCourseLessons,
                'percent' => $totalCourseLessons > 0
                    ? (int) round(($completedCourseLessons / $totalCourseLessons) * 100)
                    : 0,
            ];
        }

        return view('courses.show', compact(
            'course',
            'user',
            'enrollment',
            'review',
            'reviews',
            'completedLessonIds',
            'moduleProgress',
            'courseProgress'
        ));
    }

    public function showLesson($course, $module, $lesson)
    {
        $user = $this->sessionUser();
        [$course, $enrollment, $redirect] = $this->resolveInternalClassAccess((int) $course, $user, ['modules.lessons']);

        if ($redirect) {
            return $redirect;
        }

        $module = Module::with('lessons')
            ->where('course_id', $course->id)
            ->where('status', Module::STATUS_PUBLISHED)
            ->find($module);

        if (! $module) {
            return redirect()->route('courses.show', $course->id)->with('error', 'Bai hoc khong ton tai.');
        }

        $lesson = Lesson::with('quiz')->where('module_id', $module->id)->find($lesson);

        if (! $lesson) {
            return redirect()->route('courses.show', $course->id)->with('error', 'Bai hoc khong ton tai.');
        }

        $lessonProgress = null;

        if ($user && $user->isStudent() && $enrollment) {
            $lessonProgress = LessonProgress::firstOrNew([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ]);

            if (! $lessonProgress->started_at) {
                $lessonProgress->started_at = now();
            }

            $lessonProgress->is_completed = true;
            $lessonProgress->completed_at = $lessonProgress->completed_at ?? now();
            $lessonProgress->time_spent = max((int) $lessonProgress->time_spent, 300);
            $lessonProgress->save();
        }

        return view('courses.lesson', compact('course', 'module', 'lesson', 'user', 'lessonProgress'));
    }

    public function showQuiz($course, $quiz)
    {
        $user = $this->sessionUser();
        [$course, , $redirect] = $this->resolveInternalClassAccess((int) $course, $user);

        if ($redirect) {
            return $redirect;
        }

        $quiz = $this->findQuizForCourse($course, (int) $quiz);

        if (! $quiz) {
            return redirect()->route('courses.show', $course->id)->with('error', 'Quiz khong ton tai.');
        }

        return view('courses.quiz', compact('course', 'quiz', 'user'));
    }

    public function submitQuiz(Request $request, $course, $quiz)
    {
        $user = $this->sessionUser();
        [$course, $enrollment, $redirect] = $this->resolveInternalClassAccess((int) $course, $user);

        if ($redirect) {
            return $redirect;
        }

        if (! $user->isStudent() || ! $enrollment) {
            return redirect()->route('courses.show', $course->id)->with('error', 'Chi hoc vien da duoc xep lop moi co the nop quiz.');
        }

        $quiz = $this->findQuizForCourse($course, (int) $quiz);

        if (! $quiz) {
            return redirect()->route('courses.show', $course->id)->with('error', 'Quiz khong ton tai.');
        }

        $answers = $request->input('answers', []);
        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points;
            $selected = $answers[$question->id] ?? null;

            if ($question->type === 'short_answer') {
                $isCorrect = false;
            } else {
                $option = Option::find($selected);
                $isCorrect = $option ? (bool) $option->is_correct : false;

                if ($isCorrect) {
                    $earnedPoints += $question->points;
                }
            }

            QuizAnswer::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'question_id' => $question->id,
                'option_id' => $selected,
                'answer_text' => is_array($selected) ? json_encode($selected) : $selected,
                'is_correct' => $isCorrect,
                'attempt' => QuizAnswer::where('user_id', $user->id)->where('quiz_id', $quiz->id)->count() + 1,
            ]);
        }

        $score = $totalPoints ? round($earnedPoints / $totalPoints * 100, 2) : 0;
        $passed = $score >= ($quiz->passing_score ?: 70);

        if ($passed) {
            Certificate::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'certificate_number' => 'KT' . time() . rand(100, 999),
                'score' => $score,
                'issued_at' => now(),
                'status' => 'issued',
            ]);
        }

        return redirect()->route('courses.show', $course->id)->with('status', "Quiz hoan thanh: $score%. " . ($passed ? 'Dat chung chi.' : 'Khong dat.'));
    }

    public function redirectEnroll($id)
    {
        $course = Course::find($id);

        if (! $course) {
            return redirect()->route('courses.index')->with('error', 'Lop hoc khong ton tai.');
        }

        return redirect()->route('khoa-hoc.show', $course->subject_id)->with('error', 'Hoc vien khong dang ky truc tiep vao lop hoc. Vui long gui yeu cau o trang khoa hoc.');
    }

    public function review(Request $request, $id)
    {
        $user = $this->sessionUser();

        if (! $user || ! $user->isStudent()) {
            return redirect()->route('login');
        }

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $id)
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'Ban chua duoc xep vao lop hoc nay.');
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $id],
            $data
        );

        return back()->with('status', 'Danh gia da duoc gui.');
    }

    public function showSubject($id)
    {
        $user = $this->sessionUser();
        $subject = Subject::with(['category', 'courses.teacher'])->find($id);

        if (! $subject || ($subject->category && ! $subject->category->isActive())) {
            return redirect()->route('courses.index')->with('error', 'Khoa hoc khong ton tai hoac dang tam an.');
        }

        $userEnrollment = $user && $user->isStudent()
            ? $this->findSubjectEnrollment($user->id, $subject->id, ['assignedTeacher', 'course'])
            : null;

        return view('subjects.show', compact('subject', 'user', 'userEnrollment'));
    }

    public function enrollSubject(Request $request, $id, StudentEnrollmentService $enrollmentService)
    {
        $user = $this->sessionUser();

        if (! $user || ! $user->isStudent()) {
            return redirect()->route('login')->with('error', 'Vui long dang nhap bang tai khoan hoc vien.');
        }

        $subject = Subject::find($id);

        if (! $subject) {
            return back()->with('error', 'Khoa hoc khong ton tai.');
        }

        $data = validator(
            StoreStudentScheduleRequest::sanitize($request->all()),
            StoreStudentScheduleRequest::rulesList()
        )->validate();

        try {
            $message = $enrollmentService->submitCustomScheduleRequest($user, $subject, $data);
        } catch (EnrollmentOperationException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('status', $message);
    }

    public function legacySubjectShow($id)
    {
        return redirect()->route('khoa-hoc.show', $id);
    }

    private function findQuizForCourse(Course $course, int $quizId): ?Quiz
    {
        return Quiz::with('questions.options')
            ->whereHas('lesson.module', function ($query) use ($course) {
                $query->where('course_id', $course->id)
                    ->where('status', Module::STATUS_PUBLISHED);
            })
            ->find($quizId);
    }
}
