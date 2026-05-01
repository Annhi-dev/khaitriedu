<?php $__env->startSection('title', 'Chi tiết lớp học'); ?>
<?php $__env->startSection('eyebrow', 'Lớp học của tôi'); ?>

<?php $__env->startSection('content'); ?>
<?php
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
        \App\Models\GhiDanh::STATUS_COMPLETED => 'bg-slate-100 text-slate-700',
        \App\Models\GhiDanh::STATUS_ACTIVE => 'bg-emerald-100 text-emerald-700',
        \App\Models\GhiDanh::STATUS_SCHEDULED, \App\Models\GhiDanh::STATUS_ENROLLED, \App\Models\GhiDanh::STATUS_APPROVED => 'bg-cyan-100 text-cyan-700',
        default => 'bg-amber-100 text-amber-700',
    };

    $schedules = $classRoom?->schedules?->sortBy(fn ($schedule) => array_search($schedule->day_of_week, array_keys(\App\Models\LichHoc::$dayOptions), true))->values() ?? collect();
    $progressPercent = null;
    if ($classRoom?->scheduleRangeStart() && $classRoom?->scheduleRangeEnd()) {
        $start = $classRoom->scheduleRangeStart();
        $end = $classRoom->scheduleRangeEnd();
        $totalSeconds = max(1, $start->diffInSeconds($end));
        $elapsedSeconds = max(0, min($totalSeconds, $start->diffInSeconds(now(), false)));
        $progressPercent = (int) round(($elapsedSeconds / $totalSeconds) * 100);
    }
?>

