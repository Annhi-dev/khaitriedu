@extends('bo_cuc.giao_vien')

@section('title', 'Xin phép nghỉ')
@section('eyebrow', 'Hỗ trợ học tập')

@section('content')
@php
    $requestList = method_exists($requests, 'getCollection') ? $requests->getCollection() : collect($requests);
    $statusOptions = \App\Models\LeaveRequest::statusOptions();
@endphp

<div class="space-y-6">
    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="border-b border-slate-100 p-6 lg:border-b-0 lg:border-r lg:p-7">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">Leave requests</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Yêu cầu xin phép nghỉ từ học viên</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                    Xem các yêu cầu nghỉ học mà học viên gửi cho bạn, duyệt hoặc từ chối ngay trong khu vực giảng viên.
                </p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white">
                        {{ $requests->total() }} yêu cầu
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700">
                        Bộ lọc trạng thái
                    </span>
                </div>
            </div>
            <div class="grid gap-3 bg-slate-50 p-6 sm:grid-cols-2 lg:grid-cols-1">
                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Đang hiển thị</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $requestList->count() }} mục</p>
                    <p class="mt-1 text-sm text-slate-500">Theo bộ lọc hiện tại</p>
                </div>
                <a href="{{ route('teacher.dashboard') }}" class="rounded-2xl border border-white bg-white p-4 shadow-sm transition hover:border-cyan-200 hover:bg-cyan-50/60">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Quay lại</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">Dashboard</p>
                    <p class="mt-1 text-sm text-slate-500">Xem tổng quan lớp học</p>
                </a>
            </div>
        </div>
    </section>

    <section class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="{{ route('teacher.leave-requests.index') }}" class="grid gap-4 md:grid-cols-[1fr_220px_auto] md:items-end">
            <div>
                <label class="text-sm font-medium text-slate-700">Tìm kiếm</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tên học viên, lớp, môn học, lý do..." class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Trạng thái</label>
                <select name="status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none">
                    <option value="">Tất cả trạng thái</option>
                    @foreach ($statusOptions as $status => $label)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">Lọc</button>
                <a href="{{ route('teacher.leave-requests.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="grid gap-4">
        @forelse($requestList as $leaveRequest)
            @php
                $badgeClasses = match ($leaveRequest->status) {
                    \App\Models\LeaveRequest::STATUS_ACCEPTED => 'bg-emerald-100 text-emerald-700',
                    \App\Models\LeaveRequest::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
                    \App\Models\LeaveRequest::STATUS_ACKNOWLEDGED => 'bg-cyan-100 text-cyan-700',
                    default => 'bg-amber-100 text-amber-700',
                };
            @endphp
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-semibold text-slate-900">{{ $leaveRequest->student?->displayName() ?? 'Học viên' }}</h3>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ $leaveRequest->statusLabel() }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">
                            Lớp: {{ $leaveRequest->targetLabel() }}
                            <span class="mx-2">•</span>
                            Ngày xin phép: {{ $leaveRequest->attendance_date?->format('d/m/Y') ?? 'Chưa rõ' }}
                            <span class="mx-2">•</span>
                            Lịch: {{ $leaveRequest->scheduleLabel() }}
                        </p>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-700">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Lý do</p>
                                <p class="mt-2">{{ $leaveRequest->reason }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-700">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Ghi chú thêm</p>
                                <p class="mt-2">{{ $leaveRequest->note ?: 'Không có ghi chú' }}</p>
                            </div>
                        </div>
                        @if ($leaveRequest->teacher_note)
                            <div class="mt-4 rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-3 text-sm leading-6 text-cyan-900">
                                <p class="font-semibold">Ghi chú xử lý</p>
                                <p class="mt-2">{{ $leaveRequest->teacher_note }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="flex shrink-0 flex-col gap-3 lg:items-end">
                        <a href="{{ route('teacher.leave-requests.show', $leaveRequest) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                            <span>Xem chi tiết</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                        <p class="text-xs text-slate-400">Gửi lúc {{ optional($leaveRequest->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center shadow-sm xl:col-span-2">
                <i class="fas fa-file-circle-exclamation mb-3 block text-4xl text-slate-300"></i>
                <p class="text-lg font-semibold text-slate-700">Chưa có yêu cầu xin phép nào.</p>
                <p class="mt-2 text-slate-500">Khi học viên gửi xin phép nghỉ, yêu cầu sẽ xuất hiện ở đây.</p>
            </div>
        @endforelse
    </section>

    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
        {{ $requests->links() }}
    </div>
</div>
@endsection
