<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\User;

class GradeController extends Controller
{
    public function index()
    {
        [$user, $redirect] = $this->requireRole(User::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        $grades = Grade::whereHas('enrollment', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['enrollment.course.subject', 'module'])->get();

        return view('student.grades', compact('user', 'grades'));
    }
}
