<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\OtpHelper;
use App\Http\Controllers\Controller;
use App\Models\VaiTro;
use App\Models\NguoiDung;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectAfterLogin(Auth::user());
        }

        return view('xac_thuc.dang_nhap');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required',
        ]);

        $throttleKey = 'login:' . Str::lower($request->input('login')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->with('error', "Quá nhiều lần thử đăng nhập. Vui lòng thử lại sau {$seconds} giây.");
        }

        $user = NguoiDung::where('email', $request->login)
            ->orWhere('username', $request->login)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return back()->with('error', 'Tên đăng nhập/email hoặc mật khẩu không đúng.');
        }

        if ($user->status !== NguoiDung::STATUS_ACTIVE) {
            return back()->with('error', 'Tài khoản của bạn hiện không thể đăng nhập. Vui lòng liên hệ quản trị viên.');
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user);

        $request->session()->regenerate();

        return $this->redirectAfterLogin($user);
    }

    public function showVerifyEmail()
    {
        return view('xac_thuc.xac_thuc_email');
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        $row = DB::table('otp_codes')
            ->where('email', $request->email)
            ->where('type', 'register')
            ->where('code', $request->code)
            ->where('expires_at', '>=', Carbon::now())
            ->first();

        if (! $row) {
            return back()->with('error', 'Mã OTP không đúng hoặc đã hết hạn.');
        }

        DB::table('otp_codes')->where('id', $row->id)->delete();
        $pending = session('pending_user');

        if (! $pending || $pending['email'] !== $request->email) {
            return redirect()->route('register')->with('error', 'Dữ liệu đăng ký không tồn tại hoặc đã hết hạn. Vui lòng đăng ký lại.');
        }

        if (NguoiDung::where('username', $pending['username'])->orWhere('email', $pending['email'])->exists()) {
            session()->forget('pending_user');

            return redirect()->route('login')->with('status', 'Tài khoản đã tồn tại. Vui lòng đăng nhập.');
        }

        NguoiDung::create([
            'username' => $pending['username'],
            'name' => $pending['name'],
            'email' => $pending['email'],
            'phone' => $pending['phone'],
            'password' => $pending['password_hash'],
            'role_id' => $pending['role_id'],
            'status' => NguoiDung::STATUS_ACTIVE,
            'email_verified_at' => Carbon::now(),
        ]);

        session()->forget('pending_user');

        return redirect()->route('login')->with('status', 'Xác nhận OTP thành công. Tài khoản đã được kích hoạt. Vui lòng đăng nhập.');
    }

    public function resendVerifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $pending = session('pending_user');

        if (! $pending || $pending['email'] !== $request->email) {
            return response()->json(['error' => 'Dữ liệu đăng ký không tồn tại hoặc đã hết hạn.'], 400);
        }

        OtpHelper::sendOtp($request->email, 'register');

        return response()->json(['message' => 'Mã OTP mới đã được gửi đến email của bạn.']);
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('xac_thuc.dang_ky');
    }

    public function register(Request $request)
    {
        $currentUser = Auth::user();
        $isAdminCreating = $currentUser && $currentUser->isAdmin();

        $rules = [
            'username' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:6',
        ];

        if ($isAdminCreating) {
            $rules['role'] = 'required|in:admin,teacher,student';
        }

        $data = $request->validate($rules);

        if (NguoiDung::where('username', $data['username'])->exists() || NguoiDung::where('email', $data['email'])->exists()) {
            return back()->withErrors(['email' => 'Tên đăng nhập hoặc email đã tồn tại.'])->withInput();
        }

        if ($isAdminCreating) {
            NguoiDung::create([
                'username' => $data['username'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'role_id' => VaiTro::idByName($data['role']),
                'status' => NguoiDung::STATUS_ACTIVE,
                'email_verified_at' => Carbon::now(),
            ]);

            return redirect()->route('admin.users')->with('status', 'Người dùng đã được tạo và kích hoạt ngay lập tức.');
        }

        session(['pending_user' => [
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password_hash' => Hash::make($data['password']),
            'role_id' => VaiTro::idByName(NguoiDung::ROLE_STUDENT),
        ]]);

        OtpHelper::sendOtp($data['email'], 'register');

        return redirect()->route('verify.email', ['email' => $data['email']])->with('status', 'Mã xác nhận đã gửi đến email.');
    }

    public function showForgotPassword()
    {
        return view('xac_thuc.mat_khau');
    }

    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = NguoiDung::where('email', $request->email)->first();

        if (! $user || ! $user->email_verified_at) {
            return back()->with('status', 'Nếu email tồn tại và đã được xác minh, chúng tôi đã gửi mã OTP.');
        }

        OtpHelper::sendOtp($request->email, 'password_reset');

        return redirect()->route('forgot.verify', ['email' => $request->email])->with('status', 'Mã OTP đã gửi đến email.');
    }

    public function showForgotVerify()
    {
        return view('xac_thuc.quen_xac_thuc');
    }

    public function verifyForgotOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        $row = DB::table('otp_codes')
            ->where('email', $request->email)
            ->where('type', 'password_reset')
            ->where('code', $request->code)
            ->where('expires_at', '>=', Carbon::now())
            ->first();

        if (! $row) {
            return back()->with('error', 'Mã OTP không đúng hoặc đã hết hạn.');
        }

        DB::table('otp_codes')->where('id', $row->id)->delete();

        return redirect()->route('forgot.reset', ['email' => $request->email])->with('status', 'Mã OTP hợp lệ. Nhập mật khẩu mới.');
    }

    public function showForgotReset()
    {
        return view('xac_thuc.dat_lai_mat_khau');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = NguoiDung::where('email', $request->email)->first();

        if (! $user) {
            return redirect()->route('forgot.reset', ['email' => $request->email])->with('status', 'Email không tồn tại.');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('login')->with('status', 'Mật khẩu đã đặt lại thành công. Đăng nhập lại.');
    }

    private function redirectAfterLogin(NguoiDung $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        }

        return redirect()->route('student.dashboard');
    }
}
