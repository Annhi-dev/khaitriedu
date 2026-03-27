<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class HomeController extends Controller
{
    public function home()
    {
        if ($this->sessionUser()) {
            return redirect()->route('dashboard');
        }

        $studentCount = User::where('role', User::ROLE_STUDENT)->count();
        $courseCount = Subject::visibleOnCatalog()->count();
        $teacherCount = User::where('role', User::ROLE_TEACHER)->count();
        $teachers = User::where('role', User::ROLE_TEACHER)->limit(6)->get();
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

        if ($user->role === User::ROLE_ADMIN) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === User::ROLE_TEACHER) {
            return redirect()->route('teacher.dashboard');
        }

        return view('dashboard', compact('user'));
    }

    public function logout()
    {
        session()->forget(['user_id']);

        return redirect()->route('home');
    }

    public function fallback()
    {
        return redirect()->route('home');
    }
}