@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <!-- Sidebar -->
    <div class="lg:col-span-1">
        <div class="card bg-white p-6 rounded-2xl shadow-md">
            <h5 class="text-lg font-semibold text-primary-dark mb-4">Admin Panel</h5>
            <ul class="space-y-2">
                <li><a href="{{ route('admin.subjects') }}" class="block nav-link px-3 py-2 rounded-lg hover:bg-primary-light/30 transition">Quản lý môn học</a></li>
                <li><a href="{{ route('admin.courses') }}" class="block nav-link px-3 py-2 rounded-lg hover:bg-primary-light/30 transition">Quản lý khóa học</a></li>
                <li>
                  <a href="{{ route('admin.enrollments') }}" class="block nav-link px-3 py-2 rounded-lg hover:bg-primary-light/30 transition flex items-center gap-2">
                    Quản lý đăng ký
                    @if($newEnrollments > 0)
                      <span class="inline-flex items-center justify-center w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full">{{ $newEnrollments }}</span>
                    @endif
                  </a>
                </li>
                <li><a href="{{ route('admin.users') }}" class="block nav-link px-3 py-2 rounded-lg hover:bg-primary-light/30 transition">Quản lý người dùng</a></li>
            </ul>
        </div>
    </div>

    <!-- Main -->
    <div class="lg:col-span-3">
        <div class="card bg-white p-6 rounded-2xl shadow-md">
            <div class="flex flex-wrap justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-primary-dark">Admin Dashboard</h3>
                    <p class="text-gray-600">Xin chào, <span class="font-medium">{{ $user->name }}</span> (Admin).</p>
                </div>
                <a href="{{ route('logout') }}" class="btn px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition">Đăng xuất</a>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div class="card p-4 bg-primary-light/30 rounded-xl">
                    <h6 class="text-sm text-primary-dark font-medium">Khoá học</h6>
                    <p class="text-2xl font-bold text-primary-dark">{{ $courseCount ?? 0 }}</p>
                </div>
                <div class="card p-4 bg-primary-light/30 rounded-xl">
                    <h6 class="text-sm text-primary-dark font-medium">Môn học</h6>
                    <p class="text-2xl font-bold text-primary-dark">{{ $subjectCount ?? 0 }}</p>
                </div>
                <div class="card p-4 bg-primary-light/30 rounded-xl">
                    <h6 class="text-sm text-primary-dark font-medium">Học viên</h6>
                    <p class="text-2xl font-bold text-primary-dark">{{ $studentCount ?? 0 }}</p>
                </div>
                <div class="card p-4 bg-primary-light/30 rounded-xl">
                    <h6 class="text-sm text-primary-dark font-medium">Giảng viên</h6>
                    <p class="text-2xl font-bold text-primary-dark">{{ $teacherCount ?? 0 }}</p>
                </div>
            </div>

            <div class="mt-6">
                <h5 class="text-lg font-semibold text-primary-dark mb-3">Chức năng chính</h5>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.courses') }}" class="btn px-5 py-2 bg-primary text-white rounded-xl shadow hover:bg-primary-dark transition">Thêm khóa học</a>
                    <a href="{{ route('admin.enrollments') }}" class="btn px-5 py-2 border-2 border-primary text-primary rounded-xl hover:bg-primary-light/20 transition flex items-center gap-2">
                      Duyệt đăng ký
                      @if($newEnrollments > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full">{{ $newEnrollments }}</span>
                      @endif
                    </a>
                    <a href="{{ route('admin.report') }}" class="btn px-5 py-2 border border-gray-300 rounded-xl hover:bg-gray-100 transition">Báo cáo</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection