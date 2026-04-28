<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title', 'actions' => null, 'subtitle' => null, 'kicker' => 'Admin Console']));

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

foreach (array_filter((['title', 'actions' => null, 'subtitle' => null, 'kicker' => 'Admin Console']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
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

<section class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
    <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-cyan-100/60 blur-3xl"></div>
    <div class="absolute -bottom-14 right-8 h-36 w-36 rounded-full bg-slate-100 blur-3xl"></div>

    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">
                <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                <?php echo e($kicker); ?>

            </div>

            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl"><?php echo e($title); ?></h1>

            <?php if($subtitle): ?>
                <p class="mt-3 text-sm leading-6 text-slate-600"><?php echo e($subtitle); ?></p>
            <?php endif; ?>
        </div>

        <?php if($resolvedActions): ?>
            <div class="flex flex-wrap gap-3">
                <?php echo e($resolvedActions); ?>

            </div>
        <?php endif; ?>
    </div>
</section>
<?php /**PATH D:\XXamp\htdocs\khaitriedu-main\resources\views/components/quan_tri/tieu_de_trang.blade.php ENDPATH**/ ?>