<?php $__env->startSection('title', 'Ứng tuyển giảng viên - KhaiTriEdu'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-20">
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg p-8">
        <h1 class="text-3xl font-bold text-primary-dark mb-4">Ứng tuyển làm giảng viên</h1>
        <p class="text-gray-600 mb-6">Chúng tôi tìm kiếm giảng viên năng động, nhiệt huyết và có kinh nghiệm giảng dạy.</p>

        <?php if(session('status')): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-4"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <form action="<?php echo e(route('apply-teacher.post')); ?>" method="POST" class="space-y-5">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Họ và tên</label>
                <input type="text" name="name" value="<?php echo e(old('name')); ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Nhập họ tên">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="<?php echo e(old('email')); ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Nhập email">
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Số điện thoại</label>
                <input type="text" name="phone" value="<?php echo e(old('phone')); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Nhập số điện thoại">
                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Kinh nghiệm giảng dạy</label>
                <textarea name="experience" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Mô tả kinh nghiệm của bạn"><?php echo e(old('experience')); ?></textarea>
                <?php $__errorArgs = ['experience'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Lý do ứng tuyển</label>
                <textarea name="message" rows="4" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Cho chúng tôi biết tại sao bạn phù hợp"><?php echo e(old('message')); ?></textarea>
                <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <p class="text-sm text-gray-500">Admin sẽ kiểm tra hồ sơ và phản hồi trong vòng 24-48 giờ.</p>

            <button type="submit" class="w-full bg-primary text-white rounded-lg py-3 font-semibold hover:bg-primary-dark transition">Gửi hồ sơ ứng tuyển</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\pages\apply-teacher.blade.php ENDPATH**/ ?>