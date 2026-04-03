@extends('layouts.admin')
@section('title', 'Xep lich hoc vien')
@section('content')
@php
    $dayLabels = \App\Models\Course::dayOptions();
    $preferredDays = $enrollment->preferred_days;
    $selectedDays = is_array($preferredDays)
        ? $preferredDays
        : ((is_string($preferredDays) && $preferredDays !== '') ? (json_decode($preferredDays, true) ?: []) : []);
    $forceCreateNewClass = $enrollment->isCustomScheduleRequest()
        && in_array($enrollment->normalizedStatus(), [
            \App\Models\Enrollment::STATUS_PENDING,
            \App\Models\Enrollment::STATUS_APPROVED,
        ], true);
    $defaultMeetingDays = old('day_of_week', $forceCreateNewClass
        ? $selectedDays
        : ($enrollment->course?->meetingDayValues() ?: $selectedDays));
    $defaultMeetingDays = is_array($defaultMeetingDays)
        ? $defaultMeetingDays
        : (($defaultMeetingDays !== null && $defaultMeetingDays !== '') ? [$defaultMeetingDays] : []);
    $selectedCourseId = (string) old('course_id', '');
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('admin.schedules.queue') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                <i class="fas fa-arrow-left"></i>
                Quay lai hang cho xep lich
            </a>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Xep lich cho {{ $enrollment->user?->name }}</h1>
            <p class="mt-2 text-sm text-slate-600">
                {{ $forceCreateNewClass
                    ? 'Ho so nay se duoc luu vao lop cho mo. Admin chi chot ngay bat dau va ngay ket thuc sau khi lop du toi thieu ' . ($minimumStudentsToOpen ?? \App\Models\Course::minimumStudentsToOpen()) . ' hoc vien.'
                    : 'Chon lop hoc hien co hoac tao lop moi, sau do gan giang vien va lich hoc chinh thuc.' }}
            </p>
        </div>
        <span class="inline-flex rounded-full bg-cyan-50 px-4 py-2 text-sm font-semibold text-cyan-700">{{ $enrollment->statusLabel() }}</span>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(380px,0.9fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Nhu cau hoc vien</h2>
            <div class="mt-5 grid gap-4 text-sm text-slate-600 md:grid-cols-2">
                <p><strong>Hoc vien:</strong> {{ $enrollment->user?->name ?? 'Khong co du lieu' }}</p>
                <p><strong>Khoa hoc:</strong> {{ $enrollment->subject?->name ?? 'Chua xac dinh' }}</p>
                <p><strong>Nhom hoc:</strong> {{ $enrollment->subject?->category?->name ?? 'Chua phan nhom' }}</p>
                <p><strong>Loai ho so:</strong> {{ $enrollment->requestSourceLabel() }}</p>
                <p><strong>Khung gio mong muon:</strong> {{ $enrollment->start_time ?: '--:--' }} - {{ $enrollment->end_time ?: '--:--' }}</p>
                <p class="md:col-span-2"><strong>Ngay co the hoc:</strong> {{ $selectedDays ? implode(', ', array_map(fn ($day) => $dayLabels[$day] ?? $day, $selectedDays)) : 'Chua chon ngay hoc' }}</p>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                <p class="font-medium text-slate-800">Luu y kiem tra</p>
                <ul class="mt-2 space-y-2 leading-6">
                    <li>1. Lop cho mo chi luu giang vien, ngay hoc va khung gio; ngay khai giang se chot sau.</li>
                    <li>2. Neu da co lop cho mo cung mon phu hop, admin co the ghep them hoc vien vao lop do.</li>
                    <li>3. Khi du hoc vien, admin se mo lop va he thong se thong bao lai cho tung hoc vien.</li>
                </ul>
            </div>
        </section>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="post" action="{{ route('admin.schedules.enrollments.store', $enrollment) }}" class="space-y-4">
                @csrf

                @if ($forceCreateNewClass)
                    <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-4 text-sm text-cyan-800">
                        Buoc nay se luu lop cho mo. Ngay bat dau va ngay ket thuc se duoc admin chon o buoc mo lop khi du {{ $minimumStudentsToOpen ?? \App\Models\Course::minimumStudentsToOpen() }} hoc vien.
                    </div>

                    @if (($waitingCourses ?? collect())->isNotEmpty())
                        <div>
                            <label class="text-sm font-medium text-slate-700">Ghep vao lop cho mo da co</label>
                            <select id="waiting-course-select" name="course_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                                <option value="">Tao lop cho mo moi ben duoi</option>
                                @foreach ($waitingCourses as $waitingCourse)
                                    <option
                                        value="{{ $waitingCourse->id }}"
                                        data-title="{{ $waitingCourse->title }}"
                                        data-teacher="{{ $waitingCourse->teacher_id }}"
                                        data-days='@json($waitingCourse->meetingDayValues())'
                                        data-start-time="{{ $waitingCourse->start_time }}"
                                        data-end-time="{{ $waitingCourse->end_time }}"
                                        data-capacity="{{ $waitingCourse->capacity ?? 20 }}"
                                        @selected($selectedCourseId === (string) $waitingCourse->id)
                                    >
                                        {{ $waitingCourse->title }} - {{ $waitingCourse->scheduled_students_count }}/{{ $minimumStudentsToOpen ?? \App\Models\Course::minimumStudentsToOpen() }} hoc vien
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @endif
                @else
                    <div>
                        <label class="text-sm font-medium text-slate-700">Chon lop hoc co san</label>
                        <select name="course_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                            <option value="">Tao lop moi ben duoi</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" @selected(old('course_id', $enrollment->course_id) == $course->id)>
                                    {{ $course->title }} - {{ $course->formattedSchedule() }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                @endif

                <div class="rounded-2xl border border-dashed border-slate-300 p-4">
                    <h3 class="text-sm font-semibold text-slate-800">{{ $forceCreateNewClass ? 'Tao lop cho mo moi' : 'Hoac tao lop moi' }}</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Ten lop hoc</label>
                            <input id="new-course-title" type="text" name="new_course_title" value="{{ old('new_course_title', $suggestedCourseTitle ?? '') }}" readonly class="mt-2 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none">
                            <p class="mt-1 text-xs text-slate-500">Ten lop duoc tu sinh theo mon hoc da dang ky va chi tang so khoa hoc.</p>
                            @error('new_course_title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Mo ta lop hoc</label>
                            <textarea name="new_course_description" rows="3" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">{{ old('new_course_description') }}</textarea>
                            @error('new_course_description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Giang vien</label>
                    <select id="teacher-select" name="teacher_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        <option value="">Chon giang vien</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('teacher_id', $enrollment->assigned_teacher_id) == $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    @error('teacher_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Ngay hoc trong tuan</label>
                        <p class="mt-1 text-xs text-slate-500">Co the chon nhieu ngay voi cung mot khung gio.</p>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            @foreach ($dayLabels as $value => $label)
                                <label class="flex items-center gap-3 rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50/40">
                                    <input type="checkbox" name="day_of_week[]" value="{{ $value }}" @checked(in_array($value, $defaultMeetingDays, true)) class="day-checkbox h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('day_of_week')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        @error('day_of_week.*')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    @if (! $forceCreateNewClass)
                        <div>
                            <label class="text-sm font-medium text-slate-700">Ngay bat dau</label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                            @error('start_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @else
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                            Ngay bat dau va ngay ket thuc se duoc chon sau khi lop du toi thieu {{ $minimumStudentsToOpen ?? \App\Models\Course::minimumStudentsToOpen() }} hoc vien.
                        </div>
                    @endif

                    <div>
                        <label class="text-sm font-medium text-slate-700">Gio bat dau</label>
                        <input id="start-time-input" type="time" name="start_time" value="{{ old('start_time', $enrollment->start_time) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        @error('start_time')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Gio ket thuc</label>
                        <input id="end-time-input" type="time" name="end_time" value="{{ old('end_time', $enrollment->end_time) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        @error('end_time')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    @if (! $forceCreateNewClass)
                        <div>
                            <label class="text-sm font-medium text-slate-700">Ngay ket thuc</label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                            @error('end_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    <div>
                        <label class="text-sm font-medium text-slate-700">Si so toi da</label>
                        <input id="capacity-input" type="number" min="1" max="999" name="capacity" value="{{ old('capacity', 20) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        @error('capacity')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Ghi chu admin</label>
                    <textarea name="note" rows="4" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">{{ old('note', $enrollment->note) }}</textarea>
                    @error('note')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white hover:bg-cyan-700">
                    {{ $forceCreateNewClass ? 'Luu lop cho mo' : 'Xac nhan xep lich chinh thuc' }}
                </button>
            </form>
        </aside>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const waitingCourseSelect = document.getElementById('waiting-course-select');
    const titleInput = document.getElementById('new-course-title');
    const teacherSelect = document.getElementById('teacher-select');
    const startTimeInput = document.getElementById('start-time-input');
    const endTimeInput = document.getElementById('end-time-input');
    const capacityInput = document.getElementById('capacity-input');
    const dayCheckboxes = Array.from(document.querySelectorAll('.day-checkbox'));
    const suggestedTitle = @json($suggestedCourseTitle ?? '');

    function setCheckedDays(days) {
        dayCheckboxes.forEach((checkbox) => {
            checkbox.checked = Array.isArray(days) && days.includes(checkbox.value);
        });
    }

    function applyWaitingCourseOption() {
        if (!waitingCourseSelect) {
            if (titleInput && !titleInput.value.trim()) {
                titleInput.value = suggestedTitle;
            }
            return;
        }

        const option = waitingCourseSelect.options[waitingCourseSelect.selectedIndex];

        if (!option || !option.value) {
            titleInput.value = suggestedTitle;
            return;
        }

        titleInput.value = option.dataset.title || suggestedTitle;
        if (teacherSelect && option.dataset.teacher) {
            teacherSelect.value = option.dataset.teacher;
        }
        if (startTimeInput && option.dataset.startTime) {
            startTimeInput.value = option.dataset.startTime;
        }
        if (endTimeInput && option.dataset.endTime) {
            endTimeInput.value = option.dataset.endTime;
        }
        if (capacityInput && option.dataset.capacity) {
            capacityInput.value = option.dataset.capacity;
        }

        try {
            setCheckedDays(JSON.parse(option.dataset.days || '[]'));
        } catch (error) {
            setCheckedDays([]);
        }
    }

    if (titleInput) {
        titleInput.readOnly = true;
    }

    applyWaitingCourseOption();

    if (waitingCourseSelect) {
        waitingCourseSelect.addEventListener('change', applyWaitingCourseOption);
    }
});
</script>
@endsection
