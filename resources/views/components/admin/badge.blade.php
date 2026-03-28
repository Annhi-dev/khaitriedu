@props(['type' => 'default', 'text' => ''])

@php
    $classes = match($type) {
        'success' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'warning' => 'bg-amber-100 text-amber-800 border-amber-200',
        'danger' => 'bg-rose-100 text-rose-800 border-rose-200',
        'info' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
        'default' => 'bg-slate-100 text-slate-700 border-slate-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $classes }}">
    {{ $text }}
</span>