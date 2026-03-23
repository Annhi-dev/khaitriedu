@extends('layouts.app')
@section('title', 'Đặt lại mật khẩu')
@section('content')
<div class="max-w-md mx-auto">
    <div class="card bg-white p-8 rounded-2xl shadow-md">
        <h3 class="text-2xl font-bold text-primary-dark mb-6 text-center">Đặt lại mật khẩu</h3>

        @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg">{{ session('status') }}</div>
        @endif

        <form action="{{ route('forgot.reset.post') }}" method="post">
            @csrf
            <input type="hidden" name="email" value="{{ request('email') }}">
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
            </div>
            <button type="submit" class="btn w-full py-3 bg-primary text-white rounded-xl shadow-md hover:bg-primary-dark transition">Lưu mật khẩu</button>
        </form>
    </div>
</div>
@endsection