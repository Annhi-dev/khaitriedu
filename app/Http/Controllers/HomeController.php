<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subject;
use App\Models\User;

class HomeController extends Controller
{
    public function home()
    {
        if ($this->sessionUser()) {
            return redirect()->route('dashboard');
        }

        $studentCount = User::where('role', 'hoc_vien')->count();
        $courseCount = Subject::count();
        $teacherCount = User::where('role', 'giang_vien')->count();
        $teachers = User::where('role', 'giang_vien')->limit(6)->get();
        $courses = Subject::with('category')
            ->withCount(['courses', 'enrollments'])
            ->orderBy('id', 'desc')
            ->limit(3)
            ->get();
        $categories = Category::withCount('subjects')->orderBy('order')->get();

        return view('home', compact('studentCount', 'courseCount', 'teacherCount', 'courses', 'teachers', 'categories'));
    }

    public function dashboard()
    {
        $user = $this->sessionUser();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'giang_vien') {
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