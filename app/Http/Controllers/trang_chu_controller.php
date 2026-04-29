<?php

namespace App\Http\Controllers;

use App\Models\NhomHoc;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function home()
    {
        $this->sessionUser();

        $studentCount = NguoiDung::students()->count();
        $courseCount = MonHoc::visibleOnCatalog()->count();
        $teacherCount = NguoiDung::teachers()->count();
        $teachers = NguoiDung::teachers()->limit(6)->get();
        $courses = MonHoc::with('category')
            ->withCount(['courses', 'enrollments'])
            ->visibleOnCatalog()
            ->orderBy('id', 'desc')
            ->limit(3)
            ->get();
        $categories = NhomHoc::active()
            ->withCount([
                'subjects as subjects_count' => fn (Builder $query) => $query->publiclyAvailable(),
            ])
            ->orderBy('order')
            ->get();

        return view('trang_chu', compact('studentCount', 'courseCount', 'teacherCount', 'courses', 'teachers', 'categories'));
    }

    public function dashboard()
    {
        $user = $this->sessionUser();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        }

        return redirect()->route('student.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function fallback()
    {
        return redirect()->route('home');
    }
}
