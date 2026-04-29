@extends('bo_cuc.hoc_vien')

@section('title', 'Chi tiết xin phép nghỉ')
@section('eyebrow', 'Hỗ trợ học tập')

@section('content')
@php
    $badgeClasses = match ($leaveRequest->status) {
        \App\Models\YeuCauXinPhep::STATUS_ACCEPTED => 'bg-emerald-100 text-emerald-700',
        \App\Models\YeuCauXinPhep::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
        \App\Models\YeuCauXinPhep::STATUS_ACKNOWLEDGED => 'bg-cyan-100 text-cyan-700',
        default => 'bg-amber-100 text-amber-700',
    };
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <a href="{{ route('student.leave-requests.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại danh sách xin phép nghỉ
                </a>
                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Xin phép nghỉ</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Chi tiết yêu cầu xin phép nghỉ</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Theo dõi yêu cầu nghỉ học của bạn, xem giảng viên phụ trách và trạng thái xử lý mới nhất ngay tại đây.
                </p>
            </div>

            <div class="flex flex-col items-start gap-3">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ $leaveRequest->statusLabel() }}</span>
                <a href="{{ route('student.leave-requests.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    <i class="fas fa-file-circle-plus"></i>
                    Gửi xin phép mới
                </a>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(320px,0.75fr)]">
        <section class="space-y-6">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-xl font-semibold text-slate-900">Thông tin yêu cầu</h3>
                <div class="mt-5 grid gap-4 md:grid-cols-2 text-sm text-slate-600">
                    <p><strong>Lớp học:</strong> {{ $leaveRequest->targetLabel() }}</p>
                    <p><strong>Giảng viên:</strong> {{ $leaveRequest->teacher?->displayName() ?? 'Chưa phân công' }}</p>
                    <p><strong>Ngày xin phép:</strong> {{ $leaveRequest->attendance_date?->format('d/m/Y') ?? 'Chưa rõ' }}</p>
                    <p><strong>Lịch buổi học:</strong> {{ $leaveRequest->scheduleLabel() }}</p>
                    <p><strong>Ngày gửi:</strong> {{ optional($leaveRequest->created_at)->format('d/m/Y H:i') }}</p>
                    <p><strong>Cập nhật:</strong> {{ optional($leaveRequest->reviewed_at ?? $leaveRequest->updated_at)->format('d/m/Y H:i') }}</p>
                </div>

                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-700">
                    <p class="font-semibold text-slate-900">Lý do xin phép</p>
                    <p class="mt-2">{{ $leaveRequest->reason }}</p>
                </div>

                @if ($leaveRequest->note)
                    <div class="mt-4 rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-4 text-sm leading-6 text-cyan-900">
                        <p class="font-semibold">Ghi chú thêm</p>
                        <p class="mt-2">{{ $leaveRequest->note }}</p>
                    </div>
                @endif
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-xl font-semibold text-slate-900">Kết quả xử lý</h3>
                @if ($leaveRequest->isPending())
                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm leading-6 text-amber-800">
                        Yêu cầu của bạn đang chờ giảng viên phản hồi. Khi được xử lý, trạng thái sẽ cập nhật ở đây và bạn cũng sẽ nhận được thông báo trên hệ thống.
                    </div>
                @else
                    <div class="mt-5 grid gap-4 md:grid-cols-2 text-sm text-slate-600">
                        <p><strong>Người duyệt:</strong> {{ $leaveRequest->reviewer?->displayName() ?? $leaveRequest->teacher?->displayName() ?? 'Chưa xác định' }}</p>
                        <p><strong>Thời gian xử lý:</strong> {{ optional($leaveRequest->reviewed_at)->format('d/m/Y H:i') ?? 'Chưa có' }}</p>
                    </div>

                    @if ($leaveRequest->teacher_note)
                        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-700">
                            <p class="font-semibold text-slate-900">Ghi chú từ giảng viên</p>
                            <p class="mt-2">{{ $leaveRequest->teacher_note }}</p>
                        </div>
                    @endif
                @endif
            </article>
        </section>

        <aside class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Tóm tắt nhanh</h3>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Trạng thái</p>
                        <p class="mt-2 font-semibold text-slate-900">{{ $leaveRequest->statusLabel() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Lớp</p>
                        <p class="mt-2 font-semibold text-slate-900">{{ $leaveRequest->targetLabel() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Buổi học</p>
                        <p class="mt-2 font-semibold text-slate-900">{{ $leaveRequest->scheduleLabel() }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-6">
                <p class="text-sm font-semibold text-slate-900">Cần gửi thêm yêu cầu?</p>
                <p class="mt-2 text-sm leading-6 text-slate-600">Bạn có thể tạo một yêu cầu mới nếu có buổi nghỉ khác cần báo cho giảng viên.</p>
                <a href="{{ route('student.leave-requests.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-700 ring-1 ring-cyan-200 transition hover:bg-cyan-50">
                    Tạo yêu cầu mới
                </a>
            </div>
        </aside>
    </div>
</div>
@endsection
