<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();
        $sessionUserId = $request->session()->get('user_id');

        if ($sessionUserId && (! $user || (int) $user->id !== (int) $sessionUserId)) {
            $user = User::with('role')->find($sessionUserId);

            if ($user) {
                Auth::setUser($user);
            }
        }

        if ($user && ! $user->relationLoaded('role')) {
            $reloadedUser = User::with('role')->find($user->id);

            if ($reloadedUser) {
                $user = $reloadedUser;
                Auth::setUser($user);
            }
        }

        if (! $user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập.');
        }

        if (! $user->hasRole(...$roles)) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        return $next($request);
    }
}
