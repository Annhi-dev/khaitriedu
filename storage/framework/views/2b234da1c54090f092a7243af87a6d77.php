<?php $__env->startSection('title', 'Quản lý nhóm học'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => 'Quản lý nhóm học','subtitle' => 'Danh sách này bám theo bảng danh mục và đếm đúng số khóa học thực tế đang nằm trong từng nhóm.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Quản lý nhóm học','subtitle' => 'Danh sách này bám theo bảng danh mục và đếm đúng số khóa học thực tế đang nằm trong từng nhóm.']); ?>
        <a href="<?php echo e(route('admin.categories.create-page')); ?>" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
            <i class="fas fa-plus mr-1"></i> Thêm nhóm học
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-bar','data' => ['route' => ''.e(route('admin.categories')).'','searchPlaceholder' => 'Tên, slug, mô tả...','statuses' => ['active' => 'Hoạt động', 'inactive' => 'Ngừng hoạt động']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => ''.e(route('admin.categories')).'','searchPlaceholder' => 'Tên, slug, mô tả...','statuses' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['active' => 'Hoạt động', 'inactive' => 'Ngừng hoạt động'])]); ?>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Nhóm học</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Chương trình</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Khóa học trong nhóm</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $createCourseUrl = $category->defaultSubject
                            ? route('admin.courses', ['subject_id' => $category->defaultSubject->id, 'return_to_category_id' => $category->id])
                            : route('admin.subjects.create-page', ['category_id' => $category->id, 'return_to_category_id' => $category->id]);
                    ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800"><?php echo e($category->name); ?></div>
                            <div class="text-xs text-slate-500">/<?php echo e($category->slug); ?></div>
                            <p class="text-sm text-slate-600 mt-2 max-w-md"><?php echo e(Str::limit($category->description, 80)); ?></p>
                        </td>
                        <td class="px-6 py-4">
                            <div><?php echo e($category->program ?: 'Chưa cấu hình'); ?></div>
                            <div class="text-xs text-slate-500">Cấp độ: <?php echo e($category->level ?: 'Chưa'); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => $category->status === 'active' ? 'success' : 'warning','text' => $category->statusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($category->status === 'active' ? 'success' : 'warning'),'text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($category->statusLabel())]); ?>
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
                            <div class="font-medium text-slate-800"><?php echo e($category->courses_count); ?> khóa học</div>
                            <div class="mt-1 text-xs text-slate-500">
                                <?php echo e($category->courses_count > 0 ? 'Đã có khóa học thực tế trong nhóm này.' : 'Chưa có khóa học nào trong nhóm này.'); ?>

                            </div>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="<?php echo e($createCourseUrl); ?>" class="text-emerald-600 hover:text-emerald-800" title="Tạo khóa học trong nhóm"><i class="fas fa-plus-circle"></i></a>
                            <a href="<?php echo e(route('admin.categories.show', $category)); ?>" class="text-cyan-600 hover:text-cyan-800" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                            <a href="<?php echo e(route('admin.categories.edit', $category)); ?>" class="text-slate-600 hover:text-slate-800" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                            <?php if($category->status === 'active'): ?>
                                <form class="inline" method="post" action="<?php echo e(route('admin.categories.deactivate', $category)); ?>" onsubmit="return confirm('Ngừng hoạt động nhóm học này?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-amber-600 hover:text-amber-800" title="Ngừng hoạt động"><i class="fas fa-pause-circle"></i></button>
                                </form>
                            <?php else: ?>
                                <form class="inline" method="post" action="<?php echo e(route('admin.categories.activate', $category)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800" title="Kích hoạt lại"><i class="fas fa-play-circle"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">Chưa có nhóm học nào</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            <?php echo e($categories->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/study_groups/index.blade.php ENDPATH**/ ?>