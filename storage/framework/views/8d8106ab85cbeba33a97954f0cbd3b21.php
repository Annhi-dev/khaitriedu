<?php $__env->startSection('title', $subject->name); ?>
<?php $__env->startSection('content'); ?>
<?php
    $days = [
        'Monday' => 'Thứ 2',
        'Tuesday' => 'Thứ 3',
        'Wednesday' => 'Thứ 4',
        'Thursday' => 'Thứ 5',
        'Friday' => 'Thứ 6',
        'Saturday' => 'Thứ 7',
        'Sunday' => 'Chủ nhật',
    ];
    $selectedDays = old('preferred_days', $userEnrollment ? (json_decode($userEnrollment->preferred_days, true) ?: []) : []);
    $normalizedStatus = $userEnrollment?->normalizedStatus();
    $hasCourseAccess = $userEnrollment?->hasCourseAccess();
?>
<div class="max-w-5xl mx-auto">
  <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <p class="text-sm font-semibold uppercase tracking-[0.2em] text-primary"><?php echo e($subject->category?->name ?? 'Chưa phân nhóm'); ?></p>
      <h1 class="mt-2 text-3xl font-bold text-gray-900"><?php echo e($subject->name); ?></h1>
      <p class="mt-2 text-gray-600"><?php echo e($subject->description ?? 'Khóa học này sẽ được admin xếp vào lớp phù hợp sau khi duyệt yêu cầu đăng ký.'); ?></p>
    </div>
    <a href="<?php echo e(route('courses.index')); ?>" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2 font-medium text-gray-700 hover:border-primary hover:text-primary transition">
      <i class="fas fa-arrow-left text-sm"></i>
      Quay lại danh sách khóa học
    </a>
  </div>

  <?php if(session('status')): ?>
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800"><?php echo e(session('status')); ?></div>
  <?php endif; ?>
  <?php if(session('error')): ?>
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800"><?php echo e(session('error')); ?></div>
  <?php endif; ?>

  <div class="mb-6 grid gap-4 md:grid-cols-3">
    <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
      <div class="text-sm font-medium text-gray-500">Nhóm học</div>
      <div class="mt-2 text-xl font-bold text-gray-900"><?php echo e($subject->category?->name ?? 'Chưa phân nhóm'); ?></div>
      <p class="mt-2 text-sm text-gray-500">Bạn chọn khóa học theo nhóm học, admin sẽ xem lịch phù hợp để phân lớp.</p>
    </div>
    <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
      <div class="text-sm font-medium text-gray-500">Học phí tham khảo</div>
      <div class="mt-2 text-xl font-bold text-primary"><?php echo e(number_format($subject->price ?? 0, 0, ',', '.')); ?>đ</div>
      <p class="mt-2 text-sm text-gray-500">Mức phí thực tế có thể được tư vấn lại theo lớp học và lịch khai giảng.</p>
    </div>
    <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
      <div class="text-sm font-medium text-gray-500">Lớp hiện có</div>
      <div class="mt-2 text-xl font-bold text-gray-900"><?php echo e($subject->courses->count()); ?></div>
      <p class="mt-2 text-sm text-gray-500">Nếu chưa có lớp phù hợp, admin vẫn có thể tiếp nhận yêu cầu và xếp lớp sau.</p>
    </div>
  </div>

  <?php if($userEnrollment): ?>
    <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
      <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div>
          <div class="text-sm font-semibold uppercase tracking-wide text-blue-700">Trạng thái đăng ký</div>
          <div class="mt-2">
            <?php if($normalizedStatus === \App\Models\Enrollment::STATUS_PENDING): ?>
              <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-sm font-semibold text-yellow-800">Đang chờ admin duyệt</span>
            <?php elseif($normalizedStatus === \App\Models\Enrollment::STATUS_APPROVED): ?>
              <span class="inline-flex rounded-full bg-cyan-100 px-3 py-1 text-sm font-semibold text-cyan-800">Đã duyệt, chờ xếp lớp</span>
            <?php elseif(in_array($normalizedStatus, [\App\Models\Enrollment::STATUS_SCHEDULED, \App\Models\Enrollment::STATUS_ACTIVE, \App\Models\Enrollment::STATUS_COMPLETED], true)): ?>
              <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800"><?php echo e($userEnrollment->statusLabel()); ?></span>
            <?php else: ?>
              <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-800">Đăng ký bị từ chối</span>
            <?php endif; ?>
          </div>
          <?php if($userEnrollment->course): ?>
            <div class="mt-3 text-sm text-gray-700">
              <p><strong>Lớp đã xếp:</strong> <?php echo e($userEnrollment->course->title); ?></p>
              <?php if($userEnrollment->assignedTeacher): ?>
                <p><strong>Giảng viên:</strong> <?php echo e($userEnrollment->assignedTeacher->name); ?></p>
              <?php endif; ?>
              <?php if($userEnrollment->schedule): ?>
                <p><strong>Lịch admin đã chốt:</strong> <?php echo e($userEnrollment->schedule); ?></p>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="text-sm text-gray-600">
          <?php if($userEnrollment->submitted_at): ?>
            <p><strong>Gửi lúc:</strong> <?php echo e($userEnrollment->submitted_at->format('d/m/Y H:i')); ?></p>
          <?php endif; ?>
          <?php if($userEnrollment->start_time && $userEnrollment->end_time): ?>
            <p class="mt-1"><strong>Khung giờ mong muốn:</strong> <?php echo e($userEnrollment->start_time); ?> - <?php echo e($userEnrollment->end_time); ?></p>
          <?php endif; ?>
          <?php if($selectedDays): ?>
            <p class="mt-1"><strong>Các ngày:</strong> <?php echo e(implode(', ', array_map(fn ($day) => $days[$day] ?? $day, $selectedDays))); ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php if($userEnrollment->note): ?>
        <div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
          <strong><?php echo e($normalizedStatus === \App\Models\Enrollment::STATUS_REJECTED ? 'Lý do từ chối' : 'Ghi chú từ admin'); ?>:</strong> <?php echo e($userEnrollment->note); ?>

        </div>
      <?php endif; ?>
      <?php if($hasCourseAccess && $userEnrollment->course_id): ?>
        <div class="mt-4">
          <a href="<?php echo e(route('courses.show', $userEnrollment->course_id)); ?>" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 font-semibold text-white hover:bg-primary-dark transition">
            <i class="fas fa-graduation-cap"></i>
            Vào lớp học nội bộ
          </a>
        </div>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900 shadow-sm">
      <h2 class="text-lg font-semibold">Cách đăng ký khóa học này</h2>
      <p class="mt-2 text-sm leading-6">Bạn chỉ cần chọn khung giờ và các ngày có thể học. Sau khi gửi yêu cầu, admin sẽ xem lớp hiện có và xếp bạn vào lớp phù hợp nhất.</p>
    </div>
  <?php endif; ?>

  <div class="grid gap-6 lg:grid-cols-5">
    <div class="lg:col-span-3 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-bold text-gray-900">Đăng ký khóa học</h2>
      <p class="mt-2 text-sm text-gray-600">Điền thời gian bạn có thể học. Admin sẽ căn cứ vào thông tin này để sắp xếp lớp học thích hợp.</p>

      <?php if(!$user || $user->role !== 'hoc_vien'): ?>
        <div class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-5 text-sm text-blue-800">
          Bạn cần đăng nhập bằng tài khoản học viên trước khi gửi yêu cầu đăng ký.
          <div class="mt-4">
            <a href="<?php echo e(route('login')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 font-semibold text-white hover:bg-primary-dark transition">
              <i class="fas fa-right-to-bracket"></i>
              Đăng nhập để đăng ký
            </a>
          </div>
        </div>
      <?php elseif($hasCourseAccess): ?>
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-800">
          Bạn đã được admin xếp vào lớp học chính thức. Việc đổi lịch sẽ được xử lý ở các bước sau của hệ thống, nên biểu mẫu đăng ký tại đây đã được khóa lại.
        </div>
      <?php else: ?>
        <form method="post" action="<?php echo e(route('khoa-hoc.enroll', $subject->id)); ?>" class="mt-6 space-y-5">
          <?php echo csrf_field(); ?>
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-700">Giờ bắt đầu</label>
              <input type="time" name="start_time" required class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 focus:border-primary focus:outline-none" value="<?php echo e(old('start_time', $userEnrollment->start_time ?? '')); ?>" />
              <?php $__errorArgs = ['start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Giờ kết thúc</label>
              <input type="time" name="end_time" required class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 focus:border-primary focus:outline-none" value="<?php echo e(old('end_time', $userEnrollment->end_time ?? '')); ?>" />
              <?php $__errorArgs = ['end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Những ngày có thể học</label>
            <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-4">
              <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:border-primary transition">
                  <input type="checkbox" name="preferred_days[]" value="<?php echo e($day); ?>" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" <?php echo e(in_array($day, $selectedDays) ? 'checked' : ''); ?> />
                  <span><?php echo e($label); ?></span>
                </label>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php $__errorArgs = ['preferred_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark transition">
            <i class="fas fa-paper-plane text-sm"></i>
            <?php if($userEnrollment): ?>
              <?php if($normalizedStatus === \App\Models\Enrollment::STATUS_REJECTED): ?>
                Gửi lại yêu cầu đăng ký
              <?php elseif($normalizedStatus === \App\Models\Enrollment::STATUS_APPROVED): ?>
                Cập nhật thời gian mong muốn
              <?php else: ?>
                Cập nhật yêu cầu đăng ký
              <?php endif; ?>
            <?php else: ?>
              Gửi yêu cầu đăng ký khóa học
            <?php endif; ?>
          </button>
        </form>
      <?php endif; ?>
    </div>

    <div class="space-y-6 lg:col-span-2">
      <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900">Admin sẽ xử lý như thế nào?</h3>
        <ul class="mt-4 space-y-3 text-sm leading-6 text-gray-600">
          <li>1. Tiếp nhận khóa học bạn chọn và khung giờ mong muốn.</li>
          <li>2. Duyệt đăng ký trước khi xếp bạn vào lớp học nội bộ phù hợp.</li>
          <li>3. Phân giảng viên, chốt lịch và thông báo lại cho bạn.</li>
        </ul>
      </div>

      <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900">Các lớp hiện có của khóa này</h3>
        <?php if($subject->courses->isEmpty()): ?>
          <p class="mt-3 text-sm text-gray-500">Hiện chưa có lớp cố định nào. Bạn vẫn có thể gửi đăng ký để admin xếp lớp sau.</p>
        <?php else: ?>
          <div class="mt-4 space-y-3">
            <?php $__currentLoopData = $subject->courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div class="rounded-2xl border border-gray-200 p-4">
                <div class="font-semibold text-gray-900"><?php echo e($course->title); ?></div>
                <p class="mt-1 text-sm text-gray-600"><?php echo e($course->description ?? 'Lớp đang chờ admin cập nhật mô tả chi tiết.'); ?></p>
                <div class="mt-3 space-y-1 text-xs text-gray-500">
                  <p><strong>Lịch:</strong> <?php echo e($course->schedule ?: 'Chưa chốt'); ?></p>
                  <p><strong>Giảng viên:</strong> <?php echo e($course->teacher?->name ?? 'Chưa phân công'); ?></p>
                </div>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/subjects/show.blade.php ENDPATH**/ ?>