<?php $__env->startSection('title', 'Cập nhật nhóm học'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 5</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Cập nhật nhóm học</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Điều chỉnh thông tin nhóm học để giữ đúng cấu trúc public `nhóm học -> khóa học`, còn lớp học nội bộ sẽ được admin xử lý ở bước sau.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?php echo e(route('admin.categories.show', $category)); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Xem chi tiết</a>
            <a href="<?php echo e(route('admin.categories')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Danh sách nhóm học</a>
        </div>
    </div>

    <form method="post" action="<?php echo e(route('admin.categories.update', $category)); ?>" enctype="multipart/form-data" class="space-y-6">
        <?php echo csrf_field(); ?>
        <?php echo $__env->make('admin.study_groups._form', ['category' => $category], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="<?php echo e(route('admin.categories.show', $category)); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Hủy</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Lưu thay đổi</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/study_groups/edit.blade.php ENDPATH**/ ?>