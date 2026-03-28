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

        return view('dashboard', compact('user'));
    }
}
