<?php $__env->startSection('title', 'Chi tiết lớp học'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Lớp học nội bộ</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900"><?php echo e($course->title); ?></h1>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                <span><?php echo e($course->subject?->name ?? 'Chưa gắn khóa học public'); ?></span>
                <span class="text-slate-300">|</span>
                <span><?php echo e($course->subject?->category?->name ?? 'Chưa phân nhóm học'); ?></span>
                <span class="text-slate-300">|</span>
                <span><?php echo e($course->schedule ?: 'Chưa chốt lịch học'); ?></span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?php echo e(route('admin.courses.modules.index', $course)); ?>" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Quản lý module</a>
            <a href="<?php echo e(route('admin.courses')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Danh sách lớp học</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Thông tin lớp học</h2>
            <form method="post" action="<?php echo e(route('admin.courses.update', $course->id)); ?>" class="mt-5 grid gap-4">
                <?php echo csrf_field(); ?>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Tên lớp học</label>
                        <input name="title" value="<?php echo e($course->title); ?>" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" required />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Thuộc khóa học public</label>
                        <select name="subject_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" required>
                            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($subject->id); ?>" <?php if($course->subject_id == $subject->id): echo 'selected'; endif; ?>><?php echo e($subject->name); ?><?php echo e($subject->category ? ' - ' . $subject->category->name : ''); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Giảng viên</label>
                        <select name="teacher_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                            <option value="">Chưa phân công</option>
                            <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($teacher->id); ?>" <?php if($course->teacher_id == $teacher->id): echo 'selected'; endif; ?>><?php echo e($teacher->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Lịch học</label>
                        <select name="schedule" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                            <option value="">Chọn lịch học...</option>
                            <option value="Tối T2-T4-T6, 18:00 - 20:30" <?php if(old('schedule', $course->schedule) === 'Tối T2-T4-T6, 18:00 - 20:30'): echo 'selected'; endif; ?>>Tối T2-T4-T6, 18:00 - 20:30</option>
                            <option value="Tối T3-T5-T7, 18:00 - 20:30" <?php if(old('schedule', $course->schedule) === 'Tối T3-T5-T7, 18:00 - 20:30'): echo 'selected'; endif; ?>>Tối T3-T5-T7, 18:00 - 20:30</option>
                            <option value="Sáng T7-CN, 08:30 - 11:30" <?php if(old('schedule', $course->schedule) === 'Sáng T7-CN, 08:30 - 11:30'): echo 'selected'; endif; ?>>Sáng T7-CN, 08:30 - 11:30</option>
                            <option value="Chiều T7-CN, 14:00 - 17:00" <?php if(old('schedule', $course->schedule) === 'Chiều T7-CN, 14:00 - 17:00'): echo 'selected'; endif; ?>>Chiều T7-CN, 14:00 - 17:00</option>
                            <option value="Linh hoạt (Thỏa thuận)" <?php if(old('schedule', $course->schedule) === 'Linh hoạt (Thỏa thuận)'): echo 'selected'; endif; ?>>Linh hoạt (Thỏa thuận)</option>
                        </select>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Giá khóa học</label>
                        <div class="relative">
                            <input type="number" name="price" value="<?php echo e($course->price ?? 0); ?>" min="0" placeholder="Nhập giá" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 pr-12 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-sm text-slate-500">VNĐ</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Mô tả lớp học</label>
                    <textarea name="description" rows="4" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100"><?php echo e($course->description); ?></textarea>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">Lưu thay đổi lớp học</button>
                </div>
            </form>
        </section>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thống kê nhanh</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Học viên đã xếp</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($course->enrollments_count ?? 0); ?></p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Số module</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($course->modules->count()); ?></p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Ghi chú</h2>
                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-600">
                    <p>Module đang được quản lý riêng để phase 7 có thể thêm, sửa, sắp xếp thứ tự và ẩn/hiện nội dung mà không làm rối phần thông tin lớp học.</p>
                </div>
            </section>
        </aside>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Danh sách module hiện có</h2>
                <p class="text-sm text-slate-500">Từ đây admin nhìn thấy nhanh thứ tự và trạng thái các module, còn thao tác đầy đủ nằm ở màn quản lý module.</p>
            </div>
            <a href="<?php echo e(route('admin.courses.modules.index', $course)); ?>" class="inline-flex items-center justify-center rounded-2xl border border-cyan-200 px-4 py-2.5 text-sm font-medium text-cyan-700 hover:bg-cyan-50">Mở quản lý module</a>
        </div>

        <div class="mt-5 grid gap-4">
            <?php $__empty_1 = true; $__currentLoopData = $course->modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $statusClasses = $module->status === \App\Models\Module::STATUS_PUBLISHED
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                        : 'border-amber-200 bg-amber-50 text-amber-700';
                ?>
                <div class="rounded-2xl border border-slate-200 px-4 py-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="text-sm font-semibold text-slate-900"><?php echo e($module->position); ?>. <?php echo e($module->title); ?></p>
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold <?php echo e($statusClasses); ?>"><?php echo e($module->statusLabel()); ?></span>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-slate-600"><?php echo e($module->content ?: 'Chưa có mô tả cho module này.'); ?></p>
                            <div class="mt-3 text-xs text-slate-500"><?php echo e($module->durationLabel()); ?></div>
                        </div>
                        <a href="<?php echo e(route('admin.courses.modules.edit', [$course, $module])); ?>" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 px-3 py-2 text-xs font-medium text-cyan-700 hover:bg-cyan-50">Sửa module</a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Lớp học này chưa có module nào.</div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/course/show.blade.php ENDPATH**/ ?>