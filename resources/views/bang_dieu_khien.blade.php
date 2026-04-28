@extends('bo_cuc.bang_dieu_khien')
@section('title', 'Dashboard')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-white p-8 rounded-2xl shadow-md">
        <h2 class="text-3xl font-bold text-primary-dark">Xin chào, {{ $user->name }}!</h2>
        <p class="text-gray-600 mt-2">Bạn đang đăng nhập với vai trò <span class="font-semibold text-primary">{{ $user->roleLabel() }}</span>.</p>

        @if($user->isAdmin())
            <div class="mt-4 p-4 bg-blue-50 text-blue-700 rounded-xl">Khu vực quản trị: người dùng, khóa học, lớp học và báo cáo.</div>
            <a href="{{ route('admin.dashboard') }}" class="btn inline-block mt-4 px-6 py-3 bg-primary text-white rounded-xl shadow hover:bg-primary-dark transition">Mở bảng quản trị</a>
        @elseif($user->isTeacher())
            <div class="mt-4 p-4 bg-green-50 text-green-700 rounded-xl">Khu vực giảng viên: lớp được phân công, bài học và điểm số.</div>
            <a href="{{ route('teacher.dashboard') }}" class="btn inline-block mt-4 px-6 py-3 bg-primary text-white rounded-xl shadow hover:bg-primary-dark transition">Mở khu giảng viên</a>
        @else
            <div class="mt-4 p-4 bg-yellow-50 text-yellow-700 rounded-xl">Khu vực học viên: khóa học, đăng ký lớp và lịch học cá nhân.</div>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <a href="{{ route('courses.index') }}" class="btn block px-4 py-3 bg-primary text-white rounded-xl shadow hover:bg-primary-dark transition text-center">Khám phá khóa học</a>
                <a href="{{ route('student.enroll.index') }}" class="btn block px-4 py-3 bg-indigo-500 text-white rounded-xl shadow hover:bg-indigo-600 transition text-center">Đăng ký học</a>
                <a href="{{ route('student.classes.index') }}" class="btn block px-4 py-3 bg-cyan-500 text-white rounded-xl shadow hover:bg-cyan-600 transition text-center">Lớp học của tôi</a>
                <a href="{{ route('student.schedule') }}" class="btn block px-4 py-3 bg-green-500 text-white rounded-xl shadow hover:bg-green-600 transition text-center">Lịch học</a>
                <a href="{{ route('student.grades') }}" class="btn block px-4 py-3 bg-blue-500 text-white rounded-xl shadow hover:bg-blue-600 transition text-center">Xem điểm số</a>
            </div>
        @endif

        <div class="mt-6">
            <a href="{{ route('home') }}" class="text-primary hover:underline">← Quay về trang chủ</a>
        </div>
    </div>
</div>
@endsection
