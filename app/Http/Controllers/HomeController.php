<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function home()
    {
        $this->sessionUser();

        $studentCount = User::students()->count();
        $courseCount = Subject::visibleOnCatalog()->count();
        $teacherCount = User::teachers()->count();
        $teachers = User::teachers()->limit(6)->get();
        $courses = Subject::with('category')
            ->withCount(['courses', 'enrollments'])
            ->visibleOnCatalog()
            ->orderBy('id', 'desc')
            ->limit(3)
            ->get();
        $categories = Category::active()
            ->withCount([
                'subjects as subjects_count' => fn (Builder $query) => $query->publiclyAvailable(),
            ])
            ->orderBy('order')
            ->get();

        return view('home', compact('studentCount', 'courseCount', 'teacherCount', 'courses', 'teachers', 'categories'));
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
