<?php $__env->startSection('title', 'Quản lý đăng ký khóa học'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">
  <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Quản lý đăng ký khóa học</h1>
      <?php $newCount = $enrollments->where('status', 'pending')->where('is_submitted', true)->count(); ?>
      <p class="text-gray-600">Xem khóa học học viên đã chọn và xếp các bạn vào lớp học phù hợp.</p>
      <?php if($newCount > 0): ?>
        <p class="mt-1 text-sm font-medium text-red-600">Hiện có <?php echo e($newCount); ?> yêu cầu mới đang chờ xử lý.</p>
      <?php endif; ?>
    </div>
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Quay lại</a>
  </div>

  <?php if(session('status')): ?><div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800"><?php echo e(session('status')); ?></div><?php endif; ?>
  <?php if(session('error')): ?><div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800"><?php echo e(session('error')); ?></div><?php endif; ?>

  <div class="overflow-x-auto rounded-3xl border border-gray-200 bg-white shadow-sm">
    <table class="min-w-full border-collapse text-sm">
      <thead>
        <tr class="bg-slate-50 text-left">
          <th class="border-b p-3 font-semibold text-gray-700">Học viên</th>
          <th class="border-b p-3 font-semibold text-gray-700">Khóa học đã chọn</th>
          <th class="border-b p-3 font-semibold text-gray-700">Lớp được xếp</th>
          <th class="border-b p-3 font-semibold text-gray-700">Lịch mong muốn</th>
          <th class="border-b p-3 font-semibold text-gray-700">Giảng viên</th>
          <th class="border-b p-3 font-semibold text-gray-700">Trạng thái</th>
          <th class="border-b p-3 font-semibold text-gray-700">Xử lý</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $enrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <?php
            $isNew = $enrollment->status === 'pending' && $enrollment->is_submitted;
            $requestedSubject = $enrollment->subject?->name ?? $enrollment->course?->subject?->name ?? 'Chưa xác định';
            $selectedDays = $enrollment->preferred_days ? json_decode($enrollment->preferred_days, true) : [];
            $dayLabels = [
              'Monday' => 'T2',
              'Tuesday' => 'T3',
              'Wednesday' => 'T4',
              'Thursday' => 'T5',
              'Friday' => 'T6',
              'Saturday' => 'T7',
              'Sunday' => 'CN',
            ];
            $availableCourses = $enrollment->subject_id ? $courses->where('subject_id', $enrollment->subject_id) : $courses;
            if ($availableCourses->isEmpty()) {
              $availableCourses = $courses;
            }
          ?>
          <tr class="align-top <?php echo e($isNew ? 'bg-yellow-50' : 'bg-white'); ?>">
            <td class="border-b p-3">
              <div class="font-semibold text-gray-900"><?php echo e($enrollment->user?->name); ?></div>
              <div class="mt-1 text-xs text-gray-500">#<?php echo e($enrollment->id); ?></div>
              <?php if($isNew): ?>
                <div class="mt-2 inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">Yêu cầu mới</div>
              <?php endif; ?>
            </td>
            <td class="border-b p-3">
              <div class="font-semibold text-gray-900"><?php echo e($requestedSubject); ?></div>
              <div class="mt-1 text-xs text-gray-500"><?php echo e($enrollment->subject?->category?->name ?? $enrollment->course?->subject?->category?->name ?? 'Chưa phân nhóm'); ?></div>
            </td>
            <td class="border-b p-3">
              <div class="font-semibold text-gray-900"><?php echo e($enrollment->course?->title ?? 'Chưa xếp lớp'); ?></div>
              <div class="mt-1 text-xs text-gray-500"><?php echo e($enrollment->schedule ?: 'Chưa có lịch chốt'); ?></div>
            </td>
            <td class="border-b p-3 text-gray-700">
              <?php if($enrollment->start_time && $enrollment->end_time): ?>
                <div><?php echo e($enrollment->start_time); ?> - <?php echo e($enrollment->end_time); ?></div>
              <?php else: ?>
                <div><?php echo e($enrollment->preferred_schedule ?? 'Chưa cung cấp'); ?></div>
              <?php endif; ?>
              <div class="mt-1 text-xs text-gray-500">
                <?php echo e($selectedDays ? implode(', ', array_map(fn ($day) => $dayLabels[$day] ?? $day, $selectedDays)) : 'Không có ngày cụ thể'); ?>

              </div>
            </td>
            <td class="border-b p-3 text-gray-700"><?php echo e($enrollment->assignedTeacher?->name ?? 'Chưa phân công'); ?></td>
            <td class="border-b p-3">
              <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo e($enrollment->status === 'confirmed' ? 'bg-green-100 text-green-800' : ($enrollment->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')); ?>">
                <?php echo e($enrollment->status); ?>

              </span>
              <?php if($enrollment->status === 'rejected' && $enrollment->note): ?>
                <p class="mt-2 rounded-xl bg-red-50 px-3 py-2 text-xs text-red-700"><?php echo e($enrollment->note); ?></p>
              <?php endif; ?>
            </td>
            <td class="border-b p-3">
              <form method="post" action="<?php echo e(route('admin.enrollments.update', $enrollment->id)); ?>" class="min-w-[260px] space-y-3">
                <?php echo csrf_field(); ?>
                <div>
                  <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Xếp lớp học</label>
                  <select name="course_id" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Chưa chọn lớp</option>
                    <?php $__currentLoopData = $availableCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $courseItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <option value="<?php echo e($courseItem->id); ?>" <?php if($enrollment->course_id == $courseItem->id): echo 'selected'; endif; ?>>
                        <?php echo e($courseItem->title); ?><?php echo e($courseItem->schedule ? ' - ' . $courseItem->schedule : ''); ?>

                      </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>

                <div>
                  <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Trạng thái</label>
                  <select name="status" class="status-select w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                    <option value="pending" <?php if($enrollment->status === 'pending'): echo 'selected'; endif; ?>>pending</option>
                    <option value="confirmed" <?php if($enrollment->status === 'confirmed'): echo 'selected'; endif; ?>>confirmed</option>
                    <option value="rejected" <?php if($enrollment->status === 'rejected'): echo 'selected'; endif; ?>>rejected</option>
                  </select>
                </div>

                <div>
                  <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Giảng viên</label>
                  <select name="assigned_teacher_id" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Chưa phân công</option>
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <option value="<?php echo e($teacher->id); ?>" <?php if($enrollment->assigned_teacher_id == $teacher->id): echo 'selected'; endif; ?>><?php echo e($teacher->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>

                <div>
                  <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Lịch chốt với học viên</label>
                  <input name="schedule" value="<?php echo e($enrollment->schedule); ?>" placeholder="Ví dụ: T2-T4-T6, 18:00-20:00" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm" />
                </div>

                <div class="rejection-reason-container <?php echo e($enrollment->status !== 'rejected' ? 'hidden' : ''); ?>">
                  <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Lý do yêu cầu học viên chỉnh lại</label>
                  <textarea name="note" rows="3" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm" placeholder="Lý do sẽ hiển thị lại cho học viên"><?php echo e($enrollment->note); ?></textarea>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark transition">
                  <i class="fas fa-floppy-disk"></i>
                  Cập nhật
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="7" class="p-10 text-center text-gray-500">Chưa có yêu cầu đăng ký nào.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.status-select').forEach(function (select) {
      const container = select.closest('form').querySelector('.rejection-reason-container');
      const toggle = function () {
        container.classList.toggle('hidden', select.value !== 'rejected');
      };
      select.addEventListener('change', toggle);
      toggle();
    });
  });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/enrollments.blade.php ENDPATH**/ ?>