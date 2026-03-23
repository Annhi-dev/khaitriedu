@extends('layouts.app')
@section('title', 'Chi tiết khóa học')
@section('content')
<div class="max-w-4xl mx-auto">
  <a href="{{ route('teacher.courses') }}" class="text-primary hover:underline">← Quay lại</a>
  <div class="card p-4 rounded-xl shadow mt-3">
    <h1 class="text-2xl font-bold">{{ $course->title }}</h1>
    <div class="text-sm text-gray-500">Môn: {{ $course->subject->name ?? 'N/A' }}</div>
    <div class="mt-2">{{ $course->description }}</div>
    <div class="mt-4"><h3 class="font-semibold">Module</h3><ul class="mt-2 space-y-2">@foreach($course->modules as $m)<li class="border rounded p-2"><div class="font-medium">{{ $m->position }}. {{ $m->title }}</div><div class="text-gray-600 text-sm">{{ $m->content }}</div></li>@endforeach</ul></div>
    <div class="mt-4"><h3 class="font-semibold">Học viên đã duyệt</h3>
      @if($course->enrollments->where('status', 'confirmed')->isEmpty())
        <p class="text-gray-500 mt-2">Chưa có học viên nào được duyệt.</p>
      @else
        <div class="mt-2 space-y-3">
          @foreach($course->enrollments->where('status', 'confirmed') as $enrollment)
            <div class="border rounded p-3">
              <div class="flex justify-between items-start mb-2">
                <div>
                  <strong>{{ $enrollment->user->name }}</strong>
                  <div class="text-sm text-gray-600">Lịch: {{ $enrollment->schedule ?: 'Chưa có' }}</div>
                </div>
              </div>
              <div class="grid gap-2">
                @foreach($course->modules as $module)
                  @php $grade = \App\Models\Grade::where('enrollment_id', $enrollment->id)->where('module_id', $module->id)->first(); @endphp
                  <form method="post" action="{{ route('teacher.grades.update') }}" class="flex gap-2 items-center">
                    @csrf
                    <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}" />
                    <input type="hidden" name="module_id" value="{{ $module->id }}" />
                    <span class="text-sm w-32">{{ $module->title }}:</span>
                    <input name="score" value="{{ $grade->score ?? '' }}" placeholder="Điểm" class="border rounded px-2 py-1 w-20" type="number" min="0" max="100" />
                    <input name="grade" value="{{ $grade->grade ?? '' }}" placeholder="A/B/C" class="border rounded px-2 py-1 w-16" maxlength="5" />
                    <input name="feedback" value="{{ $grade->feedback ?? '' }}" placeholder="Phản hồi" class="border rounded px-2 py-1 flex-1" />
                    <button type="submit" class="btn bg-primary text-white px-3 py-1 rounded text-sm">Lưu</button>
                  </form>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>
@endsection