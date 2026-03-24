
<?php $__env->startSection('title','Danh sách khóa học'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto">
  <div class="mb-4"><h1 class="text-2xl font-bold">Khóa học</h1><p class="text-gray-600">Xem chi tiết, module, và đăng ký</p></div>
  <?php if(session('status')): ?><div class="bg-green-100 text-green-800 p-2 rounded mb-3"><?php echo e(session('status')); ?></div><?php endif; ?>
  <?php if(session('error')): ?><div class="bg-red-100 text-red-800 p-2 rounded mb-3"><?php echo e(session('error')); ?></div><?php endif; ?>
  <div class="grid md:grid-cols-2 gap-3">
    <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <a href="<?php echo e(route('courses.show', $c->id)); ?>" class="card border p-3 rounded-xl hover:shadow">
        <div class="font-semibold"><?php echo e($c->title); ?></div>
        <div class="text-xs text-gray-500">Môn: <?php echo e($c->subject->name ?? 'N/A'); ?></div>
        <div class="text-xs text-gray-500">Giảng viên: <?php echo e($c->teacher?->name ?? 'Chưa gán'); ?></div>
        <div class="text-xs text-gray-500">Lịch: <?php echo e($c->schedule ?? 'Chưa có'); ?></div>
      </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/courses/index.blade.php ENDPATH**/ ?>