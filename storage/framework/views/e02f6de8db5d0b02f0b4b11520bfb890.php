<?php $__env->startSection('title', 'Cập nhật khóa học'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 6</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Cập nhật khóa học</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Điều chỉnh thông tin khóa học public, trạng thái mở đăng ký và nhóm học cha mà học viên sẽ nhìn thấy ngoài website.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?php echo e(route('admin.subject.show', $subject)); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Xem chi tiết</a>
            <a href="<?php echo e(route('admin.subjects')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Danh sách khóa học</a>
        </div>
    </div>

    <form method="post" action="<?php echo e(route('admin.subjects.update', $subject)); ?>" enctype="multipart/form-data" class="space-y-6">
        <?php echo csrf_field(); ?>
        <?php echo $__env->make('admin.subject._form', ['subject' => $subject], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="<?php echo e(route('admin.subject.show', $subject)); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Hủy</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Lưu thay đổi</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/subject/edit.blade.php ENDPATH**/ ?>