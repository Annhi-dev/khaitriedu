<?php $__env->startSection('title', 'Quản lý người dùng'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
  <div class="flex justify-between items-center mb-4">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Quản lý người dùng</h1>
      <p class="text-gray-600">Thêm, sửa, xoá học viên/giảng viên/admin.</p>
    </div>
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn rounded-lg border border-gray-300 px-4 py-2">Quay lại dashboard</a>
  </div>

  <?php if(session('status')): ?><div class="alert alert-success mb-3"><?php echo e(session('status')); ?></div><?php endif; ?>
  <?php if(session('error')): ?><div class="alert alert-danger mb-3"><?php echo e(session('error')); ?></div><?php endif; ?>
  <?php if($errors->any()): ?>
    <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded mb-3">
      <ul class="list-disc pl-5">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="grid lg:grid-cols-2 gap-4">
    <div class="card bg-white p-4 rounded-xl shadow-sm">
      <h3 class="font-semibold mb-2">Thêm người dùng mới</h3>
      <form method="post" action="<?php echo e(route('admin.users.create')); ?>">
        <?php echo csrf_field(); ?>
        <div class="mb-2"><input name="name" placeholder="Tên" required class="w-full border rounded-md px-3 py-2" /></div>
        <div class="mb-2"><input name="username" placeholder="Tên đăng nhập" required class="w-full border rounded-md px-3 py-2" /></div>
        <div class="mb-2"><input name="email" placeholder="Email" type="email" required class="w-full border rounded-md px-3 py-2" /></div>
        <div class="mb-2"><input name="password" placeholder="Mật khẩu" type="password" required class="w-full border rounded-md px-3 py-2" /></div>
        <div class="mb-2"><select name="role" required class="w-full border rounded-md px-3 py-2"><option value="hoc_vien">Học viên</option><option value="giang_vien">Giảng viên</option><option value="admin">Admin</option></select></div>
        <button class="btn bg-primary text-white rounded-xl px-3 py-2">Tạo người dùng</button>
      </form>
    </div>

    <div class="card bg-white p-4 rounded-xl shadow-sm overflow-x-auto">
      <h3 class="font-semibold mb-2">Danh sách người dùng</h3>
      <table class="w-full text-left text-sm border-collapse">
        <thead class="bg-gray-50">
          <tr><th class="border p-2">ID</th><th class="border p-2">Tên</th><th class="border p-2">Email</th><th class="border p-2">Role</th><th class="border p-2">Hành động</th></tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="odd:bg-white even:bg-gray-50">
              <td class="border p-2"><?php echo e($u->id); ?></td>
              <td class="border p-2"><?php echo e($u->name); ?></td>
              <td class="border p-2"><?php echo e($u->email); ?></td>
              <td class="border p-2"><?php echo e(ucfirst(str_replace('_', ' ', $u->role))); ?></td>
              <td class="border p-2 flex gap-1">
                <a href="<?php echo e(route('admin.user.show', $u->id)); ?>" class="px-2 py-1 text-xs bg-blue-500 text-white rounded">Sửa</a>
                <form method="post" action="<?php echo e(route('admin.users.delete', $u->id)); ?>" onsubmit="return confirm('Xóa người dùng?');">
                  <?php echo csrf_field(); ?>
                  <button type="submit" class="px-2 py-1 text-xs bg-red-500 text-white rounded">Xóa</button>
                </form>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\admin\users.blade.php ENDPATH**/ ?>