@extends('layouts.teacher')

@section('title', 'Lich Giang Day')
@section('eyebrow', 'Teaching Schedule')

@section('content')
@php
    $groupedSchedule = $scheduleItems->groupBy(fn ($item) => $item['starts_at']->format('Y-m-d'));
    $modeOptions = [
        'week' => 'Theo tuan',
        'month' => 'Theo thang',
        'year' => 'Theo nam',
    ];
@endphp

<div class="space-y-6" x-data="{ activeModal: null }">
    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-700">{{ $modeEyebrow }}</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $modeTitle }}</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">{{ $modeDescription }}</p>
                <p class="mt-3 inline-flex rounded-full bg-cyan-50 px-4 py-1.5 text-sm font-semibold text-cyan-700">Ky dang xem: {{ $periodLabel }}</p>
            </div>

            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                <p><strong>Tong buoi trong ky:</strong> {{ $scheduleItems->count() }}</p>
                <p class="mt-1"><strong>Cho xu ly:</strong> {{ $pendingRequestsCount }}</p>
            </div>
        </div>

        <div class="mt-5 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="inline-flex rounded-2xl border border-slate-200 bg-slate-50 p-1">
                @foreach ($modeOptions as $key => $label)
                    <a
                        href="{{ route('teacher.schedules.index', ['mode' => $key, 'date' => $anchorDate->format('Y-m-d')]) }}"
                        class="rounded-xl px-4 py-2 text-sm font-medium transition {{ $scheduleMode === $key ? 'bg-cyan-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-cyan-700' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('teacher.schedules.index', ['mode' => $scheduleMode, 'date' => $prevDate]) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Truoc
                </a>
                <a href="{{ route('teacher.schedules.index', ['mode' => $scheduleMode, 'date' => now()->format('Y-m-d')]) }}" class="inline-flex items-center justify-center rounded-2xl border border-cyan-200 px-4 py-2.5 text-sm font-medium text-cyan-700 hover:bg-cyan-50">
                    Hom nay
                </a>
                <a href="{{ route('teacher.schedules.index', ['mode' => $scheduleMode, 'date' => $nextDate]) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Sau
                </a>
                <form method="GET" action="{{ route('teacher.schedules.index') }}" class="ml-2 flex items-center gap-2">
                    <input type="hidden" name="mode" value="{{ $scheduleMode }}">
                    <input type="date" name="date" value="{{ $anchorDate->format('Y-m-d') }}" class="rounded-2xl border border-slate-300 px-3 py-2 text-sm focus:border-cyan-500 focus:outline-none">
                    <button type="submit" class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Xem</button>
                </form>
            </div>
        </div>
    </section>

    @if ($scheduleMode === 'week')
        @include('shared.schedule-grid', [
            'grid' => $weeklyTimetable,
            'sectionEyebrow' => $modeEyebrow,
            'sectionTitle' => $modeTitle,
            'sectionSubtitle' => 'Moi o la mot khung gio thuc te. Bam vao tung buoi hoc de xem chi tiet va thao tac.',
            'emptyMessage' => 'Chua co lich giang nao trong tuan nay.',
        ])
    @else
        @forelse ($groupedSchedule as $date => $items)
            <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-400">{{ \Carbon\Carbon::parse($date)->translatedFormat('l') }}</p>
                        <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h3>
                    </div>
                    <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">{{ $items->count() }} buoi</span>
                </div>

                <div class="mt-6 grid gap-4">
                    @foreach ($items as $item)
                        @php
                            $modalId = 'schedule-modal-' . $item['schedule']->id . '-' . $item['starts_at']->format('Ymd');
                            $currentRoomId = $item['schedule']->room_id ?: $item['class_room']->room_id;
                        @endphp
                        <article class="rounded-3xl border border-slate-200 p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="text-sm font-medium text-cyan-700">{{ $item['schedule']->timeRangeLabel() }}</p>
                                    <h4 class="mt-2 text-xl font-semibold text-slate-900">{{ $item['class_room']->displayName() }}</h4>
                                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-500">
                                        <span>{{ $item['class_room']->subject?->name ?? 'Chua co mon hoc' }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $item['room_label'] }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $item['class_room']->students_count ?? 0 }} hoc vien</span>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('teacher.classes.show', ['classRoom' => $item['class_room']->id, 'tab' => 'attendance', 'schedule_id' => $item['schedule']->id, 'date' => $item['starts_at']->format('Y-m-d')]) }}"
                                        class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                        Diem danh
                                    </a>
                                    <button type="button"
                                        class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700"
                                        @click="activeModal = '{{ $modalId }}'">
                                        Yeu cau doi lich
                                    </button>
                                </div>
                            </div>
                        </article>

                        <div x-show="activeModal === '{{ $modalId }}'" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/60 px-4 py-6">
                            <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium uppercase tracking-[0.18em] text-cyan-700">Change Request</p>
                                        <h4 class="mt-2 text-2xl font-semibold text-slate-900">{{ $item['class_room']->displayName() }}</h4>
                                        <p class="mt-2 text-sm text-slate-500">Lich hien tai: {{ $item['schedule']->label() }}</p>
                                    </div>
                                    <button type="button" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-600" @click="activeModal = null">
                                        <i class="fas fa-xmark"></i>
                                    </button>
                                </div>

                                <form method="POST" action="{{ route('teacher.schedules.change-requests.store', $item['schedule']) }}" class="mt-6 space-y-4">
                                    @csrf
                                    <div class="grid gap-4 md:grid-cols-3">
                                        <div class="md:col-span-3">
                                            <label class="text-sm font-medium text-slate-700">Ngay moi</label>
                                            <input type="date" name="requested_date"
                                                value="{{ old('requested_date', $item['starts_at']->addDay()->format('Y-m-d')) }}"
                                                required
                                                class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-slate-700">Gio bat dau moi</label>
                                            <input type="time" name="requested_start_time"
                                                value="{{ old('requested_start_time', $item['starts_at']->addDay()->format('H:i')) }}"
                                                required
                                                class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-slate-700">Gio ket thuc moi</label>
                                            <input type="time" name="requested_end_time"
                                                value="{{ old('requested_end_time', $item['ends_at']->addDay()->format('H:i')) }}"
                                                required
                                                class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="text-sm font-medium text-slate-700">Phong hoc moi (tuy chon)</label>
                                            <select name="requested_room_id"
                                                class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                                                <option value="">Giu phong hien tai ({{ $item['room_label'] }})</option>
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
                                        <label class="text-sm font-medium text-slate-700">Ly do doi lich</label>
                                        <textarea name="reason" rows="4"
                                            required
                                            class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none"
                                            placeholder="Vi du: trung lich chuyen mon, thay doi phong, de xuat toi uu cho hoc vien...">{{ old('reason') }}</textarea>
                                    </div>

                                    <div class="flex flex-wrap justify-end gap-3">
                                        <button type="button" class="rounded-2xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50" @click="activeModal = null">
                                            Huy
                                        </button>
                                        <button type="submit" class="rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">
                                            Gui admin duyet
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <section class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center text-sm text-slate-500">
                Chua co lich giang nao trong che do {{ $modeOptions[$scheduleMode] ?? 'hien tai' }}.
            </section>
        @endforelse
    @endif
</div>
@endsection
