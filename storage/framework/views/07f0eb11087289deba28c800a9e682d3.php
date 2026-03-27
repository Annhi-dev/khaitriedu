<?php $__env->startSection('title', 'Quản lý khóa học'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 6</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Quản lý khóa học</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Quản lý các khóa học public nằm trong nhóm học, kiểm soát học phí, thời lượng, trạng thái mở đăng ký và theo dõi số lớp nội bộ, số học viên quan tâm.</p>
        </div>
        <a href="<?php echo e(route('admin.subjects.create-page')); ?>" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Tạo khóa học mới</a>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="<?php echo e(route('admin.subjects')); ?>" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_260px_220px_auto]">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Tìm kiếm</label>
                <input name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Tên khóa học hoặc mô tả" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Nhóm học</label>
                <select name="category_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">Tất cả nhóm học</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php if((string) ($filters['category_id'] ?? '') === (string) $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Trạng thái</label>
                <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">Tất cả trạng thái</option>
                    <option value="<?php echo e(\App\Models\Subject::STATUS_DRAFT); ?>" <?php if(($filters['status'] ?? '') === \App\Models\Subject::STATUS_DRAFT): echo 'selected'; endif; ?>>Nháp</option>
                    <option value="<?php echo e(\App\Models\Subject::STATUS_OPEN); ?>" <?php if(($filters['status'] ?? '') === \App\Models\Subject::STATUS_OPEN): echo 'selected'; endif; ?>>Đang mở</option>
                    <option value="<?php echo e(\App\Models\Subject::STATUS_CLOSED); ?>" <?php if(($filters['status'] ?? '') === \App\Models\Subject::STATUS_CLOSED): echo 'selected'; endif; ?>>Đóng đăng ký</option>
                    <option value="<?php echo e(\App\Models\Subject::STATUS_ARCHIVED); ?>" <?php if(($filters['status'] ?? '') === \App\Models\Subject::STATUS_ARCHIVED): echo 'selected'; endif; ?>>Lưu trữ</option>
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">Áp dụng</button>
                <a href="<?php echo e(route('admin.subjects')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Xóa lọc</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Danh sách khóa học</h2>
                <p class="text-sm text-slate-500">Tổng cộng <?php echo e($subjects->total()); ?> khóa học phù hợp với bộ lọc hiện tại.</p>
            </div>
        </div>

        <?php if($subjects->count()): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Khóa học</th>
                            <th class="px-5 py-4">Nhóm học</th>
                            <th class="px-5 py-4">Trạng thái</th>
                            <th class="px-5 py-4">Quy mô</th>
                            <th class="px-5 py-4 text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $statusClasses = match ($subject->status) {
                                    \App\Models\Subject::STATUS_OPEN => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    \App\Models\Subject::STATUS_DRAFT => 'border-slate-200 bg-slate-100 text-slate-700',
                                    \App\Models\Subject::STATUS_CLOSED => 'border-amber-200 bg-amber-50 text-amber-700',
                                    \App\Models\Subject::STATUS_ARCHIVED => 'border-rose-200 bg-rose-50 text-rose-700',
                                    default => 'border-slate-200 bg-slate-100 text-slate-700',
                                };
                            ?>
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900"><?php echo e($subject->name); ?></div>
                                    <div class="mt-2 grid gap-1 text-sm text-slate-600">
                                        <p>Học phí: <?php echo e(number_format((float) $subject->price, 0, ',', '.')); ?> đ</p>
                                        <p>Thời lượng: <?php echo e($subject->durationLabel()); ?></p>
                                    </div>
                                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600"><?php echo e($subject->description ?: 'Chưa có mô tả cho khóa học này.'); ?></p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <div><?php echo e($subject->category?->name ?? 'Chưa gắn nhóm học'); ?></div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold <?php echo e($statusClasses); ?>"><?php echo e($subject->statusLabel()); ?></span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <div><?php echo e($subject->courses_count); ?> lớp học nội bộ</div>
                                    <div class="mt-1 text-xs text-slate-500"><?php echo e($subject->modules_count); ?> module hiện có</div>
                                    <div class="mt-1 text-xs text-slate-500"><?php echo e($subject->enrollments_count); ?> lượt đăng ký</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="<?php echo e(route('admin.subject.show', $subject)); ?>" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">Xem</a>
                                        <a href="<?php echo e(route('admin.subjects.edit', $subject)); ?>" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 px-3 py-2 text-xs font-medium text-cyan-700 hover:bg-cyan-50">Sửa</a>
                                        <?php if($subject->status === \App\Models\Subject::STATUS_ARCHIVED): ?>
                                            <form method="post" action="<?php echo e(route('admin.subjects.reopen', $subject)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Mở lại</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="<?php echo e(route('admin.subjects.archive', $subject)); ?>" onsubmit="return confirm('Chuyển khóa học này sang trạng thái lưu trữ?');">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">Lưu trữ</button>
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
                <?php echo e($subjects->links()); ?>

            </div>
        <?php else: ?>
            <div class="px-6 py-14 text-center">
                <h3 class="text-lg font-semibold text-slate-900">Chưa có khóa học phù hợp</h3>
                <p class="mt-2 text-sm text-slate-500">Hãy đổi bộ lọc hoặc tạo khóa học mới để bắt đầu.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/subject/index.blade.php ENDPATH**/ ?>