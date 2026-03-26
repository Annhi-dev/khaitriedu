<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Grade;

class StudentController extends Controller
{
    public function schedule()
    {
        [$user, $redirect] = $this->requireRole('hoc_vien');

        if ($redirect) {
            return $redirect;
        }

        $enrollments = Enrollment::where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->whereNotNull('course_id')
            ->with(['course.subject', 'assignedTeacher'])
            ->orderByDesc('id')
            ->get();

        return view('student.schedule', compact('user', 'enrollments'));
    }

    public function grades()
    {
        [$user, $redirect] = $this->requireRole('hoc_vien');

        if ($redirect) {
            return $redirect;
        }

        $grades = Grade::whereHas('enrollment', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['enrollment.course.subject', 'module'])->get();

        return view('student.grades', compact('user', 'grades'));
    }
}