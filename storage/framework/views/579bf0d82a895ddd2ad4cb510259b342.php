
<?php $__env->startSection('title', 'Chi tiết lớp học'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto space-y-6">
  <a href="<?php echo e(route('teacher.courses')); ?>" class="inline-flex items-center gap-2 text-primary hover:gap-3 transition font-semibold">
    <i class="fas fa-arrow-left"></i>
    Quay lại lớp học phụ trách
  </a>

  <?php if(session('status')): ?>
    <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-green-800"><?php echo e(session('status')); ?></div>
  <?php endif; ?>

  <?php if(session('error')): ?>
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800"><?php echo e(session('error')); ?></div>
  <?php endif; ?>

  <section class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-1 text-sm font-semibold text-blue-700">
          <i class="fas fa-chalkboard-user"></i>
          Lớp đang giảng dạy
        </span>
        <h1 class="mt-4 text-3xl font-bold text-gray-900"><?php echo e($course->title); ?></h1>
        <p class="mt-3 text-gray-600"><?php echo e($course->description ?: 'Admin đã xếp lớp này cho bạn phụ trách sau khi duyệt yêu cầu của học viên.'); ?></p>
      </div>
      <div class="rounded-2xl bg-slate-50 px-5 py-4 text-sm text-gray-600 shadow-sm">
        <div><strong>Khóa học:</strong> <?php echo e($course->subject?->name ?? 'Chưa gắn khóa học'); ?></div>
        <div class="mt-1"><strong>Nhóm ngành:</strong> <?php echo e($course->subject?->category?->name ?? 'Chưa phân nhóm'); ?></div>
        <div class="mt-1"><strong>Lịch lớp:</strong> <?php echo e($course->schedule ?: 'Chưa chốt'); ?></div>
      </div>
    </div>
  </section>

  <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900">Module trong lớp</h2>
        <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700"><?php echo e($course->modules->count()); ?> module</span>
      </div>

      <?php $__empty_1 = true; $__currentLoopData = $course->modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="mb-4 rounded-2xl border border-gray-200 p-4 last:mb-0">
          <div class="text-sm font-semibold uppercase tracking-wide text-primary">Module <?php echo e($module->position ?? $loop->iteration); ?></div>
          <h3 class="mt-1 text-lg font-bold text-gray-900"><?php echo e($module->title); ?></h3>
          <p class="mt-2 text-gray-600"><?php echo e($module->content ?: 'Chưa có mô tả cho module này.'); ?></p>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-gray-500">
          Chưa có module nào trong lớp học này.
        </div>
      <?php endif; ?>
    </section>

    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
      <?php $confirmedEnrollments = $course->enrollments->where('status', 'confirmed'); ?>
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900">Học viên đã được xếp lớp</h2>
        <span class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-800"><?php echo e($confirmedEnrollments->count()); ?> học viên</span>
      </div>

      <?php if($confirmedEnrollments->isEmpty()): ?>
        <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-gray-500">
          Chưa có học viên nào được xếp vào lớp này.
        </div>
      <?php else: ?>
        <div class="space-y-4">
          <?php $__currentLoopData = $confirmedEnrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-2xl border border-gray-200 p-4">
              <div>
                <div class="text-lg font-semibold text-gray-900"><?php echo e($enrollment->user?->name); ?></div>
                <div class="text-sm text-gray-500">Lịch đã chốt: <?php echo e($enrollment->schedule ?: $course->schedule ?: 'Chưa có'); ?></div>
              </div>

              <div class="mt-4 space-y-3">
                <?php $__currentLoopData = $course->modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php $grade = $gradeMap->get($enrollment->id . '-' . $module->id); ?>
                  <form method="post" action="<?php echo e(route('teacher.grades.update')); ?>" class="grid gap-2 rounded-2xl bg-slate-50 p-3">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="enrollment_id" value="<?php echo e($enrollment->id); ?>" />
                    <input type="hidden" name="module_id" value="<?php echo e($module->id); ?>" />
                    <div class="text-sm font-semibold text-gray-700"><?php echo e($module->title); ?></div>
                    <div class="grid gap-2 md:grid-cols-[120px_100px_1fr_auto]">
                      <input name="score" value="<?php echo e($grade->score ?? ''); ?>" placeholder="Điểm" class="rounded-xl border border-gray-300 px-3 py-2" type="number" min="0" max="100" />
                      <input name="grade" value="<?php echo e($grade->grade ?? ''); ?>" placeholder="A/B/C" class="rounded-xl border border-gray-300 px-3 py-2" maxlength="5" />
                      <input name="feedback" value="<?php echo e($grade->feedback ?? ''); ?>" placeholder="Phản hồi cho học viên" class="rounded-xl border border-gray-300 px-3 py-2" />
                      <button type="submit" class="rounded-xl bg-primary px-4 py-2 font-semibold text-white hover:bg-primary-dark transition">Lưu</button>
                    </div>
                  </form>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\teacher\course_show.blade.php ENDPATH**/ ?>