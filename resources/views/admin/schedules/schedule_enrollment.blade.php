@extends('layouts.admin')
@section('title', 'Xếp lịch học viên')
@section('content')
@php
    $dayLabels = [
        'Monday' => 'Thứ 2',
        'Tuesday' => 'Thứ 3',
        'Wednesday' => 'Thứ 4',
        'Thursday' => 'Thứ 5',
        'Friday' => 'Thứ 6',
        'Saturday' => 'Thứ 7',
        'Sunday' => 'Chủ nhật',
    ];
    $selectedDays = $enrollment->preferred_days ? (json_decode($enrollment->preferred_days, true) ?: []) : [];
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('admin.schedules.queue') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                <i class="fas fa-arrow-left"></i>
                Quay lại hàng chờ xếp lịch
            </a>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Xếp lịch cho {{ $enrollment->user?->name }}</h1>
            <p class="mt-2 text-sm text-slate-600">Chọn lớp học hiện có hoặc tạo lớp mới, sau đó gán giảng viên và lịch học chính thức.</p>
        </div>
        <span class="inline-flex rounded-full bg-cyan-50 px-4 py-2 text-sm font-semibold text-cyan-700">{{ $enrollment->statusLabel() }}</span>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(380px,0.9fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Nhu cầu học viên</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2 text-sm text-slate-600">
                <p><strong>Học viên:</strong> {{ $enrollment->user?->name ?? 'Không có dữ liệu' }}</p>
                <p><strong>Khóa học:</strong> {{ $enrollment->subject?->name ?? 'Chưa xác định' }}</p>
                <p><strong>Nhóm học:</strong> {{ $enrollment->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                <p><strong>Khung giờ mong muốn:</strong> {{ $enrollment->start_time ?: '--:--' }} - {{ $enrollment->end_time ?: '--:--' }}</p>
                <p class="md:col-span-2"><strong>Ngày có thể học:</strong> {{ $selectedDays ? implode(', ', array_map(fn ($day) => $dayLabels[$day] ?? $day, $selectedDays)) : 'Chưa chọn ngày học' }}</p>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                <p class="font-medium text-slate-800">Lưu ý kiểm tra</p>
                <ul class="mt-2 space-y-2 leading-6">
                    <li>1. Không để trùng lịch giảng viên ở cùng ngày và khung giờ.</li>
                    <li>2. Không xếp học viên vào lớp có lịch trùng với lớp khác đang học.</li>
                    <li>3. Không vượt quá sĩ số lớp học.</li>
                </ul>
            </div>
        </section>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="post" action="{{ route('admin.schedules.enrollments.store', $enrollment) }}" class="space-y-4">
                @csrf
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

                <div class="rounded-2xl border border-dashed border-slate-300 p-4">
                    <h3 class="text-sm font-semibold text-slate-800">Hoặc tạo lớp mới</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Tên lớp học mới</label>
                            <input type="text" name="new_course_title" value="{{ old('new_course_title') }}" placeholder="Ví dụ: {{ $enrollment->subject?->name }} - Ca tối" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                            @error('new_course_title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Mô tả lớp học mới</label>
                            <textarea name="new_course_description" rows="3" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">{{ old('new_course_description') }}</textarea>
                            @error('new_course_description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Giảng viên</label>
                    <select name="teacher_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        <option value="">Chọn giảng viên</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('teacher_id', $enrollment->assigned_teacher_id) == $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    @error('teacher_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Ngày học trong tuần</label>
                        <select name="day_of_week" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                            <option value="">Chọn ngày</option>
                            @foreach ($dayLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('day_of_week') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('day_of_week')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Ngày bắt đầu</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        @error('start_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Giờ bắt đầu</label>
                        <input type="time" name="start_time" value="{{ old('start_time', $enrollment->start_time) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        @error('start_time')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Giờ kết thúc</label>
                        <input type="time" name="end_time" value="{{ old('end_time', $enrollment->end_time) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        @error('end_time')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Ngày kết thúc</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        @error('end_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Sĩ số tối đa</label>
                        <input type="number" min="1" max="999" name="capacity" value="{{ old('capacity', 20) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        @error('capacity')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Ghi chú admin</label>
                    <textarea name="note" rows="4" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">{{ old('note', $enrollment->note) }}</textarea>
                    @error('note')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Xác nhận xếp lịch chính thức</button>
            </form>
        </aside>
    </div>
</div>
@endsection