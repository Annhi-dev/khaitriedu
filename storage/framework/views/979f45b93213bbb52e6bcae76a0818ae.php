<?php $__env->startSection('title', 'Xác minh quên mật khẩu'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-md mx-auto">
    <div class="card bg-white p-8 rounded-2xl shadow-md">
        <h3 class="text-2xl font-bold text-primary-dark mb-6 text-center">Xác minh OTP</h3>

        <?php if(session('status')): ?>
            <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg"><?php echo e(session('status')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <form action="<?php echo e(route('forgot.verify.post')); ?>" method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="email" value="<?php echo e(request('email')); ?>">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Mã OTP</label>
                <input type="text" name="code" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
            </div>
            <button type="submit" class="btn w-full py-3 bg-primary text-white rounded-xl shadow-md hover:bg-primary-dark transition">Xác nhận</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\auth\forgot_verify.blade.php ENDPATH**/ ?>