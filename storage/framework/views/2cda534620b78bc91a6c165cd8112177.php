<?php if($errors->any()): ?>
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        <div class="font-semibold">Dữ liệu chưa hợp lệ.</div>
        <ul class="mt-2 list-disc pl-5 space-y-1">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Loại phòng</label>
        <select name="type" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            <option value="theory" <?php if(old('type', $room->type) === 'theory'): echo 'selected'; endif; ?>>Phòng lý thuyết</option>
            <option value="practice" <?php if(old('type', $room->type) === 'practice'): echo 'selected'; endif; ?>>Phòng thực hành</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Tên phòng</label>
        <input type="text" name="name" value="<?php echo e(old('name', $room->name)); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Vị trí</label>
        <input type="text" name="location" value="<?php echo e(old('location', $room->location)); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sức chứa</label>
        <input type="number" min="1" name="capacity" value="<?php echo e(old('capacity', $room->capacity)); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
        <textarea name="note" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500"><?php echo e(old('note', $room->note)); ?></textarea>
    </div>
</div>

<div class="mt-6 flex flex-wrap items-center justify-end gap-3">
    <a href="<?php echo e(route('admin.rooms.index')); ?>" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition">Quay lại</a>
    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
        <?php echo e($submitLabel); ?>

    </button>
</div>
<?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/rooms/_form.blade.php ENDPATH**/ ?>