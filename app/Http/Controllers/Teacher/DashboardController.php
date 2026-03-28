<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        [$user, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        return redirect()->route('teacher.courses');
    }
}
