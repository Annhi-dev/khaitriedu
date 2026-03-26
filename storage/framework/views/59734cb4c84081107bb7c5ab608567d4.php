
<?php $__env->startSection('title', $course->title); ?>
<?php $__env->startSection('content'); ?>
<?php
  $backUrl = route('dashboard');
  $backLabel = 'Quay lại dashboard';

  if (($user->role ?? null) === 'giang_vien') {
      $backUrl = route('teacher.courses');
      $backLabel = 'Quay lại lớp học phụ trách';
  } elseif (($user->role ?? null) === 'admin') {
      $backUrl = route('admin.courses');
      $backLabel = 'Quay lại quản lý lớp học';
  } elseif (($user->role ?? null) === 'hoc_vien') {
      $backUrl = route('student.schedule');
      $backLabel = 'Quay lại lịch học';
  }

  $averageRating = $reviews->count() ? number_format($reviews->avg('rating'), 1) : null;
?>
<div class="max-w-5xl mx-auto space-y-6">
  <a href="<?php echo e($backUrl); ?>" class="inline-flex items-center gap-2 text-primary hover:gap-3 transition font-semibold">
    <i class="fas fa-arrow-left"></i>
    <?php echo e($backLabel); ?>

  </a>

  <?php if(session('status')): ?>
    <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-green-800"><?php echo e(session('status')); ?></div>
  <?php endif; ?>

  <?php if(session('error')): ?>
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800"><?php echo e(session('error')); ?></div>
  <?php endif; ?>

  <section class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-1 text-sm font-semibold text-blue-700">
          <i class="fas fa-users-rectangle"></i>
          Lớp học nội bộ
        </span>
        <h1 class="mt-4 text-3xl font-bold text-gray-900"><?php echo e($course->title); ?></h1>
        <p class="mt-3 max-w-3xl text-gray-600">
          <?php echo e($course->description ?: 'Đây là lớp học mà admin đã xếp cho học viên sau khi duyệt yêu cầu đăng ký khóa học.'); ?>

        </p>
      </div>

      <?php if($averageRating): ?>
        <div class="rounded-2xl bg-amber-50 px-5 py-4 text-center text-amber-800 shadow-sm">
          <div class="text-sm font-semibold uppercase tracking-wide">Đánh giá trung bình</div>
          <div class="mt-2 text-3xl font-black"><?php echo e($averageRating); ?></div>
          <div class="mt-1 text-sm"><?php echo e($reviews->count()); ?> đánh giá</div>
        </div>
      <?php endif; ?>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <div class="rounded-2xl bg-slate-50 p-4">
        <div class="text-sm text-gray-500">Khóa học học viên đã đăng ký</div>
        <div class="mt-2 text-lg font-bold text-gray-900"><?php echo e($course->subject?->name ?? 'Chưa gắn khóa học'); ?></div>
      </div>
      <div class="rounded-2xl bg-slate-50 p-4">
        <div class="text-sm text-gray-500">Nhóm ngành</div>
        <div class="mt-2 text-lg font-bold text-gray-900"><?php echo e($course->subject?->category?->name ?? 'Chưa phân nhóm'); ?></div>
      </div>
      <div class="rounded-2xl bg-slate-50 p-4">
        <div class="text-sm text-gray-500">Giảng viên phụ trách</div>
        <div class="mt-2 text-lg font-bold text-gray-900"><?php echo e($course->teacher?->name ?? 'Chưa phân công'); ?></div>
      </div>
      <div class="rounded-2xl bg-slate-50 p-4">
        <div class="text-sm text-gray-500">Lịch lớp</div>
        <div class="mt-2 text-lg font-bold text-gray-900"><?php echo e($course->schedule ?: ($enrollment->schedule ?? 'Chưa chốt lịch')); ?></div>
      </div>
    </div>

    <?php if(($user->role ?? null) === 'hoc_vien' && $enrollment): ?>
      <div class="mt-6 rounded-2xl border border-primary/20 bg-primary-light/10 p-5">
        <h2 class="text-lg font-semibold text-primary-dark">Thông tin xếp lớp của bạn</h2>
        <div class="mt-3 grid gap-4 md:grid-cols-3 text-sm text-gray-700">
          <div>
            <div class="font-medium text-gray-500">Trạng thái</div>
            <div class="mt-1 font-semibold text-green-700">Đã được xếp lớp</div>
          </div>
          <div>
            <div class="font-medium text-gray-500">Lịch đã chốt</div>
            <div class="mt-1 font-semibold text-gray-900"><?php echo e($enrollment->schedule ?: $course->schedule ?: 'Admin sẽ cập nhật sau'); ?></div>
          </div>
          <div>
            <div class="font-medium text-gray-500">Giảng viên</div>
            <div class="mt-1 font-semibold text-gray-900"><?php echo e($enrollment->assignedTeacher?->name ?? $course->teacher?->name ?? 'Chưa phân công'); ?></div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </section>

  <section class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Lộ trình trong lớp học</h2>
        <p class="text-gray-600">Danh sách module và bài học bạn có thể theo dõi trong lớp này.</p>
      </div>
      <div class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">
        <?php echo e($course->modules->count()); ?> module
      </div>
    </div>

    <?php $__empty_1 = true; $__currentLoopData = $course->modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <div class="mb-4 rounded-2xl border border-gray-200 p-5 last:mb-0">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
          <div>
            <div class="text-sm font-semibold uppercase tracking-wide text-primary">Module <?php echo e($module->position ?? $loop->iteration); ?></div>
            <h3 class="mt-1 text-xl font-bold text-gray-900"><?php echo e($module->title); ?></h3>
            <p class="mt-2 text-gray-600"><?php echo e($module->content ?: 'Module này đang được cập nhật nội dung chi tiết.'); ?></p>
          </div>
          <div class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">
            <?php echo e($module->lessons->count()); ?> bài học
          </div>
        </div>

        <?php if($module->lessons->isEmpty()): ?>
          <div class="mt-4 rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-500">
            Chưa có bài học nào trong module này.
          </div>
        <?php else: ?>
          <div class="mt-4 grid gap-3">
            <?php $__currentLoopData = $module->lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <a href="<?php echo e(route('courses.lesson.show', [$course->id, $module->id, $lesson->id])); ?>" class="flex flex-col gap-3 rounded-2xl border border-gray-200 px-4 py-4 transition hover:border-primary hover:bg-primary-light/10 md:flex-row md:items-center md:justify-between">
                <div>
                  <div class="text-sm font-semibold text-primary">Bài <?php echo e($lesson->order ?? $loop->iteration); ?></div>
                  <div class="mt-1 text-lg font-semibold text-gray-900"><?php echo e($lesson->title); ?></div>
                  <p class="mt-1 text-sm text-gray-600"><?php echo e($lesson->description ?: 'Mở bài học để xem nội dung chi tiết.'); ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                  <?php if($lesson->duration): ?>
                    <span class="rounded-full bg-slate-100 px-3 py-1"><?php echo e($lesson->duration); ?> phút</span>
                  <?php endif; ?>
                  <?php if($lesson->quiz): ?>
                    <span class="rounded-full bg-amber-100 px-3 py-1 font-semibold text-amber-800">Có quiz</span>
                  <?php endif; ?>
                  <span class="font-semibold text-primary">Vào bài học</span>
                </div>
              </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-gray-500">
        Lớp học này chưa có module. Admin hoặc giảng viên sẽ cập nhật nội dung sớm.
      </div>
    <?php endif; ?>
  </section>

  <?php if(($user->role ?? null) === 'hoc_vien' && $enrollment): ?>
    <section class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Đánh giá lớp học</h2>
        <p class="text-gray-600">Chia sẻ trải nghiệm sau khi bạn đã được xếp và học trong lớp này.</p>
      </div>

      <?php if($review): ?>
        <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5">
          <div class="text-sm font-semibold text-amber-800">Đánh giá của bạn</div>
          <div class="mt-2 text-lg font-bold text-gray-900"><?php echo e(str_repeat('★', $review->rating)); ?> <span class="text-sm font-medium text-gray-500">(<?php echo e($review->rating); ?>/5)</span></div>
          <?php if($review->comment): ?>
            <p class="mt-3 text-gray-700"><?php echo e($review->comment); ?></p>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <form method="post" action="<?php echo e(route('courses.review', $course->id)); ?>" class="mt-5 grid gap-4">
          <?php echo csrf_field(); ?>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Số sao</label>
            <select name="rating" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5">
              <option value="">Chọn số sao</option>
              <option value="5">5 sao</option>
              <option value="4">4 sao</option>
              <option value="3">3 sao</option>
              <option value="2">2 sao</option>
              <option value="1">1 sao</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Nhận xét</label>
            <textarea name="comment" rows="4" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" placeholder="Điều gì hữu ích nhất trong lớp này?"></textarea>
          </div>
          <div>
            <button type="submit" class="rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark transition">Gửi đánh giá</button>
          </div>
        </form>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <section class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Phản hồi từ học viên</h2>
        <p class="text-gray-600">Những chia sẻ gần đây của học viên đang theo lớp này.</p>
      </div>
      <div class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">
        <?php echo e($reviews->count()); ?> phản hồi
      </div>
    </div>

    <?php if($reviews->isEmpty()): ?>
      <div class="mt-5 rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-gray-500">
        Chưa có đánh giá nào cho lớp học này.
      </div>
    <?php else: ?>
      <div class="mt-5 space-y-4">
        <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="rounded-2xl border border-gray-200 p-5">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
              <div>
                <div class="text-lg font-semibold text-gray-900"><?php echo e($item->user?->name ?? 'Học viên'); ?></div>
                <div class="mt-1 text-sm font-semibold text-amber-600"><?php echo e(str_repeat('★', $item->rating)); ?> <span class="text-gray-500">(<?php echo e($item->rating); ?>/5)</span></div>
              </div>
              <div class="text-sm text-gray-500"><?php echo e($item->created_at?->format('d/m/Y')); ?></div>
            </div>
            <?php if($item->comment): ?>
              <p class="mt-3 text-gray-700"><?php echo e($item->comment); ?></p>
            <?php endif; ?>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    <?php endif; ?>
  </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\courses\show.blade.php ENDPATH**/ ?>