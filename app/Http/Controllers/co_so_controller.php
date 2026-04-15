<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Database\QueryException;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function sessionUser(): ?User
    {
        $user = Auth::user();
        $sessionUserId = session('user_id');

        if ($sessionUserId && (! $user || (int) $user->id !== (int) $sessionUserId)) {
            $user = User::with('role')->find($sessionUserId);

            if ($user) {
                Auth::setUser($user);
            }
        }

        return $user;
    }

    protected function requireRole(?string $role = null): array
    {
        $user = $this->sessionUser();

        if (! $user || ($role !== null && ! $user->hasRole($role))) {
            return [null, redirect()->route('login')];
        }

        return [$user, null];
    }

    protected function findSubjectEnrollment(int $userId, int $subjectId, array $with = []): ?Enrollment
    {
        return Enrollment::query()
            ->with($with)
            ->forUserSubject($userId, $subjectId)
            ->latest('id')
            ->first();
    }

    protected function isMissingTableException(QueryException $exception, array $tableNames = []): bool
    {
        $queryExceptionCode = (string) $exception->getCode();
        $driverErrorCode = (int) ($exception->errorInfo[1] ?? 0);
        $message = strtolower($exception->getMessage());

        if ($queryExceptionCode !== '42S02' && $driverErrorCode !== 1146 && ! str_contains($message, "doesn't exist")) {
            return false;
        }

        if ($tableNames === []) {
            return true;
        }

        foreach ($tableNames as $tableName) {
            if (str_contains($message, strtolower($tableName))) {
                return true;
            }
        }

        return false;
    }

    protected function resolveInternalClassAccess(int $courseId, ?User $user, array $with = []): array
    {
        $course = Course::with($with)->find($courseId);

        if (! $course) {
            return [null, null, redirect()->route('courses.index')->with('error', 'Lớp học không tồn tại.')];
        }

        if (! $user) {
            return [$course, null, redirect()->route('login')->with('error', 'Vui lòng đăng nhập để truy cập lớp học.')];
        }

        if ($user->isAdmin()) {
            return [$course, null, null];
        }

        if ($user->isTeacher()) {
            if ($course->teacher_id === $user->id) {
                return [$course, null, null];
            }

            return [$course, null, redirect()->route('teacher.courses')->with('error', 'Bạn không có quyền truy cập lớp học này.')];
        }

        if ($user->isStudent()) {
            $enrollment = Enrollment::with('assignedTeacher')
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->whereIn('status', Enrollment::courseAccessStatuses())
                ->latest('id')
                ->first();

            if ($enrollment) {
                if ($course->isPendingOpen()) {
                    return [
                        $course,
                        $enrollment,
                        redirect()->route('khoa-hoc.show', $course->subject_id)->with('error', 'Lop nay dang cho du hoc vien de mo chinh thuc. Ban se duoc thong bao ngay khi admin chot ngay khai giang.'),
                    ];
                }

                return [$course, $enrollment, null];
            }

            return [$course, null, redirect()->route('khoa-hoc.show', $course->subject_id)->with('error', 'Bạn chưa được xếp vào lớp học này. Hãy đăng ký ở trang khóa học và chờ admin duyệt.')];
        }

        return [$course, null, redirect()->route('dashboard')->with('error', 'Bạn không có quyền truy cập lớp học này.')];
    }
}
