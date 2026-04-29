@extends('bo_cuc.hoc_vien')

@section('title', 'Xin phép nghỉ')
@section('eyebrow', 'Hỗ trợ học tập')

@section('header_actions')
    <a href="{{ route('student.leave-requests.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
        <i class="fas fa-file-circle-plus"></i>
        <span>Gửi xin phép</span>
    </a>
@endsection

@section('content')
@php
    $requestList = method_exists($requests, 'getCollection') ? $requests->getCollection() : collect($requests);
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Xin phép nghỉ</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Gửi và theo dõi yêu cầu xin phép nghỉ</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Khi bận hoặc bị ốm, bạn có thể gửi yêu cầu xin phép cho giảng viên phụ trách lớp. Trạng thái xử lý sẽ hiển thị ngay tại đây.
                </p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('student.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    <i class="fas fa-gauge-high"></i>
                    Tổng quan
                </a>
                <a href="{{ route('student.leave-requests.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    <i class="fas fa-paper-plane"></i>
                    Tạo yêu cầu
                </a>
            </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Tổng yêu cầu</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($requests->total()) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Chờ xử lý</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($requestList->where('status', \App\Models\YeuCauXinPhep::STATUS_PENDING)->count()) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Đã xử lý</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($requestList->filter(fn ($item) => $item->isResolved())->count()) }}</p>
            </div>
        </div>
    </section>

    <section class="space-y-4">
        @forelse($requestList as $leaveRequest)
            @php
                $badge = match ($leaveRequest->status) {
                    \App\Models\YeuCauXinPhep::STATUS_ACCEPTED => 'bg-emerald-100 text-emerald-700',
                    \App\Models\YeuCauXinPhep::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
                    \App\Models\YeuCauXinPhep::STATUS_ACKNOWLEDGED => 'bg-cyan-100 text-cyan-700',
                    default => 'bg-amber-100 text-amber-700',
                };
            @endphp
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-semibold text-slate-900">{{ $leaveRequest->targetLabel() }}</h3>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">{{ $leaveRequest->statusLabel() }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">
                            Ngày xin phép: {{ $leaveRequest->attendance_date?->format('d/m/Y') ?? 'Chưa rõ' }}
                            <span class="mx-2">•</span>
                            Lịch: {{ $leaveRequest->scheduleLabel() }}
                        </p>
                        <p class="mt-4 text-sm leading-6 text-slate-700">{{ $leaveRequest->reason }}</p>
                        @if($leaveRequest->note)
                            <p class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600">{{ $leaveRequest->note }}</p>
                        @endif
                    </div>

                    <div class="flex shrink-0 flex-col gap-3 lg:items-end">
                        <a href="{{ route('student.leave-requests.show', $leaveRequest) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                            <span>Xem chi tiết</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center shadow-sm">
                <i class="fas fa-file-circle-plus mb-3 block text-4xl text-slate-300"></i>
                <p class="text-lg font-semibold text-slate-700">Bạn chưa gửi yêu cầu xin phép nào.</p>
                <p class="mt-2 text-slate-500">Khi cần nghỉ học, bạn có thể tạo yêu cầu mới ở nút bên trên.</p>
                <a href="{{ route('student.leave-requests.create') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-3 font-semibold text-white transition hover:bg-cyan-700">
                    Gửi xin phép đầu tiên
                </a>
            </div>
        @endforelse
    </section>

    <x-quan_tri.phan_trang :paginator="$requests" label="yêu cầu xin phép" />
</div>
@endsection
