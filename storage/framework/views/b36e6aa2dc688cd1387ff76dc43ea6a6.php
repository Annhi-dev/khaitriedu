<?php $__env->startSection('title', 'Chứng chỉ của tôi'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Chứng chỉ của tôi</h1>
    <?php if($certificates->isEmpty()): ?>
        <div class="bg-blue-50 border border-blue-200 p-6 rounded-lg text-center">Bạn chưa có chứng chỉ nào.</div>
    <?php else: ?>
        <div class="grid md:grid-cols-2 gap-6">
            <?php $__currentLoopData = $certificates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white border border-gray-200 p-5 rounded-lg">
                    <h2 class="text-xl font-semibold mb-2"><?php echo e($cert->course->title ?? 'Khóa học'); ?></h2>
                    <p class="text-sm text-gray-500">Mã chứng chỉ: <?php echo e($cert->certificate_number); ?></p>
                    <p class="text-sm text-gray-500">Điểm: <?php echo e($cert->score); ?>%</p>
                    <p class="text-sm text-gray-500">Ngày cấp: <?php echo e($cert->issued_at ? $cert->issued_at->format('d/m/Y') : '-'); ?></p>
                    <p class="text-sm text-gray-500">Trạng thái: <?php echo e(ucfirst($cert->status)); ?></p>
                    <a href="<?php echo e(route('certificates.show', $cert->id)); ?>" class="inline-block mt-3 text-primary hover:underline">Xem chi tiết</a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\certificates\index.blade.php ENDPATH**/ ?>