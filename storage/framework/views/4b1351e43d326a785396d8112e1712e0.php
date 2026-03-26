<?php $__env->startSection('title', 'Quản lý nhóm ngành'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
  <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Quản lý nhóm ngành</h1>
      <p class="text-gray-600">Nhóm ngành dùng để gom các khóa học có cùng lĩnh vực đào tạo.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="<?php echo e(route('admin.subjects')); ?>" class="rounded-lg border border-primary px-3 py-2 text-sm font-medium text-primary hover:bg-primary-light/20 transition">Khóa học</a>
      <a href="<?php echo e(route('admin.courses')); ?>" class="rounded-lg border border-primary px-3 py-2 text-sm font-medium text-primary hover:bg-primary-light/20 transition">Lớp học</a>
      <a href="<?php echo e(route('admin.dashboard')); ?>" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Dashboard</a>
    </div>
  </div>

  <?php if(session('status')): ?><div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800"><?php echo e(session('status')); ?></div><?php endif; ?>
  <?php if(session('error')): ?><div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800"><?php echo e(session('error')); ?></div><?php endif; ?>

  <div class="mb-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-gray-900">Thêm nhóm ngành</h2>
    <form method="post" action="<?php echo e(route('admin.categories.create')); ?>" enctype="multipart/form-data" class="mt-4 grid gap-4 lg:grid-cols-2">
      <?php echo csrf_field(); ?>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Tên nhóm ngành</label>
        <input name="name" placeholder="Ví dụ: Ngoại ngữ - Tin học" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Slug</label>
        <input name="slug" placeholder="ngoai-ngu-tin-hoc" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Thứ tự hiển thị</label>
        <input name="order" type="number" placeholder="0" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Ảnh đại diện</label>
        <input name="image" type="file" accept="image/*" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
      </div>
      <div class="lg:col-span-2">
        <label class="mb-1 block text-sm font-medium text-gray-700">Mô tả</label>
        <textarea name="description" rows="4" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" placeholder="Mô tả ngắn về nhóm ngành"></textarea>
      </div>
      <div class="lg:col-span-2">
        <button class="rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark transition">Thêm nhóm ngành</button>
      </div>
    </form>
  </div>

  <div class="space-y-4">
    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900"><?php echo e($category->name); ?></h3>
            <div class="mt-2 space-y-1 text-sm text-gray-600">
              <p><strong>Slug:</strong> <?php echo e($category->slug); ?></p>
              <p><strong>Thứ tự:</strong> <?php echo e($category->order); ?></p>
            </div>
            <p class="mt-3 text-sm text-gray-600"><?php echo e($category->description ?? 'Chưa có mô tả cho nhóm ngành này.'); ?></p>
          </div>
          <div class="flex gap-2">
            <form method="post" action="<?php echo e(route('admin.categories.delete', $category->id)); ?>" onsubmit="return confirm('Xóa nhóm ngành này?');">
              <?php echo csrf_field(); ?>
              <button class="rounded-xl bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 transition">Xóa</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\admin\categories.blade.php ENDPATH**/ ?>