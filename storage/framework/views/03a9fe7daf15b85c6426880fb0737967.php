<?php $__env->startSection('title', 'Lịch học toàn hệ thống'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <span class="sr-only">Phase 9</span>

    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => 'Lịch học toàn hệ thống','subtitle' => 'Theo dõi các lớp đã xếp lịch và phân bổ giảng viên trong toàn trung tâm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Lịch học toàn hệ thống','subtitle' => 'Theo dõi các lớp đã xếp lịch và phân bổ giảng viên trong toàn trung tâm']); ?>
        <a href="<?php echo e(route('admin.schedules.queue')); ?>" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Hàng chờ xếp lịch</a>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

    <form method="get" action="<?php echo e(route('admin.schedules.index')); ?>" class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Giảng viên</label>
                <select name="teacher_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">Tất cả</option>
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($teacher->id); ?>" <?php if(request('teacher_id') == $teacher->id): echo 'selected'; endif; ?>><?php echo e($teacher->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Học viên</label>
                <select name="student_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">Tất cả</option>
                    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($student->id); ?>" <?php if(request('student_id') == $student->id): echo 'selected'; endif; ?>><?php echo e($student->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Lớp học</label>
                <select name="course_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">Tất cả</option>
                    <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $courseOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($courseOption->id); ?>" <?php if(request('course_id') == $courseOption->id): echo 'selected'; endif; ?>><?php echo e($courseOption->title); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Ngày học</label>
                <input type="date" name="date" value="<?php echo e(request('date')); ?>" class="w-full rounded-xl border px-3 py-2">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-xl">Lọc</button>
                <a href="<?php echo e(route('admin.schedules.index')); ?>" class="border px-4 py-2 rounded-xl">Xóa</a>
            </div>
        </div>
    </form>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex justify-between gap-4">
                    <div>
                        <p class="text-xs text-slate-500"><?php echo e($course->subject?->category?->name ?? 'Chưa phân nhóm'); ?></p>
                        <h3 class="font-semibold text-lg"><?php echo e($course->title); ?></h3>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'info','text' => $course->statusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('info'),'text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($course->statusLabel())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $attributes = $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $component = $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                    <p><span class="text-slate-500">Giảng viên:</span> <?php echo e($course->teacher?->name ?? 'Chưa phân công'); ?></p>
                    <p><span class="text-slate-500">Lịch:</span> <?php echo e($course->formattedSchedule()); ?></p>
                    <p><span class="text-slate-500">Sĩ số:</span> <?php echo e($course->scheduled_students_count); ?>/<?php echo e($course->capacity ?? 20); ?></p>
                    <p><span class="text-slate-500">Ngày học:</span> <?php echo e($course->dayLabel()); ?></p>
                </div>
                <div class="mt-4 p-3 bg-slate-50 rounded-xl text-sm">
                    <p class="font-medium">Học viên:</p>
                    <div class="flex flex-wrap gap-1 mt-1">
                        <?php $__empty_2 = true; $__currentLoopData = $course->enrollments->whereIn('status', \App\Models\Enrollment::courseAccessStatuses()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                            <span class="px-2 py-1 bg-white rounded-full text-xs border"><?php echo e($enrollment->user?->name); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                            <span class="text-xs text-slate-500">Chưa có</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-2 text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300">Chưa có lớp học nào được xếp lịch chính thức.</div>
        <?php endif; ?>
    </div>

    <?php if($schedules->hasPages()): ?>
        <div><?php echo e($schedules->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/schedules/index.blade.php ENDPATH**/ ?>