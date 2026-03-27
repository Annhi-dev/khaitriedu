@extends('layouts.app')
@section('title', 'Gui yeu cau doi lich')
@section('content')
@php
  $dayLabels = [
    'Monday' => 'Thu 2',
    'Tuesday' => 'Thu 3',
    'Wednesday' => 'Thu 4',
    'Thursday' => 'Thu 5',
    'Friday' => 'Thu 6',
    'Saturday' => 'Thu 7',
    'Sunday' => 'Chu nhat',
  ];
@endphp
<div class="max-w-6xl mx-auto space-y-6">
  <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
      <a href="{{ route('teacher.course.show', $course->id) }}" class="inline-flex items-center gap-2 text-primary hover:gap-3 transition font-semibold">
        <i class="fas fa-arrow-left"></i>
        Quay lai chi tiet lop hoc
      </a>
      <p class="mt-4 text-sm font-medium uppercase tracking-[0.2em] text-primary">Phase 10</p>
      <h1 class="mt-2 text-3xl font-bold text-gray-900">Gui yeu cau doi lich</h1>
      <p class="mt-2 text-gray-600">Giang vien de xuat lich moi, admin se la nguoi duyet va cap nhat lich chinh thuc.</p>
    </div>
    <a href="{{ route('teacher.schedule-change-requests.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Xem cac yeu cau da gui</a>
  </div>

  @error('course')
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
  @enderror

  <div class="grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(380px,0.95fr)]">
    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
      <h2 class="text-xl font-bold text-gray-900">Thong tin lop hoc</h2>
      <div class="mt-5 grid gap-4 text-sm text-gray-600 md:grid-cols-2">
        <p><strong>Lop hoc:</strong> {{ $course->title }}</p>
        <p><strong>Khoa hoc:</strong> {{ $course->subject?->name ?? 'Chua xac dinh' }}</p>
        <p><strong>Nhom hoc:</strong> {{ $course->subject?->category?->name ?? 'Chua phan nhom' }}</p>
        <p><strong>Lich hien tai:</strong> {{ $course->formattedSchedule() }}</p>
      </div>

      <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-gray-600">
        <p class="font-semibold text-gray-800">Luu y</p>
        <ul class="mt-2 space-y-2 leading-6">
          <li>1. Giang vien chi gui de xuat, admin moi la nguoi phe duyet.</li>
          <li>2. Neu admin duyet, lich lop va lich hoc vien se duoc cap nhat theo lich moi.</li>
          <li>3. Neu admin tu choi, lich cu duoc giu nguyen.</li>
        </ul>
      </div>

      <div class="mt-6">
        <h3 class="text-lg font-semibold text-gray-900">Lich su yeu cau cua lop nay</h3>
        <div class="mt-4 space-y-3">
          @forelse($course->scheduleChangeRequests as $scheduleChangeRequest)
            @php
              $badgeClasses = match ($scheduleChangeRequest->status) {
                \App\Models\ScheduleChangeRequest::STATUS_APPROVED => 'bg-emerald-100 text-emerald-800',
                \App\Models\ScheduleChangeRequest::STATUS_REJECTED => 'bg-rose-100 text-rose-800',
                default => 'bg-amber-100 text-amber-800',
              };
            @endphp
            <div class="rounded-2xl border border-gray-200 px-4 py-4 text-sm text-gray-600">
              <div class="flex items-center justify-between gap-3">
                <p class="font-semibold text-gray-800">{{ optional($scheduleChangeRequest->created_at)->format('d/m/Y H:i') }}</p>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ $scheduleChangeRequest->statusLabel() }}</span>
              </div>
              <p class="mt-2"><strong>De xuat:</strong> {{ $scheduleChangeRequest->requestedScheduleLabel() }}</p>
              <p class="mt-1"><strong>Ly do:</strong> {{ $scheduleChangeRequest->reason }}</p>
              @if ($scheduleChangeRequest->admin_note)
                <p class="mt-1"><strong>Phan hoi admin:</strong> {{ $scheduleChangeRequest->admin_note }}</p>
              @endif
            </div>
          @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-gray-500">
              Chua co yeu cau doi lich nao cho lop hoc nay.
            </div>
          @endforelse
        </div>
      </div>
    </section>

    <aside class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
      <h2 class="text-xl font-bold text-gray-900">Thong tin de xuat moi</h2>
      <form method="post" action="{{ route('teacher.schedule-change-requests.store', $course) }}" class="mt-5 space-y-4">
        @csrf
        <div>
          <label class="text-sm font-medium text-gray-700">Ngay hoc trong tuan</label>
          <select name="requested_day_of_week" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            @foreach ($dayLabels as $value => $label)
              <option value="{{ $value }}" @selected(old('requested_day_of_week', $course->day_of_week) === $value)>{{ $label }}</option>
            @endforeach
          </select>
          @error('requested_day_of_week')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="text-sm font-medium text-gray-700">Ngay bat dau</label>
            <input type="date" name="requested_date" value="{{ old('requested_date', optional($course->start_date)->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            @error('requested_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Ngay ket thuc</label>
            <input type="date" name="requested_end_date" value="{{ old('requested_end_date', optional($course->end_date)->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            @error('requested_end_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Gio bat dau</label>
            <input type="time" name="requested_start_time" value="{{ old('requested_start_time', $course->start_time) }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            @error('requested_start_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Gio ket thuc</label>
            <input type="time" name="requested_end_time" value="{{ old('requested_end_time', $course->end_time) }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            @error('requested_end_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
          </div>
        </div>

        <div>
          <label class="text-sm font-medium text-gray-700">Ly do doi lich</label>
          <textarea name="reason" rows="5" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Mo ta ly do can doi lich de admin xem xet.">{{ old('reason') }}</textarea>
          @error('reason')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-primary px-4 py-3 text-sm font-semibold text-white hover:bg-primary-dark transition">Gui yeu cau toi admin</button>
      </form>
    </aside>
  </div>
</div>
@endsection