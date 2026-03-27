<?php $__env->startSection('title', 'Quản lý giảng viên'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 3</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Quản lý giảng viên</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Admin quản lý tài khoản giảng viên, theo dõi số lớp phụ trách và tình trạng đổi lịch trước khi đi sâu sang các phase lịch học và phân công.</p>
        </div>
        <a href="<?php echo e(route('admin.teachers.create')); ?>" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Thêm giảng viên mới</a>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="<?php echo e(route('admin.teachers.index')); ?>" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_240px_auto]">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Tìm kiếm</label>
                <input name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Tên, email hoặc số điện thoại" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Lọc trạng thái</label>
                <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">Tất cả trạng thái</option>
                    <option value="<?php echo e(\App\Models\User::STATUS_ACTIVE); ?>" <?php if(($filters['status'] ?? '') === \App\Models\User::STATUS_ACTIVE): echo 'selected'; endif; ?>>Hoạt động</option>
                    <option value="<?php echo e(\App\Models\User::STATUS_INACTIVE); ?>" <?php if(($filters['status'] ?? '') === \App\Models\User::STATUS_INACTIVE): echo 'selected'; endif; ?>>Tạm dừng</option>
                    <option value="<?php echo e(\App\Models\User::STATUS_LOCKED); ?>" <?php if(($filters['status'] ?? '') === \App\Models\User::STATUS_LOCKED): echo 'selected'; endif; ?>>Đã khóa</option>
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">Áp dụng</button>
                <a href="<?php echo e(route('admin.teachers.index')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Xóa lọc</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Danh sách giảng viên</h2>
                <p class="text-sm text-slate-500">Tổng cộng <?php echo e($teachers->total()); ?> giảng viên phù hợp với bộ lọc hiện tại.</p>
            </div>
        </div>

        <?php if($teachers->count()): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Giảng viên</th>
                            <th class="px-5 py-4">Liên hệ</th>
                            <th class="px-5 py-4">Trạng thái</th>
                            <th class="px-5 py-4">Lớp phụ trách</th>
                            <th class="px-5 py-4">Yêu cầu đổi lịch</th>
                            <th class="px-5 py-4 text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $statusClasses = match ($teacher->status) {
                                    \App\Models\User::STATUS_ACTIVE => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    \App\Models\User::STATUS_INACTIVE => 'border-amber-200 bg-amber-50 text-amber-700',
                                    \App\Models\User::STATUS_LOCKED => 'border-rose-200 bg-rose-50 text-rose-700',
                                    default => 'border-slate-200 bg-slate-100 text-slate-700',
                                };
                            ?>
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900"><?php echo e($teacher->name); ?></div>
                                    <div class="mt-1 text-xs uppercase tracking-wide text-slate-500"><?php echo e($teacher->username); ?></div>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <div><?php echo e($teacher->email); ?></div>
                                    <div class="mt-1 text-xs text-slate-500"><?php echo e($teacher->phone ?: 'Chưa cập nhật số điện thoại'); ?></div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold <?php echo e($statusClasses); ?>"><?php echo e($teacher->statusLabel()); ?></span>
                                </td>
                                <td class="px-5 py-4 text-slate-600"><?php echo e($teacher->taught_courses_count); ?></td>
                                <td class="px-5 py-4 text-slate-600"><?php echo e($teacher->schedule_change_requests_count); ?></td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="<?php echo e(route('admin.teachers.show', $teacher)); ?>" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">Xem</a>
                                        <a href="<?php echo e(route('admin.teachers.edit', $teacher)); ?>" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 px-3 py-2 text-xs font-medium text-cyan-700 hover:bg-cyan-50">Sửa</a>
                                        <?php if($teacher->isLocked()): ?>
                                            <form method="post" action="<?php echo e(route('admin.teachers.unlock', $teacher)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Mở khóa</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="<?php echo e(route('admin.teachers.lock', $teacher)); ?>" onsubmit="return confirm('Khóa tài khoản giảng viên này?');">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">Khóa</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-5 py-4">
                <?php echo e($teachers->links()); ?>

            </div>
        <?php else: ?>
            <div class="px-6 py-14 text-center">
                <h3 class="text-lg font-semibold text-slate-900">Chưa có giảng viên phù hợp</h3>
                <p class="mt-2 text-sm text-slate-500">Hãy đổi bộ lọc hoặc tạo giảng viên mới để bắt đầu quản lý.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/teachers/index.blade.php ENDPATH**/ ?>