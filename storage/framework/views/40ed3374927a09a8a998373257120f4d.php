<?php $__env->startSection('title', 'Bao cao he thong'); ?>
<?php $__env->startSection('content'); ?>
<?php
    $summaryCards = [
        ['label' => 'Tong hoc vien', 'value' => $summary['totalStudents'], 'meta' => $summary['studentsInPeriod'] . ' moi trong ky', 'icon' => 'fas fa-user-graduate', 'tone' => 'cyan'],
        ['label' => 'Tong giang vien', 'value' => $summary['totalTeachers'], 'meta' => $summary['teachersInPeriod'] . ' moi trong ky', 'icon' => 'fas fa-chalkboard-user', 'tone' => 'emerald'],
        ['label' => 'Lop dang co lich/hoat dong', 'value' => $summary['activeClasses'], 'meta' => $summary['newEnrollments'] . ' dang ky hoc moi trong ky', 'icon' => 'fas fa-calendar-days', 'tone' => 'violet'],
        ['label' => 'Tong don ung tuyen', 'value' => $summary['totalTeacherApplications'], 'meta' => $summary['teacherApplicationsInPeriod'] . ' don moi trong ky', 'icon' => 'fas fa-file-signature', 'tone' => 'rose'],
        ['label' => 'Dang ky cho duyet', 'value' => $summary['pendingEnrollments'], 'meta' => 'Cho admin xu ly va xep lop', 'icon' => 'fas fa-clipboard-check', 'tone' => 'amber'],
        ['label' => 'Yeu cau doi lich cho duyet', 'value' => $summary['pendingScheduleChanges'], 'meta' => 'Can admin review va phan hoi', 'icon' => 'fas fa-calendar-rotate', 'tone' => 'slate'],
    ];
    $toneMap = [
        'cyan' => ['wrapper' => 'bg-cyan-50 text-cyan-700', 'icon' => 'bg-cyan-500/10 text-cyan-700'],
        'emerald' => ['wrapper' => 'bg-emerald-50 text-emerald-700', 'icon' => 'bg-emerald-500/10 text-emerald-700'],
        'amber' => ['wrapper' => 'bg-amber-50 text-amber-700', 'icon' => 'bg-amber-500/10 text-amber-700'],
        'rose' => ['wrapper' => 'bg-rose-50 text-rose-700', 'icon' => 'bg-rose-500/10 text-rose-700'],
        'violet' => ['wrapper' => 'bg-violet-50 text-violet-700', 'icon' => 'bg-violet-500/10 text-violet-700'],
        'slate' => ['wrapper' => 'bg-slate-100 text-slate-700', 'icon' => 'bg-slate-500/10 text-slate-700'],
    ];
    $trendSets = [
        ['label' => 'Hoc vien moi', 'data' => $activityTrend['students'], 'color' => 'bg-sky-500'],
        ['label' => 'Dang ky hoc', 'data' => $activityTrend['enrollments'], 'color' => 'bg-cyan-500'],
        ['label' => 'Don ung tuyen', 'data' => $activityTrend['applications'], 'color' => 'bg-amber-500'],
        ['label' => 'Danh gia', 'data' => $activityTrend['reviews'], 'color' => 'bg-emerald-500'],
    ];
