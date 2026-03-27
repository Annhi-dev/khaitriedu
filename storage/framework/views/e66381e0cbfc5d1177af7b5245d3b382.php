<?php $__env->startSection('title', 'Chi tiết học viên'); ?>
<?php $__env->startSection('content'); ?>
<?php
    $statusClasses = match ($student->status) {
        \App\Models\User::STATUS_ACTIVE => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        \App\Models\User::STATUS_INACTIVE => 'border-amber-200 bg-amber-50 text-amber-700',
        \App\Models\User::STATUS_LOCKED => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-100 text-slate-700',
    };
?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Hồ sơ học viên</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900"><?php echo e($student->name); ?></h1>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                <span><?php echo e($student->email); ?></span>
                <span class="text-slate-300">|</span>
                <span><?php echo e($student->phone ?: 'Chưa có số điện thoại'); ?></span>
                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold <?php echo e($statusClasses); ?>"><?php echo e($student->statusLabel()); ?></span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?php echo e(route('admin.students.index')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Danh sách học viên</a>
            <a href="<?php echo e(route('admin.students.edit', $student)); ?>" class="inline-flex items-center justify-center rounded-2xl border border-cyan-200 px-4 py-2.5 text-sm font-medium text-cyan-700 hover:bg-cyan-50">Sửa thông tin</a>
            <?php if($student->isLocked()): ?>
                <form method="post" action="<?php echo e(route('admin.students.unlock', $student)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Mở khóa tài khoản</button>
                </form>
            <?php else: ?>
                <form method="post" action="<?php echo e(route('admin.students.lock', $student)); ?>" onsubmit="return confirm('Khóa tài khoản học viên này?');">
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
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?php echo e($student->enrollments_count); ?> đăng ký học</span>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Họ và tên</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($student->name); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Tên đăng nhập</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($student->username); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Email</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($student->email); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Số điện thoại</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($student->phone ?: 'Chưa cập nhật'); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Trạng thái</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($student->statusLabel()); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Ngày tạo</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e(optional($student->created_at)->format('d/m/Y H:i') ?: 'Không có dữ liệu'); ?></p>
                </div>
            </div>
        </section>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Thanh toán</h2>
            <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-600">
                <p class="font-medium text-slate-800">Chưa có dữ liệu thanh toán</p>
                <p class="mt-2 leading-6">Phase 2 chưa bổ sung module thanh toán. Khi phân hệ payment sẵn sàng, trạng thái thanh toán của học viên sẽ hiển thị tại đây.</p>
            </div>
        </aside>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">Đăng ký học gần đây</h2>
            <span class="text-sm text-slate-500">Hiển thị luồng học viên gửi yêu cầu và chờ admin xử lý.</span>
        </div>
        <div class="mt-5 grid gap-4">
            <?php $__empty_1 = true; $__currentLoopData = $enrollments->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $subjectName = $enrollment->course->subject->name ?? $enrollment->subject->name ?? 'Chưa xác định';
                    $courseTitle = $enrollment->course->title ?? 'Chưa xếp lớp';
                    $normalizedStatus = $enrollment->normalizedStatus();
                    $enrollmentStatusClasses = match ($normalizedStatus) {
                        \App\Models\Enrollment::STATUS_SCHEDULED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        \App\Models\Enrollment::STATUS_ACTIVE => 'border-violet-200 bg-violet-50 text-violet-700',
                        \App\Models\Enrollment::STATUS_COMPLETED => 'border-slate-300 bg-slate-100 text-slate-700',
                        \App\Models\Enrollment::STATUS_REJECTED => 'border-rose-200 bg-rose-50 text-rose-700',
                        default => 'border-amber-200 bg-amber-50 text-amber-700',
                    };
                ?>
                <div class="rounded-2xl border border-slate-200 px-4 py-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900"><?php echo e($subjectName); ?></p>
                            <p class="mt-1 text-sm text-slate-500">Lớp: <?php echo e($courseTitle); ?></p>
                            <div class="mt-3 grid gap-2 text-sm text-slate-600 md:grid-cols-2">
                                <p>Lịch mong muốn: <?php echo e($enrollment->preferred_schedule ?: 'Chưa cung cấp'); ?></p>
                                <p>Lịch đã chốt: <?php echo e($enrollment->schedule ?: 'Chưa có lịch chính thức'); ?></p>
                                <p>Giảng viên: <?php echo e($enrollment->assignedTeacher->name ?? 'Chưa phân công'); ?></p>
                                <p>Ghi chú: <?php echo e($enrollment->note ?: 'Không có'); ?></p>
                            </div>
                        </div>
                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold <?php echo e($enrollmentStatusClasses); ?>"><?php echo e($enrollment->status); ?></span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Học viên này chưa có đăng ký học nào.</div>
            <?php endif; ?>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">Lịch học hiện tại</h2>
            <span class="text-sm text-slate-500">Chỉ lấy từ các đăng ký đã được xếp lớp và có lịch học hiện hành.</span>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <?php $__empty_1 = true; $__currentLoopData = $currentSchedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="rounded-2xl border border-slate-200 px-4 py-4">
                    <p class="text-sm font-semibold text-slate-900"><?php echo e($enrollment->course->title); ?></p>
                    <div class="mt-3 space-y-2 text-sm text-slate-600">
                        <p>Khóa học: <?php echo e($enrollment->course->subject->name ?? 'Chưa xác định'); ?></p>
                        <p>Lịch học: <?php echo e($enrollment->schedule ?: 'Chưa có lịch cụ thể'); ?></p>
                        <p>Giảng viên: <?php echo e($enrollment->assignedTeacher->name ?? 'Chưa phân công'); ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500 md:col-span-2">Học viên chưa có lớp học chính thức ở thời điểm hiện tại.</div>
            <?php endif; ?>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Hoạt động học tập cơ bản</h2>
                <span class="text-sm text-slate-500">Điểm số gần nhất</span>
            </div>
            <div class="mt-5 grid gap-4">
                <?php $__empty_1 = true; $__currentLoopData = $grades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <p class="text-sm font-semibold text-slate-900"><?php echo e($grade->enrollment->course->title ?? 'Lớp học'); ?></p>
                        <div class="mt-3 grid gap-2 text-sm text-slate-600 md:grid-cols-2">
                            <p>Module: <?php echo e($grade->module->title ?? 'Tổng kết'); ?></p>
                            <p>Điểm số: <?php echo e($grade->score ?? 'Chưa có'); ?></p>
                            <p>Điểm chữ: <?php echo e($grade->grade ?? 'Chưa có'); ?></p>
                            <p>Cập nhật: <?php echo e(optional($grade->updated_at)->format('d/m/Y') ?: 'Không có dữ liệu'); ?></p>
                        </div>
                        <p class="mt-3 text-sm text-slate-600">Phản hồi: <?php echo e($grade->feedback ?: 'Không có'); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Chưa có dữ liệu điểm số hoặc tiến độ học tập cho học viên này.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Phản hồi khóa học</h2>
                <span class="text-sm text-slate-500">Đánh giá gần nhất của học viên</span>
            </div>
            <div class="mt-5 grid gap-4">
                <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <p class="text-sm font-semibold text-slate-900"><?php echo e($review->course->subject->name ?? $review->course->title ?? 'Khóa học'); ?></p>
                        <div class="mt-3 grid gap-2 text-sm text-slate-600 md:grid-cols-2">
                            <p>Lớp học: <?php echo e($review->course->title ?? 'Không có dữ liệu'); ?></p>
                            <p>Đánh giá: <?php echo e($review->rating); ?>/5</p>
                        </div>
                        <p class="mt-3 text-sm text-slate-600">Nhận xét: <?php echo e($review->comment ?: 'Không có bình luận'); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Học viên chưa có đánh giá khóa học nào.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/students/show.blade.php ENDPATH**/ ?>