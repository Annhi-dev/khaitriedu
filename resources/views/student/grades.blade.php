@extends('layouts.student')
@section('title', 'Điểm số của tôi')
@section('eyebrow', 'Academic Records')
@section('content')
<div class="max-w-6xl mx-auto">
  <div class="flex justify-between items-center mb-4">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Điểm số của tôi</h1>
      <p class="text-gray-600">Xem kết quả học tập của các khóa học.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn rounded-lg border border-gray-300 px-4 py-2">Quay lại dashboard</a>
  </div>

  <div class="grid gap-4">
    @forelse($grades as $grade)
      <div class="card bg-white p-4 rounded-xl shadow-sm">
        <h3 class="font-semibold text-lg mb-2">{{ $grade->enrollment->course->title }}</h3>
        <div class="grid md:grid-cols-3 gap-4 text-sm">
          <div>
            <p><strong>Module:</strong> {{ $grade->module->title ?? 'Tổng kết' }}</p>
            <p><strong>Điểm số:</strong> {{ $grade->score ?? 'Chưa có' }}</p>
          </div>
          <div>
            <p><strong>Điểm chữ:</strong> {{ $grade->grade ?? 'Chưa có' }}</p>
            <p><strong>Ngày cập nhật:</strong> {{ $grade->updated_at->format('d/m/Y') }}</p>
          </div>
          <div>
            <p><strong>Phản hồi:</strong></p>
            <p class="text-gray-700">{{ $grade->feedback ?: 'Không có' }}</p>
          </div>
        </div>
      </div>
    @empty
      <div class="text-center py-8">
        <p class="text-gray-500">Bạn chưa có điểm số nào.</p>
      </div>
    @endforelse
  </div>
</div>
@endsection
