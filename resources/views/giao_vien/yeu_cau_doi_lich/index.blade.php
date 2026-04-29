@extends('bo_cuc.giao_vien')

@section('title', 'Yêu Cầu Dời Buổi')
@section('eyebrow', 'Yêu cầu dời buổi')

@section('content')
<div class="space-y-6">
    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="border-b border-slate-100 p-6 lg:border-b-0 lg:border-r lg:p-7">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">History</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Lịch sử yêu cầu dời buổi</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">Theo dõi các buổi đã xin dời, lịch dạy bù đã gửi và phản hồi từ admin theo từng trạng thái.</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white">
                        {{ $requests->count() }} yêu cầu
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700">
                        Bộ lọc lịch sử
                    </span>
                </div>
            </div>
            <div class="grid gap-3 bg-slate-50 p-6 sm:grid-cols-2 lg:grid-cols-1">
                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Đang hiển thị</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $requests->count() }} mục</p>
                    <p class="mt-1 text-sm text-slate-500">Theo bộ lọc hiện tại</p>
                </div>
                <a href="{{ route('teacher.schedules.index') }}" class="rounded-2xl border border-white bg-white p-4 shadow-sm transition hover:border-cyan-200 hover:bg-cyan-50/60">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Yêu cầu mới</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">Tạo yêu cầu</p>
                    <p class="mt-1 text-sm text-slate-500">Gửi từ lịch giảng dạy</p>
                </a>
            </div>
        </div>
    </section>

    <section class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="{{ route('teacher.schedule-change-requests.index') }}" class="grid gap-4 md:grid-cols-[1fr_220px_auto] md:items-end">
            <div>
                <label class="text-sm font-medium text-slate-700">Tìm kiếm</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tên lớp, môn học, lý do..." class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Trạng thái</label>
                <select name="status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none">
                    <option value="">Tất cả trạng thái</option>
                    @foreach (\App\Models\YeuCauDoiLich::filterableStatuses() as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">Lọc</button>
                <a href="{{ route('teacher.schedule-change-requests.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse($requests as $scheduleChangeRequest)
            @php
                $badgeClasses = match ($scheduleChangeRequest->status) {
                    \App\Models\YeuCauDoiLich::STATUS_APPROVED => 'bg-emerald-50 text-emerald-700',
                    \App\Models\YeuCauDoiLich::STATUS_REJECTED => 'bg-rose-50 text-rose-700',
                    default => 'bg-amber-50 text-amber-700',
                };
            @endphp
            <article class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-6">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">{{ $scheduleChangeRequest->subjectName() }}</p>
                            <h3 class="mt-2 text-xl font-semibold tracking-tight text-slate-950">{{ $scheduleChangeRequest->targetTitle() }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $scheduleChangeRequest->categoryName() }}</p>
                            <p class="mt-2 text-xs uppercase tracking-[0.18em] text-slate-400">Gửi lúc {{ optional($scheduleChangeRequest->created_at)->format('d/m/Y H:i') }}</p>
                        </div>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ $scheduleChangeRequest->statusLabel() }}</span>
                    </div>
                </div>

                <div class="space-y-3 p-6 text-sm text-slate-600">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Buổi bận / lịch gốc</p>
                            <p class="mt-2 leading-6 text-slate-800">{{ $scheduleChangeRequest->currentScheduleLabel() }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Buổi dạy bù / lịch mới</p>
                            <p class="mt-2 leading-6 text-slate-800">{{ $scheduleChangeRequest->requestedScheduleLabel() }}</p>
                        </div>
                    </div>

                    @if ($scheduleChangeRequest->isClassScheduleRequest())
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Phòng đề xuất</p>
                            <p class="mt-2 leading-6 text-slate-800">{{ $scheduleChangeRequest->requestedRoomLabel() }}</p>
                        </div>
                    @endif

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="font-semibold text-slate-800">Lý do</p>
                        <p class="mt-2 leading-6">{{ $scheduleChangeRequest->reason }}</p>
                    </div>

                    @if ($scheduleChangeRequest->admin_note)
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                            <p class="font-semibold text-slate-800">Phản hồi từ admin</p>
                            <p class="mt-2 leading-6">{{ $scheduleChangeRequest->admin_note }}</p>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-slate-500 xl:col-span-2">
                Bạn chưa gửi yêu cầu dời buổi nào.
            </div>
        @endforelse
    </section>

    @if ($requests->hasPages())
        <div>{{ $requests->links() }}</div>
    @endif
</div>
@endsection
