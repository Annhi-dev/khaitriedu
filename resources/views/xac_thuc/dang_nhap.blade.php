@extends('bo_cuc.ung_dung')
@section('title', 'Đăng nhập')
@section('content')
<div class="max-w-md mx-auto">
    <div class="card bg-white p-8 rounded-2xl shadow-md">
        <h3 class="text-2xl font-bold text-primary-dark mb-6 text-center">Đăng nhập</h3>

        @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg">{{ session('error') }}</div>
        @endif

        <form action="{{ route('login.post') }}" method="post">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên đăng nhập hoặc Email</label>
                <input type="text" name="login" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
            </div>
            <button type="submit" class="btn w-full py-3 bg-primary text-white rounded-xl shadow-md hover:bg-primary-dark transition font-medium">Đăng nhập</button>
        </form>

        <div class="flex justify-between text-sm mt-4">
            <a href="{{ route('password.request') }}" class="text-primary hover:underline">Quên mật khẩu?</a>
            <a href="{{ route('register') }}" class="text-primary hover:underline">Đăng ký</a>
        </div>
    </div>
</div>
@endsection