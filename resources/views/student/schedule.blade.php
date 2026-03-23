@extends('layouts.app')
@section('title', 'Lịch học của tôi')
@section('content')
<div class="max-w-6xl mx-auto">
  <div class="flex justify-between items-center mb-4">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Lịch học của tôi</h1>
      <p class="text-gray-600">Xem thời khóa biểu các khóa học đã được duyệt.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn rounded-lg border border-gray-300 px-4 py-2">Quay lại dashboard</a>
  </div>

  @if(session('status'))<div class="alert alert-success mb-3">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger mb-3">{{ session('error') }}</div>@endif

  <div class="grid gap-4">
    @forelse($enrollments as $enrollment)
      <div class="card bg-white p-4 rounded-xl shadow-sm">
        <h3 class="font-semibold text-lg mb-2">{{ $enrollment->course->title }}</h3>
        <div class="grid md:grid-cols-2 gap-4 text-sm">
          <div>
            <p><strong>Môn học:</strong> {{ $enrollment->course->subject->name }}</p>
            <p><strong>Giảng viên:</strong> {{ $enrollment->assignedTeacher->name ?? 'Chưa phân công' }}</p>
          </div>
          <div>
            <p><strong>Lịch học:</strong></p>
            <p class="text-gray-700">{{ $enrollment->schedule ?: 'Chưa có lịch cụ thể' }}</p>
          </div>
        </div>
      </div>
    @empty
      <div class="text-center py-8">
        <p class="text-gray-500">Bạn chưa có khóa học nào được duyệt.</p>
        <a href="{{ route('courses.index') }}" class="btn bg-primary text-white rounded-xl px-4 py-2 mt-2">Xem khóa học</a>
      </div>
    @endforelse
  </div>
</div>
@endsection