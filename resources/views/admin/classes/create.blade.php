@extends('layouts.admin')
@section('title', 'Tạo lớp học mới')
@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tạo lớp học mới</h1>
            <p class="mt-1 text-sm text-slate-500">Chọn khóa học, phòng, giảng viên và thêm lịch học hàng tuần.</p>
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

            <div class="grid gap-5 md:grid-cols-2">
                {{-- Môn học --}}
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Môn học <span class="text-red-500">*</span></label>
                    @php $preSubjectId = old('subject_id', request('subject_id')); @endphp
                    <select name="subject_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="">Chọn môn học...</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($preSubjectId == $subject->id)>
                                {{ $subject->name }}{{ $subject->category ? ' — ' . $subject->category->name : '' }}
                            </option>
                        @endforeach
                    </select>
                    @if(request('subject_id'))
                        <p class="mt-1 text-xs text-green-600">✅ Môn học đã được điền sẵn từ khóa học bạn chọn.</p>
                    @endif
                </div>

                {{-- Giảng viên --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Giảng viên</label>
                    <select name="teacher_id" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="">Chưa phân công</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Phòng học --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Phòng học</label>
                    <select name="room_id" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="">Chưa chọn phòng</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
                                {{ $room->name }} ({{ $room->code }}) — Sức chứa: {{ $room->capacity }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Ngày bắt đầu --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Ngày bắt đầu</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" />
                </div>

                {{-- Ghi chú --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Ghi chú</label>
                    <input type="text" name="note" value="{{ old('note') }}" placeholder="Ghi chú nội bộ..." class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" />
                </div>
            </div>

            {{-- Lịch học hàng tuần --}}
            <div class="mt-6">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-semibold text-slate-700">Lịch học hàng tuần</label>
                    <button type="button" id="add-schedule-btn" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200 transition">
                        <i class="fas fa-plus mr-1"></i> Thêm buổi
                    </button>
                </div>

                <div id="schedule-rows" class="space-y-3">
                    {{-- JS sẽ thêm rows vào đây --}}
                </div>
                <p class="mt-2 text-xs text-slate-400">Thêm từng buổi học trong tuần (ví dụ: Thứ 2 08:00–10:00, Thứ 4 08:00–10:00)</p>
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
    const days = @json(\App\Models\ClassSchedule::$dayOptions);
    const container = document.getElementById('schedule-rows');
    const addBtn = document.getElementById('add-schedule-btn');

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
    // Thêm sẵn 1 row mặc định
    addRow();
})();
</script>
@endsection
