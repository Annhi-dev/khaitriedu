
<?php $__env->startSection('title',$course->title); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">
  <div class="mb-6"><a href="<?php echo e(route('courses.index')); ?>" class="inline-flex items-center gap-2 text-primary hover:gap-3 transition font-semibold"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a></div>
  
  <!-- Premium Header Card -->
  <div class="relative mb-8">
    <!-- Gradient Background -->
    <div class="absolute inset-0 bg-gradient-to-r from-primary via-purple-500 to-primary rounded-3xl opacity-10 blur-xl"></div>
    
    <div class="relative bg-gradient-to-br from-blue-50 to-purple-50 rounded-3xl p-8 shadow-xl border border-primary/10">
      <!-- Premium Badge -->
      <div class="inline-block mb-4">
        <span class="inline-flex items-center gap-2 bg-gradient-to-r from-yellow-400 to-yellow-600 text-white px-4 py-1.5 rounded-full font-bold text-sm shadow-lg">
          <i class="fas fa-crown"></i> KHÓA HỌC PRO
        </span>
      </div>

      <!-- Title -->
      <h1 class="text-4xl font-black text-gray-800 mb-3"><?php echo e($course->title); ?></h1>
      
      <!-- Meta Info Grid -->
      <div class="grid md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-200">
        <div class="text-center">
          <div class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-primary to-primary-dark"><?php echo e($course->enrollments->count()); ?></div>
          <div class="text-sm text-gray-600 mt-1"><i class="fas fa-users mr-1"></i> Học viên</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-primary to-primary-dark"><?php echo e($course->modules->count()); ?></div>
          <div class="text-sm text-gray-600 mt-1"><i class="fas fa-book mr-1"></i> Module</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-primary to-primary-dark">4.8⭐</div>
          <div class="text-sm text-gray-600 mt-1"><i class="fas fa-star mr-1"></i> Rating</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-primary to-primary-dark"><?php echo e($course->schedule ?? '1 tháng'); ?></div>
          <div class="text-sm text-gray-600 mt-1"><i class="fas fa-calendar mr-1"></i> Thời gian</div>
        </div>
      </div>

      <!-- Descrip & Teacher -->
      <div class="mt-6 pt-6 border-t border-gray-200 grid md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
          <p class="text-gray-700 leading-relaxed"><?php echo e($course->description ?? 'Khóa học chất lượng cao với nội dung cập nhật liên tục.'); ?></p>
        </div>
        <?php if($course->teacher): ?>
        <div class="bg-white rounded-xl p-4 shadow-md">
          <p class="text-xs text-gray-600 mb-2 font-semibold uppercase">Giảng viên</p>
          <div class="flex items-center gap-3">
            <img src="https://randomuser.me/api/portraits/men/<?php echo e($course->teacher->id % 50); ?>.jpg" class="w-12 h-12 rounded-full object-cover shadow-md">
            <div>
              <div class="font-bold text-gray-800"><?php echo e($course->teacher->name); ?></div>
              <div class="text-xs text-primary">Chuyên gia</div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Modules Section -->
  <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
    <h3 class="font-black text-2xl mb-6 flex items-center gap-3"><i class="fas fa-book-open text-primary"></i> Nội dung khóa học</h3>
    <?php if($course->modules->isEmpty()): ?>
    <div class="text-center py-8 bg-gray-50 rounded-lg">
      <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
      <p class="text-gray-600">Chưa có module. Vui lòng quay lại sau.</p>
    </div>
    <?php else: ?>
      <div class="space-y-3">
      <?php $__currentLoopData = $course->modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="border-2 border-gray-100 hover:border-primary rounded-xl p-4 transition group">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary to-primary-dark text-white flex items-center justify-center font-bold"><?php echo e($m->position ?? $loop->iteration); ?></div>
            <div class="flex-1">
              <div class="font-bold text-lg text-gray-800 group-hover:text-primary transition"><?php echo e($m->title); ?></div>
              <p class="text-sm text-gray-600 mt-1"><?php echo e($m->content ?? 'Nội dung module'); ?></p>
            </div>
            <i class="fas fa-chevron-right text-gray-300 group-hover:text-primary transition"></i>
          </div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Enrollment Card -->
      <?php
        $user = \App\Models\User::find(session('user_id'));
        $userEnrollment = $user ? \App\Models\Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->first() : null;
        $selectedDays = $userEnrollment ? json_decode($userEnrollment->preferred_days, true) ?? [] : [];
        $weekdays = ['Monday' => 'Thứ 2', 'Tuesday' => 'Thứ 3', 'Wednesday' => 'Thứ 4', 'Thursday' => 'Thứ 5', 'Friday' => 'Thứ 6', 'Saturday' => 'Thứ 7', 'Sunday' => 'Chủ nhật'];
      ?>
      
      <?php if($userEnrollment): ?>
        <div class="bg-blue-50 border-l-4 border-l-blue-500 p-3 rounded mb-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-blue-900">Trạng thái yêu cầu:</p>
              <div class="mt-1">
                <?php if($userEnrollment->status === 'pending'): ?>
                  <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded text-sm font-medium">⏳ Đang chờ duyệt</span>
                <?php elseif($userEnrollment->status === 'confirmed'): ?>
                  <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded text-sm font-medium">✓ Đã được duyệt</span>
                <?php else: ?>
                  <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded text-sm font-medium">✗ Bị từ chối</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="text-right text-sm text-gray-600">
              <?php if($userEnrollment->submitted_at): ?>
                <p>Gửi: <?php echo e(\Carbon\Carbon::parse($userEnrollment->submitted_at)->format('d/m/Y H:i')); ?></p>
              <?php endif; ?>
              <?php if($userEnrollment->assignedTeacher): ?>
                <p class="mt-1">Giáo viên: <strong><?php echo e($userEnrollment->assignedTeacher->name); ?></strong></p>
              <?php endif; ?>
            </div>
          </div>
          
          <?php if($userEnrollment->status === 'rejected' && $userEnrollment->note): ?>
          <div class="mt-3 pt-3 border-t border-red-200 bg-red-50 p-2 rounded">
            <p class="text-sm text-red-800"><strong>💬 Lý do từ chối:</strong></p>
            <p class="text-sm text-red-700 mt-1"><?php echo e($userEnrollment->note); ?></p>
          </div>
          <?php endif; ?>
          
          <?php if($userEnrollment->start_time): ?>
            <div class="mt-3 pt-3 border-t border-blue-200">
              <p class="text-sm text-gray-700">
                <strong>Giờ học:</strong> <?php echo e($userEnrollment->start_time); ?> - <?php echo e($userEnrollment->end_time); ?><br>
                <strong>Các thứ:</strong> <?php echo e(implode(', ', array_map(fn($d) => $weekdays[$d] ?? $d, $selectedDays))); ?>

              </p>
            </div>
          <?php endif; ?>
        </div>
        
        <?php if($userEnrollment->status === 'rejected'): ?>
        <div class="bg-orange-50 border border-orange-200 p-3 rounded mb-3">
          <p class="text-sm text-orange-800">
            <strong>⚠️ Yêu cầu của bạn bị từ chối</strong><br>
            Vui lòng kiểm tra lý do trên và cập nhật thông tin dưới đây, sau đó gửi lại.
          </p>
        </div>
        <?php elseif($userEnrollment->status !== 'confirmed'): ?>
        <p class="text-sm text-gray-600 mb-3 bg-gray-50 p-2 rounded">
          ✏️ Bạn có thể cập nhật lịch học yêu cầu dưới đây. Khi cập nhật, admin sẽ xem xét lại yêu cầu.
        </p>
        <?php else: ?>
        <p class="text-sm text-gray-600 mb-3 bg-gray-50 p-2 rounded">
          ✓ Yêu cầu của bạn đã được duyệt. Bạn có thể cập nhật lịch nếu cần thiết.
        </p>
        <?php endif; ?>
      <?php endif; ?>
      
      <?php if(session('status')): ?><div class="bg-green-100 text-green-800 p-2 rounded mb-3"><?php echo e(session('status')); ?></div><?php endif; ?>
      
      <form method="post" action="<?php echo e(route('courses.enroll', $course->id)); ?>" class="space-y-4">
        <?php echo csrf_field(); ?>
        
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">⏱️ Giờ bắt đầu:</label>
            <input type="time" name="start_time" required value="<?php echo e(old('start_time', $userEnrollment->start_time ?? '')); ?>" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent" />
            <?php $__errorArgs = ['start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">⏱️ Giờ kết thúc:</label>
            <input type="time" name="end_time" required value="<?php echo e(old('end_time', $userEnrollment->end_time ?? '')); ?>" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent" />
            <?php $__errorArgs = ['end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">📅 Các thứ có thể học trong tuần:</label>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <?php $__currentLoopData = $weekdays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" name="preferred_days[]" value="<?php echo e($day); ?>" 
                <?php echo e(in_array($day, $selectedDays) || (old('preferred_days') && in_array($day, old('preferred_days'))) ? 'checked' : ''); ?> 
                class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" />
              <span class="text-sm text-gray-700 font-medium"><?php echo e($label); ?></span>
            </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
          <?php $__errorArgs = ['preferred_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        
        <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-medium py-2 rounded-lg transition">
          <?php if($userEnrollment): ?>
            <?php if($userEnrollment->status === 'rejected'): ?>
              🔄 Gửi lại yêu cầu
            <?php elseif($userEnrollment->status === 'confirmed'): ?>
              ✏️ Cập nhật lịch
            <?php else: ?>
              ✏️ Cập nhật yêu cầu
            <?php endif; ?>
          <?php else: ?>
            📤 Gửi đăng ký
          <?php endif; ?>
        </button>
      </form>
    </div>

    <?php
      $enrollment = $user ? \App\Models\Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->where('status', 'confirmed')->first() : null;
      $review = $enrollment ? \App\Models\Review::where('user_id', $user->id)->where('course_id', $course->id)->first() : null;
    ?>

    <?php if($enrollment): ?>
    <div class="mt-4 border-t pt-3">
      <h4 class="font-semibold mb-2">Đánh giá khóa học</h4>
      <?php if($review): ?>
        <div class="bg-gray-50 p-3 rounded">
          <p><strong>Đánh giá của bạn:</strong> <?php echo e(str_repeat('⭐', $review->rating)); ?> (<?php echo e($review->rating); ?>/5)</p>
          <?php if($review->comment): ?><p><strong>Nhận xét:</strong> <?php echo e($review->comment); ?></p><?php endif; ?>
        </div>
      <?php else: ?>
        <form method="post" action="<?php echo e(route('courses.review', $course->id)); ?>" class="space-y-3">
          <?php echo csrf_field(); ?>
          <div>
            <label class="block text-sm font-medium mb-1">Đánh giá (1-5 sao):</label>
            <select name="rating" required class="border rounded px-2 py-2 w-full">
              <option value="">Chọn số sao</option>
              <option value="5">⭐⭐⭐⭐⭐ (5 sao)</option>
              <option value="4">⭐⭐⭐⭐ (4 sao)</option>
              <option value="3">⭐⭐⭐ (3 sao)</option>
              <option value="2">⭐⭐ (2 sao)</option>
              <option value="1">⭐ (1 sao)</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Nhận xét (tùy chọn):</label>
            <textarea name="comment" rows="3" class="border rounded px-2 py-2 w-full" placeholder="Chia sẻ trải nghiệm học tập của bạn..."></textarea>
          </div>
          <button type="submit" class="btn bg-primary text-white rounded px-3 py-2">Gửi đánh giá</button>
        </form>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="mt-4 border-t pt-3">
      <h4 class="font-semibold mb-2">Đánh giá từ học viên khác</h4>
      <?php $reviews = \App\Models\Review::where('course_id', $course->id)->with('user')->get(); ?>
      <?php if($reviews->isEmpty()): ?>
        <p class="text-gray-500">Chưa có đánh giá nào.</p>
      <?php else: ?>
        <div class="space-y-3">
          <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="border rounded p-3">
              <div class="flex justify-between items-start">
                <div>
                  <strong><?php echo e($r->user->name); ?></strong>
                  <div class="text-yellow-500"><?php echo e(str_repeat('⭐', $r->rating)); ?></div>
                </div>
                <small class="text-gray-500"><?php echo e($r->created_at->format('d/m/Y')); ?></small>
              </div>
              <?php if($r->comment): ?><p class="mt-1"><?php echo e($r->comment); ?></p><?php endif; ?>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="mt-3 p-2 bg-gray-50 rounded">
          <strong>Đánh giá trung bình: <?php echo e(number_format($course->averageRating(), 1)); ?>/5 ⭐</strong>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/courses/show.blade.php ENDPATH**/ ?>