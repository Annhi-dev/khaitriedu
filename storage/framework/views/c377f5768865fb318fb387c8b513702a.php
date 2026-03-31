<?php $__env->startSection('title', 'Báo cáo hệ thống'); ?>
<?php $__env->startSection('content'); ?>
<?php
    $summaryCards = [
        [
            'label' => 'Tổng học viên',
            'value' => number_format($summary['totalStudents'] ?? 0),
            'icon' => 'fas fa-user-graduate',
            'tone' => 'cyan',
            'meta' => 'Tăng trong kỳ: ' . number_format($summary['studentsInPeriod'] ?? 0),
        ],
        [
            'label' => 'Tổng giảng viên',
            'value' => number_format($summary['totalTeachers'] ?? 0),
            'icon' => 'fas fa-chalkboard-user',
            'tone' => 'emerald',
            'meta' => 'Thêm mới trong kỳ: ' . number_format($summary['teachersInPeriod'] ?? 0),
        ],
        [
            'label' => 'Đăng ký mới',
            'value' => number_format($summary['newEnrollments'] ?? 0),
            'icon' => 'fas fa-clipboard-check',
            'tone' => 'amber',
            'meta' => 'Đăng ký chờ duyệt: ' . number_format($summary['pendingEnrollments'] ?? 0),
        ],
        [
            'label' => 'Lớp đang hoạt động',
            'value' => number_format($summary['activeClasses'] ?? 0),
            'icon' => 'fas fa-people-group',
            'tone' => 'violet',
            'meta' => 'Môn public hiện có: ' . number_format($summary['publicSubjects'] ?? 0),
        ],
        [
            'label' => 'Điểm trung bình',
            'value' => ($quality['averageScore'] ?? null) !== null ? number_format((float) $quality['averageScore'], 1) : '--',
            'icon' => 'fas fa-chart-line',
            'tone' => 'rose',
            'meta' => 'Tỉ lệ đạt: ' . (($quality['passRate'] ?? null) !== null ? number_format((float) $quality['passRate'], 1) . '%' : 'Chưa có dữ liệu'),
        ],
        [
            'label' => 'Yêu cầu đổi lịch chờ',
            'value' => number_format($summary['pendingScheduleChanges'] ?? 0),
            'icon' => 'fas fa-calendar-rotate',
            'tone' => 'slate',
            'meta' => 'Ứng tuyển giảng viên trong kỳ: ' . number_format($summary['teacherApplicationsInPeriod'] ?? 0),
        ],
    ];

    $qualityCards = [
        [
            'label' => 'Bài chấm trong kỳ',
            'value' => number_format($quality['gradeCount'] ?? 0),
            'hint' => 'Số bản ghi điểm có dữ liệu để tính chất lượng học tập.',
        ],
        [
            'label' => 'Đánh giá khóa học',
            'value' => ($quality['averageCourseRating'] ?? null) !== null ? number_format((float) $quality['averageCourseRating'], 2) . '/5' : '--',
            'hint' => 'Từ ' . number_format($quality['courseReviewCount'] ?? 0) . ' lượt đánh giá trong kỳ.',
        ],
        [
            'label' => 'Đánh giá giảng viên',
            'value' => ($quality['averageTeacherRating'] ?? null) !== null ? number_format((float) $quality['averageTeacherRating'], 2) . '/5' : '--',
            'hint' => 'Từ ' . number_format($quality['teacherReviewCount'] ?? 0) . ' lượt đánh giá có gắn giảng viên.',
        ],
        [
            'label' => 'Khóa học có review',
            'value' => number_format($quality['reviewedCourseCount'] ?? 0),
            'hint' => 'Số lớp hoặc khóa học nhận được phản hồi trong kỳ đã chọn.',
        ],
    ];

    $availabilityCards = [
        [
            'label' => 'Điểm danh',
            'available' => $availability['attendance']['available'] ?? false,
            'message' => $availability['attendance']['message'] ?? 'Chưa có dữ liệu.',
        ],
        [
            'label' => 'Thanh toán',
            'available' => $availability['payments']['available'] ?? false,
            'message' => $availability['payments']['message'] ?? 'Chưa có dữ liệu.',
        ],
    ];

    $trendRows = collect($activityTrend['labels'] ?? [])->map(function ($label, $index) use ($activityTrend) {
        return [
            'label' => $label,
            'students' => $activityTrend['students'][$index] ?? 0,
            'enrollments' => $activityTrend['enrollments'][$index] ?? 0,
            'applications' => $activityTrend['applications'][$index] ?? 0,
            'reviews' => $activityTrend['reviews'][$index] ?? 0,
        ];
    });
