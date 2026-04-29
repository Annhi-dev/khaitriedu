<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\DiemSo;
use App\Models\NguoiDung;

class GradeController extends Controller
{
    public function index()
    {
        [$user, $redirect] = $this->requireRole(NguoiDung::ROLE_STUDENT);

        if ($redirect) {
            return $redirect;
        }

        $grades = DiemSo::whereHas('enrollment', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['enrollment.course.subject', 'module'])->get();

        return view('hoc_vien.diem_so', compact('user', 'grades'));
    }
}
