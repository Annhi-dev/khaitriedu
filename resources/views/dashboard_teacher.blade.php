@extends('layouts.app')
@section('title', 'Giảng viên Dashboard')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <!-- Sidebar -->
    <div class="lg:col-span-1">
        <div class="card bg-white p-6 rounded-2xl shadow-md">
            <h5 class="text-lg font-semibold text-primary-dark mb-4">Giảng viên</h5>
            <ul class="space-y-2">
                <li><a href="#" class="block nav-link px-3 py-2 rounded-lg hover:bg-primary-light/30 transition">Khóa học của tôi</a></li>
                <li><a href="#" class="block nav-link px-3 py-2 rounded-lg hover:bg-primary-light/30 transition">Quản lý bài giảng</a></li>
                <li><a href="#" class="block nav-link px-3 py-2 rounded-lg hover:bg-primary-light/30 transition">Danh sách học viên</a></li>
            </ul>
        </div>
    </div>

    <!-- Main -->
    <div class="lg:col-span-3">
        <div class="card bg-white p-6 rounded-2xl shadow-md">
            <div class="flex flex-wrap justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-primary-dark">Giảng viên Dashboard</h3>
                    <p class="text-gray-600">Xin chào, <span class="font-medium">{{ $user->name }}</span>.</p>
                </div>
                <a href="{{ route('logout') }}" class="btn px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition">Đăng xuất</a>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div class="card p-4 bg-primary-light/30 rounded-xl">
                    <h6 class="text-sm text-primary-dark font-medium">Khóa học đang mở</h6>
                    <p class="text-2xl font-bold text-primary-dark">5 khóa</p>
                </div>
                <div class="card p-4 bg-primary-light/30 rounded-xl">
                    <h6 class="text-sm text-primary-dark font-medium">Bài giảng chờ</h6>
                    <p class="text-2xl font-bold text-primary-dark">8 bài</p>
                </div>
            </div>

            <div class="mt-6">
                <h5 class="text-lg font-semibold text-primary-dark mb-3">Công cụ nhanh</h5>
                <button class="btn px-6 py-2 bg-primary text-white rounded-xl shadow hover:bg-primary-dark transition">Tạo khóa học mới</button>
            </div>
        </div>
    </div>
</div>
@endsection