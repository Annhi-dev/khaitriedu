<?php $__env->startSection('title', 'Chi tiết giảng viên'); ?>
<?php $__env->startSection('content'); ?>
<?php
    $statusClasses = match ($teacher->status) {
        \App\Models\User::STATUS_ACTIVE => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        \App\Models\User::STATUS_INACTIVE => 'border-amber-200 bg-amber-50 text-amber-700',
        \App\Models\User::STATUS_LOCKED => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-100 text-slate-700',
    };
?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Hồ sơ giảng viên</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900"><?php echo e($teacher->name); ?></h1>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                <span><?php echo e($teacher->email); ?></span>
                <span class="text-slate-300">|</span>
                <span><?php echo e($teacher->phone ?: 'Chưa có số điện thoại'); ?></span>
                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold <?php echo e($statusClasses); ?>"><?php echo e($teacher->statusLabel()); ?></span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?php echo e(route('admin.teachers.index')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Danh sách giảng viên</a>
            <a href="<?php echo e(route('admin.teachers.edit', $teacher)); ?>" class="inline-flex items-center justify-center rounded-2xl border border-cyan-200 px-4 py-2.5 text-sm font-medium text-cyan-700 hover:bg-cyan-50">Sửa thông tin</a>
            <?php if($teacher->isLocked()): ?>
                <form method="post" action="<?php echo e(route('admin.teachers.unlock', $teacher)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Mở khóa tài khoản</button>
                </form>
            <?php else: ?>
                <form method="post" action="<?php echo e(route('admin.teachers.lock', $teacher)); ?>" onsubmit="return confirm('Khóa tài khoản giảng viên này?');">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Khóa tài khoản</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Thông tin cá nhân</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?php echo e($teacher->taught_courses_count); ?> lớp phụ trách</span>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Họ và tên</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($teacher->name); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Tên đăng nhập</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($teacher->username); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Email</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($teacher->email); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Số điện thoại</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($teacher->phone ?: 'Chưa cập nhật'); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Trạng thái</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($teacher->statusLabel()); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Ngày tạo</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e(optional($teacher->created_at)->format('d/m/Y H:i') ?: 'Không có dữ liệu'); ?></p>
                </div>
            </div>
        </section>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Thống kê nhanh</h2>
            <div class="mt-4 grid gap-3">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Lớp đang phụ trách</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($courses->count()); ?></p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Học viên phụ trách</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($studentCount); ?></p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Yêu cầu đổi lịch</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($teacher->schedule_change_requests_count); ?></p>
                </div>
            </div>
        </aside>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">Kinh nghiệm và hồ sơ năng lực</h2>
            <span class="text-sm text-slate-500">Lấy từ hồ sơ ứng tuyển nếu có</span>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 px-4 py-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">Kinh nghiệm</p>
                <p class="mt-2 text-sm leading-6 text-slate-700"><?php echo e($application?->experience ?: 'Chưa có dữ liệu kinh nghiệm trong hệ thống.'); ?></p>
            </div>
            <div class="rounded-2xl border border-slate-200 px-4 py-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">Mô tả / chuyên môn</p>
                <p class="mt-2 text-sm leading-6 text-slate-700"><?php echo e($application?->message ?: 'Chưa có mô tả chuyên môn từ hồ sơ ứng tuyển.'); ?></p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">Lớp và khóa học đang phụ trách</h2>
            <span class="text-sm text-slate-500">Hiển thị từ các lớp đã gán teacher_id</span>
        </div>
        <div class="mt-5 grid gap-4">
            <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="rounded-2xl border border-slate-200 px-4 py-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900"><?php echo e($course->title); ?></p>
                            <div class="mt-3 grid gap-2 text-sm text-slate-600 md:grid-cols-2">
                                <p>Khóa học: <?php echo e($course->subject->name ?? 'Chưa xác định'); ?></p>
                                <p>Nhóm học: <?php echo e($course->subject->category->name ?? 'Chưa xác định'); ?></p>
                                <p>Lịch dạy: <?php echo e($course->schedule ?: 'Chưa có lịch cụ thể'); ?></p>
                                <p>Số học viên: <?php echo e($course->enrollments_count); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Giảng viên chưa được phân công lớp học nào.</div>
            <?php endif; ?>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Học viên đang phụ trách</h2>
                <span class="text-sm text-slate-500">Dựa trên các enrollment thuộc lớp của giảng viên</span>
            </div>
            <div class="mt-5 grid gap-4">
                <?php $__empty_1 = true; $__currentLoopData = $enrollments->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <p class="text-sm font-semibold text-slate-900"><?php echo e($enrollment->user->name ?? 'Học viên'); ?></p>
                        <div class="mt-3 grid gap-2 text-sm text-slate-600 md:grid-cols-2">
                            <p>Lớp: <?php echo e($enrollment->course->title ?? 'Chưa xếp lớp'); ?></p>
                            <p>Khóa học: <?php echo e($enrollment->course->subject->name ?? 'Chưa xác định'); ?></p>
                            <p>Trạng thái enrollment: <?php echo e($enrollment->status); ?></p>
                            <p>Lịch chính thức: <?php echo e($enrollment->schedule ?: 'Chưa có lịch cụ thể'); ?></p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Chưa có học viên nào gắn với giảng viên này.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Yêu cầu đổi lịch gần đây</h2>
                <span class="text-sm text-slate-500">Nếu giảng viên đã gửi request trong hệ thống</span>
            </div>
            <div class="mt-5 grid gap-4">
                <?php $__empty_1 = true; $__currentLoopData = $scheduleChangeRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <p class="text-sm font-semibold text-slate-900"><?php echo e($request->course->title ?? 'Lớp học'); ?></p>
                        <div class="mt-3 grid gap-2 text-sm text-slate-600 md:grid-cols-2">
                            <p>Ngày đề xuất: <?php echo e(optional($request->requested_date)->format('d/m/Y') ?: 'Chưa chọn'); ?></p>
                            <p>Khung giờ mới: <?php echo e(trim(($request->requested_start_time ?: '--') . ' - ' . ($request->requested_end_time ?: '--'))); ?></p>
                            <p>Trạng thái: <?php echo e($request->status); ?></p>
                            <p>Người duyệt: <?php echo e($request->reviewer->name ?? 'Chưa duyệt'); ?></p>
                        </div>
                        <p class="mt-3 text-sm text-slate-600">Lý do: <?php echo e($request->reason ?: 'Không có'); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Giảng viên chưa gửi yêu cầu đổi lịch nào.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/teachers/show.blade.php ENDPATH**/ ?>