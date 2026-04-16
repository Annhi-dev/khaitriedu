@extends('bo_cuc.quan_tri')
@section('title', 'Chi tiết yêu cầu dời buổi')
@section('content')
@php
    $badgeClasses = match ($scheduleChangeRequest->status) {
        \App\Models\ScheduleChangeRequest::STATUS_APPROVED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        \App\Models\ScheduleChangeRequest::STATUS_REJECTED => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-amber-200 bg-amber-50 text-amber-700',
    };
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('admin.schedule-change-requests.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách yêu cầu dời buổi
            </a>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Chi tiết yêu cầu dời buổi</h1>
            <p class="mt-2 text-sm text-slate-600">Admin xác nhận buổi bận của giảng viên và lịch dạy bù được đề xuất trước khi cập nhật hệ thống.</p>
        </div>
        <span class="inline-flex rounded-full border px-4 py-2 text-sm font-semibold {{ $badgeClasses }}">{{ $scheduleChangeRequest->statusLabel() }}</span>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)]">
        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thông tin giảng viên và lớp học</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2 text-sm text-slate-600">
                    <p><strong>Giang vien:</strong> {{ $scheduleChangeRequest->teacher?->displayName() ?? 'Khong co du lieu' }}</p>
                    <p><strong>Email:</strong> {{ $scheduleChangeRequest->teacher?->email ?? 'Khong co du lieu' }}</p>
                    <p><strong>Lop hoc:</strong> {{ $scheduleChangeRequest->targetTitle() }}</p>
                    <p><strong>Khoa hoc:</strong> {{ $scheduleChangeRequest->subjectName() }}</p>
                    <p class="md:col-span-2"><strong>Nhom hoc:</strong> {{ $scheduleChangeRequest->categoryName() }}</p>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thông tin buổi bận và dạy bù</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2 text-sm text-slate-600">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Buổi bận / lịch gốc</p>
                        <p class="mt-1 font-medium text-slate-900">{{ $scheduleChangeRequest->currentScheduleLabel() }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Buổi dạy bù / lịch mới</p>
                        <p class="mt-1 font-medium text-slate-900">{{ $scheduleChangeRequest->requestedScheduleLabel() }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Phòng của buổi bận</p>
                        <p class="mt-1 font-medium text-slate-900">
                            {{ $scheduleChangeRequest->isClassScheduleRequest() ? $scheduleChangeRequest->currentRoomLabel() : 'Không áp dụng' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Phòng cho buổi dạy bù</p>
                        <p class="mt-1 font-medium text-slate-900">
                            {{ $scheduleChangeRequest->isClassScheduleRequest() ? $scheduleChangeRequest->requestedRoomLabel() : 'Không áp dụng' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ngày gửi</p>
                        <p class="mt-1 font-medium text-slate-900">{{ optional($scheduleChangeRequest->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Người duyệt gần nhất</p>
                        <p class="mt-1 font-medium text-slate-900">{{ $scheduleChangeRequest->reviewer?->name ?? 'Chua co' }}</p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                    <p class="font-medium text-slate-800">Lý do giảng viên</p>
                    <p class="mt-2 leading-6">{{ $scheduleChangeRequest->reason }}</p>
                </div>

                @if ($scheduleChangeRequest->admin_note)
                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-600">
                        <p class="font-medium text-slate-800">Ghi chú admin</p>
                        <p class="mt-2 leading-6">{{ $scheduleChangeRequest->admin_note }}</p>
                    </div>
                @endif
            </section>
        </div>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Xử lý yêu cầu</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Nếu duyệt, hệ thống sẽ cập nhật buổi dạy bù và đồng bộ lịch mới cho học viên. Nếu từ chối, buổi bận hiện tại được giữ nguyên.</p>

            @if ($scheduleChangeRequest->isPending())
                <form method="post" action="{{ route('admin.schedule-change-requests.review', $scheduleChangeRequest) }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm font-medium text-slate-700">Ghi chú admin</label>
                        <textarea name="admin_note" rows="5" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Dùng cho ghi chú duyệt hoặc lý do từ chối.">{{ old('admin_note', $scheduleChangeRequest->admin_note) }}</textarea>
                        @error('admin_note')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    @error('action')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror

                    <div class="grid gap-3 sm:grid-cols-2">
                        <button name="action" value="approve" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Duyệt và cập nhật lịch</button>
                        <button name="action" value="reject" class="inline-flex items-center justify-center rounded-2xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 hover:bg-rose-100">Từ chối yêu cầu</button>
                    </div>
                </form>
            @else
                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                    <p class="font-medium text-slate-800">Yêu cầu đã được xử lý</p>
                    <p class="mt-2 leading-6">{{ $scheduleChangeRequest->reviewed_at ? 'Xử lý lúc ' . $scheduleChangeRequest->reviewed_at->format('d/m/Y H:i') : 'Không có mốc thời gian xử lý.' }}</p>
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection
