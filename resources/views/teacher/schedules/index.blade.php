@extends('layouts.teacher')

@section('title', 'Lịch Giảng Dạy')
@section('eyebrow', 'Weekly Schedule')

@section('content')
@php
    $groupedSchedule = $weekSchedule->groupBy(fn ($item) => $item['starts_at']->format('Y-m-d'));
@endphp

<div class="space-y-6" x-data="{ activeModal: null }">
    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-700">This Week</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Lịch dạy theo tuần</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">Mỗi buổi hiển thị theo lớp nội bộ mà admin đã phân công. Bạn có thể gửi yêu cầu đổi lịch trực tiếp từ từng slot.</p>
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                <p><strong>Tổng buổi tuần này:</strong> {{ $weekSchedule->count() }}</p>
                <p class="mt-1"><strong>Chờ xử lý:</strong> {{ $pendingRequestsCount }}</p>
            </div>
        </div>
    </section>

    @forelse ($groupedSchedule as $date => $items)
        <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-400">{{ \Carbon\Carbon::parse($date)->translatedFormat('l') }}</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h3>
                </div>
                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">{{ $items->count() }} buổi</span>
            </div>

            <div class="mt-6 grid gap-4">
                @foreach ($items as $item)
                    @php $modalId = 'schedule-modal-' . $item['schedule']->id; @endphp
                    <article class="rounded-3xl border border-slate-200 p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-sm font-medium text-cyan-700">{{ $item['schedule']->timeRangeLabel() }}</p>
                                <h4 class="mt-2 text-xl font-semibold text-slate-900">{{ $item['class_room']->displayName() }}</h4>
                                <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-500">
                                    <span>{{ $item['class_room']->subject?->name ?? 'Chưa có môn học' }}</span>
                                    <span>•</span>
                                    <span>{{ $item['room_label'] }}</span>
                                    <span>•</span>
                                    <span>{{ $item['class_room']->students_count ?? 0 }} học viên</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('teacher.classes.show', ['classRoom' => $item['class_room']->id, 'tab' => 'attendance', 'schedule_id' => $item['schedule']->id, 'date' => $item['starts_at']->format('Y-m-d')]) }}"
                                    class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    Điểm danh
                                </a>
                                <button type="button"
                                    class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700"
                                    @click="activeModal = '{{ $modalId }}'">
                                    Yêu cầu đổi lịch
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
                                    <p class="mt-2 text-sm text-slate-500">Lịch hiện tại: {{ $item['schedule']->label() }}</p>
                                </div>
                                <button type="button" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-600" @click="activeModal = null">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </div>

                            <form method="POST" action="{{ route('teacher.schedules.change-requests.store', $item['schedule']) }}" class="mt-6 space-y-4">
                                @csrf
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="text-sm font-medium text-slate-700">Thời gian bắt đầu mới</label>
                                        <input type="datetime-local" name="requested_start_at"
                                            value="{{ $item['starts_at']->addDay()->format('Y-m-d\TH:i') }}"
                                            class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-slate-700">Thời gian kết thúc mới</label>
                                        <input type="datetime-local" name="requested_end_at"
                                            value="{{ $item['ends_at']->addDay()->format('Y-m-d\TH:i') }}"
                                            class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-slate-700">Lý do đổi lịch</label>
                                    <textarea name="reason" rows="4"
                                        class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none"
                                        placeholder="Ví dụ: trùng lịch chuyên môn, thay đổi phòng, đề xuất tối ưu cho học viên..."></textarea>
                                </div>

                                <div class="flex flex-wrap justify-end gap-3">
                                    <button type="button" class="rounded-2xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50" @click="activeModal = null">
                                        Hủy
                                    </button>
                                    <button type="submit" class="rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">
                                        Gửi admin duyệt
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
            Chưa có lịch giảng nào trong tuần hiện tại.
        </section>
    @endforelse
</div>
@endsection
