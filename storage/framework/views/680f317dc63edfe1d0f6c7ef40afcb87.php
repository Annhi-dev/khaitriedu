<?php
    $startTime = old('start_time', $courseTimeSlot->start_time ? substr((string) $courseTimeSlot->start_time, 0, 5) : '');
    $endTime = old('end_time', $courseTimeSlot->end_time ? substr((string) $courseTimeSlot->end_time, 0, 5) : '');
    $slotDate = old('slot_date', optional($courseTimeSlot->slot_date)->format('Y-m-d'));
    $registrationOpenAt = old('registration_open_at', optional($courseTimeSlot->registration_open_at)->format('Y-m-d\TH:i'));
    $registrationCloseAt = old('registration_close_at', optional($courseTimeSlot->registration_close_at)->format('Y-m-d\TH:i'));
?>

<?php if($errors->any()): ?>
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        <div class="font-semibold">Dữ liệu khung giờ chưa hợp lệ.</div>
        <ul class="mt-2 list-disc pl-5 space-y-1">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Khóa học public</label>
        <select name="subject_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
            <option value="">Chọn khóa học</option>
            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($subject->id); ?>" <?php if((string) old('subject_id', $courseTimeSlot->subject_id) === (string) $subject->id): echo 'selected'; endif; ?>>
                    <?php echo e($subject->name); ?><?php echo e($subject->category ? ' - ' . $subject->category->name : ''); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Giảng viên</label>
        <select name="teacher_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            <option value="">Chưa phân công</option>
            <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($teacher->id); ?>" <?php if((string) old('teacher_id', $courseTimeSlot->teacher_id) === (string) $teacher->id): echo 'selected'; endif; ?>><?php echo e($teacher->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Phòng học</label>
        <select name="room_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            <option value="">Chưa gán phòng</option>
            <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($room->id); ?>" <?php if((string) old('room_id', $courseTimeSlot->room_id) === (string) $room->id): echo 'selected'; endif; ?>>
                    <?php echo e($room->code); ?> - <?php echo e($room->name); ?> (<?php echo e($room->capacity); ?> chỗ)
                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Thứ học</label>
        <select name="day_of_week" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            <option value="">Chọn theo ngày cụ thể</option>
            <?php $__currentLoopData = $dayOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(old('day_of_week', $courseTimeSlot->day_of_week) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Ngày học cụ thể</label>
        <input type="date" name="slot_date" value="<?php echo e($slotDate); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái</label>
        <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(old('status', $courseTimeSlot->status) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Bắt đầu</label>
        <input type="time" name="start_time" value="<?php echo e($startTime); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Kết thúc</label>
        <input type="time" name="end_time" value="<?php echo e($endTime); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Mở đăng ký từ</label>
        <input type="datetime-local" name="registration_open_at" value="<?php echo e($registrationOpenAt); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Đóng đăng ký lúc</label>
        <input type="datetime-local" name="registration_close_at" value="<?php echo e($registrationCloseAt); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Số học viên tối thiểu</label>
        <input type="number" min="1" name="min_students" value="<?php echo e(old('min_students', $courseTimeSlot->min_students)); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Số học viên tối đa</label>
        <input type="number" min="1" name="max_students" value="<?php echo e(old('max_students', $courseTimeSlot->max_students)); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
        <textarea name="note" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500"><?php echo e(old('note', $courseTimeSlot->note)); ?></textarea>
    </div>
</div>

<div class="mt-6 flex flex-wrap items-center justify-end gap-3">
    <a href="<?php echo e(route('admin.course-time-slots.index')); ?>" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition">Quay lại</a>
    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
        <?php echo e($submitLabel); ?>

    </button>
</div>
<?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/course_time_slots/_form.blade.php ENDPATH**/ ?>