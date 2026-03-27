<?php $__env->startSection('title', 'Hàng chờ xếp lịch'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 9</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Hàng chờ xếp lịch</h1>
            <p class="mt-2 text-sm text-slate-600">Ưu tiên các đăng ký đã được duyệt, sau đó đến các yêu cầu đang chờ cần admin quyết định lịch học chính thức.</p>
        </div>
        <a href="<?php echo e(route('admin.schedules.index')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Xem lịch toàn hệ thống</a>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="<?php echo e(route('admin.schedules.queue')); ?>" class="flex flex-col gap-4 lg:flex-row lg:items-end">
            <div class="flex-1">
                <label class="text-sm font-medium text-slate-700">Tìm kiếm</label>
                <input type="text" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Tên học viên hoặc khóa học..." class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Lọc</button>
                <a href="<?php echo e(route('admin.schedules.queue')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <?php $__empty_1 = true; $__currentLoopData = $enrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400"><?php echo e($enrollment->subject?->category?->name ?? 'Chưa phân nhóm'); ?></p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900"><?php echo e($enrollment->user?->name ?? 'Học viên'); ?></h2>
                        <p class="mt-1 text-sm text-slate-600"><?php echo e($enrollment->subject?->name ?? 'Khóa học chưa xác định'); ?></p>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo e($enrollment->status === \App\Models\Enrollment::STATUS_APPROVED ? 'bg-cyan-100 text-cyan-700' : 'bg-amber-100 text-amber-700'); ?>"><?php echo e($enrollment->statusLabel()); ?></span>
                </div>
                <div class="mt-5 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                    <p><strong>Khung giờ:</strong> <?php echo e($enrollment->start_time ?: '--:--'); ?> - <?php echo e($enrollment->end_time ?: '--:--'); ?></p>
                    <p><strong>Ngày gửi:</strong> <?php echo e($enrollment->submitted_at?->format('d/m/Y H:i') ?: optional($enrollment->created_at)->format('d/m/Y H:i')); ?></p>
                </div>
                <div class="mt-5 flex items-center justify-between gap-3">
                    <p class="text-sm text-slate-500">Tại bước này admin sẽ chọn lớp, giảng viên và lịch học chính thức.</p>
                    <a href="<?php echo e(route('admin.schedules.enrollments.show', $enrollment)); ?>" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Xếp lịch</a>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-500 xl:col-span-2">
                Không còn đăng ký nào đang chờ xếp lịch.
            </div>
        <?php endif; ?>
    </section>

    <?php if($enrollments->hasPages()): ?>
        <div><?php echo e($enrollments->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/schedules/queue.blade.php ENDPATH**/ ?>