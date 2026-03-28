<?php $__env->startSection('title', 'Quản lý ứng tuyển giảng viên'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 4</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Quản lý ứng tuyển giảng viên</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Theo dõi hồ sơ ứng tuyển, tìm kiếm theo ứng viên hoặc chuyên môn, và xử lý đúng flow duyệt, từ chối hoặc yêu cầu bổ sung.</p>
        </div>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Quay lại dashboard</a>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="<?php echo e(route('admin.teacher-applications')); ?>" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_240px_auto]">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Tìm kiếm</label>
                <input name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Tên, email, kinh nghiệm hoặc mô tả" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Lọc trạng thái</label>
                <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">Tất cả trạng thái</option>
                    <option value="<?php echo e(\App\Models\TeacherApplication::STATUS_PENDING); ?>" <?php if(($filters['status'] ?? '') === \App\Models\TeacherApplication::STATUS_PENDING): echo 'selected'; endif; ?>>Pending</option>
                    <option value="<?php echo e(\App\Models\TeacherApplication::STATUS_APPROVED); ?>" <?php if(($filters['status'] ?? '') === \App\Models\TeacherApplication::STATUS_APPROVED): echo 'selected'; endif; ?>>Approved</option>
                    <option value="<?php echo e(\App\Models\TeacherApplication::STATUS_REJECTED); ?>" <?php if(($filters['status'] ?? '') === \App\Models\TeacherApplication::STATUS_REJECTED): echo 'selected'; endif; ?>>Rejected</option>
                    <option value="<?php echo e(\App\Models\TeacherApplication::STATUS_NEEDS_REVISION); ?>" <?php if(($filters['status'] ?? '') === \App\Models\TeacherApplication::STATUS_NEEDS_REVISION): echo 'selected'; endif; ?>>Needs revision</option>
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">Áp dụng</button>
                <a href="<?php echo e(route('admin.teacher-applications')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Xóa lọc</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Danh sách hồ sơ</h2>
                <p class="text-sm text-slate-500">Tổng cộng <?php echo e($applications->total()); ?> hồ sơ phù hợp với bộ lọc hiện tại.</p>
            </div>
        </div>

        <?php if($applications->count()): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Ứng viên</th>
                            <th class="px-5 py-4">Liên hệ</th>
                            <th class="px-5 py-4">Trạng thái</th>
                            <th class="px-5 py-4">Phản hồi admin</th>
                            <th class="px-5 py-4">Ngày nộp</th>
                            <th class="px-5 py-4 text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $app): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $statusClasses = match ($app->status) {
                                    \App\Models\TeacherApplication::STATUS_APPROVED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    \App\Models\TeacherApplication::STATUS_REJECTED => 'border-rose-200 bg-rose-50 text-rose-700',
                                    \App\Models\TeacherApplication::STATUS_NEEDS_REVISION => 'border-amber-200 bg-amber-50 text-amber-700',
                                    default => 'border-slate-200 bg-slate-100 text-slate-700',
                                };
                                $reviewSummary = $app->status === \App\Models\TeacherApplication::STATUS_REJECTED
                                    ? ($app->rejection_reason ?: 'Đã từ chối')
                                    : ($app->admin_note ?: 'Chưa có ghi chú');
                            ?>
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900"><?php echo e($app->name); ?></div>
                                    <div class="mt-1 text-xs text-slate-500"><?php echo e(\Illuminate\Support\Str::limit($app->experience ?: $app->message, 70)); ?></div>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <div><?php echo e($app->email); ?></div>
                                    <div class="mt-1 text-xs text-slate-500"><?php echo e($app->phone ?: 'Chưa có số điện thoại'); ?></div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold <?php echo e($statusClasses); ?>"><?php echo e($app->statusLabel()); ?></span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <div><?php echo e(\Illuminate\Support\Str::limit($reviewSummary, 80)); ?></div>
                                    <div class="mt-1 text-xs text-slate-500"><?php echo e($app->reviewer?->name ?: 'Chưa duyệt'); ?></div>
                                </td>
                                <td class="px-5 py-4 text-slate-600"><?php echo e(optional($app->created_at)->format('d/m/Y H:i')); ?></td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?php echo e(route('admin.teacher-applications.show', $app)); ?>" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 px-3 py-2 text-xs font-medium text-cyan-700 hover:bg-cyan-50">Chi tiết</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-5 py-4">
                <?php echo e($applications->links()); ?>

            </div>
        <?php else: ?>
            <div class="px-6 py-14 text-center">
                <h3 class="text-lg font-semibold text-slate-900">Chưa có hồ sơ phù hợp</h3>
                <p class="mt-2 text-sm text-slate-500">Hãy đổi bộ lọc hoặc đợi ứng viên gửi hồ sơ mới từ form public.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/teacher_applications/index.blade.php ENDPATH**/ ?>