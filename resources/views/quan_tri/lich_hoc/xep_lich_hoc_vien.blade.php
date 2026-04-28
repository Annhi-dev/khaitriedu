@extends('bo_cuc.quan_tri')
@section('title', 'Xếp lịch học viên')
@section('content')
@php
    $dayLabels = \App\Models\Course::dayOptions();
    $preferredDays = $enrollment->preferred_days;
    $normalizeTime = fn (?string $time) => \App\Helpers\ScheduleHelper::normalizeTimeValue($time);
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
                Quay lại hàng chờ xếp lịch
            </a>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Xếp lịch cho {{ $enrollment->user?->name }}</h1>
            <p class="mt-2 text-sm text-slate-500">Lưu ý kiểm tra thông tin trước khi xếp lịch.</p>
        </div>
        <span class="inline-flex rounded-full bg-cyan-50 px-4 py-2 text-sm font-semibold text-cyan-700">{{ $enrollment->statusLabel() }}</span>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(380px,0.9fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Nhu cau hoc vien</h2>
            <div class="mt-5 grid gap-4 text-sm text-slate-600 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Học viên</p>
                    <p class="mt-1 font-medium text-slate-700">{{ $enrollment->user?->name ?? 'Khong co du lieu' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Khóa học</p>
                    <p class="mt-1 font-medium text-slate-700">{{ $enrollment->subject?->name ?? 'Chua xac dinh' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Nhóm học</p>
                    <p class="mt-1 font-medium text-slate-700">{{ $enrollment->subject?->category?->name ?? 'Chua phan nhom' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Loại hồ sơ</p>
                    <p class="mt-1 font-medium text-slate-700">{{ $enrollment->requestSourceLabel() }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Khung giờ mong muốn</p>
                    <p class="mt-1 font-medium text-slate-700">{{ $enrollment->start_time ?: '--:--' }} - {{ $enrollment->end_time ?: '--:--' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3 md:col-span-2">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Ngày có thể học</p>
                    <p class="mt-1 font-medium text-slate-700">{{ $selectedDays ? implode(', ', array_map(fn ($day) => $dayLabels[$day] ?? $day, $selectedDays)) : 'Chua chon ngay hoc' }}</p>
                </div>
            </div>

        </section>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="post" action="{{ route('admin.schedules.enrollments.store', $enrollment) }}" class="space-y-4">
                @csrf

                @if ($forceCreateNewClass)
                    @if (($waitingCourses ?? collect())->isNotEmpty())
                        <div>
                        <label class="text-sm font-medium text-slate-700">Ghép vào lớp chờ mở đã có</label>
                            <select id="waiting-course-select" name="course_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                                <option value="">Tạo lớp chờ mở mới bên dưới</option>
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
                                        {{ $waitingCourse->title }} - {{ $waitingCourse->scheduled_students_count }}/{{ $minimumStudentsToOpen ?? \App\Models\Course::minimumStudentsToOpen() }} học viên
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @endif
                @else
                    <div>
                        <label class="text-sm font-medium text-slate-700">Chọn lớp học có sẵn</label>
                        <select name="course_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                            <option value="">Tạo lớp mới bên dưới</option>
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
                    <h3 class="text-sm font-semibold text-slate-800">{{ $forceCreateNewClass ? 'Tạo lớp chờ mở mới' : 'Hoặc tạo lớp mới' }}</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Tên lớp học</label>
                            <input id="new-course-title" type="text" name="new_course_title" value="{{ old('new_course_title', $suggestedCourseTitle ?? '') }}" readonly class="mt-2 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none">
                            @error('new_course_title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Mô tả lớp học</label>
                            <textarea name="new_course_description" rows="3" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">{{ old('new_course_description') }}</textarea>
                            @error('new_course_description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Giảng viên</label>
                    <select id="teacher-select" name="teacher_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        <option value="">Chọn giảng viên</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('teacher_id', $enrollment->assigned_teacher_id) == $teacher->id)>{{ $teacher->displayName() }}</option>
                        @endforeach
                    </select>
                    @if ($teachers->isEmpty())
                        <p class="mt-1 text-sm text-amber-600">Chưa có giảng viên phù hợp với chuyên môn của môn học này.</p>
                    @endif
                    @error('teacher_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        {{ $forceCreateNewClass ? 'Phòng học dự kiến (tùy chọn)' : 'Phòng học chính thức' }}
                    </label>
                    <select name="room_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                            <option value="">{{ $forceCreateNewClass ? 'Sẽ chốt khi mở lớp' : 'Chọn phòng học' }}</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" @selected((string) old('room_id') === (string) $room->id)>
                                {{ $room->name }}{{ $room->code ? ' (' . $room->code . ')' : '' }} - suc chua {{ $room->capacity }}
                            </option>
                        @endforeach
                    </select>
                    @error('room_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Ngày học trong tuần</label>
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
                            Ngày bắt đầu và ngày kết thúc sẽ được chọn sau khi lớp đủ tối thiểu {{ $minimumStudentsToOpen ?? \App\Models\Course::minimumStudentsToOpen() }} học viên.
                        </div>
                    @endif

                    <div>
                        <label class="text-sm font-medium text-slate-700">Giờ bắt đầu</label>
                        <input id="start-time-input" type="time" name="start_time" value="{{ old('start_time') !== null ? $normalizeTime(old('start_time')) : $normalizeTime($enrollment->start_time) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        @error('start_time')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Giờ kết thúc</label>
                        <input id="end-time-input" type="time" name="end_time" value="{{ old('end_time') !== null ? $normalizeTime(old('end_time')) : $normalizeTime($enrollment->end_time) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        @error('end_time')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    @if (! $forceCreateNewClass)
                        <div>
                            <label class="text-sm font-medium text-slate-700">Ngày kết thúc</label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                            @error('end_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    <div>
                        <label class="text-sm font-medium text-slate-700">Sĩ số tối đa</label>
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
                    {{ $forceCreateNewClass ? 'Lưu lớp chờ mở' : 'Xác nhận xếp lịch chính thức' }}
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
