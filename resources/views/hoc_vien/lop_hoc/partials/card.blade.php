@php
    $classRoom = $enrollment->classRoom;
    $course = $enrollment->course;
    $subject = $enrollment->subject;
    $status = $enrollment->displayStatus();
    $statusLabel = $enrollment->displayStatusLabel();
    $statusClass = match ($status) {
        \App\Models\Enrollment::STATUS_COMPLETED => 'bg-slate-100 text-slate-700',
        \App\Models\Enrollment::STATUS_ACTIVE => 'bg-emerald-100 text-emerald-700',
        \App\Models\Enrollment::STATUS_SCHEDULED, \App\Models\Enrollment::STATUS_ENROLLED, \App\Models\Enrollment::STATUS_APPROVED => 'bg-cyan-100 text-cyan-700',
        \App\Models\Enrollment::STATUS_PENDING => 'bg-amber-100 text-amber-700',
        \App\Models\Enrollment::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
        default => 'bg-slate-100 text-slate-600',
    };
    $scheduleText = $classRoom?->schedules?->isNotEmpty()
        ? $classRoom->scheduleSummary()
        : ($course?->formattedSchedule() ?? 'Chưa có lịch cụ thể');
    $gradeAverage = $enrollment->grades->whereNotNull('score')->isNotEmpty()
        ? round((float) $enrollment->grades->whereNotNull('score')->avg('score'), 2)
        : null;
@endphp

<article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Lớp học</p>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>

            <h4 class="mt-2 text-xl font-semibold text-slate-900">{{ $classRoom?->displayName() ?? $course?->title ?? $subject?->name ?? 'Lớp học' }}</h4>

            <div class="mt-4 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                <p><span class="font-medium text-slate-500">Môn học:</span> {{ $subject?->name ?? 'Chưa xác định' }}</p>
                <p><span class="font-medium text-slate-500">Khóa học:</span> {{ $course?->title ?? 'Chưa xác định' }}</p>
                <p><span class="font-medium text-slate-500">Giáo viên:</span> {{ $classRoom?->teacher?->displayName() ?? $enrollment->assignedTeacher?->displayName() ?? 'Chưa phân công' }}</p>
                <p><span class="font-medium text-slate-500">Phòng học:</span> {{ $classRoom?->room?->name ?? 'Chưa phân phòng' }}</p>
                <p><span class="font-medium text-slate-500">Lịch học:</span> {{ $scheduleText }}</p>
                <p><span class="font-medium text-slate-500">Điểm TB:</span> {{ $gradeAverage !== null ? number_format($gradeAverage, 2) : 'Chưa có' }}</p>
            </div>
        </div>

        <div class="shrink-0">
            <a href="{{ route('student.classes.show', $enrollment) }}" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 font-semibold text-white transition hover:bg-cyan-700">
                <i class="fas fa-arrow-right"></i>
                Xem chi tiết
            </a>
        </div>
    </div>
</article>
