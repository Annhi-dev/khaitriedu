@extends('layouts.admin')
@section('title', 'Yeu cau doi lich')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 10</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Yeu cau doi lich</h1>
            <p class="mt-2 text-sm text-slate-600">Admin xem, duyet hoac tu choi cac de xuat doi lich do giang vien gui len.</p>
        </div>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="{{ route('admin.schedule-change-requests.index') }}" class="grid gap-4 xl:grid-cols-[1fr_240px_auto] xl:items-end">
            <div>
                <label class="text-sm font-medium text-slate-700">Tim kiem</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Giang vien, lop hoc, khoa hoc, ly do..." class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Trang thai</label>
                <select name="status" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                    <option value="">Tat ca trang thai</option>
                    @foreach (\App\Models\ScheduleChangeRequest::filterableStatuses() as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Loc</button>
                <a href="{{ route('admin.schedule-change-requests.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Dat lai</a>
            </div>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse ($requests as $scheduleChangeRequest)
            @php
                $badgeClasses = match ($scheduleChangeRequest->status) {
                    \App\Models\ScheduleChangeRequest::STATUS_APPROVED => 'bg-emerald-100 text-emerald-800',
                    \App\Models\ScheduleChangeRequest::STATUS_REJECTED => 'bg-rose-100 text-rose-800',
                    default => 'bg-amber-100 text-amber-800',
                };
            @endphp
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $scheduleChangeRequest->course?->subject?->name ?? 'Chua gan khoa hoc' }}</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $scheduleChangeRequest->course?->title ?? 'Lop hoc da bi xoa' }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Giang vien: {{ $scheduleChangeRequest->teacher?->name ?? 'Khong co du lieu' }}</p>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ $scheduleChangeRequest->statusLabel() }}</span>
                </div>

                <div class="mt-5 grid gap-4 text-sm text-slate-600 md:grid-cols-2">
                    <div>
                        <p class="font-semibold text-slate-800">Lich hien tai</p>
                        <p class="mt-1 leading-6">{{ $scheduleChangeRequest->currentScheduleLabel() }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800">Lich de xuat</p>
                        <p class="mt-1 leading-6">{{ $scheduleChangeRequest->requestedScheduleLabel() }}</p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-800">Ly do giang vien</p>
                    <p class="mt-2 leading-6">{{ $scheduleChangeRequest->reason }}</p>
                </div>

                <div class="mt-5 flex items-center justify-between gap-3">
                    <p class="text-sm text-slate-500">Gui luc {{ optional($scheduleChangeRequest->created_at)->format('d/m/Y H:i') }}</p>
                    <a href="{{ route('admin.schedule-change-requests.show', $scheduleChangeRequest) }}" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Xem chi tiet</a>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-500 xl:col-span-2">
                Khong co yeu cau doi lich nao phu hop voi bo loc hien tai.
            </div>
        @endforelse
    </section>

    @if ($requests->hasPages())
        <div>{{ $requests->links() }}</div>
    @endif
</div>
@endsection