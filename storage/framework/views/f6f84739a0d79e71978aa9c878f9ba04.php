<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['route', 'searchPlaceholder' => 'Tìm kiếm...', 'statuses' => [], 'additionalFilters' => null]));

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

foreach (array_filter((['route', 'searchPlaceholder' => 'Tìm kiếm...', 'statuses' => [], 'additionalFilters' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<form method="get" action="<?php echo e($route); ?>" class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tìm kiếm</label>
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="<?php echo e($searchPlaceholder); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
        </div>
        <?php if(count($statuses)): ?>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái</label>
            <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
                <option value="">Tất cả</option>
                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if(request('status') == $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <?php endif; ?>
        <?php if($additionalFilters): ?>
            <?php echo e($additionalFilters); ?>

        <?php endif; ?>
        <div class="flex items-end gap-2">
            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Lọc</button>
            <a href="<?php echo e($route); ?>" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition">Xóa lọc</a>
        </div>
    </div>
</form><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/components/admin/filter-bar.blade.php ENDPATH**/ ?>