@extends('bo_cuc.quan_tri')
@section('title', 'Tạo lớp học mới')
@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tạo lớp học mới</h1>
        </div>
        <a href="{{ route('admin.classes.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Quay lại</a>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.classes.store') }}" id="create-class-form">
            @csrf

            @php
                $preSubjectId = (string) old('subject_id', request('subject_id'));
                $preCourseId = (string) old('course_id', request('course_id'));
            @endphp

            <div class="grid gap-5 md:grid-cols-2">
                
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Môn học <span class="text-red-500">*</span></label>
                    <select id="subject_id" name="subject_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="">Chọn môn học...</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($preSubjectId === (string) $subject->id)>
                                {{ $subject->name }}{{ $subject->category ? ' — ' . $subject->category->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Khóa học <span class="text-red-500">*</span></label>
                    <select id="course_id" name="course_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="">Chọn khóa học...</option>
                        @foreach($courses as $course)
                            <option
                                value="{{ $course->id }}"
                                data-subject-id="{{ $course->subject_id }}"
                                @selected($preCourseId === (string) $course->id)
                            >
                                {{ $course->title }}{{ $course->subject ? ' — ' . $course->subject->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Giảng viên <span class="text-red-500">*</span></label>
                    <select name="teacher_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="">Chọn giảng viên...</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->displayName() }}</option>
                        @endforeach
                    </select>
                </div>

                
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Phòng học <span class="text-red-500">*</span></label>
                    <select name="room_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="">Chọn phòng học...</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
                                {{ $room->name }} ({{ $room->code }}) — Sức chứa: {{ $room->capacity }}
                            </option>
                        @endforeach
                    </select>
                </div>

                
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Ngày bắt đầu</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" />
                </div>

                
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Ghi chú</label>
                    <input type="text" name="note" value="{{ old('note') }}" placeholder="Ghi chú" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" />
                </div>
            </div>

            
            <div class="mt-6">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-semibold text-slate-700">Lịch học hàng tuần</label>
                    <button type="button" id="add-schedule-btn" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200 transition">
                        <i class="fas fa-plus mr-1"></i> Thêm buổi
                    </button>
                </div>

                <div id="schedule-rows" class="space-y-3">
                    
                </div>
            </div>

            <div class="mt-6 border-t border-slate-200 pt-5 flex justify-end gap-3">
                <a href="{{ route('admin.classes.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">Hủy</a>
                <button type="submit" class="rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700 transition">Tạo lớp học</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    let idx = 0;
    const days = @json(\App\Models\LichHoc::$dayOptions);
    const container = document.getElementById('schedule-rows');
    const addBtn = document.getElementById('add-schedule-btn');
    const subjectSelect = document.getElementById('subject_id');
    const courseSelect = document.getElementById('course_id');

    function syncCourseOptionsBySubject() {
        const subjectId = subjectSelect.value;
        let hasVisibleSelected = false;

        Array.from(courseSelect.options).forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                return;
            }

            const optionSubjectId = option.dataset.subjectId;
            const visible = !subjectId || optionSubjectId === subjectId;
            option.hidden = !visible;

            if (visible && option.selected) {
                hasVisibleSelected = true;
            }
        });

        if (!hasVisibleSelected) {
            courseSelect.value = '';
        }
    }

    function syncSubjectByCourse() {
        const selected = courseSelect.options[courseSelect.selectedIndex];
        const optionSubjectId = selected ? selected.dataset.subjectId : null;

        if (!optionSubjectId) {
            return;
        }

        subjectSelect.value = optionSubjectId;
        syncCourseOptionsBySubject();
    }

    function addRow() {
        const key = idx++;
        const dayOpts = Object.entries(days).map(([v, l]) =>
            `<option value="${v}">${l}</option>`
        ).join('');

        const row = document.createElement('div');
        row.className = 'flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3';
        row.innerHTML = `
            <select name="schedules[${key}][day]" required class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">${dayOpts}</select>
            <span class="text-sm text-slate-500">từ</span>
            <input type="time" name="schedules[${key}][start]" required class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm" />
            <span class="text-sm text-slate-500">đến</span>
            <input type="time" name="schedules[${key}][end]" required class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm" />
            <button type="button" onclick="this.closest('div').remove()" class="ml-auto text-red-400 hover:text-red-600 text-xs">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(row);
    }

    addBtn.addEventListener('click', addRow);
    subjectSelect.addEventListener('change', syncCourseOptionsBySubject);
    courseSelect.addEventListener('change', syncSubjectByCourse);

    syncCourseOptionsBySubject();
    syncSubjectByCourse();

    addRow();
})();
</script>
@endsection
