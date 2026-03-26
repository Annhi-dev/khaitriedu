<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Subject;
use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PublicPageController extends Controller
{
    public function about()
    {
        $studentCount = User::where('role', 'hoc_vien')->count();
        $courseCount = Subject::count();
        $teacherCount = User::where('role', 'giang_vien')->count();

        return view('pages.about', compact('studentCount', 'courseCount', 'teacherCount'));
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function sendContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        Mail::raw("Từ: {$request->name} ({$request->email})\n\n{$request->message}", function ($message) use ($request) {
            $message->to('admin@khaitriedu.com')->subject("Liên hệ: {$request->subject}");
        });

        return back()->with('status', 'Tin nhắn đã được gửi thành công. Chúng tôi sẽ liên hệ lại trong sớm nhất.');
    }

    public function blog()
    {
        $posts = Announcement::where('status', 'published')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        return view('pages.blog', compact('posts'));
    }

    public function teachers()
    {
        $teachers = User::where('role', 'giang_vien')->get();

        return view('pages.teachers', compact('teachers'));
    }

    public function careers()
    {
        return view('pages.careers');
    }

    public function help()
    {
        return view('pages.help');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function showApplyTeacher()
    {
        return view('pages.apply-teacher');
    }

    public function submitTeacherApplication(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'experience' => 'nullable|string|max:2000',
            'message' => 'required|string|max:2000',
        ]);

        TeacherApplication::create($data);

        return redirect()->route('apply-teacher')->with('status', 'Đã gửi hồ sơ ứng tuyển. Admin sẽ phản hồi sớm.');
    }
}