<?php $__env->startSection('title', 'Lich hoc toan he thong'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <span class="sr-only">Phase 9</span>

    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => 'Lich hoc toan he thong','subtitle' => 'Theo doi cac lop da xep lich, lop cho mo va phan bo giang vien trong toan trung tam']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Lich hoc toan he thong','subtitle' => 'Theo doi cac lop da xep lich, lop cho mo va phan bo giang vien trong toan trung tam']); ?>
        <a href="<?php echo e(route('admin.schedules.queue')); ?>" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Hang cho xep lich</a>
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

    <form method="get" action="<?php echo e(route('admin.schedules.index')); ?>" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div>
                <label class="block text-sm font-medium text-slate-700">Giang vien</label>
                <select name="teacher_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">Tat ca</option>
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($teacher->id); ?>" <?php if(request('teacher_id') == $teacher->id): echo 'selected'; endif; ?>><?php echo e($teacher->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Hoc vien</label>
                <select name="student_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">Tat ca</option>
                    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($student->id); ?>" <?php if(request('student_id') == $student->id): echo 'selected'; endif; ?>><?php echo e($student->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Lop hoc</label>
                <select name="course_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">Tat ca</option>
                    <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $courseOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($courseOption->id); ?>" <?php if(request('course_id') == $courseOption->id): echo 'selected'; endif; ?>><?php echo e($courseOption->title); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Ngay hoc</label>
                <input type="date" name="date" value="<?php echo e(request('date')); ?>" class="w-full rounded-xl border px-3 py-2">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-white">Loc</button>
                <a href="<?php echo e(route('admin.schedules.index')); ?>" class="rounded-xl border px-4 py-2">Xoa</a>
            </div>
        </div>
    </form>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $studentsNeeded = max(0, \App\Models\Course::minimumStudentsToOpen() - (int) $course->scheduled_students_count);
            ?>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex justify-between gap-4">
                    <div>
                        <p class="text-xs text-slate-500"><?php echo e($course->subject?->category?->name ?? 'Chua phan nhom'); ?></p>
                        <h3 class="text-lg font-semibold"><?php echo e($course->title); ?></h3>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => $course->isPendingOpen() ? 'warning' : 'info','text' => $course->statusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($course->isPendingOpen() ? 'warning' : 'info'),'text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($course->statusLabel())]); ?>
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
                    <p><span class="text-slate-500">Giang vien:</span> <?php echo e($course->teacher?->name ?? 'Chua phan cong'); ?></p>
                    <p><span class="text-slate-500">Lich:</span> <?php echo e($course->formattedSchedule()); ?></p>
                    <p><span class="text-slate-500">Si so:</span> <?php echo e($course->scheduled_students_count); ?>/<?php echo e($course->capacity ?? 20); ?></p>
                    <p><span class="text-slate-500">Ngay hoc:</span> <?php echo e($course->meetingDaysLabel()); ?></p>
                </div>

                <?php if($course->isPendingOpen()): ?>
                    <div class="mt-4 rounded-xl <?php echo e($studentsNeeded === 0 ? 'border border-emerald-200 bg-emerald-50 text-emerald-800' : 'border border-amber-200 bg-amber-50 text-amber-800'); ?> px-4 py-3 text-sm">
                        <?php if($studentsNeeded === 0): ?>
                            Lop da du toi thieu <?php echo e(\App\Models\Course::minimumStudentsToOpen()); ?> hoc vien va co the mo lop ngay bay gio.
                        <?php else: ?>
                            Lop dang cho mo. Con thieu <?php echo e($studentsNeeded); ?> hoc vien nua de chot ngay khai giang.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="mt-4 rounded-xl bg-slate-50 p-3 text-sm">
                    <p class="font-medium">Hoc vien:</p>
                    <div class="mt-1 flex flex-wrap gap-1">
                        <?php $__empty_2 = true; $__currentLoopData = $course->enrollments->whereIn('status', \App\Models\Enrollment::courseAccessStatuses()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                            <span class="rounded-full border bg-white px-2 py-1 text-xs"><?php echo e($enrollment->user?->name); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                            <span class="text-xs text-slate-500">Chua co</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if($course->isPendingOpen()): ?>
                    <div class="mt-4 flex justify-end">
                        <a href="<?php echo e(route('admin.schedules.courses.open', $course)); ?>" class="inline-flex items-center justify-center rounded-2xl <?php echo e($studentsNeeded === 0 ? 'bg-cyan-600 text-white hover:bg-cyan-700' : 'border border-slate-300 text-slate-600 hover:bg-slate-50'); ?> px-4 py-2.5 text-sm font-semibold">
                            <?php echo e($studentsNeeded === 0 ? 'Chon ngay va mo lop' : 'Xem dieu kien mo lop'); ?>

                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-2 rounded-2xl border border-dashed border-slate-300 bg-white py-12 text-center">
                Chua co lop hoc nao duoc luu trong he thong.
            </div>
        <?php endif; ?>
    </div>

    <?php if($schedules->hasPages()): ?>
        <div><?php echo e($schedules->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu-main\resources\views/admin/schedules/index.blade.php ENDPATH**/ ?>