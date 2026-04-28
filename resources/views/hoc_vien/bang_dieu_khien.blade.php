@extends('bo_cuc.hoc_vien')

@section('title', 'Tổng quan học viên')
@section('eyebrow', 'Tổng quan')

@section('header_actions')
    <a href="{{ route('student.enroll.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
        <i class="fas fa-book-open"></i>
        <span>Đăng ký học</span>
    </a>
@endsection

@section('content')
@php
    $notificationList = collect($notifications ?? []);
    $totalNotifications = $notificationList->count();
    $readNotifications = $notificationList->where('is_read', true)->count();
    $unreadNotifications = $totalNotifications - $readNotifications;
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Tổng quan học viên</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">
                    Xin chào, {{ $user->name }}.
                </h2>
                <p class="mt-3 max-w-xl text-sm leading-7 text-slate-600">
                    Theo dõi khóa học, lịch học, lớp của tôi và kết quả học tập trong một giao diện gọn gàng, đồng nhất với phong cách quản trị.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[420px]">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Vai trò</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $user->roleLabel() }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Trạng thái</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $user->statusLabel() }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Thông báo</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $totalNotifications }}</p>
                    <p class="text-xs text-slate-500">{{ $unreadNotifications }} chưa đọc</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('student.enroll.index') }}" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700">
                <i class="fas fa-book-open"></i>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Đăng ký học</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">Khám phá các khóa học đang mở và gửi yêu cầu ngay.</p>
        </a>

        <a href="{{ route('student.classes.index') }}" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-700">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Lớp học của tôi</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">Theo dõi lớp đã đăng ký, đang học và đã hoàn thành.</p>
        </a>

        <a href="{{ route('student.schedule') }}" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                <i class="fas fa-calendar-days"></i>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Thời khóa biểu</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">Xem lịch học theo tuần, tháng hoặc năm.</p>
        </a>

        <a href="{{ route('student.grades') }}" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
                <i class="fas fa-square-poll-horizontal"></i>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Kết quả học tập</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">Xem điểm số và phản hồi theo từng khóa học.</p>
        </a>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Lối tắt thường dùng</h3>
                    <p class="mt-1 text-sm text-slate-500">Các thao tác hay dùng được đặt ngay bên dưới cho dễ bấm.</p>
                </div>
                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">Học viên</span>
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-2">
                <a href="{{ route('student.enroll.index') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    <i class="fas fa-compass mr-2 text-slate-400"></i>
                    Mở danh sách khóa học
                </a>
                <a href="{{ route('student.classes.index') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    <i class="fas fa-layer-group mr-2 text-slate-400"></i>
                    Xem lớp học của tôi
                </a>
                <a href="{{ route('student.schedule') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    <i class="fas fa-calendar-check mr-2 text-slate-400"></i>
                    Xem lịch học hiện tại
                </a>
                <a href="{{ route('home') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    <i class="fas fa-house mr-2 text-slate-400"></i>
                    Quay về website
                </a>
            </div>
        </div>

        <div id="thong-bao" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Thông báo gần đây</h3>
                    <p class="mt-1 text-sm text-slate-500">Những cập nhật mới nhất từ hệ thống.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $unreadNotifications }} chưa đọc</span>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($notificationList as $notification)
                    <a href="{{ route('student.notifications.show', $notification) }}" class="block rounded-2xl border {{ $notification->is_read ? 'border-slate-200 bg-slate-50' : 'border-cyan-200 bg-cyan-50/70' }} px-4 py-4 transition hover:border-cyan-300 hover:bg-cyan-50">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-medium text-slate-900">{{ $notification->title }}</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">{{ $notification->message }}</p>
                            </div>
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $notification->is_read ? 'bg-slate-300' : 'bg-cyan-500' }}"></span>
                        </div>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500">
                        Chưa có thông báo nào gửi đến bạn. Khi có cập nhật mới, hệ thống sẽ hiển thị tại đây.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
