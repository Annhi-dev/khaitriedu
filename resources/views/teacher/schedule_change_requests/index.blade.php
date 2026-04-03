@extends('layouts.teacher')

@section('title', 'Yêu Cầu Đổi Lịch')
@section('eyebrow', 'Change Requests')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-700">History</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Lịch sử yêu cầu đổi lịch</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Theo dõi tất cả đề xuất mà bạn đã gửi cho admin, bao gồm cả yêu cầu từ lớp nội bộ và lớp học theo course.</p>
            </div>
            <a href="{{ route('teacher.schedules.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                Gửi yêu cầu mới
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <form method="get" action="{{ route('teacher.schedule-change-requests.index') }}" class="grid gap-4 md:grid-cols-[1fr_220px_auto] md:items-end">
            <div>
                <label class="text-sm font-medium text-slate-700">Tìm kiếm</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tên lớp, môn học, lý do..." class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Trạng thái</label>
                <select name="status" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                    <option value="">Tất cả trạng thái</option>
                    @foreach (\App\Models\ScheduleChangeRequest::filterableStatuses() as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Lọc</button>
                <a href="{{ route('teacher.schedule-change-requests.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse($requests as $scheduleChangeRequest)
            @php
                $badgeClasses = match ($scheduleChangeRequest->status) {
                    \App\Models\ScheduleChangeRequest::STATUS_APPROVED => 'bg-emerald-100 text-emerald-800',
                    \App\Models\ScheduleChangeRequest::STATUS_REJECTED => 'bg-rose-100 text-rose-800',
                    default => 'bg-amber-100 text-amber-800',
                };
            @endphp
            <article class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $scheduleChangeRequest->subjectName() }}</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ $scheduleChangeRequest->targetTitle() }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ $scheduleChangeRequest->categoryName() }}</p>
                        <p class="mt-2 text-xs uppercase tracking-[0.18em] text-slate-400">Gửi lúc {{ optional($scheduleChangeRequest->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ $scheduleChangeRequest->statusLabel() }}</span>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2 text-sm text-slate-600">
                    <div>
                        <p class="font-semibold text-slate-800">Lịch hiện tại</p>
                        <p class="mt-1 leading-6">{{ $scheduleChangeRequest->currentScheduleLabel() }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800">Lịch đề xuất</p>
                        <p class="mt-1 leading-6">{{ $scheduleChangeRequest->requestedScheduleLabel() }}</p>
                    </div>
                </div>

                @if ($scheduleChangeRequest->isClassScheduleRequest())
                    <div class="mt-4 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-800">Phòng đề xuất:</span> {{ $scheduleChangeRequest->requestedRoomLabel() }}</p>
                    </div>
                @endif

                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-800">Lý do</p>
                    <p class="mt-2 leading-6">{{ $scheduleChangeRequest->reason }}</p>
                </div>

                @if ($scheduleChangeRequest->admin_note)
                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-600">
                        <p class="font-semibold text-slate-800">Phản hồi từ admin</p>
                        <p class="mt-2 leading-6">{{ $scheduleChangeRequest->admin_note }}</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-slate-500 xl:col-span-2">
                Bạn chưa gửi yêu cầu đổi lịch nào.
            </div>
        @endforelse
    </section>

    @if ($requests->hasPages())
        <div>{{ $requests->links() }}</div>
    @endif
</div>
@endsection
