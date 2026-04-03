<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title', 'subtitle' => null, 'actions' => null]));

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

foreach (array_filter((['title', 'subtitle' => null, 'actions' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $resolvedActions = $actions;

    if (! $resolvedActions && isset($slot) && trim((string) $slot) !== '') {
        $resolvedActions = $slot;
    }
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800"><?php echo e($title); ?></h1>
        <?php if($subtitle): ?>
            <p class="text-slate-500 mt-1"><?php echo e($subtitle); ?></p>
        <?php endif; ?>
    </div>
    <?php if($resolvedActions): ?>
        <div class="flex gap-2">
            <?php echo e($resolvedActions); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH D:\XXamp\htdocs\khaitriedu-main\resources\views/components/admin/page-header.blade.php ENDPATH**/ ?>