
<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto">
    <div class="card bg-white p-8 rounded-2xl shadow-md">
        <h2 class="text-3xl font-bold text-primary-dark">Xin chào, <?php echo e($user->name); ?>!</h2>
        <p class="text-gray-600 mt-2">Bạn đang đăng nhập với vai trò <span class="font-semibold text-primary"><?php echo e(ucfirst(str_replace('_',' ', $user->role))); ?></span>.</p>

        <?php if($user->role === 'admin'): ?>
            <div class="mt-4 p-4 bg-blue-50 text-blue-700 rounded-xl">Bạn là Admin: quản lý người dùng, khóa học, lớp học và toàn bộ hệ thống.</div>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn inline-block mt-4 px-6 py-3 bg-primary text-white rounded-xl shadow hover:bg-primary-dark transition">Mở Admin Panel</a>
        <?php elseif($user->role === 'giang_vien'): ?>
            <div class="mt-4 p-4 bg-green-50 text-green-700 rounded-xl">Bạn là Giảng viên: theo dõi các lớp được giao, mở bài học và cập nhật điểm cho học viên.</div>
            <a href="<?php echo e(route('teacher.dashboard')); ?>" class="btn inline-block mt-4 px-6 py-3 bg-primary text-white rounded-xl shadow hover:bg-primary-dark transition">Mở Giảng viên Panel</a>
        <?php else: ?>
            <div class="mt-4 p-4 bg-yellow-50 text-yellow-700 rounded-xl">Bạn là Học viên: chọn khóa học, gửi khung giờ mong muốn và vào lớp học sau khi admin xếp lớp.</div>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <a href="<?php echo e(route('courses.index')); ?>" class="btn block px-4 py-3 bg-primary text-white rounded-xl shadow hover:bg-primary-dark transition text-center">Khám phá khóa học</a>
                <a href="<?php echo e(route('student.schedule')); ?>" class="btn block px-4 py-3 bg-green-500 text-white rounded-xl shadow hover:bg-green-600 transition text-center">Lớp học của tôi</a>
                <a href="<?php echo e(route('student.grades')); ?>" class="btn block px-4 py-3 bg-blue-500 text-white rounded-xl shadow hover:bg-blue-600 transition text-center">Xem điểm số</a>
            </div>
        <?php endif; ?>

        <div class="mt-6">
            <a href="<?php echo e(route('home')); ?>" class="text-primary hover:underline">← Quay về trang chủ</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\dashboard.blade.php ENDPATH**/ ?>