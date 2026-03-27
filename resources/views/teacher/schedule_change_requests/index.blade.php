@extends('layouts.app')
@section('title', 'Yeu cau doi lich')
@section('content')
<div class="max-w-6xl mx-auto space-y-6">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <p class="text-sm font-medium uppercase tracking-[0.2em] text-primary">Phase 10</p>
      <h1 class="mt-2 text-3xl font-bold text-gray-900">Yeu cau doi lich</h1>
      <p class="mt-2 text-gray-600">Theo doi cac de xuat doi lich ma ban da gui toi admin.</p>
    </div>
    <a href="{{ route('teacher.courses') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Quay lai lop hoc phu trach</a>
  </div>

  <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
    <form method="get" action="{{ route('teacher.schedule-change-requests.index') }}" class="grid gap-4 md:grid-cols-[1fr_220px_auto] md:items-end">
      <div>
        <label class="text-sm font-medium text-gray-700">Tim kiem</label>
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Ten lop hoc, khoa hoc, ly do..." class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Trang thai</label>
        <select name="status" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
          <option value="">Tat ca trang thai</option>
          @foreach (\App\Models\ScheduleChangeRequest::filterableStatuses() as $status)
            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
          @endforeach
        </select>
      </div>
      <div class="flex gap-3">
        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark transition">Loc</button>
        <a href="{{ route('teacher.schedule-change-requests.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Dat lai</a>
      </div>
    </form>
  </section>

  <section class="grid gap-4 lg:grid-cols-2">
    @forelse($requests as $scheduleChangeRequest)
      @php
        $badgeClasses = match ($scheduleChangeRequest->status) {
          \App\Models\ScheduleChangeRequest::STATUS_APPROVED => 'bg-emerald-100 text-emerald-800',
          \App\Models\ScheduleChangeRequest::STATUS_REJECTED => 'bg-rose-100 text-rose-800',
          default => 'bg-amber-100 text-amber-800',
        };
      @endphp
      <article class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
          <div>
            <p class="text-xs uppercase tracking-[0.2em] text-gray-400">{{ $scheduleChangeRequest->course?->subject?->name ?? 'Chua gan khoa hoc' }}</p>
            <h2 class="mt-2 text-xl font-semibold text-gray-900">{{ $scheduleChangeRequest->course?->title ?? 'Lop hoc da bi xoa' }}</h2>
            <p class="mt-2 text-sm text-gray-500">Gui luc {{ optional($scheduleChangeRequest->created_at)->format('d/m/Y H:i') }}</p>
          </div>
          <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ $scheduleChangeRequest->statusLabel() }}</span>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2 text-sm text-gray-600">
          <div>
            <p class="font-semibold text-gray-800">Lich hien tai</p>
            <p class="mt-1 leading-6">{{ $scheduleChangeRequest->currentScheduleLabel() }}</p>
          </div>
          <div>
            <p class="font-semibold text-gray-800">Lich de xuat</p>
            <p class="mt-1 leading-6">{{ $scheduleChangeRequest->requestedScheduleLabel() }}</p>
          </div>
        </div>

        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-gray-600">
          <p class="font-semibold text-gray-800">Ly do giang vien</p>
          <p class="mt-2 leading-6">{{ $scheduleChangeRequest->reason }}</p>
        </div>

        @if ($scheduleChangeRequest->admin_note)
          <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm text-gray-600">
            <p class="font-semibold text-gray-800">Phan hoi tu admin</p>
            <p class="mt-2 leading-6">{{ $scheduleChangeRequest->admin_note }}</p>
          </div>
        @endif
      </article>
    @empty
      <div class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-gray-500 lg:col-span-2">
        Ban chua gui yeu cau doi lich nao.
      </div>
    @endforelse
  </section>

  @if ($requests->hasPages())
    <div>{{ $requests->links() }}</div>
  @endif
</div>
@endsection