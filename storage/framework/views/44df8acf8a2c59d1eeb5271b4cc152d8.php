<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['type' => 'default', 'text' => '']));

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

foreach (array_filter((['type' => 'default', 'text' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = match($type) {
        'success' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'warning' => 'bg-amber-100 text-amber-800 border-amber-200',
        'danger' => 'bg-rose-100 text-rose-800 border-rose-200',
        'info' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
        'default' => 'bg-slate-100 text-slate-700 border-slate-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
?>

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?php echo e($classes); ?>">
    <?php echo e($text); ?>

</span><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/components/admin/badge.blade.php ENDPATH**/ ?>