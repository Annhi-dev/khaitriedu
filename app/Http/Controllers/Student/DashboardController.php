<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        [$user, $redirect] = $this->requireRole(User::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        $notifications = $user->notifications()
            ->latest('id')
            ->take(6)
            ->get();

        return view('student.dashboard', compact('user', 'notifications'));
    }
}
