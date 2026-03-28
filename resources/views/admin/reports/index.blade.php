@extends('layouts.admin')
@section('title', 'Bao cao he thong')
@section('content')
@php
    $summaryCards = [
        ['label' => 'Tong hoc vien', 'value' => $summary['totalStudents'], 'meta' => $summary['studentsInPeriod'] . ' moi trong ky', 'icon' => 'fas fa-user-graduate', 'tone' => 'cyan'],
        ['label' => 'Tong giang vien', 'value' => $summary['totalTeachers'], 'meta' => $summary['teachersInPeriod'] . ' moi trong ky', 'icon' => 'fas fa-chalkboard-user', 'tone' => 'emerald'],
        ['label' => 'Lop dang co lich/hoat dong', 'value' => $summary['activeClasses'], 'meta' => $summary['newEnrollments'] . ' dang ky hoc moi trong ky', 'icon' => 'fas fa-calendar-days', 'tone' => 'violet'],
        ['label' => 'Tong don ung tuyen', 'value' => $summary['totalTeacherApplications'], 'meta' => $summary['teacherApplicationsInPeriod'] . ' don moi trong ky', 'icon' => 'fas fa-file-signature', 'tone' => 'rose'],
        ['label' => 'Dang ky cho duyet', 'value' => $summary['pendingEnrollments'], 'meta' => 'Cho admin xu ly va xep lop', 'icon' => 'fas fa-clipboard-check', 'tone' => 'amber'],
        ['label' => 'Yeu cau doi lich cho duyet', 'value' => $summary['pendingScheduleChanges'], 'meta' => 'Can admin review va phan hoi', 'icon' => 'fas fa-calendar-rotate', 'tone' => 'slate'],
    ];
    $toneMap = [
        'cyan' => ['wrapper' => 'bg-cyan-50 text-cyan-700', 'icon' => 'bg-cyan-500/10 text-cyan-700'],
        'emerald' => ['wrapper' => 'bg-emerald-50 text-emerald-700', 'icon' => 'bg-emerald-500/10 text-emerald-700'],
        'amber' => ['wrapper' => 'bg-amber-50 text-amber-700', 'icon' => 'bg-amber-500/10 text-amber-700'],
        'rose' => ['wrapper' => 'bg-rose-50 text-rose-700', 'icon' => 'bg-rose-500/10 text-rose-700'],
        'violet' => ['wrapper' => 'bg-violet-50 text-violet-700', 'icon' => 'bg-violet-500/10 text-violet-700'],
        'slate' => ['wrapper' => 'bg-slate-100 text-slate-700', 'icon' => 'bg-slate-500/10 text-slate-700'],
    ];
    $trendSets = [
        ['label' => 'Hoc vien moi', 'data' => $activityTrend['students'], 'color' => 'bg-sky-500'],
        ['label' => 'Dang ky hoc', 'data' => $activityTrend['enrollments'], 'color' => 'bg-cyan-500'],
        ['label' => 'Don ung tuyen', 'data' => $activityTrend['applications'], 'color' => 'bg-amber-500'],
        ['label' => 'Danh gia', 'data' => $activityTrend['reviews'], 'color' => 'bg-emerald-500'],
    ];
