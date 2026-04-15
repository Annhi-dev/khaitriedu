@extends('bo_cuc.giao_vien')

@section('title', 'Dashboard')
@section('eyebrow', 'Teacher Workspace')

@section('content')
@php
    $pendingRequests = $requestUpdates->where('status', \App\Models\ScheduleChangeRequest::STATUS_PENDING)->count();
    $unreadNotifications = $notifications->where('is_read', false)->count();
@endphp

<div class="space-y-6">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl bg-slate-950 p-5 text-white shadow-xl shadow-slate-900/10">
            <p class="text-sm text-slate-300">Lớp đang phụ trách</p>
            <div class="mt-4 flex items-end justify-between">
                <p class="text-4xl font-semibold">{{ $classes->count() }}</p>
                <span class="rounded-full bg-white/10 px-3 py-1 text-xs text-slate-200">Classroom</span>
            </div>
        </article>

        <article class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm text-slate-500">Buổi dạy trong tuần</p>
            <div class="mt-4 flex items-end justify-between">
                <p class="text-4xl font-semibold text-slate-900">{{ $weekSchedule->count() }}</p>
                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-medium text-cyan-700">Weekly</span>
            </div>
        </article>

        <article class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm text-slate-500">Yêu cầu chờ duyệt</p>
            <div class="mt-4 flex items-end justify-between">
                <p class="text-4xl font-semibold text-slate-900">{{ $pendingRequests }}</p>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">Pending</span>
            </div>
        </article>

        <article class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm text-slate-500">Thông báo chưa đọc</p>
            <div class="mt-4 flex items-end justify-between">
                <p class="text-4xl font-semibold text-slate-900">{{ $unreadNotifications }}</p>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">Inbox</span>
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
        <div class="space-y-6">
            <article class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-700">Hôm nay</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Lịch giảng trong ngày</h2>
                    </div>
                    <a href="{{ route('teacher.schedules.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                        Xem toàn bộ lịch
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="mt-6 grid gap-4">
                    @forelse ($todaySchedule as $item)
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.22em] text-slate-400">{{ $item['starts_at']->translatedFormat('l, d/m/Y') }}</p>
                                    <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ $item['class_room']->displayName() }}</h3>
                                    <p class="mt-2 text-sm text-slate-500">{{ $item['class_room']->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                                </div>
                                <div class="rounded-2xl bg-white px-4 py-3 text-sm text-slate-600 ring-1 ring-slate-200">
                                    <p><strong>Giờ học:</strong> {{ $item['schedule']->timeRangeLabel() }}</p>
                                    <p class="mt-1"><strong>Phòng:</strong> {{ $item['room_label'] }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                            Hôm nay bạn chưa có buổi dạy nào được phân công.
                        </div>
                    @endforelse
                </div>
            </article>

            @include('dung_chung.luoi_lich_hoc', [
                'grid' => $weeklyTimetable,
                'sectionEyebrow' => 'Week View',
                'sectionTitle' => 'Khung tuần này',
                'sectionSubtitle' => 'Lịch lặp theo tuần của các lớp bạn đang phụ trách. Bam vao tung o de xem chi tiet va mo lop.',
                'emptyMessage' => 'Chua co buoi day nao trong tuan nay.',
            ])
        </div>

        <div class="space-y-6">
            <article class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Thông báo</h2>
                    </div>
                    <a href="{{ route('teacher.schedule-change-requests.index') }}" class="text-sm font-medium text-cyan-700 hover:text-cyan-800">Lịch sử</a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($notifications as $notification)
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $notification->title }}</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ $notification->message }}</p>
                                </div>
                                <span class="mt-1 h-2.5 w-2.5 rounded-full {{ $notification->is_read ? 'bg-slate-300' : 'bg-cyan-500' }}"></span>
                            </div>
                        </div>
                    @empty
                        @forelse ($requestUpdates as $requestItem)
                            @php
                                $badgeClasses = match ($requestItem->status) {
                                    \App\Models\ScheduleChangeRequest::STATUS_APPROVED => 'bg-emerald-100 text-emerald-700',
                                    \App\Models\ScheduleChangeRequest::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                            @endphp
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-medium text-slate-900">{{ $requestItem->targetTitle() }}</p>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ $requestItem->statusLabel() }}</span>
                                </div>
                                <p class="mt-2 text-sm leading-6 text-slate-500">{{ $requestItem->requestedScheduleLabel() }}</p>
                            </div>
                        @empty
                            <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                                Chưa có thông báo nào cho giảng viên.
                            </div>
                        @endforelse
                    @endforelse
                </div>
            </article>

            <article class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Lớp truy cập nhanh</h2>
                    </div>
                    <a href="{{ route('teacher.classes.index') }}" class="text-sm font-medium text-cyan-700 hover:text-cyan-800">Xem tất cả</a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($quickLinks as $classRoom)
                        <a href="{{ route('teacher.classes.show', $classRoom) }}" class="block rounded-2xl border border-slate-200 px-4 py-4 hover:border-cyan-200 hover:bg-cyan-50/50">
                            <p class="font-medium text-slate-900">{{ $classRoom->displayName() }}</p>
                            <p class="mt-2 text-sm text-slate-500">{{ $classRoom->scheduleSummary() }}</p>
                        </a>
                    @empty
                        <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                            Chưa có lớp học nào được giao.
                        </div>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
</div>
@endsection
