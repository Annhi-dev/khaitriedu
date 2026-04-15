@extends('layouts.admin')
@section('title', 'Kiem tra xung dot lich')
@section('content')
@php
    $candidate = $candidate ?? [];
    $teacherConflicts = $teacherConflicts ?? collect();
    $roomConflicts = $roomConflicts ?? collect();
    $canCheck = (bool) ($candidate['ready'] ?? false);
    $hasConflicts = (bool) ($hasConflicts ?? false);
    $selectedDays = $candidate['days'] ?? [];
    $selectedCourseId = (int) ($filters['course_id'] ?? ($candidate['course']?->id ?? 0));
    $selectedClassRoomId = (int) ($filters['class_room_id'] ?? ($candidate['classRoom']?->id ?? 0));
    $selectedTeacherId = (int) ($candidate['teacher_id'] ?? ($filters['teacher_id'] ?? 0));
    $selectedRoomId = (int) ($candidate['room_id'] ?? ($filters['room_id'] ?? 0));
    $previewCourse = $candidate['previewCourse'] ?? new \App\Models\Course();
    $sourceLabel = $candidate['source_label'] ?? 'Nhập tay';
    $scheduleLabel = $candidate['schedule_label'] ?? $previewCourse->formattedSchedule();
    $teacherName = $candidate['teacher']?->name ?? 'Chưa chọn';
    $roomName = $candidate['room']?->name ?? 'Chưa chọn';
@endphp

<div class="space-y-6">
    <x-admin.page-header title="Kiem tra xung dot lich">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Quay lại lịch</a>
            <a href="{{ route('admin.schedules.queue') }}" class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700 transition">Hàng chờ xếp lịch</a>
        </div>
    </x-admin.page-header>

    <form method="get" action="{{ route('admin.schedules.conflicts') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-5 lg:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Khóa học nguồn</label>
                <select name="course_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">-- Chọn khóa học --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected($selectedCourseId === $course->id)>
                            {{ $course->title }}{{ $course->formattedSchedule() ? ' — ' . $course->formattedSchedule() : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Lớp học hiện hành</label>
                <select name="class_room_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">-- Chọn lớp học --</option>
                    @foreach($classRooms as $classRoom)
                        <option value="{{ $classRoom->id }}" @selected($selectedClassRoomId === $classRoom->id)>
                            {{ $classRoom->displayName() }}{{ $classRoom->scheduleSummary() ? ' — ' . $classRoom->scheduleSummary() : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Giảng viên</label>
                <select name="teacher_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">-- Chọn giảng viên --</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected($selectedTeacherId === $teacher->id)>{{ $teacher->displayName() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Phòng học</label>
                <select name="room_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">-- Chọn phòng học --</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @selected($selectedRoomId === $room->id)>
                            {{ $room->name }} ({{ $room->code }}) — {{ $room->capacity }} chỗ
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700">Ngày học</label>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
                    @foreach($dayOptions as $dayValue => $dayLabel)
                        <label class="flex cursor-pointer items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50">
                            <input type="checkbox" name="day_of_week[]" value="{{ $dayValue }}" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" @checked(in_array($dayValue, $selectedDays, true))>
                            <span>{{ $dayLabel }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Ngày bắt đầu</label>
                <input type="date" name="start_date" value="{{ $candidate['start_date'] ?? ($filters['start_date'] ?? '') }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Ngày kết thúc</label>
                <input type="date" name="end_date" value="{{ $candidate['end_date'] ?? ($filters['end_date'] ?? '') }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Giờ bắt đầu</label>
                <input type="time" name="start_time" value="{{ $candidate['start_time'] ?? ($filters['start_time'] ?? '') }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Giờ kết thúc</label>
                <input type="time" name="end_time" value="{{ $candidate['end_time'] ?? ($filters['end_time'] ?? '') }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-5">
            <div class="flex gap-2">
                <a href="{{ route('admin.schedules.conflicts') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Đặt lại</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Kiểm tra</button>
            </div>
        </div>
    </form>

    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Nguồn dữ liệu</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $sourceLabel }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Lịch kiểm tra</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $scheduleLabel }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ $teacherName }} • {{ $roomName }}</p>
        </div>
        <div class="rounded-3xl border {{ $canCheck ? ($hasConflicts ? 'border-rose-200 bg-rose-50' : 'border-emerald-200 bg-emerald-50') : 'border-slate-200 bg-slate-50' }} p-5 shadow-sm">
            <p class="text-xs uppercase tracking-[0.2em] {{ $canCheck ? ($hasConflicts ? 'text-rose-500' : 'text-emerald-600') : 'text-slate-400' }}">Kết luận</p>
            <p class="mt-2 text-lg font-semibold {{ $canCheck ? ($hasConflicts ? 'text-rose-900' : 'text-emerald-900') : 'text-slate-900' }}">
                @if(! $canCheck)
                    Chưa đủ dữ liệu
                @elseif($hasConflicts)
                    Có xung đột
                @else
                    Không phát hiện xung đột
                @endif
            </p>
            <p class="mt-2 text-sm {{ $canCheck ? ($hasConflicts ? 'text-rose-700' : 'text-emerald-700') : 'text-slate-500' }}">
                @if(! $canCheck)
                    Cần ít nhất một giảng viên hoặc phòng học, ngày học, giờ học và khoảng ngày để chạy kiểm tra.
                @elseif($hasConflicts)
                    Hệ thống đã tìm thấy lịch chồng khung giờ với dữ liệu đang chọn.
                @else
                    Cấu hình hiện tại chưa ghi nhận trùng giảng viên hoặc phòng học.
                @endif
            </p>
        </div>
    </section>

    @if($canCheck)
        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Xung đột giảng viên</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Các lớp trùng người dạy</h2>
                    </div>
                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">{{ $teacherConflicts->count() }} lớp</span>
                </div>

                @if($teacherConflicts->isNotEmpty())
                    <div class="mt-6 space-y-4">
                        @foreach($teacherConflicts as $conflictCourse)
                            @php
                                $conflictClassRoom = $conflictCourse->currentClassRoom();
                            @endphp
                            <div class="rounded-2xl border border-rose-100 bg-rose-50/70 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-rose-500">{{ $conflictCourse->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                                        <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ $conflictCourse->title }}</h3>
                                        <p class="mt-1 text-sm text-slate-600">{{ $conflictCourse->formattedSchedule() }}</p>
                                    </div>
                                    <x-admin.badge :type="$conflictCourse->isPendingOpen() ? 'warning' : 'info'" :text="$conflictCourse->statusLabel()" />
                                </div>

                                <div class="mt-4 grid gap-3 md:grid-cols-2">
                                    <div class="rounded-xl bg-white px-3 py-2 text-sm text-slate-700">
                                        <span class="text-slate-400">Giảng viên:</span> {{ $conflictCourse->teacher?->displayName() ?? 'Chưa phân công' }}
                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2 text-sm text-slate-700">
                                        <span class="text-slate-400">Lớp hiện hành:</span> {{ $conflictClassRoom?->displayName() ?? 'Chưa có lớp' }}
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <a href="{{ route('admin.schedules.courses.show', $conflictCourse) }}" class="inline-flex items-center justify-center rounded-full border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">Xem chi tiết</a>
                                    @if($conflictClassRoom)
                                        <a href="{{ route('admin.classes.show', $conflictClassRoom) }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Mở lớp</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-6 rounded-2xl border border-dashed border-emerald-200 bg-emerald-50 px-4 py-10 text-center text-sm text-emerald-700">
                        Không tìm thấy lớp nào trùng giảng viên theo khung đã chọn.
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Xung đột phòng học</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Các lớp trùng phòng</h2>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $roomConflicts->count() }} lớp</span>
                </div>

                @if($roomConflicts->isNotEmpty())
                    <div class="mt-6 space-y-4">
                        @foreach($roomConflicts as $classRoom)
                            <div class="rounded-2xl border border-amber-100 bg-amber-50/70 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-amber-600">{{ $classRoom->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                                        <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ $classRoom->displayName() }}</h3>
                                        <p class="mt-1 text-sm text-slate-600">{{ $classRoom->scheduleSummary() }}</p>
                                    </div>
                                    <x-admin.badge :type="match($classRoom->status) {
                                        \App\Models\ClassRoom::STATUS_OPEN => 'success',
                                        \App\Models\ClassRoom::STATUS_FULL => 'warning',
                                        \App\Models\ClassRoom::STATUS_COMPLETED => 'info',
                                        default => 'default',
                                    }" :text="$classRoom->statusLabel()" />
                                </div>

                                <div class="mt-4 grid gap-3 md:grid-cols-2">
                                    <div class="rounded-xl bg-white px-3 py-2 text-sm text-slate-700">
                                        <span class="text-slate-400">Giảng viên:</span> {{ $classRoom->teacher?->displayName() ?? 'Chưa phân công' }}
                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2 text-sm text-slate-700">
                                        <span class="text-slate-400">Phòng:</span> {{ $classRoom->room?->name ?? 'Chưa phân phòng' }}
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <a href="{{ route('admin.classes.show', $classRoom) }}" class="inline-flex items-center justify-center rounded-full border border-amber-200 bg-white px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100">Mở lớp</a>
                                    @if($classRoom->course)
                                        <a href="{{ route('admin.schedules.courses.show', $classRoom->course) }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Xem lịch khóa</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-6 rounded-2xl border border-dashed border-emerald-200 bg-emerald-50 px-4 py-10 text-center text-sm text-emerald-700">
                        Không tìm thấy lớp nào trùng phòng theo khung đã chọn.
                    </div>
                @endif
            </div>
        </section>
    @endif
</div>
@endsection
