@extends('layouts.app')
@section('title','Khóa học giảng viên')
@section('content')
<div class="max-w-5xl mx-auto">
  <h1 class="text-2xl font-bold mb-3">Khóa học của bạn</h1>
  @if(session('status'))<div class="bg-green-100 text-green-800 p-2 rounded mb-3">{{ session('status') }}</div>@endif
  <div class="grid gap-3">
    @foreach($courses as $course)
      <a href="{{ route('teacher.course.show', $course->id) }}" class="card border p-3 rounded-lg hover:shadow">{{ $course->title }} <span class="text-xs text-gray-500">({{ $course->subject->name ?? 'N/A' }})</span></a>
    @endforeach
    @if($courses->isEmpty())<div class="p-3 bg-yellow-50 rounded">Chưa có khóa học được gán.</div>@endif
  </div>
  <div class="mt-5"><h2 class="text-xl font-semibold">Yêu cầu đăng ký</h2><table class="w-full border-collapse text-sm"><thead><tr class="bg-gray-100"><th class="border p-2">Học viên</th><th class="border p-2">Khóa học</th><th class="border p-2">Lịch</th><th class="border p-2">Trạng thái</th></tr></thead><tbody>@foreach($enrollments as $e)<tr><td class="border p-2">{{ $e->user->name ?? 'N/A' }}</td><td class="border p-2">{{ $e->course->title ?? 'N/A' }}</td><td class="border p-2">{{ $e->preferred_schedule }}</td><td class="border p-2">{{ $e->status }}</td></tr>@endforeach</tbody></table></div>
</div>
@endsection