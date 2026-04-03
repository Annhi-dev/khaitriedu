@props(['title', 'subtitle' => null, 'actions' => null])
@php
    $resolvedActions = $actions;

    if (! $resolvedActions && isset($slot) && trim((string) $slot) !== '') {
        $resolvedActions = $slot;
    }
@endphp

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-slate-500 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if($resolvedActions)
        <div class="flex gap-2">
            {{ $resolvedActions }}
        </div>
    @endif
</div>
