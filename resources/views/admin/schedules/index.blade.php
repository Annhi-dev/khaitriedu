@extends('layouts.admin')
@section('title', 'Lịch học toàn hệ thống')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 9</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Lịch học toàn hệ thống</h1>
            <p class="mt-2 text-sm text-slate-600">Theo dõi các lớp đã được xếp lịch, lọc theo giảng viên, học viên, lớp học hoặc ngày cụ thể.</p>
        </div>
        <a href="{{ route('admin.schedules.queue') }}" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Hàng chờ xếp lịch</a>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="{{ route('admin.schedules.index') }}" class="grid gap-4 xl:grid-cols-5 xl:items-end">
            <div>
                <label class="text-sm font-medium text-slate-700">Giảng viên</label>
                <select name="teacher_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                    <option value="">Tất cả giảng viên</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(($filters['teacher_id'] ?? '') == $teacher->id)>{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Học viên</label>
                <select name="student_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                    <option value="">Tất cả học viên</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected(($filters['student_id'] ?? '') == $student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Lớp học</label>
                <select name="course_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                    <option value="">Tất cả lớp học</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected(($filters['course_id'] ?? '') == $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Ngày học</label>
                <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Lọc lịch học</button>
                <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse ($schedules as $course)
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $course->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $course->title }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ $course->subject?->name ?? 'Chưa gắn khóa học public' }}</p>
                    </div>
                    <span class="inline-flex rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $course->statusLabel() }}</span>
                </div>
                <div class="mt-5 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                    <p><strong>Giảng viên:</strong> {{ $course->teacher?->name ?? 'Chưa phân công' }}</p>
                    <p><strong>Lịch:</strong> {{ $course->formattedSchedule() }}</p>
                    <p><strong>Sĩ số:</strong> {{ $course->scheduled_students_count }}/{{ $course->capacity ?? 20 }}</p>
                    <p><strong>Ngày học:</strong> {{ $course->dayLabel() }}</p>
                </div>
                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                    <p class="font-medium text-slate-800">Học viên trong lớp</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse ($course->enrollments->whereIn('status', \App\Models\Enrollment::courseAccessStatuses()) as $enrollment)
                            <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">{{ $enrollment->user?->name ?? 'Học viên' }}</span>
                        @empty
                            <span class="text-xs text-slate-500">Chưa có học viên nào được xếp vào lớp này.</span>
                        @endforelse
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-500 xl:col-span-2">
                Chưa có lớp học nào được xếp lịch chính thức. Hãy xử lý hàng chờ xếp lịch trước.
            </div>
        @endforelse
    </section>

    @if ($schedules->hasPages())
        <div>{{ $schedules->links() }}</div>
    @endif
</div>
@endsection