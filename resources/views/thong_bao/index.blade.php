@extends($layout)

@section('title', $pageTitle)
@section('eyebrow', $pageEyebrow)

@section('content')
@php
    $notificationList = $notifications;
    $totalNotifications = $totalNotifications ?? (method_exists($notificationList, 'total') ? $notificationList->total() : collect($notificationList)->count());
    $unreadNotifications = $unreadNotifications ?? 0;
    $readNotifications = $totalNotifications - $unreadNotifications;
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">Hộp thông báo</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ $pageTitle }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Chạm vào từng thông báo để mở chi tiết và đồng bộ trạng thái đã đọc.</p>
            </div>

            <a href="{{ $backRoute }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                <i class="fas fa-arrow-left"></i>
                <span>{{ $backLabel }}</span>
            </a>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Tổng số</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $totalNotifications }}</p>
            </div>
            <div class="rounded-2xl border border-cyan-200 bg-cyan-50/70 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">Chưa đọc</p>
                <p class="mt-2 text-2xl font-semibold text-cyan-800">{{ $unreadNotifications }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Đã đọc</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ max(0, $readNotifications) }}</p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Danh sách thông báo</h3>
                <p class="mt-1 text-sm text-slate-500">Thông báo mới nhất sẽ nằm ở trên cùng.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $unreadNotifications }} chưa đọc</span>
        </div>

        <div class="mt-5 space-y-3">
            @forelse ($notifications as $notification)
                @php
                    $openUrl = route($openRouteName, $notification);
                @endphp
                <a href="{{ $openUrl }}" class="block rounded-2xl border {{ $notification->is_read ? 'border-slate-200 bg-slate-50' : 'border-cyan-200 bg-cyan-50/70' }} px-4 py-4 transition hover:border-cyan-300 hover:bg-cyan-50">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-medium text-slate-900">{{ $notification->title }}</p>
                                <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">
                                    {{ $notification->type ?? 'info' }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $notification->message }}</p>
                            <p class="mt-3 text-xs text-slate-400">
                                {{ optional($notification->created_at)->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $notification->is_read ? 'bg-slate-300' : 'bg-cyan-500' }}"></span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500">
                    {{ $emptyMessage }}
                </div>
            @endforelse
        </div>

        @if (method_exists($notifications, 'links'))
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
