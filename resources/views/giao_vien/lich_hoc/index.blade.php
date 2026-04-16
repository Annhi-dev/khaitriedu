@extends('bo_cuc.giao_vien')

@section('title', 'Lịch giảng dạy')
@section('eyebrow', 'Lịch dạy')

@section('content')
@php
    $groupedSchedule = $scheduleItems->groupBy(fn ($item) => $item['starts_at']->format('Y-m-d'));
    $modeOptions = [
        'week' => 'Theo tuần',
        'month' => 'Theo tháng',
        'year' => 'Theo năm',
    ];
    $currentModeLabel = $modeOptions[$scheduleMode] ?? 'Theo tuần';
@endphp

<div class="space-y-6 xl:space-y-8" x-data="{ activeModal: null }">
    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="border-b border-slate-100 p-6 lg:border-b-0 lg:border-r lg:p-7">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-cyan-700">{{ $modeEyebrow }}</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $modeTitle }}</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">{{ $modeDescription }}</p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white">
                        Kỳ: {{ $periodLabel }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700">
                        {{ $scheduleItems->count() }} buổi
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-700">
                        {{ $pendingRequestsCount }} chờ xử lý
                    </span>
                </div>
            </div>

            <div class="grid gap-3 bg-slate-50 p-6 sm:grid-cols-3 lg:grid-cols-1">
                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Chế độ xem</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $currentModeLabel }}</p>
                    <p class="mt-1 text-sm text-slate-500">Màn hình đang mở</p>
                </div>
                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Hôm nay</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ now()->format('d/m/Y') }}</p>
                    <p class="mt-1 text-sm text-slate-500">Ngày hệ thống hiện tại</p>
                </div>
                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Đang xem</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $anchorDate->format('d/m/Y') }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $pendingRequestsCount }} yêu cầu chờ</p>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-center 2xl:justify-between">
            <div class="inline-flex overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 p-1 shadow-sm">
                @foreach ($modeOptions as $key => $label)
                    <a
                        href="{{ route('teacher.schedules.index', ['mode' => $key, 'date' => $anchorDate->format('Y-m-d')]) }}"
                        class="rounded-xl px-4 py-2.5 text-sm font-medium transition {{ $scheduleMode === $key ? 'bg-cyan-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-cyan-700' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('teacher.schedules.index', ['mode' => $scheduleMode, 'date' => $prevDate]) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                    <i class="fas fa-chevron-left text-xs"></i>
                    <span>Trước</span>
                </a>
                <a href="{{ route('teacher.schedules.index', ['mode' => $scheduleMode, 'date' => now()->format('Y-m-d')]) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-medium text-cyan-700 transition hover:bg-cyan-100">
                    <i class="fas fa-bullseye text-xs"></i>
                    <span>Hôm nay</span>
                </a>
                <a href="{{ route('teacher.schedules.index', ['mode' => $scheduleMode, 'date' => $nextDate]) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                    <span>Sau</span>
                    <i class="fas fa-chevron-right text-xs"></i>
                </a>
                <form method="GET" action="{{ route('teacher.schedules.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="mode" value="{{ $scheduleMode }}">
                    <input type="date" name="date" value="{{ $anchorDate->format('Y-m-d') }}" class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Xem
                    </button>
                </form>
            </div>
        </div>
    </section>

    @if ($scheduleMode === 'week')
        @include('dung_chung.luoi_lich_hoc', [
            'grid' => $weeklyTimetable,
            'sectionEyebrow' => $modeEyebrow,
            'sectionTitle' => $modeTitle,
            'sectionSubtitle' => 'Mỗi ô là một khung giờ thực tế. Bấm vào từng buổi học để xem chi tiết, điểm danh hoặc gửi yêu cầu dời buổi.',
            'emptyMessage' => 'Chưa có lịch giảng nào trong tuần này.',
        ])
    @else
        <div class="grid gap-5">
            @forelse ($groupedSchedule as $date => $items)
                <section class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">{{ \Carbon\Carbon::parse($date)->translatedFormat('l') }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-3">
                                <h3 class="text-2xl font-semibold tracking-tight text-slate-950">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h3>
                                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $items->count() }} buổi</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1 text-xs font-medium text-cyan-700">
                                <span class="h-2.5 w-2.5 rounded-full bg-cyan-500"></span>
                                Lịch trong ngày
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                                Dời buổi nếu cần
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5">
                    @foreach ($items as $item)
                        @php
                            $modalId = 'schedule-modal-' . $item['schedule']->id . '-' . $item['starts_at']->format('Ymd');
                            $currentRoomId = $item['schedule']->room_id ?: $item['class_room']->room_id;
                        @endphp
                        <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">
                                            {{ $item['schedule']->timeRangeLabel() }}
                                        </span>
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                            {{ $item['room_label'] }}
                                        </span>
                                    </div>
                                    <h4 class="mt-3 text-xl font-semibold tracking-tight text-slate-950">{{ $item['class_room']->displayName() }}</h4>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        {{ $item['class_room']->subject?->name ?? 'Chưa có môn học' }}
                                    </p>
                                    <div class="mt-4 flex flex-wrap gap-2 text-xs">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-600">
                                            {{ $item['class_room']->students_count ?? 0 }} học viên
                                        </span>
                                        <span class="rounded-full bg-cyan-50 px-3 py-1 font-medium text-cyan-700">
                                            {{ $item['room_label'] }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 lg:justify-end">
                                    <a href="{{ route('teacher.classes.show', ['classRoom' => $item['class_room']->id, 'tab' => 'attendance', 'schedule_id' => $item['schedule']->id, 'date' => $item['starts_at']->format('Y-m-d')]) }}"
                                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                                        Điểm danh
                                    </a>
                                    <button type="button"
                                        class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700"
                                        @click="activeModal = '{{ $modalId }}'">
                                        Yêu cầu dời buổi
                                    </button>
                                </div>
                            </div>
                        </article>

                        <div x-show="activeModal === '{{ $modalId }}'" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/60 px-4 py-6">
                            <div class="w-full max-w-2xl overflow-hidden rounded-[2rem] bg-white shadow-2xl ring-1 ring-black/5">
                                <div class="border-b border-slate-100 bg-gradient-to-r from-cyan-50 via-white to-slate-50 p-6">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">Yêu cầu dời buổi</p>
                                            <h4 class="mt-3 text-2xl font-semibold tracking-tight text-slate-950">{{ $item['class_room']->displayName() }}</h4>
                                            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                                                <span class="rounded-full bg-white px-3 py-1 font-medium text-slate-600 shadow-sm">{{ $item['schedule']->label() }}</span>
                                                <span class="rounded-full bg-white px-3 py-1 font-medium text-cyan-700 shadow-sm">{{ $item['room_label'] }}</span>
                                            </div>
                                        </div>
                                        <button type="button" class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-50" @click="activeModal = null">
                                            <i class="fas fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('teacher.schedules.change-requests.store', $item['schedule']) }}" class="space-y-5 p-6">
                                    @csrf
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="md:col-span-2">
                                            <label class="text-sm font-medium text-slate-700">Ngày dạy bù</label>
                                            <input type="date" name="requested_date"
                                                value="{{ old('requested_date', $item['starts_at']->copy()->addDay()->format('Y-m-d')) }}"
                                                required
                                                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-slate-700">Giờ bắt đầu buổi mới</label>
                                            <input type="time" name="requested_start_time"
                                                value="{{ old('requested_start_time', $item['starts_at']->format('H:i')) }}"
                                                required
                                                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-slate-700">Giờ kết thúc buổi mới</label>
                                            <input type="time" name="requested_end_time"
                                                value="{{ old('requested_end_time', $item['ends_at']->format('H:i')) }}"
                                                required
                                                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="text-sm font-medium text-slate-700">Phòng học cho buổi bù (tùy chọn)</label>
                                            <select name="requested_room_id"
                                                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none">
                                                <option value="">Giữ phòng hiện tại ({{ $item['room_label'] }})</option>
                                                @foreach ($availableRooms as $room)
                                                    @if ((int) $room->id !== (int) $currentRoomId)
                                                        <option value="{{ $room->id }}" @selected((string) old('requested_room_id') === (string) $room->id)>
                                                            {{ $room->name }}{{ $room->code ? ' (' . $room->code . ')' : '' }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-slate-700">Lý do dời buổi</label>
                                        <textarea name="reason" rows="4"
                                            required
                                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none"
                                            placeholder="Ví dụ: giảng viên bận việc cá nhân, xin dời buổi này sang ngày khác để dạy bù...">{{ old('reason') }}</textarea>
                                    </div>

                                    <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">
                                        <button type="button" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50" @click="activeModal = null">
                                            Hủy
                                        </button>
                                        <button type="submit" class="rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700">
                                            Gửi yêu cầu dạy bù
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </section>
            @empty
                <section class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white px-6 py-16 text-center text-sm text-slate-500">
                    Chưa có lịch giảng nào trong chế độ {{ $currentModeLabel }}.
                </section>
            @endforelse
        </div>
    @endif
</div>
@endsection
