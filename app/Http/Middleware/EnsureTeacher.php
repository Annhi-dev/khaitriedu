<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeacher
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = session('user_id') ? User::find(session('user_id')) : null;

        if (! $user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để truy cập khu vực giảng viên.');
        }

        if (isset($user->status) && $user->status !== User::STATUS_ACTIVE) {
            session()->forget('user_id');

            return redirect()->route('login')->with('error', 'Tài khoản của bạn hiện không thể truy cập hệ thống.');
        }

        if ($user->role !== User::ROLE_TEACHER) {
            $route = $user->role === User::ROLE_ADMIN ? 'admin.dashboard' : 'dashboard';

            return redirect()->route($route)->with('error', 'Bạn không có quyền truy cập khu vực giảng viên.');
        }

        return $next($request);
    }
}
