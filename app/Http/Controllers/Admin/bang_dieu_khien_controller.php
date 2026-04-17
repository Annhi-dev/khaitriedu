<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminDashboardService;

class DashboardController extends Controller
{
    public function index(AdminDashboardService $dashboardService)
    {
        [$user, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('quan_tri.bang_dieu_khien.index', array_merge(
            [
                'user' => $user,
                'notifications' => $user->notifications()
                    ->latest('id')
                    ->take(6)
                    ->get(),
            ],
            $dashboardService->overview()
        ));
    }
}
