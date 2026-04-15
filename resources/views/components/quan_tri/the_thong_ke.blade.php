@props(['label', 'value', 'icon', 'color' => 'cyan', 'note' => null])

@php
    $colorMap = [
        'cyan' => 'bg-cyan-50 text-cyan-600 ring-cyan-100',
        'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
        'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
        'violet' => 'bg-violet-50 text-violet-600 ring-violet-100',
        'slate' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];

    $iconClass = $colorMap[$color] ?? $colorMap['cyan'];
@endphp

<div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $label }}</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $value }}</p>
        </div>

        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $iconClass }}">
            <i class="{{ $icon }} text-lg"></i>
        </div>
    </div>

    @if ($note)
        <p class="mt-3 text-xs leading-5 text-slate-500">{{ $note }}</p>
    @endif
</div>