?>
<div class="space-y-6">
    <section class="rounded-[28px] bg-slate-950 p-6 text-white shadow-xl shadow-slate-900/10">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-300">Phase 11</p>
                <h1 class="mt-3 text-3xl font-semibold">Bao cao tong quan he thong</h1>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-300">Tong hop cac chi so quan tri quan trong theo khung thoi gian da chon, bao gom quy mo nguoi dung, tien do xu ly nghiep vu, chat luong dao tao va xu huong hoat dong.</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 px-5 py-4 text-sm text-slate-200">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Ky bao cao</p>
                <p class="mt-2 text-lg font-semibold text-white"><?php echo e($rangeLabel); ?></p>
                <p class="mt-1 text-slate-400">Tong khoa hoc public hien co: <?php echo e($summary['publicSubjects']); ?></p>
                <p class="text-slate-400">Dang ky cho duyet hien tai: <?php echo e($summary['pendingEnrollments']); ?></p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="<?php echo e(route('admin.report')); ?>" class="grid gap-4 lg:grid-cols-[1fr_1fr_auto] lg:items-end">
            <div>
                <label class="text-sm font-medium text-slate-700">Tu ngay</label>
                <input type="date" name="start_date" value="<?php echo e($filters['start_date']); ?>" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Den ngay</label>
                <input type="date" name="end_date" value="<?php echo e($filters['end_date']); ?>" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Cap nhat bao cao</button>
                <a href="<?php echo e(route('admin.report')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Mac dinh</a>
            </div>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-3">
        <?php $__currentLoopData = $summaryCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php ($tone = $toneMap[$card['tone']]); ?>
            <article class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500"><?php echo e($card['label']); ?></p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900"><?php echo e($card['value']); ?></p>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e($card['meta']); ?></p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl <?php echo e($tone['icon']); ?>">
                        <i class="<?php echo e($card['icon']); ?> text-lg"></i>
                    </div>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(340px,0.85fr)]">
        <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Xu huong hoat dong</h2>
                    <p class="mt-1 text-sm text-slate-500">Bieu do nhe theo <?php echo e($activityTrend['mode'] === 'day' ? 'ngay' : 'thang'); ?> trong ky bao cao.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?php echo e(count($activityTrend['labels'])); ?> moc du lieu</span>
            </div>
            <div class="mt-6 space-y-6">
                <?php $__currentLoopData = $trendSets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trend): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <div class="mb-3 flex items-center justify-between text-sm">
                            <span class="font-semibold text-slate-800"><?php echo e($trend['label']); ?></span>
                            <span class="text-slate-500">Tong <?php echo e(array_sum($trend['data'])); ?></span>
                        </div>
                        <div class="grid gap-2" style="grid-template-columns: repeat(<?php echo e(count($activityTrend['labels'])); ?>, minmax(0, 1fr));">
                            <?php $__currentLoopData = $trend['data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php ($height = max(10, (int) round(($value / $activityTrend['max']) * 120))); ?>
                                <div class="flex flex-col items-center gap-2">
                                    <div class="flex h-32 items-end">
                                        <div class="w-6 rounded-t-xl <?php echo e($trend['color']); ?>" style="height: <?php echo e($height); ?>px"></div>
                                    </div>
                                    <span class="text-[11px] font-medium text-slate-500"><?php echo e($activityTrend['labels'][$index]); ?></span>
                                    <span class="text-xs font-semibold text-slate-700"><?php echo e($value); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        <section class="space-y-6">
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">Chat luong dao tao</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Diem trung binh</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($quality['averageScore'] !== null ? $quality['averageScore'] : 'N/A'); ?></p>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e($quality['gradeCount']); ?> ban ghi diem trong ky</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ty le dat</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($quality['passRate'] !== null ? $quality['passRate'] . '%' : 'N/A'); ?></p>
                        <p class="mt-1 text-xs text-slate-500">Tinh tren so ban ghi diem co score</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Danh gia khoa hoc</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($quality['averageCourseRating'] !== null ? $quality['averageCourseRating'] . '/5' : 'N/A'); ?></p>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e($quality['courseReviewCount']); ?> danh gia trong ky</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Danh gia giang vien</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($quality['averageTeacherRating'] !== null ? $quality['averageTeacherRating'] . '/5' : 'N/A'); ?></p>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e($quality['teacherReviewCount']); ?> review co giang vien</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">Du lieu bo sung</h2>
                <div class="mt-5 space-y-4 text-sm text-slate-600">
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4">
                        <p class="font-semibold text-slate-800">Ty le diem danh</p>
                        <p class="mt-2 leading-6"><?php echo e($availability['attendance']['message']); ?></p>
                    </div>
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4">
                        <p class="font-semibold text-slate-800">Doanh thu / thanh toan</p>
                        <p class="mt-2 leading-6"><?php echo e($availability['payments']['message']); ?></p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Top khoa hoc theo dang ky</h2>
                    <p class="mt-1 text-sm text-slate-500">Thong ke theo so luot dang ky trong ky bao cao.</p>
                </div>
            </div>
            <div class="mt-5 space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $topCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-4">
                        <div>
                            <p class="font-semibold text-slate-900"><?php echo e($subject->name); ?></p>
                            <p class="mt-1 text-sm text-slate-500"><?php echo e($subject->category?->name ?? 'Chua phan nhom'); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-semibold text-slate-900"><?php echo e($subject->enrollments_in_period); ?></p>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Dang ky</p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Chua co dang ky khoa hoc nao trong ky nay.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Top giang vien theo danh gia</h2>
                    <p class="mt-1 text-sm text-slate-500">Tinh tren review cua cac lop hoc do giang vien phu trach.</p>
                </div>
            </div>
            <div class="mt-5 space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $topTeachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacherRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-4">
                        <div>
                            <p class="font-semibold text-slate-900"><?php echo e($teacherRow['teacher']->name); ?></p>
                            <p class="mt-1 text-sm text-slate-500"><?php echo e($teacherRow['courses_count']); ?> lop co review</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-semibold text-slate-900"><?php echo e($teacherRow['average_rating']); ?>/5</p>
                            <p class="text-xs uppercase tracking-wide text-slate-400"><?php echo e($teacherRow['reviews_count']); ?> review</p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Chua co du lieu danh gia giang vien trong ky nay.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/report.blade.php ENDPATH**/ ?>