<div class="space-y-6">
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="relative px-6 py-6 sm:px-8 sm:py-8">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(14,165,233,0.14),_transparent_40%),radial-gradient(circle_at_bottom_left,_rgba(15,23,42,0.05),_transparent_38%)]"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-cyan-700">Chi tiết lớp</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900"><?php echo e($displayTitle); ?></h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Xem tổng quan, lịch học, điểm số, bài kiểm tra, danh sách lớp, điểm danh và đánh giá trong cùng một màn hình.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="<?php echo e(route('student.classes.index')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại lớp học
                    </a>
                    <a href="<?php echo e(route('student.enroll.index')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
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
            <p class="mt-3 text-lg font-semibold text-slate-900"><?php echo e($subject?->name ?? 'Chưa xác định'); ?></p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Giáo viên</p>
            <p class="mt-3 text-lg font-semibold text-slate-900"><?php echo e($classRoom?->teacher?->displayName() ?? $enrollment->assignedTeacher?->displayName() ?? 'Chưa phân công'); ?></p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Phòng học</p>
            <p class="mt-3 text-lg font-semibold text-slate-900"><?php echo e($classRoom?->room?->name ?? 'Chưa phân phòng'); ?></p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Trạng thái</p>
            <span class="mt-3 inline-flex rounded-full px-3 py-1 text-sm font-semibold <?php echo e($statusTone); ?>"><?php echo e($statusLabel); ?></span>
        </div>
    </section>

    <nav class="sticky top-[4.5rem] z-20 rounded-3xl border border-slate-200 bg-white/95 p-2 shadow-sm backdrop-blur">
        <div class="flex flex-wrap gap-2">
            <?php $__currentLoopData = $tabItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="#<?php echo e($key); ?>" class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-semibold transition <?php echo e($selectedTab === $key ? 'bg-cyan-600 text-white' : 'bg-slate-50 text-slate-700 hover:bg-cyan-50 hover:text-cyan-700'); ?>">
                    <i class="fas <?php echo e($tab['icon']); ?>"></i>
                    <?php echo e($tab['label']); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </nav>

    <section id="overview" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Tổng quan</h3>
                <p class="mt-1 text-sm text-slate-500">Thông tin cốt lõi của lớp học và tiến độ hiện tại.</p>
            </div>
            <?php if($progressPercent !== null): ?>
                <div class="text-right">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Tiến độ</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900"><?php echo e($progressPercent); ?>%</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Tên lớp</p>
                <p class="mt-2 text-sm font-semibold text-slate-900"><?php echo e($displayTitle); ?></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Khóa học</p>
                <p class="mt-2 text-sm font-semibold text-slate-900"><?php echo e($course?->title ?? 'Chưa xác định'); ?></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Trạng thái học tập</p>
                <p class="mt-2 text-sm font-semibold text-slate-900"><?php echo e($statusLabel); ?></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Ngày bắt đầu</p>
                <p class="mt-2 text-sm font-semibold text-slate-900"><?php echo e($classRoom?->start_date?->format('d/m/Y') ?? $course?->start_date?->format('d/m/Y') ?? 'Chưa xác định'); ?></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Ngày kết thúc</p>
                <p class="mt-2 text-sm font-semibold text-slate-900"><?php echo e($classRoom?->scheduleRangeEnd()?->format('d/m/Y') ?? $course?->end_date?->format('d/m/Y') ?? 'Chưa xác định'); ?></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Giảng viên</p>
                <p class="mt-2 text-sm font-semibold text-slate-900"><?php echo e($classRoom?->teacher?->displayName() ?? $enrollment->assignedTeacher?->displayName() ?? 'Chưa phân công'); ?></p>
            </div>
        </div>
    </section>

    <section id="schedule" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Lịch học</h3>
                <p class="mt-1 text-sm text-slate-500">Theo dõi lịch dạy, phòng học và khung giờ của từng buổi.</p>
            </div>
            <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700"><?php echo e($schedules->count()); ?> buổi</span>
        </div>

        <?php if($schedules->isNotEmpty()): ?>
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
                        <?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-4 py-4 font-medium text-slate-900"><?php echo e(\App\Models\LichHoc::$dayOptions[$schedule->day_of_week] ?? $schedule->day_of_week); ?></td>
                                <td class="px-4 py-4 text-slate-600"><?php echo e(substr((string) $schedule->start_time, 0, 5)); ?></td>
                                <td class="px-4 py-4 text-slate-600"><?php echo e(substr((string) $schedule->end_time, 0, 5)); ?></td>
                                <td class="px-4 py-4 text-slate-600"><?php echo e($schedule->room?->name ?? $classRoom?->room?->name ?? 'Chưa phân phòng'); ?></td>
                                <td class="px-4 py-4 text-slate-600"><?php echo e($schedule->teacher?->displayName() ?? $classRoom?->teacher?->displayName() ?? 'Chưa phân công'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                Chưa có lịch học.
            </div>
        <?php endif; ?>
    </section>

    <section id="grades" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Điểm số</h3>
                <p class="mt-1 text-sm text-slate-500">Danh sách điểm mà giảng viên đã nhập cho lớp này.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?php echo e($grades->count()); ?> mục</span>
        </div>

        <?php if($grades->isNotEmpty()): ?>
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
                        <?php $__currentLoopData = $grades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-4 py-4 font-medium text-slate-900"><?php echo e($grade->module?->title ?? $grade->test_name ?? 'Bài đánh giá'); ?></td>
                                <td class="px-4 py-4 text-slate-700">
                                    <?php echo e($grade->score !== null ? number_format((float) $grade->score, 2) : 'Chưa có'); ?>

                                </td>
                                <td class="px-4 py-4 text-slate-600"><?php echo e($grade->feedback ?: 'Không có nhận xét'); ?></td>
                                <td class="px-4 py-4 text-slate-500"><?php echo e($grade->updated_at?->format('d/m/Y') ?? 'Chưa rõ'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                Chưa có điểm.
            </div>
        <?php endif; ?>
    </section>

    <section id="quizzes" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Bài kiểm tra</h3>
                <p class="mt-1 text-sm text-slate-500">Các quiz liên quan đến khóa học và lớp hiện tại.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?php echo e($quizzes->count()); ?> bài</span>
        </div>

        <?php if($quizzes->isNotEmpty()): ?>
            <div class="mt-5 grid gap-4">
                <?php $__currentLoopData = $quizzes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quiz): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-base font-semibold text-slate-900"><?php echo e($quiz->title); ?></h4>
                                    <?php if($quiz->is_required): ?>
                                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">Bắt buộc</span>
                                    <?php endif; ?>
                                </div>
                                <p class="mt-2 text-sm leading-6 text-slate-600"><?php echo e($quiz->description ?: 'Chưa có mô tả.'); ?></p>
                                <div class="mt-3 flex flex-wrap gap-3 text-xs text-slate-500">
                                    <span class="rounded-full bg-white px-3 py-1">Thang đạt: <?php echo e($quiz->passing_score ?? 70); ?>%</span>
                                    <span class="rounded-full bg-white px-3 py-1">Số lần làm: <?php echo e($quiz->attempt_count ?? 0); ?></span>
                                    <span class="rounded-full bg-white px-3 py-1">Còn lại: <?php echo e($quiz->remaining_attempts === null ? '∞' : $quiz->remaining_attempts); ?></span>
                                    <span class="rounded-full bg-white px-3 py-1">Điểm gần nhất: <?php echo e($quiz->latest_score !== null ? number_format((float) $quiz->latest_score, 2) : 'Chưa làm'); ?></span>
                                </div>
                            </div>

                            <div class="shrink-0">
                                <a href="<?php echo e($quiz->student_quiz_url); ?>" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                                    <i class="fas fa-arrow-right"></i>
                                    <?php echo e(($quiz->can_attempt ?? true) ? ($quiz->latest_score !== null ? 'Làm lại / xem bài' : 'Làm bài') : 'Xem kết quả'); ?>

                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                Chưa có bài kiểm tra nào liên quan.
            </div>
        <?php endif; ?>
    </section>

    <section id="classmates" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Danh sách lớp</h3>
                <p class="mt-1 text-sm text-slate-500">Chỉ hiển thị thông tin cơ bản của học viên cùng lớp.</p>
            </div>
            <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700"><?php echo e($classmates->count()); ?> người</span>
        </div>

        <?php if($classmates->isNotEmpty()): ?>
            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Họ tên</th>
                            <th class="px-4 py-3">Email / mã học viên</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php $__currentLoopData = $classmates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classmate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-4 py-4 font-medium text-slate-900"><?php echo e($classmate->displayName()); ?></td>
                                <td class="px-4 py-4 text-slate-600"><?php echo e($classmate->email ?: ('HV-' . $classmate->id)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                Chưa có học viên khác trong lớp hoặc lớp chưa được xếp chính thức.
            </div>
        <?php endif; ?>
    </section>

    <section id="attendance" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Điểm danh</h3>
                <p class="mt-1 text-sm text-slate-500">Lịch sử điểm danh của chính bạn trong lớp học này.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?php echo e($attendanceSummary['total'] ?? 0); ?> buổi</span>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Có mặt</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($attendanceSummary['present'] ?? 0); ?></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Vắng</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($attendanceSummary['absent'] ?? 0); ?></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Đi trễ</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($attendanceSummary['late'] ?? 0); ?></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Có phép</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($attendanceSummary['excused'] ?? 0); ?></p>
            </div>
        </div>

        <?php if(!empty($attendanceSummary['recent']) && collect($attendanceSummary['recent'])->isNotEmpty()): ?>
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
                        <?php $__currentLoopData = collect($attendanceSummary['recent']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-4 py-4 font-medium text-slate-900"><?php echo e($record->attendance_date?->format('d/m/Y') ?? 'Chưa rõ'); ?></td>
                                <td class="px-4 py-4 text-slate-600"><?php echo e($record->classSchedule?->label() ?? $record->classRoom?->displayName() ?? 'Chưa rõ'); ?></td>
                                <td class="px-4 py-4 text-slate-600"><?php echo e($record->statusLabel()); ?></td>
                                <td class="px-4 py-4 text-slate-600"><?php echo e($record->note ?: 'Không có'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                Chưa có dữ liệu điểm danh.
            </div>
        <?php endif; ?>
    </section>

    <section id="evaluation" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Đánh giá</h3>
                <p class="mt-1 text-sm text-slate-500">Xem trạng thái đánh giá và gửi nhận xét cho lớp/giáo viên.</p>
            </div>
            <?php if($evaluation): ?>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Đã có đánh giá</span>
            <?php else: ?>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Chưa đánh giá</span>
            <?php endif; ?>
        </div>

        <?php if($classRoom === null): ?>
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                Lớp học này chưa được xếp lớp nên chưa thể đánh giá.
            </div>
        <?php else: ?>
            <div class="mt-5 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-semibold text-slate-900">Gửi hoặc cập nhật đánh giá</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Một học viên chỉ nên có một đánh giá cho từng lớp. Bạn có thể cập nhật lại nếu muốn thay đổi nhận xét.</p>

                    <form method="POST" action="<?php echo e(route('student.classes.evaluation.store', $enrollment)); ?>" class="mt-5 space-y-5">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Số sao</label>
                            <div class="mt-3 grid grid-cols-5 gap-2">
                                <?php $__currentLoopData = $evaluationOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rating): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="rating" value="<?php echo e($rating); ?>" class="peer sr-only" <?php if((int) old('rating', $evaluation?->rating ?? 5) === $rating): echo 'checked'; endif; ?>>
                                        <span class="flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-700 transition peer-checked:border-cyan-300 peer-checked:bg-cyan-50 peer-checked:text-cyan-700">
                                            <?php echo e($rating); ?>/5
                                        </span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php $__errorArgs = ['rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label for="evaluation-comments" class="block text-sm font-semibold text-slate-700">Nhận xét</label>
                            <textarea
                                id="evaluation-comments"
                                name="comments"
                                rows="6"
                                class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none"
                                placeholder="Chia sẻ cảm nhận của bạn về giáo viên hoặc lớp học"><?php echo e(old('comments', $evaluation?->comments)); ?></textarea>
                            <?php $__errorArgs = ['comments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                                <i class="fas fa-paper-plane"></i>
                                Lưu đánh giá
                            </button>
                            <a href="<?php echo e(route('student.classes.show', $enrollment)); ?>" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
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
                            <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($evaluation?->rating !== null ? $evaluation->rating . '/5' : 'Chưa có'); ?></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Nhận xét gần nhất</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700"><?php echo e($evaluation?->comments ?: 'Chưa có nhận xét.'); ?></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Cập nhật</p>
                            <p class="mt-2 text-sm font-medium text-slate-900"><?php echo e($evaluation?->updated_at?->format('d/m/Y H:i') ?? 'Chưa có dữ liệu'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('bo_cuc.hoc_vien', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu-main\resources\views/hoc_vien/lop_hoc/show.blade.php ENDPATH**/ ?>