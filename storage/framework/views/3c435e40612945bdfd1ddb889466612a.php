<?php
    $category = $category ?? null;
    $statusValue = old('status', $category->status ?? \App\Models\Category::STATUS_ACTIVE);
?>
<div class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(280px,1fr)]">
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Thông tin nhóm học</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Tên nhóm học</label>
                    <input id="name" name="name" value="<?php echo e(old('name', $category->name ?? '')); ?>" placeholder="Ví dụ: Ngoại ngữ - Tin học" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="md:col-span-2">
                    <label for="slug" class="mb-2 block text-sm font-medium text-slate-700">Slug</label>
                    <input id="slug" name="slug" value="<?php echo e(old('slug', $category->slug ?? '')); ?>" placeholder="Để trống nếu muốn tạo tự động theo tên" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    <p class="mt-2 text-xs text-slate-500">Slug dùng cho liên kết public. Nếu để trống, hệ thống sẽ tạo tự động từ tên nhóm học.</p>
                    <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label for="program" class="mb-2 block text-sm font-medium text-slate-700">Chương trình</label>
                    <input id="program" name="program" value="<?php echo e(old('program', $category->program ?? '')); ?>" placeholder="Tổng quát, thiếu nhi, luyện thi..." class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    <?php $__errorArgs = ['program'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label for="level" class="mb-2 block text-sm font-medium text-slate-700">Cấp độ</label>
                    <input id="level" name="level" value="<?php echo e(old('level', $category->level ?? '')); ?>" placeholder="Cơ bản, nâng cao, A1, B1..." class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    <?php $__errorArgs = ['level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Mô tả</label>
                    <textarea id="description" name="description" rows="6" placeholder="Mô tả ngắn về nhóm học, đối tượng học viên và định hướng nội dung" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100"><?php echo e(old('description', $category->description ?? '')); ?></textarea>
                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Hiển thị và trạng thái</h2>
            <div class="mt-5 space-y-4">
                <div>
                    <label for="status" class="mb-2 block text-sm font-medium text-slate-700">Trạng thái</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                        <option value="<?php echo e(\App\Models\Category::STATUS_ACTIVE); ?>" <?php if($statusValue === \App\Models\Category::STATUS_ACTIVE): echo 'selected'; endif; ?>>Hoạt động</option>
                        <option value="<?php echo e(\App\Models\Category::STATUS_INACTIVE); ?>" <?php if($statusValue === \App\Models\Category::STATUS_INACTIVE): echo 'selected'; endif; ?>>Ngừng hoạt động</option>
                    </select>
                    <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label for="order" class="mb-2 block text-sm font-medium text-slate-700">Thứ tự hiển thị</label>
                    <input id="order" name="order" type="number" min="0" value="<?php echo e(old('order', $category->order ?? 0)); ?>" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    <?php $__errorArgs = ['order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label for="image" class="mb-2 block text-sm font-medium text-slate-700">Ảnh đại diện</label>
                    <input id="image" name="image" type="file" accept="image/*" class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-cyan-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-cyan-700" />
                    <?php if(! empty($category?->image_path)): ?>
                        <p class="mt-2 text-xs text-slate-500">Đã có ảnh hiện tại cho nhóm học này.</p>
                    <?php endif; ?>
                    <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Lưu ý nghiệp vụ</h2>
            <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-600">
                <p>Nhóm học là lớp logic cha của nhiều khóa học.</p>
                <p class="mt-2">Nếu nhóm học đang có khóa học liên kết, admin chỉ nên ngừng hoạt động thay vì xóa cứng để tránh mất dữ liệu.</p>
            </div>
        </div>
    </div>
</div><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/study_groups/_form.blade.php ENDPATH**/ ?>