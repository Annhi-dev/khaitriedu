@extends('layouts.app')
@section('title','Danh sách khóa học')
@section('content')
<div class="max-w-5xl mx-auto">
  <div class="mb-4"><h1 class="text-2xl font-bold">Khóa học</h1><p class="text-gray-600">Xem chi tiết, module, và đăng ký</p></div>
  @if(session('status'))<div class="bg-green-100 text-green-800 p-2 rounded mb-3">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="bg-red-100 text-red-800 p-2 rounded mb-3">{{ session('error') }}</div>@endif
  <div class="grid md:grid-cols-2 gap-3">
    @foreach($courses as $c)
      <a href="{{ route('courses.show', $c->id) }}" class="card border p-3 rounded-xl hover:shadow">
        <div class="font-semibold">{{ $c->title }}</div>
        <div class="text-xs text-gray-500">Môn: {{ $c->subject->name ?? 'N/A' }}</div>
        <div class="text-xs text-gray-500">Giảng viên: {{ $c->teacher?->name ?? 'Chưa gán' }}</div>
        <div class="text-xs text-gray-500">Lịch: {{ $c->schedule ?? 'Chưa có' }}</div>
      </a>
    @endforeach
  </div>
</div>
@endsection