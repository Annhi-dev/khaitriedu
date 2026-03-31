<?php
    $subject = $subject ?? null;
    $selectedCategory = $selectedCategory ?? null;
    $statusValue = old('status', $subject->status ?? \App\Models\Subject::STATUS_OPEN);
    $selectedCategoryId = (string) old('category_id', $subject->category_id ?? $selectedCategory?->id ?? request('category_id', ''));
    $returnToCategoryId = old('return_to_category_id', $returnToCategoryId ?? null);
?>
<?php if($returnToCategoryId): ?>
    <input type="hidden" name="return_to_category_id" value="<?php echo e($returnToCategoryId); ?>" />
<?php endif; ?>
<div class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(320px,1fr)]">
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Thông tin khóa học</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Tên khóa học</label>
                    <input id="name" name="name" value="<?php echo e(old('name', $subject->name ?? '')); ?>" placeholder="Ví dụ: Tin học văn phòng" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label for="category_id" class="mb-2 block text-sm font-medium text-slate-700">Nhóm học cha</label>
                    <select id="category_id" name="category_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                        <option value="">Chưa gắn nhóm học</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>" <?php if($selectedCategoryId === (string) $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php if($selectedCategory): ?>
                        <p class="mt-2 text-xs text-cyan-600">Đang tạo khóa học trong nhóm <?php echo e($selectedCategory->name); ?>.</p>
                    <?php endif; ?>
                    <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-medium text-slate-700">Trạng thái</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                        <option value="<?php echo e(\App\Models\Subject::STATUS_DRAFT); ?>" <?php if($statusValue === \App\Models\Subject::STATUS_DRAFT): echo 'selected'; endif; ?>>Nháp</option>
                        <option value="<?php echo e(\App\Models\Subject::STATUS_OPEN); ?>" <?php if($statusValue === \App\Models\Subject::STATUS_OPEN): echo 'selected'; endif; ?>>Đang mở</option>
                        <option value="<?php echo e(\App\Models\Subject::STATUS_CLOSED); ?>" <?php if($statusValue === \App\Models\Subject::STATUS_CLOSED): echo 'selected'; endif; ?>>Đóng đăng ký</option>
                        <option value="<?php echo e(\App\Models\Subject::STATUS_ARCHIVED); ?>" <?php if($statusValue === \App\Models\Subject::STATUS_ARCHIVED): echo 'selected'; endif; ?>>Lưu trữ</option>
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
                    <label for="price" class="mb-2 block text-sm font-medium text-slate-700">Học phí tham khảo</label>
                    <input id="price" name="price" type="number" min="0" step="0.01" value="<?php echo e(old('price', $subject->price ?? 0)); ?>" placeholder="Ví dụ: 1500000" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label for="duration" class="mb-2 block text-sm font-medium text-slate-700">Thời gian học (dự kiến)</label>
                    <select id="duration" name="duration" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                        <option value="">-- Chọn thời gian học --</option>
                        <?php $__currentLoopData = [1, 2, 3, 4, 6, 12, 18, 24]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($val); ?>" <?php if(old('duration', $subject->duration ?? '') == $val): echo 'selected'; endif; ?>><?php echo e($val); ?> tháng</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['duration'];
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
                    <textarea id="description" name="description" rows="6" placeholder="Mô tả giá trị khóa học, đối tượng học viên, kết quả đầu ra" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100"><?php echo e(old('description', $subject->description ?? '')); ?></textarea>
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
            <h2 class="text-lg font-semibold text-slate-900">Hiển thị</h2>
            <div class="mt-5 space-y-4">
                <div>
                    <label for="image" class="mb-2 block text-sm font-medium text-slate-700">Ảnh đại diện</label>
                    <input id="image" name="image" type="file" accept="image/*" class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-cyan-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-cyan-700" />
                    <?php if(! empty($subject?->image)): ?>
                        <p class="mt-2 text-xs text-slate-500">Khóa học này đã có ảnh đại diện hiện tại.</p>
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
                <p>Khóa học ở đây là màn public để học viên đăng ký.</p>
                <p class="mt-2">Giảng viên chính thức vẫn được phân ở cấp lớp học nội bộ để admin chủ động xếp lịch và phân lớp ở các phase sau.</p>
            </div>
        </div>
    </div>
</div><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/subject/_form.blade.php ENDPATH**/ ?>