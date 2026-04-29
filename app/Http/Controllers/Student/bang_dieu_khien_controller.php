<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;

class DashboardController extends Controller
{
    public function index()
    {
        [$user, $redirect] = $this->requireRole(NguoiDung::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        $notifications = $user->notifications()
            ->latest('id')
            ->take(6)
            ->get();

        return view('hoc_vien.bang_dieu_khien', compact('user', 'notifications'));
    }
}
