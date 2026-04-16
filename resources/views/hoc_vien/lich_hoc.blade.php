@extends('bo_cuc.hoc_vien')
@section('title', 'Lịch học của tôi')
@section('eyebrow', 'Lịch học')

@section('content')
@php
    $enrollmentList = method_exists($enrollments, 'getCollection') ? $enrollments->getCollection() : collect($enrollments);
    $totalEnrollments = $enrollmentList->count();
    $activeEnrollments = $enrollmentList->filter(fn ($enrollment) => $enrollment->classRoom && ! $enrollment->course?->isPendingOpen())->count();
    $waitingEnrollments = $enrollmentList->filter(fn ($enrollment) => $enrollment->course?->isPendingOpen())->count();
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Lịch học cá nhân</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Lịch học của tôi</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Xem thời khóa biểu theo tuần, trạng thái ghi danh và chi tiết các lớp bạn đang theo học.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('student.enroll.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
                    <i class="fas fa-book-open"></i>
                    Đăng ký thêm
                </a>
                <a href="{{ route('student.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    <i class="fas fa-gauge-high"></i>
                    Quay lại tổng quan
                </a>
            </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Tổng ghi danh</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $totalEnrollments }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Lớp đang học</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $activeEnrollments }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Đang chờ mở</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $waitingEnrollments }}</p>
            </div>
        </div>
    </section>

    @if(!empty($weeklyTimetable['hasConflicts']))
        <section class="rounded-3xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-rose-900">
                        Phát hiện {{ $weeklyTimetable['conflictCount'] ?? 0 }} buổi học bị trùng lịch trong dữ liệu hiện tại.
                    </p>
                    <p class="mt-1 text-sm leading-6 text-rose-800">
                        Các buổi trùng lịch được đánh dấu màu đỏ để bạn nhận biết đây là dữ liệu cần xử lý lại.
                    </p>
                </div>
            </div>
        </section>
    @endif

    @include('dung_chung.luoi_lich_hoc', [
        'grid' => $weeklyTimetable,
        'sectionEyebrow' => 'Lịch học theo tuần',
        'sectionTitle' => 'Thời khóa biểu tuần này',
        'sectionSubtitle' => 'Mỗi ô hiển thị một khung giờ cụ thể trong tuần hiện tại. Chọn từng ô để xem chi tiết buổi học.',
        'emptyMessage' => 'Bạn chưa có buổi học nào trong tuần này.',
    ])

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Danh sách lớp đang theo dõi</h3>
                <p class="mt-1 text-sm text-slate-500">Thông tin từng lớp được trình bày ngắn gọn để dễ xem và thao tác.</p>
            </div>
            <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $totalEnrollments }} lớp</span>
        </div>

        <div class="mt-5 grid gap-4">
            @forelse($enrollments as $enrollment)
                @php
                    $course = $enrollment->course;
                    $waitingOpen = $course?->isPendingOpen();
                    $classRoom = $enrollment->classRoom;
                    $attendanceSummary = $classRoom ? ($attendanceSummaries->get($classRoom->id) ?? null) : null;
                    $classmates = $classRoom
                        ? $classRoom->enrollments
                            ->filter(fn ($classEnrollment) => (int) $classEnrollment->user_id !== (int) $enrollment->user_id && $classEnrollment->user !== null)
                            ->map(fn ($classEnrollment) => $classEnrollment->user)
                            ->values()
                        : collect();
                @endphp

                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Lớp học</p>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $waitingOpen ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $waitingOpen ? $course->statusLabel() : $enrollment->displayStatusLabel() }}
                                </span>
                            </div>

                            <h4 class="mt-2 text-xl font-semibold text-slate-900">{{ $course?->title ?? 'Chưa xác định' }}</h4>

                            <div class="mt-4 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                                <p><span class="font-medium text-slate-500">Khóa học:</span> {{ $course?->subject?->name ?? 'Chưa xác định' }}</p>
                                <p><span class="font-medium text-slate-500">Giảng viên:</span> {{ $enrollment->assignedTeacher->name ?? 'Chưa phân công' }}</p>
                                <p><span class="font-medium text-slate-500">Lịch học:</span> {{ $course?->formattedSchedule() ?? 'Chưa có lịch' }}</p>
                                <p><span class="font-medium text-slate-500">Trạng thái:</span> <span class="font-semibold {{ $waitingOpen ? 'text-amber-700' : 'text-emerald-700' }}">{{ $waitingOpen ? 'Đang chờ mở lớp' : $enrollment->displayStatusLabel() }}</span></p>
                            </div>

                            @if ($waitingOpen)
                                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                    Lớp đang chờ mở chính thức.
                                </div>
                            @endif

                            @if (! $waitingOpen && $classRoom)
                                <div class="mt-5 grid gap-4 xl:grid-cols-2">
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="text-sm font-semibold text-slate-900">Thống kê điểm danh</p>
                                        @if ($attendanceSummary)
                                            <div class="mt-3 grid grid-cols-2 gap-3 text-sm text-slate-700">
                                                <p><span class="text-slate-500">Tổng buổi:</span> {{ $attendanceSummary['total'] }}</p>
                                                <p><span class="text-slate-500">Tỷ lệ có mặt:</span> {{ $attendanceSummary['present_rate'] }}%</p>
                                                <p><span class="text-slate-500">Có mặt:</span> {{ $attendanceSummary['present'] }}</p>
                                                <p><span class="text-slate-500">Đi trễ:</span> {{ $attendanceSummary['late'] }}</p>
                                                <p><span class="text-slate-500">Có phép:</span> {{ $attendanceSummary['excused'] }}</p>
                                                <p><span class="text-slate-500">Vắng:</span> {{ $attendanceSummary['absent'] }}</p>
                                            </div>

                                            <div class="mt-4 border-t border-slate-200 pt-3">
                                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Lần điểm danh gần đây</p>
                                                <ul class="mt-2 space-y-1 text-xs text-slate-600">
                                                    @foreach ($attendanceSummary['recent'] as $attendanceItem)
                                                        <li>
                                                            {{ $attendanceItem->attendance_date?->format('d/m/Y') ?? 'Chưa rõ ngày' }}:
                                                            {{ $attendanceItem->statusLabel() }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @else
                                            <p class="mt-2 text-sm text-slate-600">Giảng viên chưa điểm danh lớp này.</p>
                                        @endif
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="text-sm font-semibold text-slate-900">Bạn học cùng lớp</p>
                                        @if ($classmates->isNotEmpty())
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach ($classmates->take(8) as $classmate)
                                                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-700">
                                                        {{ $classmate->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                            @if ($classmates->count() > 8)
                                                <p class="mt-2 text-xs text-slate-500">Và {{ $classmates->count() - 8 }} bạn khác trong lớp.</p>
                                            @endif
                                        @else
                                            <p class="mt-2 text-sm text-slate-600">Hiện chưa có thêm học viên nào khác trong lớp này.</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="shrink-0">
                            @if (! $waitingOpen)
                                <a href="{{ route('courses.show', $course->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 font-semibold text-white transition hover:bg-cyan-700">
                                    <i class="fas fa-arrow-right"></i>
                                    Vào lớp học
                                </a>
                            @else
                                <span class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700">
                                    <i class="fas fa-hourglass-half"></i>
                                    Đang chờ mở lớp
                                </span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                    <p class="text-lg font-semibold text-slate-700">Bạn chưa được xếp vào lớp học nào.</p>
                    <p class="mt-2 text-slate-500">Hãy chọn khóa học và gửi yêu cầu lịch học phù hợp.</p>
                    <a href="{{ route('student.enroll.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-3 font-semibold text-white transition hover:bg-cyan-700">
                        <i class="fas fa-book-open"></i>
                        Xem khóa học
                    </a>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
