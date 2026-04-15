@extends('layouts.admin')
@section('title', 'Chi tiết lịch học')
@section('content')
@php
    $capacity = max(1, (int) ($course->capacity ?? $classRoom?->room?->capacity ?? 20));
    $occupiedSeats = (int) $scheduledStudentsCount;
    $occupancyPercent = min(100, (int) round(($occupiedSeats / $capacity) * 100));
    $remainingSeats = max(0, $capacity - $occupiedSeats);
    $teacherName = $course->teacher?->displayName() ?? 'Chưa phân công';
    $teacherEmail = $course->teacher?->email ?? 'Chưa có email';
    $roomLabel = $classRoom?->room
        ? $classRoom->room->name . ($classRoom->room->code ? ' (' . $classRoom->room->code . ')' : '')
        : 'Chưa phân phòng';
    $classStatusType = match ($classRoom?->status) {
        \App\Models\ClassRoom::STATUS_OPEN => 'success',
        \App\Models\ClassRoom::STATUS_FULL => 'warning',
        \App\Models\ClassRoom::STATUS_COMPLETED => 'info',
        \App\Models\ClassRoom::STATUS_CLOSED => 'default',
        default => 'default',
    };
@endphp

<div class="space-y-6">
    <section class="overflow-hidden rounded-3xl border border-cyan-200 bg-gradient-to-br from-cyan-600 via-sky-600 to-blue-700 text-white shadow-xl">
        <div class="grid gap-6 p-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(320px,0.75fr)] xl:p-8">
            <div>
                <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-50/95 hover:text-white">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại lịch học toàn hệ thống
                </a>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-50">
                        {{ $course->subject?->category?->name ?? 'Chưa phân nhóm' }}
                    </span>
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-cyan-50">
                        {{ $course->subject?->name ?? 'Chưa có môn học' }}
                    </span>
                </div>

                <h1 class="mt-4 text-3xl font-bold tracking-tight sm:text-4xl">{{ $course->title }}</h1>
                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="rounded-full bg-white px-3 py-1.5 text-sm font-semibold text-cyan-700">{{ $course->statusLabel() }}</span>
                    <span class="rounded-full bg-white/15 px-3 py-1.5 text-sm font-semibold text-white">{{ $course->meetingDaysLabel() }}</span>
                    <span class="rounded-full bg-white/15 px-3 py-1.5 text-sm font-semibold text-white">{{ $course->formattedSchedule() }}</span>
                </div>
            </div>

            <div class="rounded-3xl bg-white/10 p-5 backdrop-blur">
                <p class="text-xs uppercase tracking-[0.22em] text-cyan-100">Tổng quan lớp</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-white/15 p-4">
                        <p class="text-xs uppercase tracking-wide text-cyan-100">Giảng viên</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $teacherName }}</p>
                        <p class="mt-1 text-xs text-cyan-100">{{ $teacherEmail }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/15 p-4">
                        <p class="text-xs uppercase tracking-wide text-cyan-100">Phòng học</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $roomLabel }}</p>
                        <p class="mt-1 text-xs text-cyan-100">{{ $classRoom?->statusLabel() ?? 'Chưa tạo lớp' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-950/20 p-4">
                        <p class="text-xs uppercase tracking-wide text-cyan-100">Buổi học</p>
                        <p class="mt-2 text-2xl font-bold text-white">{{ $classSchedules->count() }}</p>
                        <p class="mt-1 text-xs text-cyan-100">Buổi học chính thức</p>
                    </div>
                    <div class="rounded-2xl bg-slate-950/20 p-4">
                        <p class="text-xs uppercase tracking-wide text-cyan-100">Còn trống</p>
                        <p class="mt-2 text-2xl font-bold text-white">{{ $remainingSeats }}</p>
                        <p class="mt-1 text-xs text-cyan-100">/ {{ $capacity }} chỗ</p>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="flex items-center justify-between text-xs uppercase tracking-[0.18em] text-cyan-100">
                        <span>Độ đầy</span>
                        <span>{{ $occupancyPercent }}%</span>
                    </div>
                    <div class="mt-2 h-2 rounded-full bg-white/15">
                        <div class="h-2 rounded-full bg-white" style="width: {{ $occupancyPercent }}%"></div>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    @if($classRoom)
                        <a href="{{ route('admin.classes.show', $classRoom) }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-700 hover:bg-cyan-50">
                            Mở màn lớp học
                        </a>
                    @endif

                    <a href="{{ route('admin.schedules.conflicts', array_filter([
                        'course_id' => $course->id,
                        'class_room_id' => $classRoom?->id,
                    ])) }}" class="inline-flex items-center justify-center rounded-2xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/20">
                        Kiểm tra xung đột
                    </a>

                    @if($course->isPendingOpen())
                        <a href="{{ route('admin.schedules.courses.open', $course) }}" class="inline-flex items-center justify-center rounded-2xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/20">
                            Chốt ngày khai giảng
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.18fr)_minmax(320px,0.82fr)]">
        <section class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Lớp hiện hành</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $classRoom?->displayName() ?? 'Chưa có lớp học' }}</h2>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Trạng thái</p>
                        <div class="mt-2">
                            @if($classRoom)
                                <x-admin.badge :type="$classStatusType" :text="$classRoom->statusLabel()" />
                            @else
                                <x-admin.badge type="default" text="Chưa tạo lớp" />
                            @endif
                        </div>
                    </div>
                </div>

                @if($classRoom)
                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Phòng học</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $roomLabel }}</p>
                            <p class="mt-1 text-xs text-slate-500">Sức chứa: {{ $classRoom->room?->capacity ?? 'Chưa xác định' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Giảng viên</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $teacherName }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $teacherEmail }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Ngày bắt đầu</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $classRoom->start_date?->format('d/m/Y') ?? 'Chưa chốt' }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $course->formattedSchedule() }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Thời lượng</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $classRoom->duration ? $classRoom->duration . ' tháng' : 'Chưa xác định' }}</p>
                            <p class="mt-1 text-xs text-slate-500">Còn trống {{ $remainingSeats }} chỗ.</p>
                        </div>
                    </div>

                    @if($classRoom->note)
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                            <span class="font-semibold">Ghi chú lớp:</span> {{ $classRoom->note }}
                        </div>
                    @endif
                @else
                    <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        Chưa có lớp học.
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Lịch tuần</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Buổi học theo timeline</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $classSchedules->count() }} buổi</span>
                </div>

                @if($classSchedules->isNotEmpty())
                    <div class="relative mt-6 pl-7">
                        <div class="absolute bottom-2 left-3.5 top-1 w-px bg-slate-200"></div>
                        <div class="space-y-4">
                            @foreach($classSchedules as $schedule)
                                <div class="relative">
                                    <div class="absolute left-[-1.45rem] top-5 h-3.5 w-3.5 rounded-full bg-cyan-600 ring-4 ring-cyan-50"></div>
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex flex-wrap items-start justify-between gap-4">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Buổi {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                                <p class="mt-2 text-lg font-semibold text-slate-900">{{ \App\Models\ClassSchedule::$dayOptions[$schedule->day_of_week] ?? $schedule->day_of_week }}</p>
                                                <p class="mt-1 text-sm text-slate-600">{{ $schedule->start_time }} - {{ $schedule->end_time }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Phòng</p>
                                                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $schedule->room?->name ?? $roomLabel }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $schedule->teacher?->displayName() ?? $teacherName }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600">{{ \App\Models\ClassSchedule::$dayOptions[$schedule->day_of_week] ?? $schedule->day_of_week }}</span>
                                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600">{{ $schedule->start_time }} - {{ $schedule->end_time }}</span>
                                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600">{{ $schedule->room?->name ?? $roomLabel }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        Chưa có buổi học.
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Học viên</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Danh sách đang theo học</h2>
                    </div>
                    <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $activeEnrollments->count() }} học viên</span>
                </div>

                @if($activeEnrollments->isNotEmpty())
                    <div class="mt-6 grid gap-3 md:grid-cols-2">
                        @foreach($activeEnrollments as $enrollment)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-cyan-50 text-sm font-semibold text-cyan-700">
                                        {{ mb_substr((string) ($enrollment->user?->name ?? '?'), 0, 1) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-semibold text-slate-900">{{ $enrollment->user?->name ?? 'Học viên' }}</p>
                                        <p class="truncate text-sm text-slate-500">{{ $enrollment->user?->email ?? 'Chưa có email' }}</p>
                                        <p class="mt-2 text-xs text-slate-400">{{ $enrollment->schedule ?: $course->formattedSchedule() }}</p>
                                    </div>
                                    <div class="shrink-0">
                                        <x-admin.badge
                                            :type="match ($enrollment->status) {
                                                'scheduled', 'active' => 'success',
                                                'approved' => 'info',
                                                'completed' => 'default',
                                                'rejected' => 'danger',
                                                default => 'warning',
                                            }"
                                            :text="$enrollment->statusLabel()"
                                        />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        Chưa có học viên.
                    </div>
                @endif
            </div>
        </section>

        <aside class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thông tin nhanh</h2>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Nhóm học</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $course->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Môn học</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $course->subject?->name ?? 'Chưa có môn học' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Giảng viên</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $teacherName }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $teacherEmail }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Phòng học</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $roomLabel }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $classRoom?->statusLabel() ?? 'Chưa tạo lớp' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ngày khai giảng</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $course->start_date?->format('d/m/Y') ?? 'Chưa chốt' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ngày kết thúc</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $course->end_date?->format('d/m/Y') ?? 'Chưa chốt' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Lịch học</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $course->formattedSchedule() }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Sức chứa</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $capacity }} học viên</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Sĩ số hiện tại</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $occupiedSeats }} học viên</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Hành động</h2>
                <div class="mt-4 space-y-3">
                    @if($classRoom)
                        <a href="{{ route('admin.classes.show', $classRoom) }}" class="block rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm font-semibold text-cyan-700 hover:bg-cyan-100">
                            Mở màn chi tiết lớp học
                        </a>
                    @endif

                    @if($course->isPendingOpen())
                        <a href="{{ route('admin.schedules.courses.open', $course) }}" class="block rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Chọn phòng và chốt ngày khai giảng
                        </a>
                    @endif

                    <a href="{{ route('admin.schedules.index') }}" class="block rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Quay lại danh sách lịch học
                    </a>
                </div>
            </div>

        </aside>
    </div>
</div>
@endsection