@endphp
<div class="space-y-6">
    <section class="rounded-[28px] bg-slate-950 p-6 text-white shadow-xl shadow-slate-900/10">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-300">Phase 11</p>
                <h1 class="mt-3 text-3xl font-semibold">Bao cao tong quan he thong</h1>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-300">Tong hop cac chi so quan tri quan trong theo khung thoi gian da chon, bao gom quy mo nguoi dung, tien do xu ly nghiep vu, chat luong dao tao va xu huong hoat dong.</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 px-5 py-4 text-sm text-slate-200">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Ky bao cao</p>
                <p class="mt-2 text-lg font-semibold text-white">{{ $rangeLabel }}</p>
                <p class="mt-1 text-slate-400">Tong khoa hoc public hien co: {{ $summary['publicSubjects'] }}</p>
                <p class="text-slate-400">Dang ky cho duyet hien tai: {{ $summary['pendingEnrollments'] }}</p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="{{ route('admin.report') }}" class="grid gap-4 lg:grid-cols-[1fr_1fr_auto] lg:items-end">
            <div>
                <label class="text-sm font-medium text-slate-700">Tu ngay</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                @error('start_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Den ngay</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                @error('end_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Cap nhat bao cao</button>
                <a href="{{ route('admin.report') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Mac dinh</a>
            </div>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-3">
        @foreach ($summaryCards as $card)
            @php($tone = $toneMap[$card['tone']])
            <article class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $card['value'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $card['meta'] }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $tone['icon'] }}">
                        <i class="{{ $card['icon'] }} text-lg"></i>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(340px,0.85fr)]">
        <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Xu huong hoat dong</h2>
                    <p class="mt-1 text-sm text-slate-500">Bieu do nhe theo {{ $activityTrend['mode'] === 'day' ? 'ngay' : 'thang' }} trong ky bao cao.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ count($activityTrend['labels']) }} moc du lieu</span>
            </div>
            <div class="mt-6 space-y-6">
                @foreach ($trendSets as $trend)
                    <div>
                        <div class="mb-3 flex items-center justify-between text-sm">
                            <span class="font-semibold text-slate-800">{{ $trend['label'] }}</span>
                            <span class="text-slate-500">Tong {{ array_sum($trend['data']) }}</span>
                        </div>
                        <div class="grid gap-2" style="grid-template-columns: repeat({{ count($activityTrend['labels']) }}, minmax(0, 1fr));">
                            @foreach ($trend['data'] as $index => $value)
                                @php($height = max(10, (int) round(($value / $activityTrend['max']) * 120)))
                                <div class="flex flex-col items-center gap-2">
                                    <div class="flex h-32 items-end">
                                        <div class="w-6 rounded-t-xl {{ $trend['color'] }}" style="height: {{ $height }}px"></div>
                                    </div>
                                    <span class="text-[11px] font-medium text-slate-500">{{ $activityTrend['labels'][$index] }}</span>
                                    <span class="text-xs font-semibold text-slate-700">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="space-y-6">
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">Chat luong dao tao</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Diem trung binh</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quality['averageScore'] !== null ? $quality['averageScore'] : 'N/A' }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $quality['gradeCount'] }} ban ghi diem trong ky</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ty le dat</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quality['passRate'] !== null ? $quality['passRate'] . '%' : 'N/A' }}</p>
                        <p class="mt-1 text-xs text-slate-500">Tinh tren so ban ghi diem co score</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Danh gia khoa hoc</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quality['averageCourseRating'] !== null ? $quality['averageCourseRating'] . '/5' : 'N/A' }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $quality['courseReviewCount'] }} danh gia trong ky</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Danh gia giang vien</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quality['averageTeacherRating'] !== null ? $quality['averageTeacherRating'] . '/5' : 'N/A' }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $quality['teacherReviewCount'] }} review co giang vien</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">Du lieu bo sung</h2>
                <div class="mt-5 space-y-4 text-sm text-slate-600">
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4">
                        <p class="font-semibold text-slate-800">Ty le diem danh</p>
                        <p class="mt-2 leading-6">{{ $availability['attendance']['message'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4">
                        <p class="font-semibold text-slate-800">Doanh thu / thanh toan</p>
                        <p class="mt-2 leading-6">{{ $availability['payments']['message'] }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Top khoa hoc theo dang ky</h2>
                    <p class="mt-1 text-sm text-slate-500">Thong ke theo so luot dang ky trong ky bao cao.</p>
                </div>
            </div>
            <div class="mt-5 space-y-3">
                @forelse ($topCourses as $subject)
                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-4">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $subject->name }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $subject->category?->name ?? 'Chua phan nhom' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-semibold text-slate-900">{{ $subject->enrollments_in_period }}</p>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Dang ky</p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Chua co dang ky khoa hoc nao trong ky nay.</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Top giang vien theo danh gia</h2>
                    <p class="mt-1 text-sm text-slate-500">Tinh tren review cua cac lop hoc do giang vien phu trach.</p>
                </div>
            </div>
            <div class="mt-5 space-y-3">
                @forelse ($topTeachers as $teacherRow)
                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-4">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $teacherRow['teacher']->name }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $teacherRow['courses_count'] }} lop co review</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-semibold text-slate-900">{{ $teacherRow['average_rating'] }}/5</p>
                            <p class="text-xs uppercase tracking-wide text-slate-400">{{ $teacherRow['reviews_count'] }} review</p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Chua co du lieu danh gia giang vien trong ky nay.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection