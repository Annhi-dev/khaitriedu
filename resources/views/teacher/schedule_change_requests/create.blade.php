@extends('layouts.teacher')

@section('title', 'Gửi Yêu Cầu Đổi Lịch')
@section('eyebrow', 'Legacy Course Flow')

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
@endphp

<div class="grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(380px,0.95fr)]">
    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <a href="{{ route('teacher.course.show', $course->id) }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
            <i class="fas fa-arrow-left"></i>
            Quay lại lớp học theo course
        </a>

        <h2 class="mt-5 text-2xl font-semibold text-slate-900">Gửi yêu cầu đổi lịch cho lớp học hiện tại</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Luồng này được giữ lại để tương thích phần course hiện có trong hệ thống. Với lớp nội bộ mới, bạn nên gửi yêu cầu trực tiếp từ màn Lịch giảng dạy.</p>

        <div class="mt-6 grid gap-4 text-sm text-slate-600 md:grid-cols-2">
            <p><strong>Lớp học:</strong> {{ $course->title }}</p>
            <p><strong>Khóa học:</strong> {{ $course->subject?->name ?? 'Chưa xác định' }}</p>
            <p><strong>Nhóm học:</strong> {{ $course->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
            <p><strong>Lịch hiện tại:</strong> {{ $course->formattedSchedule() }}</p>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
            <p class="font-semibold text-slate-800">Lưu ý</p>
            <ul class="mt-2 space-y-2 leading-6">
                <li>1. Admin sẽ là người duyệt và quyết định cập nhật lịch chính thức.</li>
                <li>2. Chỉ nên gửi khi lịch hiện tại thực sự cần thay đổi.</li>
                <li>3. Nếu đã có yêu cầu pending, hãy chờ admin xử lý trước khi gửi thêm.</li>
            </ul>
        </div>
    </section>

    <aside class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-2xl font-semibold text-slate-900">Thông tin đề xuất mới</h2>
        <form method="post" action="{{ route('teacher.schedule-change-requests.store', $course) }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-slate-700">Ngày học trong tuần</label>
                <select name="requested_day_of_week" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                    @foreach ($dayLabels as $value => $label)
                        <option value="{{ $value }}" @selected(old('requested_day_of_week', $course->day_of_week) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-700">Ngày bắt đầu</label>
                    <input type="date" name="requested_date" value="{{ old('requested_date', optional($course->start_date)->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Ngày kết thúc</label>
                    <input type="date" name="requested_end_date" value="{{ old('requested_end_date', optional($course->end_date)->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Giờ bắt đầu</label>
                    <input type="time" name="requested_start_time" value="{{ old('requested_start_time', $course->start_time) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Giờ kết thúc</label>
                    <input type="time" name="requested_end_time" value="{{ old('requested_end_time', $course->end_time) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">Lý do đổi lịch</label>
                <textarea name="reason" rows="5" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Mô tả lý do cần đổi lịch để admin xem xét.">{{ old('reason') }}</textarea>
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white hover:bg-cyan-700">
                Gửi yêu cầu tới admin
            </button>
        </form>
    </aside>
</div>
@endsection
