<?php $__env->startSection('title', 'Điểm số của tôi'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
  <div class="flex justify-between items-center mb-4">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Điểm số của tôi</h1>
      <p class="text-gray-600">Xem kết quả học tập của các khóa học.</p>
    </div>
    <a href="<?php echo e(route('dashboard')); ?>" class="btn rounded-lg border border-gray-300 px-4 py-2">Quay lại dashboard</a>
  </div>

  <?php if(session('status')): ?><div class="alert alert-success mb-3"><?php echo e(session('status')); ?></div><?php endif; ?>
  <?php if(session('error')): ?><div class="alert alert-danger mb-3"><?php echo e(session('error')); ?></div><?php endif; ?>

  <div class="grid gap-4">
    <?php $__empty_1 = true; $__currentLoopData = $grades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <div class="card bg-white p-4 rounded-xl shadow-sm">
        <h3 class="font-semibold text-lg mb-2"><?php echo e($grade->enrollment->course->title); ?></h3>
        <div class="grid md:grid-cols-3 gap-4 text-sm">
          <div>
            <p><strong>Module:</strong> <?php echo e($grade->module->title ?? 'Tổng kết'); ?></p>
            <p><strong>Điểm số:</strong> <?php echo e($grade->score ?? 'Chưa có'); ?></p>
          </div>
          <div>
            <p><strong>Điểm chữ:</strong> <?php echo e($grade->grade ?? 'Chưa có'); ?></p>
            <p><strong>Ngày cập nhật:</strong> <?php echo e($grade->updated_at->format('d/m/Y')); ?></p>
          </div>
          <div>
            <p><strong>Phản hồi:</strong></p>
            <p class="text-gray-700"><?php echo e($grade->feedback ?: 'Không có'); ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="text-center py-8">
        <p class="text-gray-500">Bạn chưa có điểm số nào.</p>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\student\grades.blade.php ENDPATH**/ ?>