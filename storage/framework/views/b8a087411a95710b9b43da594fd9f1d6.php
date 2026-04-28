<?php $__env->startSection('title', 'Lich hoc toan he thong'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <span class="sr-only">Xếp lịch</span>

    <?php if (isset($component)) { $__componentOriginal901bc0ef5589060c637099a6fd69b6fa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal901bc0ef5589060c637099a6fd69b6fa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.quan_tri.tieu_de_trang','data' => ['title' => 'Lich hoc toan he thong','subtitle' => 'Theo doi cac lop da xep lich, lop cho mo va phan bo giang vien trong toan trung tam']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('quan_tri.tieu_de_trang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Lich hoc toan he thong','subtitle' => 'Theo doi cac lop da xep lich, lop cho mo va phan bo giang vien trong toan trung tam']); ?>
        <div class="flex flex-wrap gap-2">
            <a href="<?php echo e(route('admin.schedules.conflicts')); ?>" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2 text-sm font-semibold text-cyan-700 hover:bg-cyan-100 transition">Kiem tra xung dot</a>
            <a href="<?php echo e(route('admin.schedules.queue')); ?>" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Hang cho xep lich</a>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal901bc0ef5589060c637099a6fd69b6fa)): ?>
<?php $attributes = $__attributesOriginal901bc0ef5589060c637099a6fd69b6fa; ?>
<?php unset($__attributesOriginal901bc0ef5589060c637099a6fd69b6fa); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal901bc0ef5589060c637099a6fd69b6fa)): ?>
<?php $component = $__componentOriginal901bc0ef5589060c637099a6fd69b6fa; ?>
<?php unset($__componentOriginal901bc0ef5589060c637099a6fd69b6fa); ?>
<?php endif; ?>

    <form method="get" action="<?php echo e(route('admin.schedules.index')); ?>" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div>
                <label class="block text-sm font-medium text-slate-700">Giang vien</label>
                <select name="teacher_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">Tat ca</option>
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($teacher->id); ?>" <?php if(request('teacher_id') == $teacher->id): echo 'selected'; endif; ?>><?php echo e($teacher->displayName()); ?></option>
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
                $classRoom = $course->currentClassRoom();
                $detailUrl = route('admin.schedules.courses.show', $course);
            ?>
            <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md">
                <a href="<?php echo e($detailUrl); ?>" class="absolute inset-0 z-0 rounded-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400" aria-label="Xem chi tiết <?php echo e($course->title); ?>"></a>

                <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400"><?php echo e($course->subject?->category?->name ?? 'Chua phan nhom'); ?></p>
                        <h3 class="mt-2 text-lg font-semibold tracking-tight text-slate-950"><?php echo e($course->title); ?></h3>
                        <p class="mt-1 text-sm text-slate-500">Nhấn vào thẻ để xem chi tiết lớp học.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        <?php if (isset($component)) { $__componentOriginale49b452f9ea4b3d0a64b743d8b9520cb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale49b452f9ea4b3d0a64b743d8b9520cb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.quan_tri.huy_hieu','data' => ['type' => $course->isPendingOpen() ? 'warning' : 'info','text' => $course->statusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('quan_tri.huy_hieu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($course->isPendingOpen() ? 'warning' : 'info'),'text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($course->statusLabel())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale49b452f9ea4b3d0a64b743d8b9520cb)): ?>
<?php $attributes = $__attributesOriginale49b452f9ea4b3d0a64b743d8b9520cb; ?>
<?php unset($__attributesOriginale49b452f9ea4b3d0a64b743d8b9520cb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale49b452f9ea4b3d0a64b743d8b9520cb)): ?>
<?php $component = $__componentOriginale49b452f9ea4b3d0a64b743d8b9520cb; ?>
<?php unset($__componentOriginale49b452f9ea4b3d0a64b743d8b9520cb); ?>
<?php endif; ?>
                        <a href="<?php echo e(route('admin.course.show', $course)); ?>" class="inline-flex items-center justify-center rounded-full border border-rose-200 bg-rose-50 px-3.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                            <i class="fas fa-bolt mr-1.5 text-[10px]"></i>
                            Sửa nhanh
                        </a>
                        <a href="<?php echo e($detailUrl); ?>" class="inline-flex items-center justify-center rounded-full border border-cyan-200 bg-cyan-50 px-3.5 py-1.5 text-xs font-semibold text-cyan-700 transition hover:bg-cyan-100">
                            <i class="fas fa-calendar-day mr-1.5 text-[10px]"></i>
                            Xem lịch chi tiết
                        </a>
                        <?php if($classRoom): ?>
                            <a href="<?php echo e(route('admin.classes.show', $classRoom)); ?>" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                <i class="fas fa-door-open mr-1.5 text-[10px]"></i>
                                Xem lớp học
                            </a>
                        <?php endif; ?>
                        <form method="post" action="<?php echo e(route('admin.courses.delete', $course->id)); ?>" onsubmit="return confirm('Xóa khóa học này?');" class="relative z-10">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="inline-flex items-center justify-center rounded-full border border-rose-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                                <i class="fas fa-trash mr-1.5 text-[10px]"></i>
                                Xóa
                            </button>
                        </form>
                    </div>
                </div>

                <div class="relative z-10 mt-5 grid gap-3 text-sm text-slate-600 sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Giảng viên</span>
                        <span class="mt-1 block text-sm font-medium text-slate-700"><?php echo e($course->teacher?->displayName() ?? 'Chua phan cong'); ?></span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Lớp</span>
                        <span class="mt-1 block text-sm font-medium text-slate-700"><?php echo e($classRoom?->displayName() ?? ($course->isPendingOpen() ? 'Chua mo lop' : 'Chua co lop')); ?></span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Lịch</span>
                        <span class="mt-1 block text-sm font-medium text-slate-700"><?php echo e($course->formattedSchedule()); ?></span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Sĩ số</span>
                        <span class="mt-1 block text-sm font-medium text-slate-700"><?php echo e($course->scheduled_students_count); ?>/<?php echo e($course->capacity ?? 20); ?></span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 sm:col-span-2">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Ngày học</span>
                        <span class="mt-1 block text-sm font-medium text-slate-700"><?php echo e($course->meetingDaysLabel()); ?></span>
                    </div>
                </div>

                <?php if($course->isPendingOpen()): ?>
                    <div class="relative z-10 mt-4 rounded-2xl <?php echo e($studentsNeeded === 0 ? 'border border-emerald-200 bg-emerald-50 text-emerald-800' : 'border border-amber-200 bg-amber-50 text-amber-800'); ?> px-4 py-3 text-sm leading-6">
                        <?php if($studentsNeeded === 0): ?>
                            Lop da du toi thieu <?php echo e(\App\Models\Course::minimumStudentsToOpen()); ?> hoc vien va co the mo lop ngay bay gio.
                        <?php else: ?>
                            Lop dang cho mo. Con thieu <?php echo e($studentsNeeded); ?> hoc vien nua de chot ngay khai giang.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="relative z-10 mt-4 rounded-2xl bg-slate-50 p-4 text-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Học viên</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <?php $__empty_2 = true; $__currentLoopData = $course->enrollments->whereIn('status', \App\Models\Enrollment::courseAccessStatuses()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                            <span class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600"><?php echo e($enrollment->user?->name); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                            <span class="text-xs text-slate-500">Chua co</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if($course->isPendingOpen()): ?>
                    <div class="relative z-10 mt-4 flex justify-end">
                        <a href="<?php echo e(route('admin.schedules.courses.open', $course)); ?>" class="inline-flex items-center justify-center rounded-2xl <?php echo e($studentsNeeded === 0 ? 'bg-cyan-600 text-white hover:bg-cyan-700' : 'border border-slate-300 text-slate-600 hover:bg-slate-50'); ?> px-4 py-2.5 text-sm font-semibold transition">
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

<?php echo $__env->make('bo_cuc.quan_tri', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu-main\resources\views/quan_tri/lich_hoc/index.blade.php ENDPATH**/ ?>