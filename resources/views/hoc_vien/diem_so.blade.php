@extends('bo_cuc.hoc_vien')
@section('title', 'Điểm số của tôi')
@section('eyebrow', 'Kết quả học tập')

@section('content')
@php
    $gradeList = method_exists($grades, 'getCollection') ? $grades->getCollection() : collect($grades);
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Học tập</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Kết quả học tập</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Xem kết quả học tập, điểm số và phản hồi của giảng viên theo từng khóa học.
                </p>
            </div>

            <a href="{{ route('student.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                <i class="fas fa-gauge-high"></i>
                Quay lại tổng quan
            </a>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Tổng mục điểm</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $gradeList->count() }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Có điểm số</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $gradeList->whereNotNull('score')->count() }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Chưa có điểm</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $gradeList->whereNull('score')->count() }}</p>
            </div>
        </div>
    </section>

    <section class="grid gap-4">
        @forelse($grades as $grade)
            @php
                $courseTitle = $grade->enrollment->course->title ?? 'Chưa xác định';
                $updatedAt = $grade->updated_at?->format('d/m/Y');
            @endphp

            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-semibold text-slate-900">{{ $courseTitle }}</h3>
                        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Module</p>
                                <p class="mt-2 text-sm font-medium text-slate-900">{{ $grade->module->title ?? 'Tổng kết' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Điểm số</p>
                                <p class="mt-2 text-sm font-medium text-slate-900">{{ $grade->score ?? 'Chưa có' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Điểm chữ</p>
                                <p class="mt-2 text-sm font-medium text-slate-900">{{ $grade->grade ?? 'Chưa có' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Cập nhật</p>
                                <p class="mt-2 text-sm font-medium text-slate-900">{{ $updatedAt ?? 'Chưa rõ' }}</p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Phản hồi</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">{{ $grade->feedback ?: 'Không có phản hồi' }}</p>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center shadow-sm">
                <i class="fas fa-square-poll-horizontal mb-3 block text-4xl text-slate-300"></i>
                <p class="text-lg font-semibold text-slate-700">Bạn chưa có điểm số nào.</p>
                <p class="mt-2 text-slate-500">Kết quả học tập sẽ xuất hiện sau khi giảng viên cập nhật.</p>
            </div>
        @endforelse
    </section>
</div>
@endsection
