<?php $__env->startSection('title', 'Chi tiết khóa học'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto mt-6">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?php echo e($subject->name); ?></h1>
            <p class="text-gray-600">Chỉnh sửa khóa học học viên sẽ đăng ký và theo dõi các lớp học đang thuộc khóa này.</p>
        </div>
        <a href="<?php echo e(route('admin.subjects')); ?>" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Quay lại</a>
    </div>

    <?php if(session('status')): ?><div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800"><?php echo e(session('status')); ?></div><?php endif; ?>
    <?php if(session('error')): ?><div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800"><?php echo e(session('error')); ?></div><?php endif; ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
            <?php if($subject->image): ?>
                <img src="<?php echo e(asset('storage/' . $subject->image)); ?>" alt="<?php echo e($subject->name); ?>" class="h-56 w-full rounded-2xl object-cover" />
            <?php else: ?>
                <div class="flex h-56 w-full items-center justify-center rounded-2xl bg-slate-100 text-slate-500">Chưa có ảnh đại diện</div>
            <?php endif; ?>
            <div class="mt-4 space-y-2 text-sm text-gray-600">
                <p><strong>Nhóm ngành:</strong> <?php echo e($subject->category?->name ?? 'Chưa phân nhóm'); ?></p>
                <p><strong>Học phí:</strong> <?php echo e(number_format($subject->price ?? 0, 0, ',', '.')); ?>đ</p>
                <p><strong>Số lớp hiện có:</strong> <?php echo e($subject->courses->count()); ?></p>
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900">Cập nhật khóa học</h2>
            <form method="post" action="<?php echo e(route('admin.subjects.update', $subject->id)); ?>" enctype="multipart/form-data" class="mt-4 grid gap-4">
                <?php echo csrf_field(); ?>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Tên khóa học</label>
                        <input name="name" value="<?php echo e($subject->name); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nhóm ngành</label>
                        <select name="category_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5">
                            <option value="">Chọn nhóm ngành</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php if($subject->category_id == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Học phí tham khảo</label>
                        <input name="price" type="number" step="0.01" value="<?php echo e($subject->price); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Ảnh đại diện mới</label>
                        <input type="file" name="image" accept="image/*" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Mô tả</label>
                    <textarea name="description" rows="4" class="w-full rounded-xl border border-gray-300 px-3 py-2.5"><?php echo e($subject->description); ?></textarea>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button class="rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark transition">Lưu thay đổi</button>
                </div>
            </form>
            <form method="post" action="<?php echo e(route('admin.subjects.delete', $subject->id)); ?>" class="mt-3" onsubmit="return confirm('Xóa khóa học này và toàn bộ lớp học bên trong?');">
                <?php echo csrf_field(); ?>
                <button class="rounded-xl bg-red-600 px-5 py-3 font-semibold text-white hover:bg-red-700 transition">Xóa khóa học</button>
            </form>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900">Các lớp học đang thuộc khóa này</h2>
        <?php if($subject->courses->isEmpty()): ?>
            <p class="mt-3 text-sm text-gray-500">Chưa có lớp học nào. Bạn có thể vào mục quản lý lớp học để tạo lớp mới.</p>
        <?php else: ?>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <?php $__currentLoopData = $subject->courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-2xl border border-gray-200 p-4">
                        <div class="font-semibold text-gray-900"><?php echo e($course->title); ?></div>
                        <p class="mt-1 text-sm text-gray-600"><?php echo e($course->description ?? 'Chưa có mô tả cho lớp này.'); ?></p>
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\admin\subject\show.blade.php ENDPATH**/ ?>