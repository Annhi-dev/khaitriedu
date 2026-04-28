@extends('bo_cuc.hoc_vien')
@section('title', 'Chi tiết lớp học')
@section('eyebrow', 'Lớp học của tôi')

@section('content')
@php
    $classRoom = $classRoom ?? null;
    $course = $course ?? null;
    $subject = $subject ?? null;
    $evaluation = $evaluation ?? null;
    $selectedTab = $selectedTab ?? 'overview';
    $tabItems = [
        'overview' => ['label' => 'Tổng quan', 'icon' => 'fa-circle-info'],
        'schedule' => ['label' => 'Lịch học', 'icon' => 'fa-calendar-days'],
        'grades' => ['label' => 'Điểm số', 'icon' => 'fa-square-poll-horizontal'],
        'quizzes' => ['label' => 'Bài kiểm tra', 'icon' => 'fa-file-pen'],
        'classmates' => ['label' => 'Danh sách lớp', 'icon' => 'fa-users'],
        'attendance' => ['label' => 'Điểm danh', 'icon' => 'fa-clipboard-check'],
        'evaluation' => ['label' => 'Đánh giá', 'icon' => 'fa-comments'],
    ];

    $displayTitle = $classRoom?->displayName()
        ?? $course?->title
        ?? $subject?->name
        ?? 'Chi tiết lớp học';

    $statusLabel = $enrollment->displayStatusLabel();
    $statusTone = match ($enrollment->displayStatus()) {
        \App\Models\Enrollment::STATUS_COMPLETED => 'bg-slate-100 text-slate-700',
        \App\Models\Enrollment::STATUS_ACTIVE => 'bg-emerald-100 text-emerald-700',
        \App\Models\Enrollment::STATUS_SCHEDULED, \App\Models\Enrollment::STATUS_ENROLLED, \App\Models\Enrollment::STATUS_APPROVED => 'bg-cyan-100 text-cyan-700',
        default => 'bg-amber-100 text-amber-700',
    };

    $schedules = $classRoom?->schedules?->sortBy(fn ($schedule) => array_search($schedule->day_of_week, array_keys(\App\Models\ClassSchedule::$dayOptions), true))->values() ?? collect();
    $progressPercent = null;
    if ($classRoom?->scheduleRangeStart() && $classRoom?->scheduleRangeEnd()) {
        $start = $classRoom->scheduleRangeStart();
        $end = $classRoom->scheduleRangeEnd();
        $totalSeconds = max(1, $start->diffInSeconds($end));
        $elapsedSeconds = max(0, min($totalSeconds, $start->diffInSeconds(now(), false)));
        $progressPercent = (int) round(($elapsedSeconds / $totalSeconds) * 100);
    }
@endphp

