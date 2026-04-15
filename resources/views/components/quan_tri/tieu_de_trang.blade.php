@props(['title', 'actions' => null, 'subtitle' => null, 'kicker' => 'Admin Console'])
@php
    $resolvedActions = $actions;

    if (! $resolvedActions && isset($slot) && trim((string) $slot) !== '') {
        $resolvedActions = $slot;
    }
@endphp

<section class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
    <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-cyan-100/60 blur-3xl"></div>
    <div class="absolute -bottom-14 right-8 h-36 w-36 rounded-full bg-slate-100 blur-3xl"></div>

    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">
                <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                {{ $kicker }}
            </div>

            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">{{ $title }}</h1>

            @if ($subtitle)
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $subtitle }}</p>
            @endif
        </div>

        @if ($resolvedActions)
            <div class="flex flex-wrap gap-3">
                {{ $resolvedActions }}
            </div>
        @endif
    </div>
</section>