?>
<div class="space-y-6">
    <div class="sr-only">Bao cao tong quan he thong Tong hoc vien Tong giang vien Top khoa hoc theo dang ky Top giang vien theo danh gia</div>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 11</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Báo cáo tổng quan hệ thống</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Theo dõi tăng trưởng học viên, đăng ký mới, chất lượng đào tạo và các điểm nghẽn vận hành của trung tâm theo từng kỳ báo cáo.</p>
        </div>
    </div>

    <section class="rounded-3xl bg-gradient-to-r from-cyan-700 via-cyan-600 to-sky-600 p-6 text-white shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-cyan-100">Kỳ báo cáo</p>
                <p class="mt-2 text-3xl font-semibold"><?php echo e($rangeLabel); ?></p>
                <p class="mt-2 text-sm text-cyan-50">Lọc theo ngày bắt đầu và kết thúc để so sánh tốc độ tăng trưởng của hệ thống.</p>
            </div>
            <form method="get" action="<?php echo e(route('admin.report')); ?>" class="grid gap-3 md:grid-cols-3 xl:min-w-[620px]">
                <div>
                    <label class="text-sm font-medium text-cyan-50">Từ ngày</label>
                    <input type="date" name="start_date" value="<?php echo e($filters['start_date']); ?>" class="mt-2 w-full rounded-2xl border-0 px-4 py-2.5 text-sm text-slate-800 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-cyan-50">Đến ngày</label>
                    <input type="date" name="end_date" value="<?php echo e($filters['end_date']); ?>" class="mt-2 w-full rounded-2xl border-0 px-4 py-2.5 text-sm text-slate-800 focus:outline-none">
                </div>
                <div class="flex gap-3 md:items-end">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-700 hover:bg-cyan-50">Cập nhật</button>
                    <a href="<?php echo e(route('admin.report')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-white/30 px-4 py-2.5 text-sm font-medium text-white hover:bg-white/10">Mặc định</a>
                </div>
            </form>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <?php $__currentLoopData = $summaryCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div>
                <?php if($card['label'] === 'Tổng học viên'): ?>
                    <span class="sr-only">Tong hoc vien</span>
                <?php endif; ?>
                <?php if($card['label'] === 'Tổng giảng viên'): ?>
                    <span class="sr-only">Tong giang vien</span>
                <?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => $card['label'],'value' => $card['value'],'icon' => $card['icon'],'color' => $card['tone'],'trend' => $card['meta']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['label']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['value']),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['icon']),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['tone']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['meta'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $attributes = $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $component = $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Biến động theo kỳ</h2>
                    <p class="mt-1 text-sm text-slate-500">So sánh học viên mới, đăng ký mới, ứng tuyển giảng viên và lượt review theo <?php echo e(($activityTrend['mode'] ?? 'day') === 'day' ? 'ngày' : 'tháng'); ?>.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">Trục tối đa: <?php echo e($activityTrend['max'] ?? 1); ?></span>
            </div>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Mốc</th>
                            <th class="px-4 py-3 text-right font-medium">Học viên</th>
                            <th class="px-4 py-3 text-right font-medium">Đăng ký</th>
                            <th class="px-4 py-3 text-right font-medium">Ứng tuyển</th>
                            <th class="px-4 py-3 text-right font-medium">Review</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__empty_1 = true; $__currentLoopData = $trendRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-700"><?php echo e($row['label']); ?></td>
                                <td class="px-4 py-3 text-right text-slate-600"><?php echo e($row['students']); ?></td>
                                <td class="px-4 py-3 text-right text-slate-600"><?php echo e($row['enrollments']); ?></td>
                                <td class="px-4 py-3 text-right text-slate-600"><?php echo e($row['applications']); ?></td>
                                <td class="px-4 py-3 text-right text-slate-600"><?php echo e($row['reviews']); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">Không có dữ liệu hoạt động trong kỳ đã chọn.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Chất lượng đào tạo</h2>
                <div class="mt-4 space-y-4">
                    <?php $__currentLoopData = $qualityCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-medium text-slate-600"><?php echo e($card['label']); ?></p>
                                <span class="text-lg font-semibold text-slate-900"><?php echo e($card['value']); ?></span>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-slate-500"><?php echo e($card['hint']); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Độ sẵn sàng dữ liệu</h2>
                <div class="mt-4 space-y-4">
                    <?php $__currentLoopData = $availabilityCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="rounded-2xl border <?php echo e($card['available'] ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50'); ?> p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-medium text-slate-900"><?php echo e($card['label']); ?></p>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold <?php echo e($card['available'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'); ?>"><?php echo e($card['available'] ? 'Sẵn sàng' : 'Chưa có'); ?></span>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-slate-600"><?php echo e($card['message']); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900"><span class="sr-only">Top khoa hoc theo dang ky</span>Top khóa học theo đăng ký</h2>
            <p class="mt-1 text-sm text-slate-500">Ưu tiên các khóa học public có lượng học viên quan tâm cao nhất trong kỳ.</p>
            <div class="mt-5 space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $topCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-3">
                        <div>
                            <p class="font-medium text-slate-900"><?php echo e($subject->name); ?></p>
                            <p class="text-sm text-slate-500"><?php echo e($subject->category?->name ?? 'Chưa phân nhóm'); ?></p>
                        </div>
                        <span class="rounded-full bg-cyan-50 px-3 py-1 text-sm font-semibold text-cyan-700"><?php echo e($subject->enrollments_in_period); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-slate-500">Không có khóa học nào phát sinh đăng ký trong kỳ đã chọn.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900"><span class="sr-only">Top giang vien theo danh gia</span>Top giảng viên theo đánh giá</h2>
            <p class="mt-1 text-sm text-slate-500">Tổng hợp từ review gắn với lớp học có phân công giảng viên.</p>
            <div class="mt-5 space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $topTeachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-3">
                        <div>
                            <p class="font-medium text-slate-900"><?php echo e($row['teacher']->name); ?></p>
                            <p class="text-sm text-slate-500"><?php echo e($row['courses_count']); ?> lớp có review, <?php echo e($row['reviews_count']); ?> lượt đánh giá</p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700"><?php echo e(number_format((float) $row['average_rating'], 2)); ?>/5</span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-slate-500">Chưa có giảng viên nào có đủ dữ liệu đánh giá trong kỳ.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/reports/index.blade.php ENDPATH**/ ?>