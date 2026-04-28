<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['session']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['session']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($session->has('status')): ?>
    <div class="mb-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-start gap-3 animate-fade-in-down">
        <i class="fas fa-check-circle mt-0.5"></i>
        <span><?php echo e($session->get('status')); ?></span>
    </div>
<?php endif; ?>
<?php if($session->has('error')): ?>
    <div class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 flex items-start gap-3 animate-fade-in-down">
        <i class="fas fa-exclamation-circle mt-0.5"></i>
        <span><?php echo e($session->get('error')); ?></span>
    </div>
<?php endif; ?>
<?php if($session->has('warning')): ?>
    <div class="mb-4 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 flex items-start gap-3 animate-fade-in-down">
        <i class="fas fa-triangle-exclamation mt-0.5"></i>
        <span><?php echo e($session->get('warning')); ?></span>
    </div>
<?php endif; ?>
<?php if($errors->any()): ?>
    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-700 animate-fade-in-down shadow-sm">
        <div class="flex items-start gap-3">
            <i class="fas fa-circle-exclamation mt-0.5"></i>
            <div class="min-w-0">
                <p class="font-semibold">Dữ liệu chưa hợp lệ</p>
                <p class="mt-1 text-sm text-rose-600">Một hoặc nhiều trường chưa đúng định dạng. Các lỗi chi tiết nằm bên dưới.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH D:\XXamp\htdocs\khaitriedu-main\resources\views/components/quan_tri/thong_bao.blade.php ENDPATH**/ ?>