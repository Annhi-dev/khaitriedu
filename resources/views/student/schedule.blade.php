@extends('layouts.student')
@section('title', 'Lich hoc cua toi')
@section('eyebrow', 'Student Schedule')
@section('content')
<div class="mx-auto max-w-6xl space-y-6">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Lich hoc cua toi</h1>
      <p class="text-gray-600">Theo doi ca lop da mo chinh thuc va lop dang cho du hoc vien de khai giang.</p>
    </div>
    <a href="{{ route('student.dashboard') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-primary hover:text-primary">Quay lai dashboard</a>
  </div>

  <div class="grid gap-4">
    @forelse($enrollments as $enrollment)
      @php
        $course = $enrollment->course;
        $waitingOpen = $course?->isPendingOpen();
      @endphp
      <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <div class="text-sm font-semibold uppercase tracking-wide text-primary">Lop hoc</div>
            <div class="mt-2 flex flex-wrap items-center gap-2">
              <h2 class="text-xl font-bold text-gray-900">{{ $course?->title }}</h2>
              <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $waitingOpen ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                {{ $waitingOpen ? $course->statusLabel() : $enrollment->statusLabel() }}
              </span>
            </div>
            <div class="mt-3 grid gap-2 text-sm text-gray-600 md:grid-cols-2">
              <p><strong>Khoa hoc:</strong> {{ $course?->subject?->name ?? 'Chua xac dinh' }}</p>
              <p><strong>Giang vien:</strong> {{ $enrollment->assignedTeacher->name ?? 'Chua phan cong' }}</p>
              <p><strong>Lich hoc:</strong> {{ $course?->formattedSchedule() ?? 'Chua co lich' }}</p>
              <p><strong>Trang thai:</strong> <span class="font-semibold {{ $waitingOpen ? 'text-amber-700' : 'text-green-700' }}">{{ $waitingOpen ? 'Dang cho mo lop' : $enrollment->statusLabel() }}</span></p>
            </div>

            @if ($waitingOpen)
              <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Lop nay da duoc ghep lich nhung chua chot ngay khai giang. Admin se thong bao cho ban ngay khi lop du toi thieu 5 hoc vien va duoc mo chinh thuc.
              </div>
            @endif
          </div>

          <div class="flex items-center gap-3">
            @if (! $waitingOpen)
              <a href="{{ route('courses.show', $course->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 font-semibold text-white transition hover:bg-primary-dark">
                <i class="fas fa-arrow-right"></i>
                Vao lop hoc
              </a>
            @else
              <span class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700">
                <i class="fas fa-hourglass-half"></i>
                Dang cho mo lop
              </span>
            @endif
          </div>
        </div>
      </div>
    @empty
      <div class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
        <p class="text-lg font-semibold text-gray-700">Ban chua duoc xep vao lop hoc nao.</p>
        <p class="mt-2 text-gray-500">Hay chon khoa hoc, gui khung gio mong muon va cho admin xep vao lop phu hop.</p>
        <a href="{{ route('student.enroll.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 font-semibold text-white transition hover:bg-primary-dark">
          <i class="fas fa-book-open"></i>
          Xem khoa hoc
        </a>
      </div>
    @endforelse
  </div>
</div>
@endsection
