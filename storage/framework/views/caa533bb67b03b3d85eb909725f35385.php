<?php $__env->startSection('title', 'Quản lý lớp học'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Quản lý lớp học</h1>
            <p class="mt-1 text-sm text-slate-500">Tạo và quản lý các lớp học thực tế với phòng học và giảng viên.</p>
        </div>
        <a href="<?php echo e(route('admin.classes.create')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700 transition">
            <i class="fas fa-plus"></i> Tạo lớp mới
        </a>
    </div>

    <?php if(session('status')): ?>
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800"><?php echo e(session('status')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <ul class="space-y-1 text-sm"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
        </div>
    <?php endif; ?>

    
    <form method="GET" class="flex flex-wrap gap-3">
        <select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tất cả trạng thái</option>
            <option value="open" <?php if(request('status') === 'open'): echo 'selected'; endif; ?>>Đang mở</option>
            <option value="full" <?php if(request('status') === 'full'): echo 'selected'; endif; ?>>Đủ chỗ</option>
            <option value="closed" <?php if(request('status') === 'closed'): echo 'selected'; endif; ?>>Đã đóng</option>
            <option value="completed" <?php if(request('status') === 'completed'): echo 'selected'; endif; ?>>Hoàn thành</option>
        </select>
        <select name="subject_id" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tất cả môn học</option>
            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($subject->id); ?>" <?php if(request('subject_id') == $subject->id): echo 'selected'; endif; ?>><?php echo e($subject->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button type="submit" class="rounded-xl bg-slate-700 px-4 py-2 text-sm text-white hover:bg-slate-800">Lọc</button>
        <a href="<?php echo e(route('admin.classes.index')); ?>" class="rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Xóa lọc</a>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Môn học / Khóa học</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Giảng viên</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Phòng</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Lịch học</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Học viên</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Trạng thái</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-800"><?php echo e($class->subject->name ?? '—'); ?></div>
                        <div class="text-xs text-slate-500">Môn học</div>
                    </td>
                    <td class="px-4 py-3 text-slate-700"><?php echo e($class->teacher->name ?? 'Chưa phân công'); ?></td>
                    <td class="px-4 py-3 text-slate-700"><?php echo e($class->room->name ?? 'Chưa chọn'); ?></td>
                    <td class="px-4 py-3">
                        <?php $__currentLoopData = $class->schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="block text-xs text-slate-600"><?php echo e(\App\Models\ClassSchedule::$dayOptions[$s->day_of_week] ?? $s->day_of_week); ?>: <?php echo e($s->start_time); ?>–<?php echo e($s->end_time); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($class->schedules->isEmpty()): ?> <span class="text-xs text-slate-400">Chưa có lịch</span> <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-slate-700">
                        <?php echo e($class->enrollments_count); ?>

                        <?php if($class->room): ?> / <?php echo e($class->room->capacity); ?> <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php
                            $badge = match($class->status) {
                                'open'      => 'bg-green-100 text-green-700',
                                'full'      => 'bg-amber-100 text-amber-700',
                                'closed'    => 'bg-slate-100 text-slate-600',
                                'completed' => 'bg-blue-100 text-blue-700',
                                default     => 'bg-slate-100 text-slate-600',
                            };
                        ?>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($badge); ?>"><?php echo e($class->statusLabel()); ?></span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="<?php echo e(route('admin.classes.show', $class)); ?>" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">Chi tiết</a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-slate-400">Chưa có lớp học nào.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div><?php echo e($classes->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/classes/index.blade.php ENDPATH**/ ?>