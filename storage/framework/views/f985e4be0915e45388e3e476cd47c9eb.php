<?php $__env->startSection('title', 'Quản lý khóa học'); ?>
<?php $__env->startSection('content'); ?>
<?php
  $subjectOptions = $subjects->map(function ($subject) {
      return [
          'id' => $subject->id,
          'label' => $subject->name . ($subject->category ? ' - ' . $subject->category->name : ''),
      ];
  });
?>
<div class="max-w-6xl mx-auto space-y-6">
  <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark"><?php echo e($selectedCategory ? 'Khóa học trong nhóm ' . $selectedCategory->name : 'Quản lý khóa học'); ?></h1>
      <p class="text-gray-600">
        <?php if($selectedCategory): ?>
          Trang này chỉ hiển thị các khóa học thực tế thuộc nhóm học này. Khi lưu, hệ thống sẽ quay lại đúng hồ sơ nhóm để bạn tiếp tục quản lý.
        <?php else: ?>
          Mỗi dòng bên dưới là một khóa học hoặc lớp thực tế mà admin có thể phân công giảng viên và xếp học viên.
        <?php endif; ?>
      </p>
    </div>
    <div class="flex flex-wrap gap-2">
      <?php if($selectedCategory): ?>
        <a href="<?php echo e(route('admin.courses')); ?>" class="rounded-lg border border-primary px-3 py-2 text-sm font-medium text-primary hover:bg-primary-light/20 transition">Tất cả khóa học</a>
        <a href="<?php echo e(route('admin.categories.show', $selectedCategory)); ?>" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Quay lại nhóm học</a>
      <?php else: ?>
        <a href="<?php echo e(route('admin.subjects')); ?>" class="rounded-lg border border-primary px-3 py-2 text-sm font-medium text-primary hover:bg-primary-light/20 transition">Khóa gốc</a>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Dashboard</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if(session('status')): ?><div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800"><?php echo e(session('status')); ?></div><?php endif; ?>
  <?php if(session('error')): ?><div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800"><?php echo e(session('error')); ?></div><?php endif; ?>

  <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-gray-900"><?php echo e($selectedCategory ? 'Tạo khóa học mới trong nhóm' : 'Tạo khóa học mới'); ?></h2>
    <?php if($selectedCategory): ?>
      <p class="mt-2 text-sm text-gray-600">Nhóm <?php echo e($selectedCategory->name); ?> hiện có <?php echo e($courses->count()); ?> khóa học. Form bên dưới sẽ chỉ cho phép tạo khóa thuộc đúng nhóm này.</p>
    <?php endif; ?>

    <?php if($selectedCategory && $subjects->isEmpty()): ?>
      <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Nhóm học này chưa có cấu hình khóa gốc để gắn khóa học mới.
        <a href="<?php echo e(route('admin.subjects.create-page', ['category_id' => $selectedCategory->id, 'return_to_category_id' => $selectedCategory->id])); ?>" class="font-semibold underline underline-offset-2">Tạo cấu hình trước</a>
      </div>
    <?php else: ?>
      <form method="post" action="<?php echo e(route('admin.courses.create')); ?>" class="mt-4 grid gap-4 lg:grid-cols-2">
        <?php echo csrf_field(); ?>
        <?php if($returnToCategoryId): ?>
          <input type="hidden" name="return_to_category_id" value="<?php echo e($returnToCategoryId); ?>" />
        <?php endif; ?>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Thuộc khóa gốc</label>
          <select name="subject_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5">
            <option value="">Chọn khóa gốc</option>
            <?php $__currentLoopData = $subjectOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subjectOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($subjectOption['id']); ?>" <?php if((string) old('subject_id', $selectedSubject?->id) === (string) $subjectOption['id']): echo 'selected'; endif; ?>><?php echo e($subjectOption['label']); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Tên khóa học</label>
          <input name="title" value="<?php echo e(old('title')); ?>" required placeholder="Ví dụ: Tin học văn phòng" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Giá khóa học</label>
          <div class="relative">
            <input type="number" name="price" value="<?php echo e(old('price', 0)); ?>" min="0" placeholder="Nhập giá" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 pr-12" />
            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-500">VNĐ</span>
          </div>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Lịch dự kiến</label>
          <input name="schedule" value="<?php echo e(old('schedule')); ?>" placeholder="Ví dụ: T2-T4-T6, 18:00-20:00" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
        </div>
        <div class="lg:col-span-2">
          <label class="mb-1 block text-sm font-medium text-gray-700">Mô tả</label>
          <textarea name="description" rows="3" placeholder="Ghi chú ngắn về khóa học này" class="w-full rounded-xl border border-gray-300 px-3 py-2.5"><?php echo e(old('description')); ?></textarea>
        </div>
        <div class="lg:col-span-2">
          <button class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark transition">
            <i class="fas fa-plus"></i>
            Tạo khóa học
          </button>
        </div>
      </form>
    <?php endif; ?>
  </div>

  <div class="grid gap-4 lg:grid-cols-2">
    <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="text-lg font-semibold text-gray-900"><?php echo e($course->title); ?></h3>
              <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700"><?php echo e($course->subject?->name ?? 'Chưa gắn khóa gốc'); ?></span>
            </div>
            <div class="mt-2 space-y-1 text-sm text-gray-600">
              <p><strong>Nhóm học:</strong> <?php echo e($course->subject?->category?->name ?? 'Chưa phân nhóm'); ?></p>
              <p><strong>Giá:</strong> <?php echo e($course->price == 0 ? 'Miễn phí' : number_format($course->price) . ' VNĐ'); ?></p>
              <p><strong>Lịch:</strong> <?php echo e($course->formattedSchedule()); ?></p>
              <p><strong>Giảng viên:</strong> <?php echo e($course->teacher?->name ?? 'Chưa phân công'); ?></p>
              <p><strong>Học viên đã xếp:</strong> <?php echo e($course->enrollments_count ?? 0); ?></p>
            </div>
            <p class="mt-3 text-sm leading-6 text-gray-600"><?php echo e($course->description ?? 'Chưa có mô tả cho khóa học này.'); ?></p>
          </div>
          <div class="flex gap-2">
            <a href="<?php echo e(route('admin.course.show', $course->id)); ?>" class="rounded-xl bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">Chỉnh sửa</a>
            <form method="post" action="<?php echo e(route('admin.courses.delete', $course->id)); ?>" onsubmit="return confirm('Xóa khóa học này?');">
              <?php echo csrf_field(); ?>
              <button class="rounded-xl bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 transition">Xóa</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500 lg:col-span-2">
        <?php echo e($selectedCategory ? 'Nhóm học này chưa có khóa học nào. Tạo mới ở form phía trên.' : 'Chưa có khóa học nào. Tạo khóa mới để admin có thể phân công giảng viên và xếp học viên.'); ?>

      </div>
    <?php endif; ?>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/courses.blade.php ENDPATH**/ ?>