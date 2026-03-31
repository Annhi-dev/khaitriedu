<?php $__env->startSection('title', 'Chi tiết nhóm học'); ?>
<?php $__env->startSection('content'); ?>
<?php
    $statusClasses = $category->status === \App\Models\Category::STATUS_ACTIVE
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-amber-200 bg-amber-50 text-amber-700';
    $createCourseUrl = $category->defaultSubject
        ? route('admin.courses', ['subject_id' => $category->defaultSubject->id, 'return_to_category_id' => $category->id])
        : route('admin.subjects.create-page', ['category_id' => $category->id, 'return_to_category_id' => $category->id]);
    $courseFlowReady = $category->defaultSubject !== null;
?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Hồ sơ nhóm học</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900"><?php echo e($category->name); ?></h1>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                <span>/<?php echo e($category->slug); ?></span>
                <span class="text-slate-300">|</span>
                <span><?php echo e($category->program ?: 'Chưa cấu hình chương trình'); ?></span>
                <span class="text-slate-300">|</span>
                <span><?php echo e($category->level ?: 'Chưa xác định cấp độ'); ?></span>
                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold <?php echo e($statusClasses); ?>"><?php echo e($category->statusLabel()); ?></span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?php echo e(route('admin.categories')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Danh sách nhóm học</a>
            <a href="<?php echo e($createCourseUrl); ?>" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Tạo khóa học mới</a>
            <a href="<?php echo e(route('admin.categories.edit', $category)); ?>" class="inline-flex items-center justify-center rounded-2xl border border-cyan-200 px-4 py-2.5 text-sm font-medium text-cyan-700 hover:bg-cyan-50">Sửa thông tin</a>
            <?php if($category->status === \App\Models\Category::STATUS_ACTIVE): ?>
                <form method="post" action="<?php echo e(route('admin.categories.deactivate', $category)); ?>" onsubmit="return confirm('Ngừng hoạt động nhóm học này?');">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-600">Ngừng hoạt động</button>
                </form>
            <?php else: ?>
                <form method="post" action="<?php echo e(route('admin.categories.activate', $category)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Kích hoạt lại</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Thông tin tổng quan</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Thứ tự hiển thị <?php echo e($category->order); ?></span>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Tên nhóm học</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($category->name); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Trạng thái</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($category->statusLabel()); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Chương trình</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($category->program ?: 'Chưa cấu hình'); ?></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Cấp độ</p>
                    <p class="mt-1 text-sm font-medium text-slate-900"><?php echo e($category->level ?: 'Chưa cấu hình'); ?></p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Mô tả</p>
                    <p class="mt-1 text-sm leading-6 text-slate-700"><?php echo e($category->description ?: 'Chưa có mô tả cho nhóm học này.'); ?></p>
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thống kê nhanh</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Khóa học thực tế</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($category->courses_count); ?></p>
                        <p class="mt-1 text-xs text-slate-500">Số khóa học đang thuộc nhóm học này.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Luồng tạo khóa học</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($courseFlowReady ? 'Sẵn sàng' : 'Cần khởi tạo'); ?></p>
                        <p class="mt-1 text-xs text-slate-500">
                            <?php echo e($courseFlowReady ? 'Bạn có thể tạo khóa học mới trực tiếp từ nhóm này.' : 'Nhóm này cần tạo khung khóa gốc trước khi thêm khóa học.'); ?>

                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Ảnh đại diện</h2>
                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4">
                    <?php if($category->image_path): ?>
                        <img src="<?php echo e(asset('storage/' . $category->image_path)); ?>" alt="<?php echo e($category->name); ?>" class="h-48 w-full rounded-2xl object-cover" />
                    <?php else: ?>
                        <div class="flex h-48 items-center justify-center rounded-2xl bg-slate-100 text-sm text-slate-500">Chưa có ảnh đại diện</div>
                    <?php endif; ?>
                </div>
            </section>
        </aside>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Danh sách khóa học trong nhóm</h2>
                <p class="text-sm text-slate-500">Mỗi dòng bên dưới là một khóa học thực tế đang nằm trong nhóm học này. Nếu nhóm chưa có khóa nào, hệ thống sẽ hiển thị khung hiện có để bạn tiếp tục tạo mới.</p>
            </div>
            <a href="<?php echo e($createCourseUrl); ?>" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Tạo khóa học mới</a>
        </div>

        <div class="mt-5 grid gap-4">
            <?php if($courses->isNotEmpty()): ?>
                <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900"><?php echo e($course->title); ?></p>
                                <p class="mt-2 text-sm leading-6 text-slate-600"><?php echo e($course->description ?: 'Chưa có mô tả cho khóa học này.'); ?></p>
                                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-500">
                                    <span>Thuộc khung: <?php echo e($course->subject?->name ?? 'Chưa gắn'); ?></span>
                                    <span>Giảng viên: <?php echo e($course->teacher?->name ?? 'Chưa phân công'); ?></span>
                                    <span>Lịch: <?php echo e($course->formattedSchedule()); ?></span>
                                    <span><?php echo e($course->enrollments_count ?? 0); ?> học viên đã xếp</span>
                                </div>
                            </div>
                            <a href="<?php echo e(route('admin.course.show', $course)); ?>" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 px-3 py-2 text-xs font-medium text-cyan-700 hover:bg-cyan-50">Xem khóa học</a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <?php $__empty_1 = true; $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900"><?php echo e($subject->name); ?></p>
                                <p class="mt-2 text-sm leading-6 text-slate-600"><?php echo e($subject->description ?: 'Đây là khung đang được dùng để khởi tạo khóa học cho nhóm này.'); ?></p>
                                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-500">
                                    <span><?php echo e($subject->courses_count); ?> khóa học đang gắn phía dưới</span>
                                    <span><?php echo e($subject->statusLabel()); ?></span>
                                </div>
                            </div>
                            <a href="<?php echo e(route('admin.subject.show', $subject)); ?>" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 px-3 py-2 text-xs font-medium text-cyan-700 hover:bg-cyan-50">Xem khung hiện có</a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Nhóm học này chưa có khóa học nào được liên kết.</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/study_groups/show.blade.php ENDPATH**/ ?>