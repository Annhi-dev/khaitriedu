<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label', 'value', 'icon', 'color' => 'cyan', 'trend' => null, 'trendValue' => null]));

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

foreach (array_filter((['label', 'value', 'icon', 'color' => 'cyan', 'trend' => null, 'trendValue' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $colorMap = [
        'cyan' => 'bg-cyan-50 text-cyan-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
        'violet' => 'bg-violet-50 text-violet-600',
        'slate' => 'bg-slate-100 text-slate-600',
    ];
    $iconBg = $colorMap[$color] ?? $colorMap['cyan'];
?>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500"><?php echo e($label); ?></p>
            <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo e($value); ?></p>
            <?php if($trend): ?>
                <p class="text-xs text-slate-400 mt-1">
                    <?php echo e($trend); ?>

                    <?php if($trendValue): ?>
                        <span class="font-medium text-emerald-600"><?php echo e($trendValue); ?></span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
        <div class="w-12 h-12 rounded-xl <?php echo e($iconBg); ?> flex items-center justify-center">
            <i class="<?php echo e($icon); ?> text-xl"></i>
        </div>
    </div>
</div><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/components/admin/stat-card.blade.php ENDPATH**/ ?>