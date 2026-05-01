<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['paginator', 'label' => 'kết quả']));

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

foreach (array_filter((['paginator', 'label' => 'kết quả']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($paginator->hasPages()): ?>
    <div class="rounded-3xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Phân trang</p>
                <p class="mt-2 text-sm text-slate-600">
                    Hiển thị
                    <span class="font-semibold text-slate-900"><?php echo e(number_format($paginator->firstItem() ?? 0)); ?></span>
                    -
                    <span class="font-semibold text-slate-900"><?php echo e(number_format($paginator->lastItem() ?? 0)); ?></span>
                    trên
                    <span class="font-semibold text-slate-900"><?php echo e(number_format($paginator->total())); ?></span>
                    <?php echo e($label); ?>

                </p>
            </div>

            <div class="overflow-x-auto">
                <?php echo e($paginator->onEachSide(1)->links()); ?>

            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH D:\XXamp\htdocs\khaitriedu-main\resources\views/components/quan_tri/phan_trang.blade.php ENDPATH**/ ?>