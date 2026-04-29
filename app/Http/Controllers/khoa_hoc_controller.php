<?php

namespace App\Http\Controllers;

use App\Exceptions\EnrollmentOperationException;
use App\Http\Requests\Student\StoreStudentScheduleRequest;
use App\Models\NhomHoc;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\BaiHoc;
use App\Models\TienDoBaiHoc;
use App\Models\HocPhan;
use App\Models\BaiKiemTra;
use App\Models\DanhGia;
use App\Models\MonHoc;
use App\Services\CourseQuizService;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->sessionUser();
        $categorySlug = $request->query('category');
        $query = MonHoc::with('category')
            ->withCount(['courses', 'enrollments'])
            ->visibleOnCatalog()
            ->orderBy('id', 'desc');

        if ($categorySlug) {
            $query->whereHas('category', function ($categoryQuery) use ($categorySlug) {
                $categoryQuery->active()->where('slug', $categorySlug);
            });
        }

        $courses = $query->paginate(12)->withQueryString();
        $categories = NhomHoc::active()->orderBy('order')->get();

        return view('trang_tinh.khoa_hoc', compact('courses', 'user', 'categories'));
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
            $course->modules->where('status', HocPhan::STATUS_PUBLISHED)->values()
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
                $completedLessonIds = TienDoBaiHoc::query()
                    ->where('user_id', $user->id)
                    ->whereIn('lesson_id', $lessonIds)
                    ->where('is_completed', true)
                    ->pluck('lesson_id')
                    ->all();
            }

            $completedLessonLookup = array_flip($completedLessonIds);

            foreach ($course->modules as $module) {
                $totalLessons = $module->plannedSessionCount();
                $completedLessons = $module->lessons
                    ->filter(fn ($lesson) => isset($completedLessonLookup[$lesson->id]))
                    ->count();
                $completedLessons = min($completedLessons, $totalLessons);

                $moduleProgress[$module->id] = [
                    'completed' => $completedLessons,
                    'total' => $totalLessons,
                    'percent' => $totalLessons > 0
                        ? (int) round(($completedLessons / $totalLessons) * 100)
                        : 0,
                ];
            }

            $totalCourseLessons = (int) $course->modules->sum(fn ($module) => $module->plannedSessionCount());
            $completedCourseLessons = min(count($completedLessonIds), $totalCourseLessons);

            $courseProgress = [
                'completed' => $completedCourseLessons,
                'total' => $totalCourseLessons,
                'percent' => $totalCourseLessons > 0
                    ? (int) round(($completedCourseLessons / $totalCourseLessons) * 100)
                    : 0,
            ];
        }

        return view('khoa_hoc.show', compact(
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

        $module = HocPhan::with('lessons')
            ->where('course_id', $course->id)
            ->where('status', HocPhan::STATUS_PUBLISHED)
            ->find($module);

        if (! $module) {
            return redirect()->route('courses.show', $course->id)->with('error', 'Bai hoc khong ton tai.');
        }

        $lesson = BaiHoc::with('quiz')->where('module_id', $module->id)->find($lesson);

        if (! $lesson) {
            return redirect()->route('courses.show', $course->id)->with('error', 'Bai hoc khong ton tai.');
        }

        $lessonProgress = null;

        if ($user && $user->isStudent() && $enrollment) {
            $lessonProgress = TienDoBaiHoc::firstOrNew([
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

        return view('khoa_hoc.lesson', compact('course', 'module', 'lesson', 'user', 'lessonProgress'));
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
            return redirect()->route('courses.show', $course->id)->with('error', 'BaiKiemTra khong ton tai.');
        }

        $quizProgress = $user && $user->isStudent()
            ? app(CourseQuizService::class)->getStudentQuizProgress($user, $quiz)
            : null;

        $quizReport = $user && ($user->isTeacher() || $user->isAdmin())
            ? app(CourseQuizService::class)->getTeacherQuizReport($quiz)
            : null;

        return view('khoa_hoc.quiz', compact('course', 'quiz', 'user', 'quizProgress', 'quizReport'));
    }

    public function submitQuiz(Request $request, $course, $quiz, CourseQuizService $quizService)
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
            return redirect()->route('courses.show', $course->id)->with('error', 'BaiKiemTra khong ton tai.');
        }

        $answers = $request->input('answers', []);
        if (! is_array($answers)) {
            $answers = [];
        }

        $result = $quizService->submit($user, $course, $quiz, $answers);

        return redirect()->route('courses.quiz.show', [$course->id, $quiz->id])->with('status', 'Bạn đã nộp bài lần ' . $result['attempt'] . ' với điểm ' . $result['score'] . '%. ' . ($result['passed'] ? 'Đạt.' : 'Chưa đạt.'));
    }

    public function redirectEnroll($id)
    {
        $course = KhoaHoc::find($id);

        if (! $course) {
            return redirect()->route('courses.index')->with('error', 'Lớp học không tồn tại.');
        }

        return redirect()->route('khoa-hoc.show', $course->subject_id)->with('error', 'Học viên không đăng ký trực tiếp vào lớp học. Vui lòng gửi yêu cầu ở trang khóa học.');
    }

    public function review(Request $request, $id)
    {
        $user = $this->sessionUser();

        if (! $user || ! $user->isStudent()) {
            return redirect()->route('login');
        }

        $enrollment = GhiDanh::where('user_id', $user->id)
            ->where('course_id', $id)
            ->whereIn('status', GhiDanh::courseAccessStatuses())
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'Bạn chưa được xếp vào lớp học này.');
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        DanhGia::updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $id],
            $data
        );

        return back()->with('status', 'Đánh giá đã được gửi.');
    }

    public function showSubject($id)
    {
        $user = $this->sessionUser();
        $subject = MonHoc::with(['category', 'courses.teacher'])->find($id);

        if (! $subject || ($subject->category && ! $subject->category->isActive())) {
            return redirect()->route('courses.index')->with('error', 'Khóa học không tồn tại hoặc đang tạm ẩn.');
        }

        $userEnrollment = $user && $user->isStudent()
            ? $this->findSubjectEnrollment($user->id, $subject->id, ['assignedTeacher', 'course'])
            : null;

        return view('mon_hoc.show', compact('subject', 'user', 'userEnrollment'));
    }

    public function enrollSubject(Request $request, $id, StudentEnrollmentService $enrollmentService)
    {
        $user = $this->sessionUser();

        if (! $user || ! $user->isStudent()) {
            return redirect()->route('login')->with('error', 'Vui long dang nhap bang tai khoan hoc vien.');
        }

        $subject = MonHoc::find($id);

        if (! $subject) {
            return back()->with('error', 'Khóa học không tồn tại.');
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

    private function findQuizForCourse(KhoaHoc $course, int $quizId): ?BaiKiemTra
    {
        $quiz = BaiKiemTra::with(['questions.options', 'lesson.module.course', 'classRoom'])
            ->published()
            ->find($quizId);

        if (! $quiz) {
            return null;
        }

        if ((int) ($quiz->course_id ?? 0) === (int) $course->id) {
            return $quiz;
        }

        if ((int) ($quiz->classRoom?->course_id ?? 0) === (int) $course->id) {
            return $quiz;
        }

        if ((int) ($quiz->lesson?->module?->course_id ?? 0) === (int) $course->id) {
            return $quiz;
        }

        return null;
    }
}
