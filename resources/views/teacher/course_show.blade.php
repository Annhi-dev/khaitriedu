@extends('layouts.app')
@section('title', 'Chi tiết lớp học')
@section('content')
<div class="max-w-6xl mx-auto space-y-6">
  <a href="{{ route('teacher.courses') }}" class="inline-flex items-center gap-2 text-primary hover:gap-3 transition font-semibold">
    <i class="fas fa-arrow-left"></i>
    Quay lại lớp học phụ trách
  </a>

  @if(session('status'))
    <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('status') }}</div>
  @endif

  @if(session('error'))
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
  @endif

  <section class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-1 text-sm font-semibold text-blue-700">
          <i class="fas fa-chalkboard-user"></i>
          Lớp đang giảng dạy
        </span>
        <h1 class="mt-4 text-3xl font-bold text-gray-900">{{ $course->title }}</h1>
        <p class="mt-3 text-gray-600">{{ $course->description ?: 'Admin đã xếp lớp này cho bạn phụ trách sau khi duyệt yêu cầu của học viên.' }}</p>
      </div>
      <div class="rounded-2xl bg-slate-50 px-5 py-4 text-sm text-gray-600 shadow-sm">
        <div><strong>Khóa học:</strong> {{ $course->subject?->name ?? 'Chưa gắn khóa học' }}</div>
        <div class="mt-1"><strong>Nhóm học:</strong> {{ $course->subject?->category?->name ?? 'Chưa phân nhóm' }}</div>
        <div class="mt-1"><strong>Lịch lớp:</strong> {{ $course->formattedSchedule() }}</div>
      </div>
    </div>
  </section>

  <div class="flex flex-wrap gap-3">
    <a href="{{ route('teacher.schedule-change-requests.create', $course) }}" class="inline-flex items-center justify-center rounded-2xl bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark transition">Gui yeu cau doi lich</a>
    <a href="{{ route('teacher.schedule-change-requests.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Xem lich su yeu cau</a>
  </div>

  <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900">Module trong lớp</h2>
        <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">{{ $course->modules->count() }} module</span>
      </div>

      @forelse($course->modules as $module)
        <div class="mb-4 rounded-2xl border border-gray-200 p-4 last:mb-0">
          <div class="text-sm font-semibold uppercase tracking-wide text-primary">Module {{ $module->position ?? $loop->iteration }}</div>
          <h3 class="mt-1 text-lg font-bold text-gray-900">{{ $module->title }}</h3>
          <p class="mt-2 text-gray-600">{{ $module->content ?: 'Chưa có mô tả cho module này.' }}</p>
        </div>
      @empty
        <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-gray-500">
          Chưa có module nào trong lớp học này.
        </div>
      @endforelse
    </section>

    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
      @php $activeEnrollments = $course->enrollments; @endphp
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900">Học viên đã được xếp lớp</h2>
        <span class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-800">{{ $activeEnrollments->count() }} học viên</span>
      </div>

      @if($activeEnrollments->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-gray-500">
          Chưa có học viên nào được xếp vào lớp này.
        </div>
      @else
        <div class="space-y-4">
          @foreach($activeEnrollments as $enrollment)
            <div class="rounded-2xl border border-gray-200 p-4">
              <div>
                <div class="text-lg font-semibold text-gray-900">{{ $enrollment->user?->name }}</div>
                <div class="text-sm text-gray-500">{{ $enrollment->statusLabel() }} - Lịch đã chốt: {{ $enrollment->schedule ?: $course->formattedSchedule() }}</div>
              </div>

              @php
                $courseStat = $studentCourseProgress[$enrollment->id] ?? ['completed' => 0, 'total' => 0, 'percent' => 0];
              @endphp
              <div class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm">
                <div class="flex items-center justify-between">
                  <div class="font-semibold text-emerald-800">Tien do khoa hoc</div>
                  <div class="font-black text-emerald-700">{{ $courseStat['percent'] }}%</div>
                </div>
                <div class="mt-1 text-emerald-700">Da hoc {{ $courseStat['completed'] }}/{{ $courseStat['total'] }} bai.</div>
              </div>

              <div class="mt-4 space-y-3">
                @foreach($course->modules as $module)
                  @php
                    $grade = $gradeMap->get($enrollment->id . '-' . $module->id);
                    $moduleStat = $studentModuleProgress[$enrollment->id][$module->id] ?? [
                      'completed' => 0,
                      'total' => $module->lessons->count(),
                      'percent' => 0,
                    ];
                  @endphp
                  <form method="post" action="{{ route('teacher.grades.update') }}" class="grid gap-2 rounded-2xl bg-slate-50 p-3">
                    @csrf
                    <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}" />
                    <input type="hidden" name="module_id" value="{{ $module->id }}" />
                    <div class="flex items-center justify-between gap-3 text-sm">
                      <div class="font-semibold text-gray-700">{{ $module->title }}</div>
                      <div class="rounded-full bg-emerald-100 px-3 py-1 font-semibold text-emerald-700">
                        {{ $moduleStat['completed'] }}/{{ $moduleStat['total'] }} bai - {{ $moduleStat['percent'] }}%
                      </div>
                    </div>
                    <div class="grid gap-2 md:grid-cols-[120px_100px_1fr_auto]">
                      <input name="score" value="{{ $grade->score ?? '' }}" placeholder="Điểm" class="rounded-xl border border-gray-300 px-3 py-2" type="number" min="0" max="100" />
                      <input name="grade" value="{{ $grade->grade ?? '' }}" placeholder="A/B/C" class="rounded-xl border border-gray-300 px-3 py-2" maxlength="5" />
                      <input name="feedback" value="{{ $grade->feedback ?? '' }}" placeholder="Phản hồi cho học viên" class="rounded-xl border border-gray-300 px-3 py-2" />
                      <button type="submit" class="rounded-xl bg-primary px-4 py-2 font-semibold text-white hover:bg-primary-dark transition">Lưu</button>
                    </div>
                  </form>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </section>
  </div>
</div>
@endsection

