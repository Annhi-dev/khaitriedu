@extends('layouts.app')
@section('title', 'Quên mật khẩu')
@section('content')
<div class="max-w-md mx-auto">
    <div class="card bg-white p-8 rounded-2xl shadow-md">
        <h3 class="text-2xl font-bold text-primary-dark mb-6 text-center">Quên mật khẩu</h3>

        @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg">{{ session('status') }}</div>
        @endif

        <form action="{{ route('password.email') }}" method="post">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
            </div>
            <button type="submit" class="btn w-full py-3 bg-primary text-white rounded-xl shadow-md hover:bg-primary-dark transition">Gửi mã OTP xác minh</button>
        </form>

        <p class="text-sm text-center mt-4">
            Đã nhớ mật khẩu? <a href="{{ route('login') }}" class="text-primary hover:underline">Đăng nhập</a>
        </p>
    </div>
</div>
@endsection