<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpHelper
{
    public static function sendOtp(string $email, string $type): string
    {
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        DB::table('otp_codes')->insert([
            'email' => $email,
            'code' => $code,
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(15),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        try {
            Mail::raw("Mã OTP của bạn là: $code (hết hạn 15 phút)", function ($message) use ($email, $type) {
                $message->to($email)->subject('KhaiTriEdu OTP ' . ($type === 'register' ? 'xác minh email' : 'quên mật khẩu'));
            });
        } catch (\Throwable $e) {
            Log::error('OTP mail send failed', ['email' => $email, 'type' => $type, 'error' => $e->getMessage()]);
        }
        return $code;
    }
}
