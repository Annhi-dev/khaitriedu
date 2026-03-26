@extends('layouts.app')
@section('title', 'Báo cáo Admin')
@section('content')
<div class="max-w-4xl mx-auto mt-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Báo cáo hệ thống</h1>
            <p class="text-gray-600">Thống kê tổng quan cho quản trị viên.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn border border-gray-300 px-3 py-2 rounded-lg hover:bg-gray-100">Quay lại</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="card p-4 bg-white rounded-xl shadow">
            <p class="text-sm text-gray-500">Tổng số lớp học</p>
            <p class="text-3xl font-bold">{{ $courseCount }}</p>
        </div>
        <div class="card p-4 bg-white rounded-xl shadow">
            <p class="text-sm text-gray-500">Tổng số khóa học</p>
            <p class="text-3xl font-bold">{{ $subjectCount }}</p>
        </div>
        <div class="card p-4 bg-white rounded-xl shadow">
            <p class="text-sm text-gray-500">Tổng số học viên</p>
            <p class="text-3xl font-bold">{{ $studentCount }}</p>
        </div>
        <div class="card p-4 bg-white rounded-xl shadow">
            <p class="text-sm text-gray-500">Tổng số giảng viên</p>
            <p class="text-3xl font-bold">{{ $teacherCount }}</p>
        </div>
        <div class="card p-4 bg-white rounded-xl shadow">
            <p class="text-sm text-gray-500">Đăng ký chờ duyệt</p>
            <p class="text-3xl font-bold">{{ $pendingEnrollments }}</p>
        </div>
    </div>
</div>
@endsection