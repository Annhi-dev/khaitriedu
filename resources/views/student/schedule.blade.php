@extends('layouts.app')
@section('title', 'Lịch học của tôi')
@section('content')
<div class="max-w-6xl mx-auto space-y-6">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Lịch học của tôi</h1>
      <p class="text-gray-600">Các lớp học admin đã xếp cho bạn sau khi duyệt yêu cầu đăng ký khóa học.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Quay lại dashboard</a>
  </div>

  @if(session('status'))
    <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('status') }}</div>
  @endif

  @if(session('error'))
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
  @endif

  <div class="grid gap-4">
    @forelse($enrollments as $enrollment)
      <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <div class="text-sm font-semibold uppercase tracking-wide text-primary">Lớp học</div>
            <h2 class="mt-1 text-xl font-bold text-gray-900">{{ $enrollment->course->title }}</h2>
            <div class="mt-3 grid gap-2 text-sm text-gray-600 md:grid-cols-2">
              <p><strong>Khóa học:</strong> {{ $enrollment->course->subject->name ?? 'Chưa xác định' }}</p>
              <p><strong>Giảng viên:</strong> {{ $enrollment->assignedTeacher->name ?? 'Chưa phân công' }}</p>
              <p><strong>Lịch học:</strong> {{ $enrollment->schedule ?: 'Chưa có lịch cụ thể' }}</p>
              <p><strong>Trạng thái:</strong> <span class="font-semibold text-green-700">Đã được xếp lớp</span></p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <a href="{{ route('courses.show', $enrollment->course->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 font-semibold text-white hover:bg-primary-dark transition">
              <i class="fas fa-arrow-right"></i>
              Vào lớp học
            </a>
          </div>
        </div>
      </div>
    @empty
      <div class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
        <p class="text-lg font-semibold text-gray-700">Bạn chưa được xếp vào lớp học nào.</p>
        <p class="mt-2 text-gray-500">Hãy chọn khóa học, gửi khung giờ mong muốn và chờ admin duyệt để xếp lớp phù hợp.</p>
        <a href="{{ route('courses.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark transition">
          <i class="fas fa-book-open"></i>
          Xem khóa học
        </a>
      </div>
    @endforelse
  </div>
</div>
@endsection
