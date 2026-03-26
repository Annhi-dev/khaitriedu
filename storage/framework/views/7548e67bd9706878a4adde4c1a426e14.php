<?php $__env->startSection('title', 'Chi tiết người dùng'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto">
  <div class="flex justify-between items-center mb-4">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Chi tiết người dùng</h1>
      <p class="text-gray-600">Sửa thông tin người dùng.</p>
    </div>
    <a href="<?php echo e(route('admin.users')); ?>" class="btn rounded-lg border border-gray-300 px-4 py-2">Quay lại danh sách</a>
  </div>

  <?php if(session('status')): ?><div class="alert alert-success mb-3"><?php echo e(session('status')); ?></div><?php endif; ?>
  <?php if(session('error')): ?><div class="alert alert-danger mb-3"><?php echo e(session('error')); ?></div><?php endif; ?>

  <div class="card bg-white p-4 rounded-xl shadow-sm">
    <form method="post" action="<?php echo e(route('admin.users.update', $target->id)); ?>">
      <?php echo csrf_field(); ?>
      <div class="mb-3">
        <label class="block text-sm font-medium">Tên</label>
        <input name="name" value="<?php echo e(old('name', $target->name)); ?>" required class="w-full border rounded-md px-3 py-2" />
      </div>
      <div class="mb-3">
        <label class="block text-sm font-medium">Email</label>
        <input value="<?php echo e($target->email); ?>" disabled class="w-full border rounded-md px-3 py-2 bg-gray-100" />
      </div>
      <div class="mb-3">
        <label class="block text-sm font-medium">Role</label>
        <select name="role" required class="w-full border rounded-md px-3 py-2">
          <option value="hoc_vien" <?php if($target->role=='hoc_vien'): ?> selected <?php endif; ?>>Học viên</option>
          <option value="giang_vien" <?php if($target->role=='giang_vien'): ?> selected <?php endif; ?>>Giảng viên</option>
          <option value="admin" <?php if($target->role=='admin'): ?> selected <?php endif; ?>>Admin</option>
        </select>
      </div>
      <button type="submit" class="btn bg-blue-600 text-white rounded-xl px-3 py-2">Cập nhật</button>
    </form>
    <form method="post" action="<?php echo e(route('admin.users.delete', $target->id)); ?>" onsubmit="return confirm('Xóa người dùng này?');" class="mt-3">
      <?php echo csrf_field(); ?>
      <button type="submit" class="btn bg-red-600 text-white rounded-xl px-3 py-2">Xóa người dùng</button>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\admin\user\show.blade.php ENDPATH**/ ?>