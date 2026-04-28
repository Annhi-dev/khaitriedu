@extends('bo_cuc.hoc_vien')
@section('title', 'Lớp học của tôi')
@section('eyebrow', 'Lớp học của tôi')

@section('content')
@php
    $search = $search ?? '';
    $statusFilter = $statusFilter ?? 'all';
    $statusOptions = [
        'all' => 'Tất cả',
        'ongoing' => 'Đang học',
        'completed' => 'Đã hoàn thành',
    ];
@endphp

<div class="space-y-6">
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="relative px-6 py-6 sm:px-8 sm:py-8">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(6,182,212,0.12),_transparent_42%),radial-gradient(circle_at_bottom_left,_rgba(15,23,42,0.06),_transparent_38%)]"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-cyan-700">Lộ trình học tập</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Lớp học của tôi</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Theo dõi các lớp đã đăng ký, đang học và đã hoàn thành trong một không gian riêng, tách biệt với phần đăng ký học mới.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('student.enroll.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
                        <i class="fas fa-book-open"></i>
                        Đăng ký học
                    </a>
                    <a href="{{ route('student.schedule') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                        <i class="fas fa-calendar-days"></i>
                        Lịch học
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Tổng lớp</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $summary['totalClasses'] ?? 0 }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Lớp đang học</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $summary['ongoingClasses'] ?? 0 }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Lớp đã hoàn thành</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $summary['completedClasses'] ?? 0 }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Điểm trung bình</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">
                {{ isset($summary['averageGrade']) && $summary['averageGrade'] !== null ? number_format((float) $summary['averageGrade'], 2) : '--' }}
            </p>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('student.classes.index') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_240px_160px] lg:items-end">
            <div>
                <label for="student-class-search" class="block text-sm font-semibold text-slate-700">Tìm lớp</label>
                <div class="mt-2 flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 focus-within:border-cyan-300 focus-within:bg-white">
                    <i class="fas fa-magnifying-glass text-slate-400"></i>
                    <input
                        id="student-class-search"
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Tìm theo tên lớp, môn học, khóa học..."
                        class="w-full border-0 bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                    >
                </div>
            </div>

            <div>
                <label for="student-class-status" class="block text-sm font-semibold text-slate-700">Bộ lọc</label>
                <select id="student-class-status" name="status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-cyan-300 focus:bg-white focus:outline-none">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    <i class="fas fa-filter"></i>
                    Lọc
                </button>
                @if($search !== '' || $statusFilter !== 'all')
                    <a href="{{ route('student.classes.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Xóa lọc
                    </a>
                @endif
            </div>
        </form>
    </section>

    @if($enrollments->isEmpty())
        <section class="rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center shadow-sm">
            <i class="fas fa-graduation-cap mb-3 block text-4xl text-slate-300"></i>
            @if($search !== '' || $statusFilter !== 'all')
                <p class="text-lg font-semibold text-slate-700">Không tìm thấy lớp phù hợp.</p>
                <p class="mt-2 text-slate-500">Thử đổi từ khóa tìm kiếm hoặc xóa bộ lọc để xem toàn bộ lớp học của bạn.</p>
                <a href="{{ route('student.classes.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    Xem tất cả lớp
                </a>
            @else
                <p class="text-lg font-semibold text-slate-700">Bạn chưa có lớp học nào.</p>
                <p class="mt-2 text-slate-500">Hãy đăng ký khóa học để bắt đầu.</p>
                <a href="{{ route('student.enroll.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    <i class="fas fa-book-open"></i>
                    Đăng ký học
                </a>
            @endif
        </section>
    @else
        <section class="space-y-5">
            @if($ongoingEnrollments->isNotEmpty())
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Đang học</h3>
                        <p class="mt-1 text-sm text-slate-500">Các lớp đang học, đã duyệt hoặc đã xếp lớp.</p>
                    </div>
                    <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $ongoingEnrollments->count() }} lớp</span>
                </div>

                <div class="grid gap-4">
                    @foreach($ongoingEnrollments as $enrollment)
                        @include('hoc_vien.lop_hoc.partials.card', ['enrollment' => $enrollment])
                    @endforeach
                </div>
            @endif

            @if($completedEnrollments->isNotEmpty())
                <div class="flex items-center justify-between gap-3 pt-2">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Đã hoàn thành</h3>
                        <p class="mt-1 text-sm text-slate-500">Những lớp bạn đã kết thúc và có thể xem lại bất cứ lúc nào.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $completedEnrollments->count() }} lớp</span>
                </div>

                <div class="grid gap-4">
                    @foreach($completedEnrollments as $enrollment)
                        @include('hoc_vien.lop_hoc.partials.card', ['enrollment' => $enrollment])
                    @endforeach
                </div>
            @endif
        </section>
    @endif
</div>
@endsection
