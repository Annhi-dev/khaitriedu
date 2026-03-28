@props(['label', 'value', 'icon', 'color' => 'cyan', 'trend' => null, 'trendValue' => null])

@php
    $colorMap = [
        'cyan' => 'bg-cyan-50 text-cyan-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
        'violet' => 'bg-violet-50 text-violet-600',
        'slate' => 'bg-slate-100 text-slate-600',
    ];
    $iconBg = $colorMap[$color] ?? $colorMap['cyan'];
@endphp

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500">{{ $label }}</p>
            <p class="text-3xl font-bold text-slate-800 mt-2">{{ $value }}</p>
            @if($trend)
                <p class="text-xs text-slate-400 mt-1">
                    {{ $trend }}
                    @if($trendValue)
                        <span class="font-medium text-emerald-600">{{ $trendValue }}</span>
                    @endif
                </p>
            @endif
        </div>
        <div class="w-12 h-12 rounded-xl {{ $iconBg }} flex items-center justify-center">
            <i class="{{ $icon }} text-xl"></i>
        </div>
    </div>
</div>