<?php

namespace App\Http\Controllers;

use App\Helpers\OtpHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->login)
            ->orWhere('username', $request->login)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Tên đăng nhập/email hoặc mật khẩu không đúng.');
        }

        if ($user->status !== User::STATUS_ACTIVE) {
            return back()->with('error', 'Tài khoản của bạn hiện không thể đăng nhập. Vui lòng liên hệ quản trị viên.');
        }

        session(['user_id' => $user->id]);

        return $this->redirectAfterLogin($user);
    }

    public function showVerifyEmail()
    {
        return view('auth.verify_email');
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

        if (User::where('username', $pending['username'])->orWhere('email', $pending['email'])->exists()) {
            session()->forget('pending_user');

            return redirect()->route('login')->with('status', 'Tài khoản đã tồn tại. Vui lòng đăng nhập.');
        }

        User::create([
            'username' => $pending['username'],
            'name' => $pending['name'],
            'email' => $pending['email'],
            'phone' => $pending['phone'],
            'password' => Hash::make($pending['password']),
            'role' => $pending['role'],
            'status' => User::STATUS_ACTIVE,
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
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,giang_vien,hoc_vien',
        ]);

        if (User::where('username', $data['username'])->exists() || User::where('email', $data['email'])->exists()) {
            return back()->withErrors(['email' => 'Tên đăng nhập hoặc email đã tồn tại.'])->withInput();
        }

        $current = $this->sessionUser();

        if ($current && $current->role === User::ROLE_ADMIN) {
            User::create([
                'username' => $data['username'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => Carbon::now(),
            ]);

            return redirect()->route('admin.users')->with('status', 'Người dùng đã được tạo và kích hoạt ngay lập tức.');
        }

        session(['pending_user' => [
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => $data['role'],
        ]]);

        OtpHelper::sendOtp($data['email'], 'register');

        return redirect()->route('verify.email', ['email' => $data['email']])->with('status', 'Mã xác nhận đã gửi đến email.');
    }

    public function showForgotPassword()
    {
        return view('auth.password');
    }

    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! $user->email_verified_at) {
            return back()->with('status', 'Nếu email tồn tại và đã được xác minh, chúng tôi đã gửi mã OTP.');
        }

        OtpHelper::sendOtp($request->email, 'password_reset');

        return redirect()->route('forgot.verify', ['email' => $request->email])->with('status', 'Mã OTP đã gửi đến email.');
    }

    public function showForgotVerify()
    {
        return view('auth.forgot_verify');
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
        return view('auth.reset_password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return redirect()->route('forgot.reset', ['email' => $request->email])->with('status', 'Email không tồn tại.');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('login')->with('status', 'Mật khẩu đã đặt lại thành công. Đăng nhập lại.');
    }

    private function redirectAfterLogin(User $user)
    {
        if ($user->role === User::ROLE_ADMIN) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === User::ROLE_TEACHER) {
            return redirect()->route('teacher.dashboard');
        }

        return redirect()->route('dashboard');
    }
}