@extends('bo_cuc.quan_tri')
@section('title', 'Chi tiết yêu cầu lịch học riêng')
@section('content')
@php
    $dayLabels = \App\Models\Course::dayOptions();
    $preferredDays = $enrollment->preferred_days;
    $selectedDays = is_array($preferredDays)
        ? $preferredDays
        : ((is_string($preferredDays) && $preferredDays !== '') ? (json_decode($preferredDays, true) ?: []) : []);
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('admin.enrollments') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách đăng ký học
            </a>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Chi tiết yêu cầu lịch học riêng #{{ $enrollment->id }}</h1>
            <p class="mt-2 text-sm text-slate-600">
                Hồ sơ chờ xếp khóa học triển khai. Duyệt để chuyển sang bước xử lý tiếp theo.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-quan_tri.huy_hieu :type="$enrollment->requestSourceBadgeType()" :text="$enrollment->requestSourceLabel()" />
            <span class="inline-flex rounded-full bg-cyan-50 px-4 py-2 text-sm font-semibold text-cyan-700">{{ $enrollment->statusLabel() }}</span>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(360px,0.9fr)]">
        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thông tin học viên</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Họ và tên</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->user?->name ?? 'Không có dữ liệu' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Email</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->user?->email ?? 'Không có dữ liệu' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Số điện thoại</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->user?->phone ?: 'Chưa cập nhật' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ngày gửi yêu cầu</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->submitted_at?->format('d/m/Y H:i') ?: optional($enrollment->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Nhu cầu học của học viên</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Khóa học công khai</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->subject?->name ?? 'Chưa xác định' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Nhóm học</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Khung giờ mong muốn</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->start_time ?: '--:--' }} - {{ $enrollment->end_time ?: '--:--' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ngày có thể học</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $selectedDays ? implode(', ', array_map(fn ($day) => $dayLabels[$day] ?? $day, $selectedDays)) : 'Chưa chọn ngày học' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ghi chú mong muốn</p>
                        <p class="mt-1 whitespace-pre-line text-sm font-medium text-slate-900">{{ $enrollment->preferred_schedule ?: 'Chưa có ghi chú thêm' }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thông tin xử lý gần nhất</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Trạng thái</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->statusLabel() }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Người xử lý</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->reviewer?->name ?? 'Chưa xử lý' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ghi chú admin</p>
                        <p class="mt-1 whitespace-pre-line text-sm font-medium text-slate-900">{{ $enrollment->note ?: 'Chưa có ghi chú.' }}</p>
                    </div>
                </div>
                @if ($enrollment->reviewed_at)
                    <p class="mt-4 text-xs text-slate-400">Cập nhật gần nhất lúc {{ $enrollment->reviewed_at->format('d/m/Y H:i') }}</p>
                @endif
            </section>
        </div>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Xử lý yêu cầu lịch học riêng</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Duyệt, ghi chú hoặc từ chối hồ sơ.
            </p>

            <a href="{{ route('admin.schedules.enrollments.show', $enrollment) }}" class="mt-4 inline-flex items-center justify-center rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 hover:bg-cyan-100">
                Mở màn xếp lịch
            </a>

            <div class="mt-4 rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-4 text-sm text-cyan-800">
                Hồ sơ chưa gắn lớp cố định.
            </div>

            <form method="post" action="{{ route('admin.enrollments.review', $enrollment) }}" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label class="text-sm font-medium text-slate-700">Ghi chú phản hồi</label>
                    <textarea name="note" rows="5" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Ghi chú cho học viên hoặc lý do từ chối.">{{ old('note', $enrollment->note) }}</textarea>
                    @error('note')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                @error('action')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror

                <div class="grid gap-3 sm:grid-cols-2">
                    <button name="action" value="approve" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Duyệt đăng ký</button>
                    <button name="action" value="request_update" class="inline-flex items-center justify-center rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 hover:bg-amber-100">Yêu cầu bổ sung</button>
                    <button name="action" value="reject" class="inline-flex items-center justify-center rounded-2xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 hover:bg-rose-100">Từ chối đăng ký</button>
                </div>
            </form>
        </aside>
    </div>
</div>
@endsection
