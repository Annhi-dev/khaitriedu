<?php $__env->startSection('title', 'Admin Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div class="grid gap-6 lg:grid-cols-4">
    <div class="lg:col-span-1">
        <div class="rounded-2xl bg-white p-6 shadow-md">
            <h5 class="mb-4 text-lg font-semibold text-primary-dark">Admin Panel</h5>
            <ul class="space-y-2 text-sm">
                <li><a href="<?php echo e(route('admin.categories')); ?>" class="block rounded-lg px-3 py-2 hover:bg-primary-light/30 transition">Quản lý nhóm ngành</a></li>
                <li><a href="<?php echo e(route('admin.subjects')); ?>" class="block rounded-lg px-3 py-2 hover:bg-primary-light/30 transition">Quản lý khóa học</a></li>
                <li><a href="<?php echo e(route('admin.courses')); ?>" class="block rounded-lg px-3 py-2 hover:bg-primary-light/30 transition">Quản lý lớp học</a></li>
                <li>
                  <a href="<?php echo e(route('admin.enrollments')); ?>" class="flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-primary-light/30 transition">
                    Duyệt đăng ký
                    <?php if($newEnrollments > 0): ?>
                      <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"><?php echo e($newEnrollments); ?></span>
                    <?php endif; ?>
                  </a>
                </li>
                <li><a href="<?php echo e(route('admin.teacher-applications')); ?>" class="block rounded-lg px-3 py-2 hover:bg-primary-light/30 transition">Ứng tuyển giảng viên</a></li>
                <li><a href="<?php echo e(route('admin.users')); ?>" class="block rounded-lg px-3 py-2 hover:bg-primary-light/30 transition">Người dùng</a></li>
            </ul>
        </div>
    </div>

    <div class="lg:col-span-3">
        <div class="rounded-2xl bg-white p-6 shadow-md">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-2xl font-bold text-primary-dark">Admin Dashboard</h3>
                    <p class="text-gray-600">Xin chào, <span class="font-medium"><?php echo e($user->name); ?></span> (Admin).</p>
                </div>
                <a href="<?php echo e(route('logout')); ?>" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-100 transition">Đăng xuất</a>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-primary-light/30 p-4">
                    <h6 class="text-sm font-medium text-primary-dark">Khóa học</h6>
                    <p class="text-2xl font-bold text-primary-dark"><?php echo e($subjectCount ?? 0); ?></p>
                </div>
                <div class="rounded-xl bg-primary-light/30 p-4">
                    <h6 class="text-sm font-medium text-primary-dark">Lớp học</h6>
                    <p class="text-2xl font-bold text-primary-dark"><?php echo e($courseCount ?? 0); ?></p>
                </div>
                <div class="rounded-xl bg-primary-light/30 p-4">
                    <h6 class="text-sm font-medium text-primary-dark">Học viên</h6>
                    <p class="text-2xl font-bold text-primary-dark"><?php echo e($studentCount ?? 0); ?></p>
                </div>
                <div class="rounded-xl bg-primary-light/30 p-4">
                    <h6 class="text-sm font-medium text-primary-dark">Giảng viên</h6>
                    <p class="text-2xl font-bold text-primary-dark"><?php echo e($teacherCount ?? 0); ?></p>
                </div>
                <div class="rounded-xl bg-yellow-100 p-4">
                    <h6 class="text-sm font-medium text-yellow-700">Ứng tuyển mới</h6>
                    <p class="text-2xl font-bold text-yellow-700"><?php echo e($pendingTeacherApplications ?? 0); ?></p>
                </div>
            </div>

            <div class="mt-6">
                <h5 class="mb-3 text-lg font-semibold text-primary-dark">Thao tác nhanh</h5>
                <div class="flex flex-wrap gap-3">
                    <a href="<?php echo e(route('admin.subjects')); ?>" class="rounded-xl bg-primary px-5 py-2 text-sm font-semibold text-white shadow hover:bg-primary-dark transition">Thêm khóa học</a>
                    <a href="<?php echo e(route('admin.courses')); ?>" class="rounded-xl border-2 border-primary px-5 py-2 text-sm font-semibold text-primary hover:bg-primary-light/20 transition">Quản lý lớp học</a>
                    <a href="<?php echo e(route('admin.enrollments')); ?>" class="flex items-center gap-2 rounded-xl border-2 border-primary px-5 py-2 text-sm font-semibold text-primary hover:bg-primary-light/20 transition">
                        Duyệt đăng ký
                        <?php if($newEnrollments > 0): ?>
                          <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"><?php echo e($newEnrollments); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo e(route('admin.teacher-applications')); ?>" class="rounded-xl bg-primary px-5 py-2 text-sm font-semibold text-white shadow hover:bg-primary-dark transition">Ứng tuyển giảng viên</a>
                    <a href="<?php echo e(route('admin.report')); ?>" class="rounded-xl border border-gray-300 px-5 py-2 text-sm font-semibold hover:bg-gray-100 transition">Báo cáo</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/dashboard_admin.blade.php ENDPATH**/ ?>