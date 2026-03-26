<?php $__env->startSection('title', 'Chứng chỉ ' . $cert->certificate_number); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">
    <a href="<?php echo e(route('certificates.index')); ?>" class="text-primary hover:underline">← Quay lại chứng chỉ</a>
    <div class="bg-white p-6 rounded-xl shadow-sm mt-4">
        <h1 class="text-3xl font-bold mb-4">Chứng chỉ hoàn thành</h1>
        <p class="text-lg">Khóa học: <strong><?php echo e($cert->course->title ?? 'N/A'); ?></strong></p>
        <p class="text-md">Mã: <strong><?php echo e($cert->certificate_number); ?></strong></p>
        <p class="text-md">Học viên: <strong><?php echo e($cert->user->name ?? 'N/A'); ?></strong></p>
        <p class="text-md">Điểm: <strong><?php echo e($cert->score); ?>%</strong></p>
        <p class="text-md">Ngày cấp: <strong><?php echo e($cert->issued_at ? $cert->issued_at->format('d/m/Y H:i') : '-'); ?></strong></p>
        <p class="text-md">Trạng thái: <strong><?php echo e(ucfirst($cert->status)); ?></strong></p>
        <a href="#" class="mt-4 inline-block bg-primary text-white px-5 py-2 rounded-lg hover:bg-primary-dark">Tải PDF (chưa triển khai)</a>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\certificates\show.blade.php ENDPATH**/ ?>