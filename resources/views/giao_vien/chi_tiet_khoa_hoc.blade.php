@extends('bo_cuc.giao_vien')

@section('title', 'Chi tiết lớp học')
@section('eyebrow', 'Lớp học của tôi')

@section('content')
<div class="space-y-6">
    <a href="{{ route('teacher.courses') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
        <i class="fas fa-arrow-left"></i>
        Quay lại lớp học phụ trách
    </a>

    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('status') }}</div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">{{ session('error') }}</div>
    @endif

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="border-b border-slate-100 p-6 lg:border-b-0 lg:border-r lg:p-7">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">Lớp đang giảng dạy</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $course->title }}</h1>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">{{ $course->description ?: 'Lớp này đã được phân cho bạn phụ trách.' }}</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white">
                        {{ $course->modules->count() }} module
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700">
                        {{ $course->enrollments->count() }} học viên
                    </span>
                </div>
            </div>

            <div class="grid gap-3 bg-slate-50 p-6 sm:grid-cols-2 lg:grid-cols-1">
                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Khóa học</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $course->subject?->name ?? 'Chưa gắn khóa học' }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $course->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                </div>
                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Lịch lớp</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $course->formattedSchedule() }}</p>
                    <p class="mt-1 text-sm text-slate-500">Lịch cố định của lớp</p>
                </div>
            </div>
        </div>
    </section>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('teacher.schedule-change-requests.create', $course) }}" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700">Gửi yêu cầu dời buổi</a>
        <a href="{{ route('teacher.schedule-change-requests.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Xem lịch sử yêu cầu</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <section class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 p-5">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Module trong lớp</h2>
                    <p class="mt-1 text-sm text-slate-500">Các module đang áp dụng cho lớp học này.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">{{ $course->modules->count() }} module</span>
            </div>

            <div class="space-y-4 p-5">
                @forelse($course->modules as $module)
                    <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700">Module {{ $module->position ?? $loop->iteration }}</p>
                                <h3 class="mt-2 text-lg font-semibold text-slate-950">{{ $module->title }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $module->learningSummary() }}</p>
                            </div>
                            <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">{{ $module->sessionCountLabel() }}</span>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500">
                        Chưa có module nào trong lớp học này.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 p-5">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Học viên đã được xếp lớp</h2>
                    <p class="mt-1 text-sm text-slate-500">Quản lý điểm, đánh giá và tiến độ học tập.</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">{{ $course->enrollments->count() }} học viên</span>
            </div>

            <div class="p-5">
                @php $activeEnrollments = $course->enrollments; @endphp
                @if($activeEnrollments->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-500">
                        Chưa có học viên nào được xếp vào lớp này.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($activeEnrollments as $enrollment)
                            <article class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-950">{{ $enrollment->user?->name }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ $enrollment->displayStatusLabel() }} - Lịch đã chốt: {{ $enrollment->schedule ?: $course->formattedSchedule() }}</p>
                                    </div>
                                    @php
                                        $courseStat = $studentCourseProgress[$enrollment->id] ?? ['completed' => 0, 'total' => 0, 'percent' => 0];
                                    @endphp
                                    <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $courseStat['percent'] }}%</span>
                                </div>

                                <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="font-semibold text-emerald-800">Tiến độ khóa học</div>
                                        <div class="font-black text-emerald-700">{{ $courseStat['percent'] }}%</div>
                                    </div>
                                    <div class="mt-1 text-emerald-700">Đã học {{ $courseStat['completed'] }}/{{ $courseStat['total'] }} bài.</div>
                                </div>

                                <div class="mt-4 space-y-3">
                                    @foreach($course->modules as $module)
                                        @php
                                            $grade = $gradeMap->get($enrollment->id . '-' . $module->id);
                                            $moduleStat = $studentModuleProgress[$enrollment->id][$module->id] ?? [
                                                'completed' => 0,
                                                'total' => $module->lessons->count(),
                                                'percent' => 0,
                                            ];
                                        @endphp
                                        <form method="post" action="{{ route('teacher.grades.update') }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                            @csrf
                                            <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}" />
                                            <input type="hidden" name="module_id" value="{{ $module->id }}" />
                                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900">{{ $module->title }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $moduleStat['completed'] }}/{{ $moduleStat['total'] }} bài - {{ $moduleStat['percent'] }}%</p>
                                                </div>
                                                <div class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                                                    Module {{ $module->position ?? $loop->iteration }}
                                                </div>
                                            </div>
                                            <div class="mt-4 grid gap-2 md:grid-cols-[120px_100px_1fr_auto]">
                                                <input name="score" value="{{ $grade->score ?? '' }}" placeholder="Điểm" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-cyan-500 focus:outline-none" type="number" min="0" max="100" />
                                                <input name="grade" value="{{ $grade->grade ?? '' }}" placeholder="A/B/C" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-cyan-500 focus:outline-none" maxlength="5" />
                                                <input name="feedback" value="{{ $grade->feedback ?? '' }}" placeholder="Phản hồi cho học viên" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-cyan-500 focus:outline-none" />
                                                <button type="submit" class="rounded-xl bg-cyan-600 px-4 py-2 font-semibold text-white transition hover:bg-cyan-700">Lưu</button>
                                            </div>
                                        </form>
                                    @endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection
