@extends('layouts.app')
@section('title', 'Lớp học phụ trách')
@section('content')
<div class="max-w-6xl mx-auto space-y-6">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Lớp học phụ trách</h1>
      <p class="text-gray-600">Danh sách các lớp nội bộ admin đã phân cho bạn giảng dạy.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Quay lại dashboard</a>
  </div>

  @if(session('status'))
    <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('status') }}</div>
  @endif

  @if(session('error'))
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
  @endif

  <section class="grid gap-4 lg:grid-cols-2">
    @forelse($courses as $course)
      <a href="{{ route('teacher.course.show', $course->id) }}" class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-primary hover:shadow-md">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $course->title }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $course->subject?->name ?? 'Chưa gắn khóa học' }}{{ $course->subject?->category ? ' - ' . $course->subject->category->name : '' }}</p>
          </div>
          <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $course->enrollments_count ?? 0 }} học viên</span>
        </div>
        <div class="mt-4 grid gap-2 text-sm text-gray-600 md:grid-cols-2">
          <p><strong>Lịch lớp:</strong> {{ $course->schedule ?: 'Chưa chốt' }}</p>
          <p><strong>Module:</strong> {{ $course->modules->count() }}</p>
        </div>
      </a>
    @empty
      <div class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-gray-500 lg:col-span-2">
        Chưa có lớp học nào được giao cho bạn.
      </div>
    @endforelse
  </section>

  <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-4">
      <h2 class="text-xl font-bold text-gray-900">Học viên đang theo các lớp của bạn</h2>
      <p class="text-gray-600">Xem nhanh thông tin xếp lớp và khung giờ học viên đã gửi cho admin.</p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse text-sm">
        <thead>
          <tr class="bg-slate-50 text-left">
            <th class="border-b p-3 font-semibold text-gray-700">Học viên</th>
            <th class="border-b p-3 font-semibold text-gray-700">Khóa học</th>
            <th class="border-b p-3 font-semibold text-gray-700">Lớp học</th>
            <th class="border-b p-3 font-semibold text-gray-700">Khung giờ mong muốn</th>
            <th class="border-b p-3 font-semibold text-gray-700">Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          @forelse($enrollments as $enrollment)
            @php
              $days = $enrollment->preferred_days ? json_decode($enrollment->preferred_days, true) : [];
              $labels = [
                'Monday' => 'T2',
                'Tuesday' => 'T3',
                'Wednesday' => 'T4',
                'Thursday' => 'T5',
                'Friday' => 'T6',
                'Saturday' => 'T7',
                'Sunday' => 'CN',
              ];
            @endphp
            <tr>
              <td class="border-b p-3 text-gray-700">{{ $enrollment->user?->name ?? 'N/A' }}</td>
              <td class="border-b p-3 text-gray-700">{{ $enrollment->course?->subject?->name ?? 'Chưa xác định' }}</td>
              <td class="border-b p-3 text-gray-700">{{ $enrollment->course?->title ?? 'Chưa xếp lớp' }}</td>
              <td class="border-b p-3 text-gray-700">
                @if($enrollment->start_time && $enrollment->end_time)
                  <div>{{ $enrollment->start_time }} - {{ $enrollment->end_time }}</div>
                  <div class="mt-1 text-xs text-gray-500">{{ $days ? implode(', ', array_map(fn ($day) => $labels[$day] ?? $day, $days)) : 'Không có ngày cụ thể' }}</div>
                @else
                  <div>{{ $enrollment->preferred_schedule ?: 'Chưa cung cấp' }}</div>
                @endif
              </td>
              <td class="border-b p-3">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $enrollment->status === 'confirmed' ? 'bg-green-100 text-green-800' : ($enrollment->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                  {{ $enrollment->status }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="p-6 text-center text-gray-500">Chưa có học viên nào trong các lớp bạn phụ trách.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
@endsection
