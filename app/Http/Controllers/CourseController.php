<?php

namespace App\Http\Controllers;

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
use Carbon\Carbon;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->sessionUser();
        $categorySlug = $request->query('category');
        $query = Subject::with('category')
            ->withCount(['courses', 'enrollments'])
            ->orderBy('id', 'desc');

        if ($categorySlug) {
            $query->whereHas('category', function ($categoryQuery) use ($categorySlug) {
                $categoryQuery->where('slug', $categorySlug);
            });
        }

        $courses = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('order')->get();

        return view('pages.courses', compact('courses', 'user', 'categories'));
    }

    public function show($id)
    {
        $user = $this->sessionUser();
        [$course, $enrollment, $redirect] = $this->resolveInternalClassAccess((int) $id, $user, ['subject.category', 'teacher', 'modules.lessons.quiz', 'reviews.user']);

        if ($redirect) {
            return $redirect;
        }

        $reviews = $course->reviews->sortByDesc('created_at')->values();
        $review = $user && $user->role === 'hoc_vien' ? $reviews->firstWhere('user_id', $user->id) : null;

        return view('courses.show', compact('course', 'user', 'enrollment', 'review', 'reviews'));
    }

    public function showLesson($course, $module, $lesson)
    {
        $user = $this->sessionUser();
        [$course, $enrollment, $redirect] = $this->resolveInternalClassAccess((int) $course, $user, ['modules.lessons']);

        if ($redirect) {
            return $redirect;
        }

        $module = Module::with('lessons')->where('course_id', $course->id)->find($module);

        if (! $module) {
            return redirect()->route('courses.show', $course->id)->with('error', 'Bài học không tồn tại.');
        }

        $lesson = Lesson::with('quiz')->where('module_id', $module->id)->find($lesson);

        if (! $lesson) {
            return redirect()->route('courses.show', $course->id)->with('error', 'Bài học không tồn tại.');
        }

        if ($user && $user->role === 'hoc_vien' && $enrollment) {
            LessonProgress::updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                ['started_at' => now(), 'is_completed' => true, 'completed_at' => now(), 'time_spent' => 300]
            );
        }

        return view('courses.lesson', compact('course', 'module', 'lesson', 'user'));
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
            return redirect()->route('courses.show', $course->id)->with('error', 'Quiz không tồn tại.');
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

        if ($user->role !== 'hoc_vien' || ! $enrollment) {
            return redirect()->route('courses.show', $course->id)->with('error', 'Chỉ học viên đã được xếp lớp mới có thể nộp quiz.');
        }

        $quiz = $this->findQuizForCourse($course, (int) $quiz);

        if (! $quiz) {
            return redirect()->route('courses.show', $course->id)->with('error', 'Quiz không tồn tại.');
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

        return redirect()->route('courses.show', $course->id)->with('status', "Quiz hoàn thành: $score%. " . ($passed ? 'Đạt chứng chỉ.' : 'Không đạt.'));
    }

    public function redirectEnroll($id)
    {
        $course = Course::find($id);

        if (! $course) {
            return redirect()->route('courses.index')->with('error', 'Lớp học không tồn tại.');
        }

        return redirect()->route('khoa-hoc.show', $course->subject_id)->with('error', 'Học viên không đăng ký trực tiếp vào lớp học. Vui lòng gửi yêu cầu ở trang khóa học.');
    }

    public function review(Request $request, $id)
    {
        $user = $this->sessionUser();

        if (! $user || $user->role !== 'hoc_vien') {
            return redirect()->route('login');
        }

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $id)
            ->where('status', 'confirmed')
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'Bạn chưa hoàn thành lớp học này.');
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $id],
            $data
        );

        return back()->with('status', 'Đánh giá đã được gửi.');
    }

    public function showSubject($id)
    {
        $user = $this->sessionUser();
        $subject = Subject::with(['category', 'courses.teacher'])->find($id);

        if (! $subject) {
            return redirect()->route('courses.index')->with('error', 'Khóa học không tồn tại.');
        }

        $userEnrollment = $user && $user->role === 'hoc_vien'
            ? $this->findSubjectEnrollment($user->id, $subject->id, ['assignedTeacher', 'course'])
            : null;

        return view('subjects.show', compact('subject', 'user', 'userEnrollment'));
    }

    public function enrollSubject(Request $request, $id)
    {
        $user = $this->sessionUser();

        if (! $user || $user->role !== 'hoc_vien') {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập bằng tài khoản học viên.');
        }

        $subject = Subject::find($id);

        if (! $subject) {
            return back()->with('error', 'Khóa học không tồn tại.');
        }

        $data = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'preferred_days' => 'required|array|min:1',
            'preferred_days.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);

        $existing = $this->findSubjectEnrollment($user->id, $subject->id);
        $payload = [
            'subject_id' => $subject->id,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'preferred_days' => json_encode($data['preferred_days']),
            'is_submitted' => true,
            'submitted_at' => Carbon::now(),
            'note' => null,
        ];

        if ($existing) {
            if ($existing->status === 'rejected') {
                $payload['status'] = 'pending';
                $payload['course_id'] = null;
                $payload['assigned_teacher_id'] = null;
                $payload['schedule'] = null;
            } else {
                $payload['status'] = $existing->status;
            }

            $existing->update($payload);

            return back()->with('status', 'Yêu cầu đăng ký khóa học đã được cập nhật. Admin sẽ xem lại và xếp lớp phù hợp.');
        }

        Enrollment::create([
            'user_id' => $user->id,
            'subject_id' => $subject->id,
            'course_id' => null,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'preferred_days' => json_encode($data['preferred_days']),
            'is_submitted' => true,
            'submitted_at' => Carbon::now(),
            'status' => 'pending',
        ]);

        return back()->with('status', 'Đăng ký khóa học đã gửi. Admin sẽ xem lịch mong muốn và xếp bạn vào lớp phù hợp.');
    }

    public function legacySubjectShow($id)
    {
        return redirect()->route('khoa-hoc.show', $id);
    }

    private function findQuizForCourse(Course $course, int $quizId): ?Quiz
    {
        return Quiz::with('questions.options')
            ->whereHas('lesson.module', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->find($quizId);
    }
}