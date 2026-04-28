<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\StudentClassService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentClassController extends Controller
{
    public function index(Request $request, StudentClassService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        $enrollments = $service->getStudentClasses($current);
        $search = trim((string) $request->string('q'));
        $statusFilter = (string) $request->string('status', 'all');

        $filteredEnrollments = $this->filterEnrollments($enrollments, $search, $statusFilter);
        $ongoingEnrollments = $filteredEnrollments->filter(fn (Enrollment $enrollment) => $this->classGroupStatus($enrollment) === 'ongoing')->values();
        $completedEnrollments = $filteredEnrollments->filter(fn (Enrollment $enrollment) => $this->classGroupStatus($enrollment) === 'completed')->values();
        $summary = $this->buildSummary($enrollments);

        return view('hoc_vien.lop_hoc.index', [
            'current' => $current,
            'enrollments' => $filteredEnrollments,
            'ongoingEnrollments' => $ongoingEnrollments,
            'completedEnrollments' => $completedEnrollments,
            'summary' => $summary,
            'search' => $search,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function show(Request $request, Enrollment $enrollment, StudentClassService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        if ((int) $enrollment->user_id !== (int) $current->id) {
            abort(403);
        }

        if (in_array($enrollment->normalizedStatus(), [Enrollment::STATUS_PENDING, Enrollment::STATUS_REJECTED], true)) {
            return redirect()
                ->route('student.classes.index')
                ->with('error', 'Lớp học này đang chờ duyệt hoặc đã bị từ chối nên chưa thể xem chi tiết.');
        }

        return $this->renderDetail($current, $enrollment, $service, (string) $request->string('tab', 'overview'));
    }

    public function evaluation(Enrollment $enrollment, StudentClassService $service)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        if ((int) $enrollment->user_id !== (int) $current->id) {
            abort(403);
        }

        if (in_array($enrollment->normalizedStatus(), [Enrollment::STATUS_PENDING, Enrollment::STATUS_REJECTED], true)) {
            return redirect()
                ->route('student.classes.index')
                ->with('error', 'Lớp học này đang chờ duyệt hoặc đã bị từ chối nên chưa thể đánh giá.');
        }

        if ($enrollment->lop_hoc_id === null) {
            return redirect()
                ->route('student.classes.show', $enrollment)
                ->with('error', 'Lớp học này chưa được xếp lớp nên chưa thể đánh giá.');
        }

        return $this->renderDetail($current, $enrollment, $service, 'evaluation');
    }

    public function storeEvaluation(Request $request, Enrollment $enrollment, StudentClassService $service): RedirectResponse
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        if ((int) $enrollment->user_id !== (int) $current->id) {
            abort(403);
        }

        if (in_array($enrollment->normalizedStatus(), [Enrollment::STATUS_PENDING, Enrollment::STATUS_REJECTED], true)) {
            return redirect()
                ->route('student.classes.index')
                ->with('error', 'Lớp học này đang chờ duyệt hoặc đã bị từ chối nên chưa thể đánh giá.');
        }

        if ($enrollment->lop_hoc_id === null) {
            return redirect()
                ->route('student.classes.show', $enrollment)
                ->with('error', 'Lớp học này chưa được xếp lớp nên chưa thể đánh giá.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ]);

        $service->saveEvaluation($current, $enrollment, $data);

        return redirect()
            ->route('student.classes.evaluation', $enrollment)
            ->with('status', 'Đánh giá của bạn đã được lưu.');
    }

    protected function renderDetail(User $student, Enrollment $enrollment, StudentClassService $service, string $tab)
    {
        $detail = $service->getStudentClassDetail($student, $enrollment);

        return view('hoc_vien.lop_hoc.show', $detail + [
            'current' => $student,
            'selectedTab' => $tab,
        ]);
    }

    protected function filterEnrollments($enrollments, string $search, string $statusFilter)
    {
        $search = trim($search);
        $statusFilter = in_array($statusFilter, ['all', 'ongoing', 'completed'], true) ? $statusFilter : 'all';

        return $enrollments
            ->filter(function (Enrollment $enrollment) use ($search, $statusFilter): bool {
                $status = $this->classGroupStatus($enrollment);

                if ($statusFilter === 'ongoing' && $status !== 'ongoing') {
                    return false;
                }

                if ($statusFilter === 'completed' && $status !== 'completed') {
                    return false;
                }

                if ($search === '') {
                    return true;
                }

                $haystacks = [
                    $enrollment->classRoom?->displayName(),
                    $enrollment->course?->title,
                    $enrollment->subject?->name,
                    $enrollment->classRoom?->teacher?->displayName(),
                    $enrollment->assignedTeacher?->displayName(),
                ];

                $needle = Str::ascii(mb_strtolower($search));

                foreach ($haystacks as $value) {
                    if ($value !== null && Str::contains(Str::ascii(mb_strtolower($value)), $needle)) {
                        return true;
                    }
                }

                return false;
            })
            ->sortBy(function (Enrollment $enrollment): string {
                $groupWeight = $this->classGroupStatus($enrollment) === 'ongoing' ? '0' : '1';
                $dateWeight = $enrollment->classRoom?->start_date?->format('Y-m-d') ?? '9999-12-31';

                return $groupWeight . '-' . $dateWeight . '-' . sprintf('%010d', $enrollment->id);
            })
            ->values();
    }

    protected function buildSummary($enrollments): array
    {
        $ongoing = $enrollments->filter(fn (Enrollment $enrollment) => $this->classGroupStatus($enrollment) === 'ongoing');
        $completed = $enrollments->filter(fn (Enrollment $enrollment) => $this->classGroupStatus($enrollment) === 'completed');
        $grades = $enrollments->flatMap(fn (Enrollment $enrollment) => $enrollment->grades)
            ->filter(fn ($grade) => $grade->score !== null);

        return [
            'totalClasses' => $enrollments->count(),
            'ongoingClasses' => $ongoing->count(),
            'completedClasses' => $completed->count(),
            'averageGrade' => $grades->isNotEmpty() ? round((float) $grades->avg('score'), 2) : null,
        ];
    }

    protected function classGroupStatus(Enrollment $enrollment): string
    {
        return in_array($enrollment->normalizedStatus(), [Enrollment::STATUS_COMPLETED], true)
            || in_array($enrollment->classRoom?->status, [\App\Models\ClassRoom::STATUS_COMPLETED, \App\Models\ClassRoom::STATUS_CLOSED], true)
            ? 'completed'
            : 'ongoing';
    }
}
