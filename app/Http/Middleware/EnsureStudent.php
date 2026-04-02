<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudent
{
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
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để truy cập khu vực học viên.');
        }

        if (isset($user->status) && $user->status !== User::STATUS_ACTIVE) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')->with('error', 'Tài khoản của bạn hiện không thể truy cập hệ thống.');
        }

        if (! $user->isStudent()) {
            $route = $user->isAdmin() ? 'admin.dashboard' : 'teacher.dashboard';

            return redirect()->route($route)->with('error', 'Bạn không có quyền truy cập khu vực học viên.');
        }

        return $next($request);
    }
}