<div class="space-y-6">
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="relative px-6 py-6 sm:px-8 sm:py-8">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(14,165,233,0.14),_transparent_40%),radial-gradient(circle_at_bottom_left,_rgba(15,23,42,0.05),_transparent_38%)]"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-cyan-700">Chi tiết lớp</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $displayTitle }}</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Xem tổng quan, lịch học, điểm số, bài kiểm tra, danh sách lớp, điểm danh và đánh giá trong cùng một màn hình.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('student.classes.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại lớp học
                    </a>
                    <a href="{{ route('student.enroll.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
                        <i class="fas fa-book-open"></i>
                        Đăng ký học
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Môn học</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $subject?->name ?? 'Chưa xác định' }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Giáo viên</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $classRoom?->teacher?->displayName() ?? $enrollment->assignedTeacher?->displayName() ?? 'Chưa phân công' }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Phòng học</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $classRoom?->room?->name ?? 'Chưa phân phòng' }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Trạng thái</p>
            <span class="mt-3 inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $statusTone }}">{{ $statusLabel }}</span>
        </div>
    </section>

    <nav class="sticky top-[4.5rem] z-20 rounded-3xl border border-slate-200 bg-white/95 p-2 shadow-sm backdrop-blur">
        <div class="flex flex-wrap gap-2">
            @foreach($tabItems as $key => $tab)
                <a href="#{{ $key }}" class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-semibold transition {{ $selectedTab === $key ? 'bg-cyan-600 text-white' : 'bg-slate-50 text-slate-700 hover:bg-cyan-50 hover:text-cyan-700' }}">
                    <i class="fas {{ $tab['icon'] }}"></i>
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    <section id="overview" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Tổng quan</h3>
                <p class="mt-1 text-sm text-slate-500">Thông tin cốt lõi của lớp học và tiến độ hiện tại.</p>
            </div>
            @if($progressPercent !== null)
                <div class="text-right">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Tiến độ</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $progressPercent }}%</p>
                </div>
            @endif
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Tên lớp</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $displayTitle }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Khóa học</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $course?->title ?? 'Chưa xác định' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Trạng thái học tập</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $statusLabel }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Ngày bắt đầu</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $classRoom?->start_date?->format('d/m/Y') ?? $course?->start_date?->format('d/m/Y') ?? 'Chưa xác định' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Ngày kết thúc</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $classRoom?->scheduleRangeEnd()?->format('d/m/Y') ?? $course?->end_date?->format('d/m/Y') ?? 'Chưa xác định' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Giảng viên</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $classRoom?->teacher?->displayName() ?? $enrollment->assignedTeacher?->displayName() ?? 'Chưa phân công' }}</p>
            </div>
        </div>
    </section>

    <section id="schedule" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Lịch học</h3>
                <p class="mt-1 text-sm text-slate-500">Theo dõi lịch dạy, phòng học và khung giờ của từng buổi.</p>
            </div>
            <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $schedules->count() }} buổi</span>
        </div>

        @if($schedules->isNotEmpty())
            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Thứ học</th>
                            <th class="px-4 py-3">Giờ bắt đầu</th>
                            <th class="px-4 py-3">Giờ kết thúc</th>
                            <th class="px-4 py-3">Phòng</th>
                            <th class="px-4 py-3">Giáo viên</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($schedules as $schedule)
                            <tr>
                                <td class="px-4 py-4 font-medium text-slate-900">{{ \App\Models\ClassSchedule::$dayOptions[$schedule->day_of_week] ?? $schedule->day_of_week }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ substr((string) $schedule->start_time, 0, 5) }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ substr((string) $schedule->end_time, 0, 5) }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $schedule->room?->name ?? $classRoom?->room?->name ?? 'Chưa phân phòng' }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $schedule->teacher?->displayName() ?? $classRoom?->teacher?->displayName() ?? 'Chưa phân công' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                Chưa có lịch học.
            </div>
        @endif
    </section>

    <section id="grades" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Điểm số</h3>
                <p class="mt-1 text-sm text-slate-500">Danh sách điểm mà giảng viên đã nhập cho lớp này.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $grades->count() }} mục</span>
        </div>

        @if($grades->isNotEmpty())
            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Nội dung / chương / bài</th>
                            <th class="px-4 py-3">Điểm</th>
                            <th class="px-4 py-3">Nhận xét</th>
                            <th class="px-4 py-3">Cập nhật</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($grades as $grade)
                            <tr>
                                <td class="px-4 py-4 font-medium text-slate-900">{{ $grade->module?->title ?? $grade->test_name ?? 'Bài đánh giá' }}</td>
                                <td class="px-4 py-4 text-slate-700">
                                    {{ $grade->score !== null ? number_format((float) $grade->score, 2) : 'Chưa có' }}
                                </td>
                                <td class="px-4 py-4 text-slate-600">{{ $grade->feedback ?: 'Không có nhận xét' }}</td>
                                <td class="px-4 py-4 text-slate-500">{{ $grade->updated_at?->format('d/m/Y') ?? 'Chưa rõ' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                Chưa có điểm.
            </div>
        @endif
    </section>

    <section id="quizzes" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Bài kiểm tra</h3>
                <p class="mt-1 text-sm text-slate-500">Các quiz liên quan đến khóa học và lớp hiện tại.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $quizzes->count() }} bài</span>
        </div>

        @if($quizzes->isNotEmpty())
            <div class="mt-5 grid gap-4">
                @foreach($quizzes as $quiz)
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-base font-semibold text-slate-900">{{ $quiz->title }}</h4>
                                    @if($quiz->is_required)
                                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">Bắt buộc</span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $quiz->description ?: 'Chưa có mô tả.' }}</p>
                                <div class="mt-3 flex flex-wrap gap-3 text-xs text-slate-500">
                                    <span class="rounded-full bg-white px-3 py-1">Thang đạt: {{ $quiz->passing_score ?? 70 }}%</span>
                                    <span class="rounded-full bg-white px-3 py-1">Số lần làm: {{ $quiz->attempt_count ?? 0 }}</span>
                                    <span class="rounded-full bg-white px-3 py-1">Còn lại: {{ $quiz->remaining_attempts === null ? '∞' : $quiz->remaining_attempts }}</span>
                                    <span class="rounded-full bg-white px-3 py-1">Điểm gần nhất: {{ $quiz->latest_score !== null ? number_format((float) $quiz->latest_score, 2) : 'Chưa làm' }}</span>
                                </div>
                            </div>

                            <div class="shrink-0">
                                <a href="{{ $quiz->student_quiz_url }}" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                                    <i class="fas fa-arrow-right"></i>
                                    {{ ($quiz->can_attempt ?? true) ? ($quiz->latest_score !== null ? 'Làm lại / xem bài' : 'Làm bài') : 'Xem kết quả' }}
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                Chưa có bài kiểm tra nào liên quan.
            </div>
        @endif
    </section>

    <section id="classmates" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Danh sách lớp</h3>
                <p class="mt-1 text-sm text-slate-500">Chỉ hiển thị thông tin cơ bản của học viên cùng lớp.</p>
            </div>
            <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $classmates->count() }} người</span>
        </div>

        @if($classmates->isNotEmpty())
            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Họ tên</th>
                            <th class="px-4 py-3">Email / mã học viên</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($classmates as $classmate)
                            <tr>
                                <td class="px-4 py-4 font-medium text-slate-900">{{ $classmate->displayName() }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $classmate->email ?: ('HV-' . $classmate->id) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                Chưa có học viên khác trong lớp hoặc lớp chưa được xếp chính thức.
            </div>
        @endif
    </section>

    <section id="attendance" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Điểm danh</h3>
                <p class="mt-1 text-sm text-slate-500">Lịch sử điểm danh của chính bạn trong lớp học này.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $attendanceSummary['total'] ?? 0 }} buổi</span>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Có mặt</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $attendanceSummary['present'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Vắng</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $attendanceSummary['absent'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Đi trễ</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $attendanceSummary['late'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Có phép</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $attendanceSummary['excused'] ?? 0 }}</p>
            </div>
        </div>

        @if(!empty($attendanceSummary['recent']) && collect($attendanceSummary['recent'])->isNotEmpty())
            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Ngày</th>
                            <th class="px-4 py-3">Buổi học</th>
                            <th class="px-4 py-3">Trạng thái</th>
                            <th class="px-4 py-3">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach(collect($attendanceSummary['recent']) as $record)
                            <tr>
                                <td class="px-4 py-4 font-medium text-slate-900">{{ $record->attendance_date?->format('d/m/Y') ?? 'Chưa rõ' }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $record->classSchedule?->label() ?? $record->classRoom?->displayName() ?? 'Chưa rõ' }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $record->statusLabel() }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $record->note ?: 'Không có' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                Chưa có dữ liệu điểm danh.
            </div>
        @endif
    </section>

    <section id="evaluation" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Đánh giá</h3>
                <p class="mt-1 text-sm text-slate-500">Xem trạng thái đánh giá và gửi nhận xét cho lớp/giáo viên.</p>
            </div>
            @if($evaluation)
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Đã có đánh giá</span>
            @else
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Chưa đánh giá</span>
            @endif
        </div>

        @if($classRoom === null)
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                Lớp học này chưa được xếp lớp nên chưa thể đánh giá.
            </div>
        @else
            <div class="mt-5 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-semibold text-slate-900">Gửi hoặc cập nhật đánh giá</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Một học viên chỉ nên có một đánh giá cho từng lớp. Bạn có thể cập nhật lại nếu muốn thay đổi nhận xét.</p>

                    <form method="POST" action="{{ route('student.classes.evaluation.store', $enrollment) }}" class="mt-5 space-y-5">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Số sao</label>
                            <div class="mt-3 grid grid-cols-5 gap-2">
                                @foreach($evaluationOptions as $rating)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="rating" value="{{ $rating }}" class="peer sr-only" @checked((int) old('rating', $evaluation?->rating ?? 5) === $rating)>
                                        <span class="flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-700 transition peer-checked:border-cyan-300 peer-checked:bg-cyan-50 peer-checked:text-cyan-700">
                                            {{ $rating }}/5
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('rating')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="evaluation-comments" class="block text-sm font-semibold text-slate-700">Nhận xét</label>
                            <textarea
                                id="evaluation-comments"
                                name="comments"
                                rows="6"
                                class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none"
                                placeholder="Chia sẻ cảm nhận của bạn về giáo viên hoặc lớp học">{{ old('comments', $evaluation?->comments) }}</textarea>
                            @error('comments')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                                <i class="fas fa-paper-plane"></i>
                                Lưu đánh giá
                            </button>
                            <a href="{{ route('student.classes.show', $enrollment) }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Hủy
                            </a>
                        </div>
                    </form>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <p class="text-sm font-semibold text-slate-900">Trạng thái hiện tại</p>
                    <div class="mt-4 space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Điểm đánh giá</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $evaluation?->rating !== null ? $evaluation->rating . '/5' : 'Chưa có' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Nhận xét gần nhất</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">{{ $evaluation?->comments ?: 'Chưa có nhận xét.' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Cập nhật</p>
                            <p class="mt-2 text-sm font-medium text-slate-900">{{ $evaluation?->updated_at?->format('d/m/Y H:i') ?? 'Chưa có dữ liệu' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>
</div>
@endsection
