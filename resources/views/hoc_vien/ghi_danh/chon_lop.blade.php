@extends('bo_cuc.hoc_vien')
@section('title', 'Chọn lớp học — ' . $subject->name)
@section('eyebrow', 'Chọn lớp học')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Khóa học đang mở</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Chọn lớp học — {{ $subject->name }}</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Chọn lớp cố định phù hợp với lịch của bạn hoặc gửi yêu cầu lịch học riêng nếu chưa có lớp phù hợp.
                </p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="rounded-full bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">{{ $subject->category?->name ?? 'Chưa phân nhóm' }}</span>
                    <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-medium text-cyan-700">{{ $subject->durationLabel() }}</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('student.enroll.request-form', $subject) }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
                    <i class="fas fa-paper-plane"></i>
                    Gửi yêu cầu lịch học
                </a>
                <a href="{{ route('student.enroll.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
            </div>
        </div>
    </section>

    @if($existingEnrollment)
        <section class="rounded-3xl border {{ $existingEnrollment->hasCourseAccess() ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} p-5 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] {{ $existingEnrollment->hasCourseAccess() ? 'text-emerald-700' : 'text-amber-700' }}">Trạng thái hiện tại</p>
                    <p class="mt-2 text-lg font-semibold {{ $existingEnrollment->hasCourseAccess() ? 'text-emerald-900' : 'text-amber-900' }}">
                        {{ $existingEnrollment->displayStatusLabel() }}
                    </p>
                    @if($existingEnrollment->classRoom)
                        <p class="mt-2 text-sm leading-6 text-slate-700">Bạn đã được ghi danh vào lớp <strong>{{ $existingEnrollment->classRoom->displayName() }}</strong>.</p>
                    @elseif($existingEnrollment->start_time && $existingEnrollment->end_time)
                        <p class="mt-2 text-sm leading-6 text-slate-700">Khung giờ đã gửi: <strong>{{ $existingEnrollment->start_time }} - {{ $existingEnrollment->end_time }}</strong></p>
                    @endif
                </div>

                <div class="text-sm leading-6 text-slate-600">
                    @if(!empty($existingEnrollment->preferred_days))
                        <p><strong>Ngày có thể học:</strong> {{ collect($existingEnrollment->preferred_days)->map(fn ($day) => $dayOptions[$day] ?? $day)->implode(', ') }}</p>
                    @endif
                    @if($existingEnrollment->preferred_schedule)
                        <p class="mt-1"><strong>Ghi chú thêm:</strong> {{ $existingEnrollment->preferred_schedule }}</p>
                    @endif
                </div>
            </div>

            @if($existingEnrollment->note)
                <div class="mt-4 rounded-2xl border border-white/80 bg-white/80 px-4 py-3 text-sm text-slate-700">
                    <strong>Ghi chú từ admin:</strong> {{ $existingEnrollment->note }}
                </div>
            @endif
        </section>
    @endif

    @if($classes->isEmpty())
        <section class="rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center text-slate-500 shadow-sm">
            <i class="fas fa-door-closed mb-3 block text-4xl text-slate-300"></i>
            <p>Hiện chưa có lớp đang mở cho khóa này.</p>
            <a href="{{ route('student.enroll.request-form', $subject) }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                <i class="fas fa-paper-plane"></i>
                Gửi yêu cầu lịch học
            </a>
        </section>
    @else
        @if(!empty($scheduleConflictMap))
            <section class="rounded-3xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-rose-900">
                            Phát hiện {{ $scheduleConflictCount ?? count($scheduleConflictMap) }} lớp bị trùng lịch với thời khóa biểu hiện tại.
                        </p>
                        <p class="mt-1 text-sm leading-6 text-rose-800">
                            Các lớp bị trùng sẽ được khóa đăng ký để tránh tạo lịch học chồng chéo.
                        </p>
                    </div>
                </div>
            </section>
        @endif

        <section class="space-y-4">
            @foreach($classes as $class)
                @php
                    $scheduleConflict = $scheduleConflictMap[$class->id] ?? null;
                    $availabilityState = $class->enrollmentAvailabilityState();
                    $availabilityLabel = $class->enrollmentAvailabilityLabel();
                    $availabilityStyles = match ($availabilityState) {
                        'available' => ['badge' => 'bg-emerald-100 text-emerald-700', 'panel' => 'border-emerald-200 bg-emerald-50', 'text' => 'text-emerald-700', 'title' => 'text-emerald-800', 'icon' => 'text-emerald-500'],
                        'full' => ['badge' => 'bg-amber-100 text-amber-700', 'panel' => 'border-amber-200 bg-amber-50', 'text' => 'text-amber-700', 'title' => 'text-amber-800', 'icon' => 'text-amber-500'],
                        'started', 'ended' => ['badge' => 'bg-rose-100 text-rose-700', 'panel' => 'border-rose-200 bg-rose-50', 'text' => 'text-rose-700', 'title' => 'text-rose-800', 'icon' => 'text-rose-500'],
                        'closed' => ['badge' => 'bg-slate-100 text-slate-700', 'panel' => 'border-slate-200 bg-slate-50', 'text' => 'text-slate-600', 'title' => 'text-slate-800', 'icon' => 'text-slate-500'],
                        default => ['badge' => 'bg-slate-100 text-slate-700', 'panel' => 'border-slate-200 bg-slate-50', 'text' => 'text-slate-600', 'title' => 'text-slate-800', 'icon' => 'text-slate-500'],
                    };
                    $canEnroll = $class->canAcceptEnrollment();
                    $blockReason = $class->enrollmentBlockReason();
                @endphp
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-cyan-200 hover:shadow-md">
                    <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $class->subject->name ?? '—' }}</h3>
                                @if($class->availableSlots() <= 3 && $canEnroll)
                                    <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-600">
                                        Còn {{ $class->availableSlots() }} chỗ
                                    </span>
                                @elseif($canEnroll)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                        Còn {{ $class->availableSlots() }} chỗ
                                    </span>
                                @else
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $availabilityStyles['badge'] }}">
                                        {{ $availabilityLabel }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-4 grid gap-3 text-sm text-slate-600 sm:grid-cols-2">
                                <div>
                                    <span class="font-medium text-slate-500">Giảng viên:</span>
                                    {{ $class->teacher?->displayName() ?? 'Chưa phân công' }}
                                </div>
                                <div>
                                    <span class="font-medium text-slate-500">Phòng:</span>
                                    {{ $class->room ? $class->room->name . ' (' . $class->room->code . ')' : 'Chưa chọn' }}
                                </div>
                                <div>
                                    <span class="font-medium text-slate-500">Ngày bắt đầu:</span>
                                    {{ $class->start_date?->format('d/m/Y') ?? 'Chưa xác định' }}
                                </div>
                                <div>
                                    <span class="font-medium text-slate-500">Thời lượng:</span>
                                    {{ $class->duration ? $class->duration . ' tháng' : '—' }}
                                </div>
                                <div>
                                    <span class="font-medium text-slate-500">Kết thúc:</span>
                                    {{ $class->scheduleRangeEnd()?->format('d/m/Y') ?? 'Chưa xác định' }}
                                </div>
                            </div>

                            @if($class->schedules->isNotEmpty())
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach($class->schedules as $s)
                                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700">
                                                <i class="fas fa-clock text-slate-400"></i>
                                            {{ \App\Models\LichHoc::$dayOptions[$s->day_of_week] ?? $s->day_of_week }}
                                            {{ $s->start_time }}–{{ $s->end_time }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-4 rounded-2xl border {{ $availabilityStyles['panel'] }} px-4 py-3 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-medium {{ $availabilityStyles['title'] }}">
                                        {{ $availabilityLabel }}
                                    </p>
                                    <i class="fas {{ $canEnroll ? 'fa-circle-check' : 'fa-circle-xmark' }} {{ $availabilityStyles['icon'] }}"></i>
                                </div>
                                @if($blockReason)
                                    <p class="mt-1 leading-6 {{ $availabilityStyles['text'] }}">{{ $blockReason }}</p>
                                @else
                                    <p class="mt-1 leading-6 text-emerald-700">Lớp đang mở và sẵn sàng nhận đăng ký.</p>
                                @endif
                            </div>

                            @if($scheduleConflict)
                                <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                                    <p class="font-semibold">Khung giờ này bị trùng với lịch học hiện tại.</p>
                                    <p class="mt-1 leading-6">{{ $scheduleConflict['message'] }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="shrink-0">
                            @if($existingEnrollment)
                                <span class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-medium text-slate-400">
                                    Đã đăng ký
                                </span>
                            @else
                                <form method="POST" action="{{ route('student.enroll.store', $subject) }}">
                                    @csrf
                                    <input type="hidden" name="lop_hoc_id" value="{{ $class->id }}">
                                    <button type="submit"
                                        @if($scheduleConflict || ! $canEnroll) disabled aria-disabled="true" @endif
                                        onclick="return confirm('Xác nhận đăng ký lớp này?')"
                                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition {{ $scheduleConflict || ! $canEnroll ? 'cursor-not-allowed bg-slate-300 text-slate-600 hover:bg-slate-300' : 'bg-cyan-600 hover:bg-cyan-700' }}">
                                        <i class="fas {{ $scheduleConflict || ! $canEnroll ? 'fa-ban' : 'fa-check' }}"></i>
                                        {{ $scheduleConflict ? 'Bị trùng lịch' : ($canEnroll ? 'Đăng ký lớp này' : 'Không thể đăng ký') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        @if(! $existingEnrollment || ! $existingEnrollment->hasCourseAccess())
            <section class="rounded-3xl border border-cyan-200 bg-cyan-50 p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-cyan-900">Chưa thấy lớp phù hợp?</h2>
                        <p class="mt-1 text-sm text-cyan-800">Bạn có thể gửi yêu cầu lịch học riêng để admin sắp xếp lớp phù hợp hơn.</p>
                    </div>
                    <a href="{{ route('student.enroll.request-form', $subject) }}" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                        <i class="fas fa-calendar-plus"></i>
                        Gửi yêu cầu lịch học
                    </a>
                </div>
            </section>
        @endif
    @endif
</div>
@endsection
