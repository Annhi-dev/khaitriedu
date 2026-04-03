@extends('layouts.admin')
@section('title', 'Chi tiết đăng ký học')
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
    $preferredDays = $enrollment->preferred_days;
    $selectedDays = is_array($preferredDays)
        ? $preferredDays
        : ((is_string($preferredDays) && $preferredDays !== '') ? (json_decode($preferredDays, true) ?: []) : []);
    $normalizedStatus = $enrollment->normalizedStatus();
    $statusClasses = match ($normalizedStatus) {
        \App\Models\Enrollment::STATUS_APPROVED => 'border-cyan-200 bg-cyan-50 text-cyan-700',
        \App\Models\Enrollment::STATUS_REJECTED => 'border-rose-200 bg-rose-50 text-rose-700',
        \App\Models\Enrollment::STATUS_SCHEDULED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        \App\Models\Enrollment::STATUS_ACTIVE => 'border-violet-200 bg-violet-50 text-violet-700',
        \App\Models\Enrollment::STATUS_COMPLETED => 'border-slate-300 bg-slate-100 text-slate-700',
        default => 'border-amber-200 bg-amber-50 text-amber-700',
    };
    $forceCreateNewClass = $enrollment->isCustomScheduleRequest()
        && in_array($normalizedStatus, [
            \App\Models\Enrollment::STATUS_PENDING,
            \App\Models\Enrollment::STATUS_APPROVED,
        ], true);
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('admin.enrollments') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách đăng ký học
            </a>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Chi tiết đăng ký #{{ $enrollment->id }}</h1>
            <p class="mt-2 text-sm text-slate-600">Theo dõi thông tin học viên, nhu cầu học và quyết định duyệt của admin.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-admin.badge :type="$enrollment->requestSourceBadgeType()" :text="$enrollment->requestSourceLabel()" />
            <span class="inline-flex rounded-full border px-4 py-2 text-sm font-semibold {{ $statusClasses }}">{{ $enrollment->statusLabel() }}</span>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(360px,0.9fr)]">
        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thông tin học viên</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Họ và tên</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->user?->name ?? 'Không có dữ liệu' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Email</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->user?->email ?? 'Không có dữ liệu' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Số điện thoại</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->user?->phone ?: 'Chưa cập nhật' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ngày gửi yêu cầu</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->submitted_at?->format('d/m/Y H:i') ?: optional($enrollment->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Khóa học đã đăng ký</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Khóa học public</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->subject?->name ?? $enrollment->course?->subject?->name ?? 'Chưa xác định' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Nhóm học</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->subject?->category?->name ?? $enrollment->course?->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Giờ mong muốn</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->start_time ?: '--:--' }} - {{ $enrollment->end_time ?: '--:--' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ngày có thể học</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $selectedDays ? implode(', ', array_map(fn ($day) => $dayLabels[$day] ?? $day, $selectedDays)) : 'Chưa chọn ngày học' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Loại hồ sơ</p>
                        <div class="mt-1">
                            <x-admin.badge :type="$enrollment->requestSourceBadgeType()" :text="$enrollment->requestSourceLabel()" />
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Xếp lớp hiện tại</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Lớp nội bộ</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->course?->title ?? 'Chưa xếp lớp' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Giảng viên</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->assignedTeacher?->name ?? $enrollment->course?->teacher?->name ?? 'Chưa phân công' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Lịch đã chốt</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->schedule ?: ($enrollment->course?->formattedSchedule() ?? 'Chưa có lịch chính thức') }}</p>
                    </div>
                </div>
                @if ($enrollment->note)
                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                        <p class="font-medium text-slate-800">Ghi chú admin</p>
                        <p class="mt-2 leading-6">{{ $enrollment->note }}</p>
                    </div>
                @endif
                @if ($enrollment->reviewer)
                    <p class="mt-4 text-xs text-slate-400">Xử lý gần nhất bởi {{ $enrollment->reviewer->name }}{{ $enrollment->reviewed_at ? ' lúc ' . $enrollment->reviewed_at->format('d/m/Y H:i') : '' }}</p>
                @endif
            </section>
        </div>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Xử lý đăng ký</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                @if ($forceCreateNewClass)
                    Hồ sơ này là yêu cầu lịch học riêng khác với lớp đang mở. Ở bước này admin nên duyệt hồ sơ rồi chuyển sang phase 9 để tạo lớp mới phù hợp.
                @else
                    Duyệt đăng ký để chuyển sang bước chờ xếp lớp, hoặc xếp lớp ngay nếu đã có lớp phù hợp. Khi để trống giảng viên và lịch, hệ thống sẽ ưu tiên lấy từ lớp nội bộ đã chọn.
                @endif
            </p>

            @if (in_array($normalizedStatus, [\App\Models\Enrollment::STATUS_PENDING, \App\Models\Enrollment::STATUS_APPROVED], true))
                <a href="{{ route('admin.schedules.enrollments.show', $enrollment) }}" class="mt-4 inline-flex items-center justify-center rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 hover:bg-cyan-100">{{ $forceCreateNewClass ? 'Mở màn tạo lớp và xếp lịch phase 9' : 'Mở màn xếp lịch phase 9' }}</a>
            @endif

            @if ($forceCreateNewClass)
                <div class="mt-4 rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-4 text-sm text-cyan-800">
                    Hồ sơ này không chọn lớp nội bộ có sẵn ở màn chi tiết. Admin sẽ tạo lớp mới theo lịch học học viên đã yêu cầu ở phase 9.
                </div>
            @endif

            <form method="post" action="{{ route('admin.enrollments.review', $enrollment) }}" class="mt-5 space-y-4">
                @csrf
                @unless ($forceCreateNewClass)
                    <div>
                        <label class="text-sm font-medium text-slate-700">Lớp nội bộ</label>
                        <select name="course_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                            <option value="">Chưa chọn lớp</option>
                            @foreach ($availableCourses as $course)
                                <option value="{{ $course->id }}" @selected(old('course_id', $enrollment->course_id) == $course->id)>
                                    {{ $course->title }} - {{ $course->formattedSchedule() }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Giảng viên phụ trách</label>
                        <select name="assigned_teacher_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                            <option value="">Tự lấy theo lớp hoặc để trống</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('assigned_teacher_id', $enrollment->assigned_teacher_id) == $teacher->id)>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_teacher_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Lịch học chính thức</label>
                        <select name="schedule" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                            <option value="">-- Chọn lịch học --</option>
                            <option value="Tối T2-T4-T6, 18:00 - 20:30" @selected(old('schedule', $enrollment->schedule) === 'Tối T2-T4-T6, 18:00 - 20:30')>Tối T2-T4-T6, 18:00 - 20:30</option>
                            <option value="Tối T3-T5-T7, 18:00 - 20:30" @selected(old('schedule', $enrollment->schedule) === 'Tối T3-T5-T7, 18:00 - 20:30')>Tối T3-T5-T7, 18:00 - 20:30</option>
                            <option value="Sáng T7-CN, 08:30 - 11:30" @selected(old('schedule', $enrollment->schedule) === 'Sáng T7-CN, 08:30 - 11:30')>Sáng T7-CN, 08:30 - 11:30</option>
                            <option value="Chiều T7-CN, 14:00 - 17:00" @selected(old('schedule', $enrollment->schedule) === 'Chiều T7-CN, 14:00 - 17:00')>Chiều T7-CN, 14:00 - 17:00</option>
                            <option value="Linh hoạt (Thỏa thuận)" @selected(old('schedule', $enrollment->schedule) === 'Linh hoạt (Thỏa thuận)')>Linh hoạt (Thỏa thuận)</option>
                        </select>
                        @error('schedule')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                @endunless

                <div>
                    <label class="text-sm font-medium text-slate-700">Ghi chú phản hồi</label>
                    <textarea name="note" rows="5" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Dùng cho lý do từ chối, yêu cầu học viên bổ sung hoặc ghi chú nội dung duyệt.">{{ old('note', $enrollment->note) }}</textarea>
                    @error('note')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                @error('action')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror

                <div class="grid gap-3 sm:grid-cols-2">
                    <button name="action" value="approve" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Duyệt đăng ký</button>
                    @unless ($forceCreateNewClass)
                        <button name="action" value="schedule" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Xếp lớp và chốt lịch</button>
                    @endunless
                    <button name="action" value="request_update" class="inline-flex items-center justify-center rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 hover:bg-amber-100">Yêu cầu bổ sung</button>
                    <button name="action" value="reject" class="inline-flex items-center justify-center rounded-2xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 hover:bg-rose-100">Từ chối đăng ký</button>
                </div>

                @if (! $forceCreateNewClass || $enrollment->course_id)
                    <div class="grid gap-3 sm:grid-cols-2">
                        <button name="action" value="activate" class="inline-flex items-center justify-center rounded-2xl border border-violet-300 bg-violet-50 px-4 py-3 text-sm font-semibold text-violet-700 hover:bg-violet-100">Chuyển sang đang học</button>
                        <button name="action" value="complete" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100">Đánh dấu hoàn thành</button>
                    </div>
                @endif
            </form>
        </aside>
    </div>
</div>
@endsection
