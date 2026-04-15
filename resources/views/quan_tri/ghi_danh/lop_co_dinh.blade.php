@extends('bo_cuc.quan_tri')
@section('title', 'Chi tiết lớp cố định')
@section('content')
@php
    $classRoom = $enrollment->currentClassRoom();
    $course = $enrollment->course ?? $classRoom?->course;
    $normalizedStatus = $enrollment->normalizedStatus();
    $officialSchedule = null;
    if ($classRoom?->schedules?->isNotEmpty()) {
        $officialSchedule = $classRoom->scheduleSummary();
    }
    if (! $officialSchedule) {
        $officialSchedule = $enrollment->schedule ?: ($course?->formattedSchedule() ?? 'Chưa có lịch chính thức');
    }
    $roomLabel = $classRoom?->room
        ? (($classRoom->room->code ? $classRoom->room->code . ' - ' : '') . $classRoom->room->name)
        : 'Chưa gán phòng';
    $teacherLabel = $classRoom?->teacher?->displayName()
        ?? $course?->teacher?->displayName()
        ?? $enrollment->assignedTeacher?->displayName()
        ?? 'Chưa phân công';
@endphp
@php
    $canApproveFixedClass = in_array($normalizedStatus, [
        \App\Models\Enrollment::STATUS_PENDING,
        \App\Models\Enrollment::STATUS_ENROLLED,
    ], true);
    $canAdvanceFixedClass = in_array($normalizedStatus, [
        \App\Models\Enrollment::STATUS_APPROVED,
        \App\Models\Enrollment::STATUS_SCHEDULED,
        \App\Models\Enrollment::STATUS_ACTIVE,
    ], true);
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('admin.enrollments') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách đăng ký học
            </a>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Chi tiết lớp cố định #{{ $enrollment->id }}</h1>
            <p class="mt-2 text-sm text-slate-600">
                Hồ sơ đã gắn với lớp, giảng viên và lịch sẵn có.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-quan_tri.huy_hieu :type="$enrollment->requestSourceBadgeType()" :text="$enrollment->requestSourceLabel()" />
            <span class="inline-flex rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">{{ $enrollment->statusLabel() }}</span>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(360px,0.9fr)]">
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
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ngày ghi danh</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $enrollment->submitted_at?->format('d/m/Y H:i') ?: optional($enrollment->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thông tin lớp cố định</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Khóa học public</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $course?->subject?->name ?? $enrollment->subject?->name ?? 'Chưa xác định' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Nhóm học</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $course?->subject?->category?->name ?? $enrollment->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Lớp nội bộ</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $classRoom?->displayName() ?? $course?->title ?? 'Chưa gắn lớp' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Giảng viên</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $teacherLabel }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Phòng học</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $roomLabel }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Lịch học chốt</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $officialSchedule }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ghi chú lớp</p>
                        <p class="mt-1 whitespace-pre-line text-sm font-medium text-slate-900">{{ $course?->description ?: 'Chưa có mô tả lớp.' }}</p>
                    </div>
                </div>

                @if ($classRoom)
                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                        <p class="font-medium text-slate-800">Trạng thái lớp</p>
                        <p class="mt-2 leading-6">{{ $classRoom->statusLabel() }} {{ $classRoom->room ? ' - ' . $roomLabel : '' }}</p>
                    </div>
                @endif

                @if ($enrollment->note || $enrollment->reviewer)
                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                        <p class="font-medium text-slate-800">Thông tin xử lý gần nhất</p>
                        <p class="mt-2 leading-6">{{ $enrollment->note ?: 'Không có ghi chú.' }}</p>
                        @if ($enrollment->reviewer)
                            <p class="mt-2 text-xs text-slate-400">Cập nhật gần nhất bởi {{ $enrollment->reviewer->name }}{{ $enrollment->reviewed_at ? ' lúc ' . $enrollment->reviewed_at->format('d/m/Y H:i') : '' }}</p>
                        @endif
                    </div>
                @endif
            </section>
        </div>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Quản lý lớp cố định</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Duyệt ghi danh, theo dõi trạng thái hoặc mở trang lớp học khi cần.
            </p>

            @if ($classRoom)
                <a href="{{ route('admin.classes.show', $classRoom) }}" class="mt-4 inline-flex items-center justify-center rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 hover:bg-cyan-100">
                    Mở trang lớp học
                </a>
            @endif

            @if ($classRoom)
                <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-800">
                    Lớp và lịch đã được thiết lập sẵn. Nếu hồ sơ còn chờ xác nhận, admin có thể duyệt ngay bên dưới.
                </div>
            @else
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                    Hồ sơ chưa gắn lớp hiện hành. Hãy mở lớp trước khi duyệt ghi danh.
                </div>
            @endif

            @if ($canApproveFixedClass || $canAdvanceFixedClass)
                <form method="post" action="{{ route('admin.enrollments.review', $enrollment) }}" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $course?->id }}">
                    <input type="hidden" name="class_room_id" value="{{ $classRoom?->id }}">

                    <div>
                        <label class="text-sm font-medium text-slate-700">Ghi chú admin</label>
                        <textarea name="note" rows="4" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Ghi chú khi chuyển trạng thái hoặc hoàn tất lớp.">{{ old('note', $enrollment->note) }}</textarea>
                        @error('note')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    @error('action')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror

                    <div class="grid gap-3 sm:grid-cols-2">
                        @if ($canApproveFixedClass)
                            <button name="action" value="approve" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Duyệt ghi danh</button>
                        @endif
                        @if ($canAdvanceFixedClass || $normalizedStatus === \App\Models\Enrollment::STATUS_ENROLLED)
                            <button name="action" value="activate" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white hover:bg-violet-700">Chuyển sang đang học</button>
                        @endif
                        @if ($canAdvanceFixedClass)
                            <button name="action" value="complete" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100">Đánh dấu hoàn thành</button>
                        @endif
                    </div>
                </form>
            @else
                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                    Trạng thái hiện tại không cần thao tác thêm.
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection
