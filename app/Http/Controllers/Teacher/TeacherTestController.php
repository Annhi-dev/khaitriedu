<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Quiz;
use App\Models\User;
use App\Services\TeacherTestService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TeacherTestController extends Controller
{
    public function index(Request $request, TeacherTestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        $filters = [
            'search' => $request->query('search', ''),
            'status' => $request->query('status', 'all'),
        ];

        $summary = $service->getDashboardSummary($current, $filters);
        $formOptions = $service->getFormOptions($current);

        return view('giao_vien.bai_kiem_tra.index', [
            'current' => $current,
            'tests' => $summary['tests'],
            'summary' => $summary['summary'],
            'filters' => $filters,
            'formOptions' => $formOptions,
        ]);
    }

    public function create(Request $request, TeacherTestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        $selectedClassRoom = null;
        $selectedClassRoomId = (int) $request->query('lop_hoc_id', 0);

        if ($selectedClassRoomId > 0) {
            $selectedClassRoom = ClassRoom::query()
                ->where('teacher_id', $current->id)
                ->with(['course.subject', 'room'])
                ->find($selectedClassRoomId);

            if (! $selectedClassRoom) {
                abort(403, 'Lớp học được chọn không thuộc giảng viên này.');
            }
        }

        return view('giao_vien.bai_kiem_tra.create', [
            'current' => $current,
            'quiz' => null,
            'formOptions' => $service->getFormOptions($current),
            'questionRows' => $service->getQuizFormRows(),
            'selectedClassRoom' => $selectedClassRoom,
            'statusOptions' => [
                Quiz::STATUS_DRAFT => 'Nháp',
                Quiz::STATUS_PUBLISHED => 'Công khai',
            ],
        ]);
    }

    public function store(Request $request, TeacherTestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        try {
            $quiz = $service->saveQuiz($current, null, $request->all());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('teacher.tests.show', $quiz)
            ->with('status', 'Đã tạo bài kiểm tra mới.');
    }

    public function show(Quiz $test, TeacherTestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        $quiz = $service->resolveOwnedQuiz($current, $test);
        $quizReport = $service->getTeacherQuizReport($quiz);

        return view('giao_vien.bai_kiem_tra.show', [
            'current' => $current,
            'quiz' => $quiz,
            'quizReport' => $quizReport,
        ]);
    }

    public function edit(Quiz $test, TeacherTestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        $quiz = $service->resolveOwnedQuiz($current, $test);

        return view('giao_vien.bai_kiem_tra.edit', [
            'current' => $current,
            'quiz' => $quiz,
            'formOptions' => $service->getFormOptions($current),
            'questionRows' => $service->getQuizFormRows($quiz),
            'selectedClassRoom' => $quiz->classRoom,
            'statusOptions' => [
                Quiz::STATUS_DRAFT => 'Nháp',
                Quiz::STATUS_PUBLISHED => 'Công khai',
            ],
        ]);
    }

    public function update(Request $request, Quiz $test, TeacherTestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        $quiz = $service->resolveOwnedQuiz($current, $test);

        try {
            $quiz = $service->saveQuiz($current, $quiz, $request->all());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('teacher.tests.show', $quiz)
            ->with('status', 'Đã cập nhật bài kiểm tra.');
    }

    public function destroy(Quiz $test, TeacherTestService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        $quiz = $service->resolveOwnedQuiz($current, $test);

        try {
            $service->deleteQuiz($current, $quiz);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('teacher.tests.index')
            ->with('status', 'Đã xóa bài kiểm tra.');
    }
}
