<?php $__env->startSection('title', 'Yêu cầu đổi lịch'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <span class="sr-only">Phase 10</span>

    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => 'Yêu cầu đổi lịch','subtitle' => 'Các đề xuất đổi lịch từ giảng viên đang chờ admin xử lý']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Yêu cầu đổi lịch','subtitle' => 'Các đề xuất đổi lịch từ giảng viên đang chờ admin xử lý']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal3f97fad59f1d161af1828ef2108f500f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3f97fad59f1d161af1828ef2108f500f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-bar','data' => ['route' => ''.e(route('admin.schedule-change-requests.index')).'','searchPlaceholder' => 'Giảng viên, lớp học, lý do...','statuses' => ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => ''.e(route('admin.schedule-change-requests.index')).'','searchPlaceholder' => 'Giảng viên, lớp học, lý do...','statuses' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'])]); ?>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requestItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex justify-between items-start gap-4">
                    <div>
                        <h3 class="font-semibold text-slate-800"><?php echo e($requestItem->course?->title ?? 'Lớp học'); ?></h3>
                        <p class="text-sm text-slate-500">Giảng viên: <?php echo e($requestItem->teacher?->name); ?></p>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => match($requestItem->status) {'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'default'},'text' => $requestItem->statusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($requestItem->status) {'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'default'}),'text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($requestItem->statusLabel())]); ?>
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
                </div>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div><span class="text-slate-500">Lịch hiện tại:</span> <?php echo e($requestItem->currentScheduleLabel()); ?></div>
                    <div><span class="text-slate-500">Lịch đề xuất:</span> <?php echo e($requestItem->requestedScheduleLabel()); ?></div>
                </div>
                <div class="mt-4 p-3 bg-slate-50 rounded-xl text-sm">
                    <span class="font-medium">Lý do:</span> <?php echo e(\Illuminate\Support\Str::limit($requestItem->reason, 100)); ?>

                </div>
                <div class="mt-4 flex justify-end">
                    <a href="<?php echo e(route('admin.schedule-change-requests.show', $requestItem)); ?>" class="inline-flex items-center px-4 py-2 rounded-xl bg-cyan-600 text-white text-sm font-semibold hover:bg-cyan-700 transition">Xử lý</a>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-2 text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300">
                <p class="text-slate-500">Không có yêu cầu đổi lịch nào.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if($requests->hasPages()): ?>
        <div><?php echo e($requests->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/schedule_change_requests/index.blade.php ENDPATH**/ ?>