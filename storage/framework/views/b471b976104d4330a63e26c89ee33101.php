<?php $__env->startSection('title', 'Chi tiết khóa học'); ?>
<?php $__env->startSection('content'); ?>
<?php
    $statusClasses = match ($subject->status) {
        \App\Models\Subject::STATUS_OPEN => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        \App\Models\Subject::STATUS_DRAFT => 'border-slate-200 bg-slate-100 text-slate-700',
        \App\Models\Subject::STATUS_CLOSED => 'border-amber-200 bg-amber-50 text-amber-700',
        \App\Models\Subject::STATUS_ARCHIVED => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-100 text-slate-700',
    };
?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Hồ sơ khóa học</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900"><?php echo e($subject->name); ?></h1>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                <span><?php echo e($subject->category?->name ?? 'Chưa gắn nhóm học'); ?></span>
                <span class="text-slate-300">|</span>
                <span><?php echo e($subject->durationLabel()); ?></span>
                <span class="text-slate-300">|</span>
                <span><?php echo e(number_format((float) $subject->price, 0, ',', '.')); ?> đ</span>
                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold <?php echo e($statusClasses); ?>"><?php echo e($subject->statusLabel()); ?></span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?php echo e(route('admin.subjects')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Danh sách khóa học</a>
            <a href="<?php echo e(route('admin.subjects.edit', $subject)); ?>" class="inline-flex items-center justify-center rounded-2xl border border-cyan-200 px-4 py-2.5 text-sm font-medium text-cyan-700 hover:bg-cyan-50">Sửa thông tin</a>
            <?php if($subject->status === \App\Models\Subject::STATUS_ARCHIVED): ?>
                <form method="post" action="<?php echo e(route('admin.subjects.reopen', $subject)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Mở lại khóa học</button>
                </form>
            <?php else: ?>
                <form method="post" action="<?php echo e(route('admin.subjects.archive', $subject)); ?>" onsubmit="return confirm('Chuyển khóa học này sang trạng thái lưu trữ?');">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Lưu trữ khóa học</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Thông tin khóa học</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?php echo e($subject->modules_count); ?> module hiện có</span>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Tên khóa học</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($subject->name); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Nhóm học cha</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($subject->category?->name ?? 'Chưa gắn nhóm học'); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Trạng thái</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($subject->statusLabel()); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Thời lượng</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($subject->durationLabel()); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Học phí</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e(number_format((float) $subject->price, 0, ',', '.')); ?> đ</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Đăng ký học</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($subject->enrollments_count); ?> lượt</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Mô tả</p>
                    <p class="mt-1 text-sm leading-6 text-slate-700"><?php echo e($subject->description ?: 'Chưa có mô tả cho khóa học này.'); ?></p>
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Ảnh đại diện</h2>
                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4">
                    <?php if($subject->image): ?>
                        <img src="<?php echo e(asset('storage/' . $subject->image)); ?>" alt="<?php echo e($subject->name); ?>" class="h-48 w-full rounded-2xl object-cover" />
                    <?php else: ?>
                        <div class="flex h-48 items-center justify-center rounded-2xl bg-slate-100 text-sm text-slate-500">Chưa có ảnh đại diện</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Lưu ý phân công</h2>
                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-600">
                    <p>Giảng viên chưa được gán trực tiếp ở cấp khóa học public.</p>
                    <p class="mt-2">Admin sẽ gán giảng viên khi tạo hoặc sắp xếp lớp học nội bộ để giữ đúng flow duyệt và phân lớp của hệ thống.</p>
                </div>
            </section>
        </aside>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Lớp học nội bộ thuộc khóa này</h2>
                <p class="text-sm text-slate-500">Hiển thị các lớp học nội bộ đang dùng khóa học này để admin nắm nhanh tiến độ chuẩn bị cho các phase xếp lớp và module.</p>
            </div>
            <a href="<?php echo e(route('admin.courses')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Mở quản lý lớp học</a>
        </div>

        <div class="mt-5 grid gap-4">
            <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="rounded-2xl border border-slate-200 px-4 py-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900"><?php echo e($course->title); ?></p>
                            <p class="mt-2 text-sm leading-6 text-slate-600"><?php echo e($course->description ?: 'Chưa có mô tả cho lớp học này.'); ?></p>
                            <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-500">
                                <span>Giảng viên: <?php echo e($course->teacher?->name ?? 'Chưa phân công'); ?></span>
                                <span>Lịch: <?php echo e($course->schedule ?: 'Chưa chốt'); ?></span>
                                <span><?php echo e($course->modules_count); ?> module</span>
                                <span><?php echo e($course->enrollments_count); ?> học viên</span>
                            </div>
                        </div>
                        <a href="<?php echo e(route('admin.course.show', $course->id)); ?>" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 px-3 py-2 text-xs font-medium text-cyan-700 hover:bg-cyan-50">Xem lớp học</a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Khóa học này chưa có lớp học nội bộ nào được tạo.</div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/subject/show.blade.php ENDPATH**/ ?>