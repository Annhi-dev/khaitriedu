@extends('layouts.app')
@section('title','Quản lý khóa học')
@section('content')
<div class="max-w-6xl mx-auto">
  <div class="flex justify-between items-center mb-4">
    <div><h1 class="text-2xl font-bold">Quản lý khóa học</h1><p class="text-sm text-gray-600">Admin tạo khoá học, gán giảng viên và thêm module.</p></div>
    <a href="{{ route('admin.dashboard') }}" class="btn border border-gray-300 px-3 py-2 rounded">Quay lại</a>
  </div>

  @if(session('status'))<div class="bg-green-100 text-green-800 p-3 rounded mb-3">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mb-3">{{ session('error') }}</div>@endif

  <div class="card bg-white p-4 rounded-xl shadow-sm mb-4">
    <h3 class="font-semibold mb-2">Tạo khóa học mới</h3>
    <form class="grid lg:grid-cols-4 gap-2" method="post" action="{{ route('admin.courses.create') }}">
      @csrf
      <select name="subject_id" required class="border rounded px-2 py-2"><option value="">Chọn môn</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }} ({{ number_format($subject->price, 0, ',', '.') }} VND)</option>@endforeach</select>
      <input name="title" required placeholder="Tên khóa học" class="border rounded px-2 py-2" />
      <input name="description" placeholder="Mô tả" class="border rounded px-2 py-2" />
  </div>

  <div class="grid lg:grid-cols-2 gap-4">
    @foreach($courses as $course)
    <div class="card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
      <div class="flex justify-between items-start">
        <div>
          <div class="flex items-center gap-2">
            <div class="text-lg font-semibold">{{ $course->title }}</div>
            <span class="text-[10px] font-semibold bg-green-100 text-green-700 px-2 py-0.5 rounded">Khung xanh</span>
          </div>
          <div class="text-xs text-gray-500">Môn: {{ $course->subject->name ?? 'N/A' }}</div>
          <div class="text-xs text-gray-500">Giá: {{ number_format($course->subject?->price ?? 0, 0, ',', '.') }} VND</div>
          <div class="text-xs text-gray-500">Sỉ số học viên: {{ $course->enrollments->count() }}</div>
          <div class="text-xs text-gray-500">Giảng viên: {{ $course->teacher?->name ?? 'Chưa gán' }}</div>
          <div class="text-xs text-gray-500">Lịch: {{ $course->schedule ?? 'Chưa đặt' }}</div>
        </div>
        <div class="flex gap-1">
          <a href="{{ route('admin.course.show', $course->id) }}" class="px-2 py-1 text-xs bg-blue-500 text-white rounded">Sửa</a>
          <form method="post" action="{{ route('admin.courses.delete', $course->id) }}" onsubmit="return confirm('Xóa khóa học?');">
            @csrf
            <button type="submit" class="px-2 py-1 text-xs bg-red-500 text-white rounded">Xóa</button>
          </form>
        </div>
      </div>
      <div class="mt-2 text-sm text-gray-600">{{ $course->description ?? 'Không có mô tả' }}</div>
    </div>
    @endforeach
  </div>
</div>
@endsection