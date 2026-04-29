@extends('bo_cuc.quan_tri')
@section('title', 'Chi tiết giảng viên')
@section('content')
@php
    $teacherSpecialty = $teacher->specialtyLabel();
    $teachingSubjects = $courses
        ->pluck('subject.name')
        ->filter()
        ->unique()
        ->values();
    $teachingSubjectCount = $teachingSubjects->count();
    $teachingPreview = $teachingSubjects->take(3)->implode(' · ');
    $teachingOverflow = max($teachingSubjectCount - 3, 0);
    $statusClasses = match ($teacher->status) {
        \App\Models\NguoiDung::STATUS_ACTIVE => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        \App\Models\NguoiDung::STATUS_INACTIVE => 'border-amber-200 bg-amber-50 text-amber-700',
        \App\Models\NguoiDung::STATUS_LOCKED => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-100 text-slate-700',
    };
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Hồ sơ giảng viên</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">{{ $teacher->displayName() }}</h1>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                <span>{{ $teacher->email }}</span>
                <span class="text-slate-300">|</span>
                <span>{{ $teacher->phone ?: 'Chưa có số điện thoại' }}</span>
                <span class="text-slate-300">|</span>
                <span>{{ $teacher->department?->name ?: 'Chưa gán phòng ban' }}</span>
                @if ($teacherSpecialty)
                    <span class="inline-flex rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">Chuyên môn: {{ $teacherSpecialty }}</span>
                @endif
                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $teacher->statusLabel() }}</span>
            </div>
            <div class="mt-5 rounded-3xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                <p class="text-xs uppercase tracking-[0.2em] text-cyan-700">Chuyên môn</p>
                @if ($teacherSpecialty)
                    <p class="mt-2 text-sm font-semibold text-slate-800">{{ $teacherSpecialty }}</p>
                @endif
                @if ($teachingSubjects->isNotEmpty())
                    <p class="mt-2 text-sm leading-6 text-slate-700">
                        @if ($teacherSpecialty)
                            <span class="text-xs uppercase tracking-wide text-slate-400">Đang giảng dạy:</span>
                        @endif
                        {{ $teachingPreview }}
                        @if ($teachingOverflow > 0)
                            +{{ $teachingOverflow }} chuyên môn khác
                        @endif
                    </p>
                @else
                    <p class="mt-2 text-sm leading-6 text-slate-500">Chưa có chuyên môn giảng dạy.</p>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Danh sách giảng viên</a>
            <a href="{{ route('admin.teachers.edit', $teacher) }}" class="inline-flex items-center justify-center rounded-2xl border border-cyan-200 px-4 py-2.5 text-sm font-medium text-cyan-700 hover:bg-cyan-50">Sửa thông tin</a>
            @if ($teacher->isLocked())
                <form method="post" action="{{ route('admin.teachers.unlock', $teacher) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Mở khóa tài khoản</button>
                </form>
            @else
                <form method="post" action="{{ route('admin.teachers.lock', $teacher) }}" onsubmit="return confirm('Khóa tài khoản giảng viên này?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Khóa tài khoản</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Thông tin cá nhân</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ number_format($teachingSubjectCount) }} chuyên môn</span>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Họ và tên</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $teacher->displayName() }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Tên đăng nhập</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $teacher->username }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Email</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $teacher->email }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Số điện thoại</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $teacher->phone ?: 'Chưa cập nhật' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Trạng thái</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $teacher->statusLabel() }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Phòng ban</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $teacher->department?->name ?: 'Chưa gán phòng ban' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Ngày tạo</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ optional($teacher->created_at)->format('d/m/Y H:i') ?: 'Không có dữ liệu' }}</p>
                </div>
            </div>
        </section>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Thống kê nhanh</h2>
            <div class="mt-4 grid gap-3">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Chuyên môn</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $teachingSubjectCount }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Học viên theo chuyên môn</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $studentCount }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Yêu cầu dời buổi</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $teacher->schedule_change_requests_count }}</p>
                </div>
            </div>
        </aside>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">Kinh nghiệm và hồ sơ năng lực</h2>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 px-4 py-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">Kinh nghiệm</p>
                <p class="mt-2 text-sm leading-6 text-slate-700">{{ $application?->experience ?: 'Chưa có thông tin kinh nghiệm từ hồ sơ ứng tuyển.' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 px-4 py-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">Mô tả / chuyên môn</p>
                <p class="mt-2 text-sm leading-6 text-slate-700">{{ $application?->message ?: 'Chưa có mô tả chuyên môn từ hồ sơ ứng tuyển.' }}</p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">Chuyên môn giảng dạy</h2>
        </div>
        <div class="mt-5 grid gap-4">
            @forelse ($courses as $course)
                <div class="rounded-2xl border border-slate-200 px-4 py-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $course->subject?->name ?? $course->title ?? 'Chưa xác định' }}</p>
                            <div class="mt-3 grid gap-2 text-sm text-slate-600 md:grid-cols-2">
                                <p>Nhóm học: {{ $course->subject?->category?->name ?? 'Chưa xác định' }}</p>
                                <p>Lịch dạy: {{ $course->schedule ?: 'Chưa có lịch cụ thể' }}</p>
                                <p>Số học viên: {{ $course->enrollments_count }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Giảng viên chưa được phân công chuyên môn nào.</div>
            @endforelse
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Học viên đang học theo chuyên môn</h2>
            </div>
            <div class="mt-5 grid gap-4">
                @forelse ($enrollments->take(8) as $enrollment)
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <p class="text-sm font-semibold text-slate-900">{{ $enrollment->user->name ?? 'Học viên' }}</p>
                        <div class="mt-3 grid gap-2 text-sm text-slate-600 md:grid-cols-2">
                            <p>Chuyên môn: {{ $enrollment->course?->subject?->name ?? 'Chưa xác định' }}</p>
                            <p>Trạng thái enrollment: {{ $enrollment->status }}</p>
                            <p>Lịch chính thức: {{ $enrollment->schedule ?: 'Chưa có lịch cụ thể' }}</p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Chưa có học viên nào gắn với giảng viên này.</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Yêu cầu dời buổi gần đây</h2>
            </div>
            <div class="mt-5 grid gap-4">
                @forelse ($scheduleChangeRequests as $request)
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <p class="text-sm font-semibold text-slate-900">{{ $request->course?->subject?->name ?? $request->course?->title ?? 'Chuyên môn' }}</p>
                        <div class="mt-3 grid gap-2 text-sm text-slate-600 md:grid-cols-2">
                            <p>Ngày đề xuất: {{ optional($request->requested_date)->format('d/m/Y') ?: 'Chưa chọn' }}</p>
                            <p>Khung giờ mới: {{ trim(($request->requested_start_time ?: '--') . ' - ' . ($request->requested_end_time ?: '--')) }}</p>
                            <p>Trạng thái: {{ $request->status }}</p>
                            <p>Người duyệt: {{ $request->reviewer->name ?? 'Chưa duyệt' }}</p>
                        </div>
                        <p class="mt-3 text-sm text-slate-600">Lý do: {{ $request->reason ?: 'Không có' }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Giảng viên chưa gửi yêu cầu dời buổi nào.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
