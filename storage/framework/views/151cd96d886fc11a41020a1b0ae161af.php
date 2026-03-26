<?php $__env->startSection('title', 'Quản lý lớp học'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
  <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Quản lý lớp học</h1>
      <p class="text-gray-600">Lớp học là nơi admin gán học viên sau khi họ đã chọn khóa học.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="<?php echo e(route('admin.subjects')); ?>" class="rounded-lg border border-primary px-3 py-2 text-sm font-medium text-primary hover:bg-primary-light/20 transition">Khóa học</a>
      <a href="<?php echo e(route('admin.dashboard')); ?>" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Dashboard</a>
    </div>
  </div>

  <?php if(session('status')): ?><div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800"><?php echo e(session('status')); ?></div><?php endif; ?>
  <?php if(session('error')): ?><div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800"><?php echo e(session('error')); ?></div><?php endif; ?>

  <div class="mb-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-gray-900">Tạo lớp học mới</h2>
    <form method="post" action="<?php echo e(route('admin.courses.create')); ?>" class="mt-4 grid gap-4 lg:grid-cols-2">
      <?php echo csrf_field(); ?>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Thuộc khóa học</label>
        <select name="subject_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5">
          <option value="">Chọn khóa học</option>
          <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($subject->id); ?>"><?php echo e($subject->name); ?><?php echo e($subject->category ? ' - ' . $subject->category->name : ''); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Tên lớp học</label>
        <input name="title" required placeholder="Ví dụ: Tin học văn phòng - Ca tối" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Lịch dự kiến</label>
        <input name="schedule" placeholder="Ví dụ: T2-T4-T6, 18:00-20:00" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
      </div>
      <div class="lg:col-span-2">
        <label class="mb-1 block text-sm font-medium text-gray-700">Mô tả</label>
        <textarea name="description" rows="3" placeholder="Ghi chú ngắn về lớp học này" class="w-full rounded-xl border border-gray-300 px-3 py-2.5"></textarea>
      </div>
      <div class="lg:col-span-2">
        <button class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark transition">
          <i class="fas fa-plus"></i>
          Tạo lớp học
        </button>
      </div>
    </form>
  </div>

  <div class="grid gap-4 lg:grid-cols-2">
    <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="text-lg font-semibold text-gray-900"><?php echo e($course->title); ?></h3>
              <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700"><?php echo e($course->subject?->name ?? 'Chưa gắn khóa học'); ?></span>
            </div>
            <div class="mt-2 space-y-1 text-sm text-gray-600">
              <p><strong>Nhóm ngành:</strong> <?php echo e($course->subject?->category?->name ?? 'Chưa phân nhóm'); ?></p>
              <p><strong>Lịch:</strong> <?php echo e($course->schedule ?: 'Chưa chốt'); ?></p>
              <p><strong>Giảng viên:</strong> <?php echo e($course->teacher?->name ?? 'Chưa phân công'); ?></p>
              <p><strong>Học viên đã xếp:</strong> <?php echo e($course->enrollments_count ?? 0); ?></p>
            </div>
            <p class="mt-3 text-sm leading-6 text-gray-600"><?php echo e($course->description ?? 'Chưa có mô tả cho lớp học này.'); ?></p>
          </div>
          <div class="flex gap-2">
            <a href="<?php echo e(route('admin.course.show', $course->id)); ?>" class="rounded-xl bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">Chỉnh sửa</a>
            <form method="post" action="<?php echo e(route('admin.courses.delete', $course->id)); ?>" onsubmit="return confirm('Xóa lớp học này?');">
              <?php echo csrf_field(); ?>
              <button class="rounded-xl bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 transition">Xóa</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500 lg:col-span-2">
        Chưa có lớp học nào. Tạo lớp để admin có thể xếp học viên sau khi duyệt đăng ký.
      </div>
    <?php endif; ?>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\admin\courses.blade.php ENDPATH**/ ?>