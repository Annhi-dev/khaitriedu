<?php $__env->startSection('title', 'Quản lý đăng ký học'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 8</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Quản lý đăng ký học</h1>
            <p class="mt-2 text-sm text-slate-600">Xử lý yêu cầu đăng ký của học viên, rà soát nhu cầu học và chuyển hồ sơ sang bước duyệt hoặc xếp lớp.</p>
        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginal3f97fad59f1d161af1828ef2108f500f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3f97fad59f1d161af1828ef2108f500f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-bar','data' => ['route' => ''.e(route('admin.enrollments')).'','searchPlaceholder' => 'Tên học viên, email, khóa học...','statuses' => $statusOptions]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => ''.e(route('admin.enrollments')).'','searchPlaceholder' => 'Tên học viên, email, khóa học...','statuses' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusOptions)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3f97fad59f1d161af1828ef2108f500f)): ?>
<?php $attributes = $__attributesOriginal3f97fad59f1d161af1828ef2108f500f; ?>
<?php unset($__attributesOriginal3f97fad59f1d161af1828ef2108f500f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3f97fad59f1d161af1828ef2108f500f)): ?>
<?php $component = $__componentOriginal3f97fad59f1d161af1828ef2108f500f; ?>
<?php unset($__componentOriginal3f97fad59f1d161af1828ef2108f500f); ?>
<?php endif; ?>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Học viên</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Khóa học</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Khung giờ mong muốn</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Xếp lớp hiện tại</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trạng thái</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-500 uppercase">Xử lý</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $enrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-4">
                            <div class="font-medium"><?php echo e($enrollment->user?->name); ?></div>
                            <div class="text-xs text-slate-500"><?php echo e($enrollment->user?->email); ?></div>
                        </td>
                        <td class="px-5 py-4"><?php echo e($enrollment->subject?->name ?? 'Chưa xác định'); ?></td>
                        <td class="px-5 py-4"><?php echo e($enrollment->start_time ?: '--'); ?> - <?php echo e($enrollment->end_time ?: '--'); ?></td>
                        <td class="px-5 py-4"><?php echo e($enrollment->course?->title ?? 'Chưa xếp lớp'); ?></td>
                        <td class="px-5 py-4">
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => match($enrollment->status) {'pending' => 'warning', 'approved' => 'info', 'scheduled' => 'success', 'active' => 'success', 'completed' => 'default', 'rejected' => 'danger', default => 'default'},'text' => $enrollment->statusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($enrollment->status) {'pending' => 'warning', 'approved' => 'info', 'scheduled' => 'success', 'active' => 'success', 'completed' => 'default', 'rejected' => 'danger', default => 'default'}),'text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($enrollment->statusLabel())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $attributes = $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $component = $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="<?php echo e(route('admin.enrollments.show', $enrollment)); ?>" class="inline-flex items-center px-3 py-1 rounded-xl bg-cyan-50 text-cyan-700 text-xs font-medium hover:bg-cyan-100 transition">Chi tiết</a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">Không có đăng ký học nào phù hợp</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-200">
            <?php echo e($enrollments->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/enrollments/index.blade.php ENDPATH**/ ?>