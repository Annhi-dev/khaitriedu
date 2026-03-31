<?php $__env->startSection('title', 'Quản lý khóa học'); ?>
<?php $__env->startSection('content'); ?>
<?php
    $createRouteParams = [];

    if (! empty($filters['category_id'])) {
        $createRouteParams['category_id'] = $filters['category_id'];
        $createRouteParams['return_to_category_id'] = $filters['category_id'];
    }
?>
<div class="space-y-6">
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => 'Quản lý khóa học','subtitle' => 'Các khóa học public để học viên đăng ký']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Quản lý khóa học','subtitle' => 'Các khóa học public để học viên đăng ký']); ?>
        <a href="<?php echo e(route('admin.subjects.create-page', $createRouteParams)); ?>" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
            <i class="fas fa-plus mr-1"></i> Thêm khóa học
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

    <form method="get" action="<?php echo e(route('admin.subjects')); ?>" class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tìm kiếm</label>
                <input type="text" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Tên khóa học" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nhóm học</label>
                <select name="category_id" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                    <option value="">Tất cả</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat->id); ?>" <?php if(($filters['category_id'] ?? '') == $cat->id): echo 'selected'; endif; ?>><?php echo e($cat->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái</label>
                <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                    <option value="">Tất cả</option>
                    <?php $__currentLoopData = ['draft' => 'Nháp', 'open' => 'Đang mở', 'closed' => 'Đóng đăng ký', 'archived' => 'Lưu trữ']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php if(($filters['status'] ?? '') == $val): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-xl text-sm font-semibold">Lọc</button>
                <a href="<?php echo e(route('admin.subjects')); ?>" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium">Xóa lọc</a>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Khóa học</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Nhóm học</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Quy mô</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800"><?php echo e($subject->name); ?></div>
                            <div class="mt-2 text-sm text-slate-600">Học phí: <?php echo e(number_format($subject->price, 0, ',', '.')); ?>đ</div>
                            <div class="text-xs text-slate-500"><?php echo e($subject->durationLabel()); ?></div>
                        </td>
                        <td class="px-6 py-4"><?php echo e($subject->category?->name ?? 'Chưa gắn'); ?></td>
                        <td class="px-6 py-4">
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => match($subject->status) {'open' => 'success', 'draft' => 'default', 'closed' => 'warning', 'archived' => 'danger', default => 'default'},'text' => $subject->statusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($subject->status) {'open' => 'success', 'draft' => 'default', 'closed' => 'warning', 'archived' => 'danger', default => 'default'}),'text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($subject->statusLabel())]); ?>
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
                        <td class="px-6 py-4">
                            <div><?php echo e($subject->courses_count); ?> lớp</div>
                            <div class="text-xs text-slate-500"><?php echo e($subject->enrollments_count); ?> đăng ký</div>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="<?php echo e(route('admin.subject.show', $subject)); ?>" class="text-cyan-600 hover:text-cyan-800"><i class="fas fa-eye"></i></a>
                            <a href="<?php echo e(route('admin.subjects.edit', $subject)); ?>" class="text-slate-600 hover:text-slate-800"><i class="fas fa-edit"></i></a>
                            <?php if($subject->status !== 'archived'): ?>
                                <form class="inline" method="post" action="<?php echo e(route('admin.subjects.archive', $subject)); ?>" onsubmit="return confirm('Chuyển sang lưu trữ?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-rose-600 hover:text-rose-800"><i class="fas fa-archive"></i></button>
                                </form>
                            <?php else: ?>
                                <form class="inline" method="post" action="<?php echo e(route('admin.subjects.reopen', $subject)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800"><i class="fas fa-undo-alt"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">Chưa có khóa học nào</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            <?php echo e($subjects->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/subject/index.blade.php ENDPATH**/ ?>