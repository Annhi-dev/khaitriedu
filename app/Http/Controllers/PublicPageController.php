<?php

namespace App\Http\Controllers;

use App\Http\Requests\Guest\StoreContactMessageRequest;
use App\Http\Requests\Guest\StoreTeacherApplicationRequest;
use App\Models\Announcement;
use App\Models\Subject;
use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class PublicPageController extends Controller
{
    public function about()
    {
        $studentCount = User::students()->count();
        $courseCount = Subject::count();
        $teacherCount = User::teachers()->count();

        return view('pages.about', compact('studentCount', 'courseCount', 'teacherCount'));
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function sendContact(StoreContactMessageRequest $request)
    {
        $data = $request->validated();

        Mail::raw("Từ: {$data['name']} ({$data['email']})\n\n{$data['message']}", function ($message) use ($data) {
            $message->to(config('site.contact.recipient'))->subject("Liên hệ: {$data['subject']}");
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
        $teachers = User::teachers()->get();

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

    public function submitTeacherApplication(StoreTeacherApplicationRequest $request)
    {
        TeacherApplication::create($request->validated());

        return redirect()->route('apply-teacher')->with('status', 'Đã gửi hồ sơ ứng tuyển. Admin sẽ phản hồi sớm.');
    }
}
