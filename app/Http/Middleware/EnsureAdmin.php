<?php

namespace App\Http\Middleware;

use App\Models\Enrollment;
use App\Models\ScheduleChangeRequest;
use App\Models\TeacherApplication;
use App\Models\User;
use App\Services\AdminScheduleConflictService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureAdmin
{
    public function __construct(protected AdminScheduleConflictService $scheduleConflictService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $sessionUserId = $request->session()->get('user_id');

        if ($sessionUserId && (! $user || (int) $user->id !== (int) $sessionUserId)) {
            $user = User::with('role')->find($sessionUserId);

            if ($user) {
                Auth::setUser($user);
            }
        }

        if (! $user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để truy cập khu vực quản trị.');
        }

        if (isset($user->status) && $user->status !== User::STATUS_ACTIVE) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')->with('error', 'Tài khoản của bạn hiện không thể truy cập hệ thống.');
        }

        if (! $user->isAdmin()) {
            $route = $user->isTeacher() ? 'teacher.dashboard' : 'dashboard';

            return redirect()->route($route)->with('error', 'Bạn không có quyền truy cập khu vực quản trị.');
        }

        try {
            $studentConflictCount = $scheduleConflictService->studentConflictPairCount();

            $adminSidebarBadges = [
                'teacher_applications_pending' => TeacherApplication::query()
                    ->where('status', TeacherApplication::STATUS_PENDING)
                    ->count(),
                'enrollments_pending' => Enrollment::query()
                    ->where('status', Enrollment::STATUS_PENDING)
                    ->count(),
                'schedule_change_requests_pending' => ScheduleChangeRequest::query()
                    ->where('status', ScheduleChangeRequest::STATUS_PENDING)
                    ->count(),
                'schedule_conflicts' => $studentConflictCount,
            ];
        } catch (Throwable $exception) {
            $adminSidebarBadges = [
                'teacher_applications_pending' => 0,
                'enrollments_pending' => 0,
                'schedule_change_requests_pending' => 0,
                'schedule_conflicts' => 0,
            ];
        }

        view()->share('adminAuthUser', $user);
        view()->share('adminSidebarBadges', $adminSidebarBadges);

        return $next($request);
    }
}
