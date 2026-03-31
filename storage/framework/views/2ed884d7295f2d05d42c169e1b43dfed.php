<?php $__env->startSection('title', 'Quản lý giảng viên'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => 'Quản lý giảng viên','subtitle' => 'Theo dõi và quản lý tài khoản giảng viên']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Quản lý giảng viên','subtitle' => 'Theo dõi và quản lý tài khoản giảng viên']); ?>
        <a href="<?php echo e(route('admin.teachers.create')); ?>" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
            <i class="fas fa-plus mr-1"></i> Thêm giảng viên
        </a>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-bar','data' => ['route' => ''.e(route('admin.teachers.index')).'','searchPlaceholder' => 'Tên, email, số điện thoại','statuses' => ['active'=>'Hoạt động', 'inactive'=>'Tạm dừng', 'locked'=>'Khóa']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => ''.e(route('admin.teachers.index')).'','searchPlaceholder' => 'Tên, email, số điện thoại','statuses' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['active'=>'Hoạt động', 'inactive'=>'Tạm dừng', 'locked'=>'Khóa'])]); ?>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Giảng viên</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Liên hệ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Lớp phụ trách</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Yêu cầu đổi lịch</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800"><?php echo e($teacher->name); ?></div>
                            <div class="text-xs text-slate-500"><?php echo e($teacher->username); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div><?php echo e($teacher->email); ?></div>
                            <div class="text-xs text-slate-500"><?php echo e($teacher->phone ?: 'Chưa có số'); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => match($teacher->status) {'active'=>'success','inactive'=>'warning','locked'=>'danger', default=>'default'},'text' => $teacher->statusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($teacher->status) {'active'=>'success','inactive'=>'warning','locked'=>'danger', default=>'default'}),'text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($teacher->statusLabel())]); ?>
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
                        <td class="px-6 py-4"><?php echo e($teacher->taught_courses_count); ?></td>
                        <td class="px-6 py-4"><?php echo e($teacher->schedule_change_requests_count); ?></td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="<?php echo e(route('admin.teachers.show', $teacher)); ?>" class="text-cyan-600 hover:text-cyan-800"><i class="fas fa-eye"></i></a>
                            <a href="<?php echo e(route('admin.teachers.edit', $teacher)); ?>" class="text-slate-600 hover:text-slate-800"><i class="fas fa-edit"></i></a>
                            <?php if($teacher->isLocked()): ?>
                                <form class="inline" method="post" action="<?php echo e(route('admin.teachers.unlock', $teacher)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800"><i class="fas fa-unlock-alt"></i></button>
                                </form>
                            <?php else: ?>
                                <form class="inline" method="post" action="<?php echo e(route('admin.teachers.lock', $teacher)); ?>" onsubmit="return confirm('Khóa tài khoản này?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-rose-600 hover:text-rose-800"><i class="fas fa-lock"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">Chưa có giảng viên nào</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            <?php echo e($teachers->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/teachers/index.blade.php ENDPATH**/ ?>