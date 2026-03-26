<?php $__env->startSection('title', 'Chi tiết hồ sơ ứng tuyển'); ?>
<?php $__env->startSection('content'); ?>
<div class="bg-white p-6 rounded-2xl shadow-md">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-primary-dark">Chi tiết hồ sơ ứng tuyển</h1>
        <a href="<?php echo e(route('admin.teacher-applications')); ?>" class="btn border border-gray-300 px-3 py-2 rounded-lg hover:bg-gray-100 transition">Quay lại danh sách</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-4 bg-gray-50 rounded-xl">
            <p class="text-sm text-gray-500">Tên</p>
            <p class="text-lg font-semibold text-gray-800"><?php echo e($application->name); ?></p>
        </div>
        <div class="p-4 bg-gray-50 rounded-xl">
            <p class="text-sm text-gray-500">Email</p>
            <p class="text-lg font-semibold text-gray-800"><?php echo e($application->email); ?></p>
        </div>
        <div class="p-4 bg-gray-50 rounded-xl">
            <p class="text-sm text-gray-500">Số điện thoại</p>
            <p class="text-lg font-semibold text-gray-800"><?php echo e($application->phone ?: '-'); ?></p>
        </div>
        <div class="p-4 bg-gray-50 rounded-xl">
            <p class="text-sm text-gray-500">Trạng thái</p>
            <p class="text-lg font-semibold <?php echo e($application->status == 'pending' ? 'text-yellow-600' : ($application->status == 'approved' ? 'text-green-600' : 'text-red-600')); ?>"><?php echo e(ucfirst($application->status)); ?></p>
        </div>
        <div class="p-4 bg-gray-50 rounded-xl md:col-span-2">
            <p class="text-sm text-gray-500">Kinh nghiệm giảng dạy</p>
            <p class="text-gray-800 whitespace-pre-wrap"><?php echo e($application->experience ?: '-'); ?></p>
        </div>
        <div class="p-4 bg-gray-50 rounded-xl md:col-span-2">
            <p class="text-sm text-gray-500">Lý do ứng tuyển</p>
            <p class="text-gray-800 whitespace-pre-wrap"><?php echo e($application->message); ?></p>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <?php if($application->status === 'pending'): ?>
        <form action="<?php echo e(route('admin.teacher-applications.review', $application->id)); ?>" method="POST" class="inline-block">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="approved">
            <button type="submit" class="btn px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">Duyệt</button>
        </form>

        <form action="<?php echo e(route('admin.teacher-applications.review', $application->id)); ?>" method="POST" class="inline-block">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="rejected">
            <button type="submit" class="btn px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">Từ chối</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\admin\teacher_application_show.blade.php ENDPATH**/ ?>