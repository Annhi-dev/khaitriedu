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

        return view('trang_tinh.gioi_thieu', compact('studentCount', 'courseCount', 'teacherCount'));
    }

    public function contact()
    {
        return view('trang_tinh.lien_he');
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

        return view('trang_tinh.bai_viet', compact('posts'));
    }

    public function teachers()
    {
        $teachers = User::teachers()->get();

        return view('trang_tinh.giao_vien', compact('teachers'));
    }

    public function careers()
    {
        return view('trang_tinh.nghe_nghiep');
    }

    public function help()
    {
        return view('trang_tinh.tro_giup');
    }

    public function terms()
    {
        return view('trang_tinh.dieu_khoan');
    }

    public function privacy()
    {
        return view('trang_tinh.bao_mat');
    }

    public function showApplyTeacher()
    {
        return view('trang_tinh.ung_tuyen_giao_vien');
    }

    public function submitTeacherApplication(StoreTeacherApplicationRequest $request)
    {
        TeacherApplication::create($request->validated());

        return redirect()->route('apply-teacher')->with('status', 'Đã gửi hồ sơ ứng tuyển. Admin sẽ phản hồi sớm.');
    }
}
