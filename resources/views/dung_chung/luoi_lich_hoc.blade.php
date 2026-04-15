@php
    $toneClasses = [
        'cyan' => 'border-cyan-200 bg-cyan-50',
        'emerald' => 'border-emerald-200 bg-emerald-50',
        'amber' => 'border-amber-200 bg-amber-50',
        'rose' => 'border-rose-200 bg-rose-50',
        'slate' => 'border-slate-200 bg-slate-50',
    ];
    $gridDays = $grid['days'] ?? [];
    $gridSlots = $grid['slots'] ?? [];
    $gridMatrix = $grid['matrix'] ?? [];
    $gridEntries = $grid['entries'] ?? [];
    $weekLabel = $grid['weekLabel'] ?? null;
@endphp

<section class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200" x-data="{ activeEntry: null }">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-700">{{ $sectionEyebrow ?? 'Schedule' }}</p>
            <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $sectionTitle ?? 'Lich tuan' }}</h2>
            @if($weekLabel)
                <p class="mt-3 inline-flex rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">Tuan {{ $weekLabel }}</p>
            @endif
        </div>

        <div class="flex flex-wrap gap-2 text-xs text-slate-500">
            <span class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1 font-medium text-cyan-700">
                <span class="h-2.5 w-2.5 rounded-full bg-cyan-500"></span>
                Lich da xep
            </span>
            <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-700">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                Dang cho mo lop
            </span>
        </div>
    </div>

    <div class="mt-5">
        <table class="w-full table-fixed border-collapse text-[11px]">
            <thead>
                <tr class="bg-cyan-600 text-white">
                    <th class="sticky left-0 z-10 border-r border-cyan-500 px-2 py-3 text-center text-[11px] font-semibold leading-none whitespace-nowrap" style="width: 68px;">Khung gio</th>
                    @foreach($gridDays as $day)
                        <th class="border-r border-cyan-500 px-2 py-3 text-center" style="width: calc((100% - 136px) / 7);">
                            <div class="text-[11px] font-semibold uppercase tracking-[0.12em]">{{ $day['label'] }}</div>
                            <div class="mt-1 text-[10px] text-cyan-100 whitespace-nowrap">{{ $day['date_label'] }}</div>
                        </th>
                    @endforeach
                    <th class="px-2 py-3 text-center text-[11px] font-semibold leading-none whitespace-nowrap" style="width: 68px;">Khung gio</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-200">
                @forelse($gridSlots as $slot)
                    <tr class="align-top">
                        <th class="sticky left-0 z-10 border-r border-slate-200 bg-slate-50 px-1.5 py-2.5 text-center text-[10px] font-semibold leading-none whitespace-nowrap text-slate-700" style="width: 68px;">
                            {{ $slot['label'] }}
                        </th>

                        @foreach($gridDays as $day)
                            @php $slotCells = $gridMatrix[$slot['key']][$day['key']] ?? []; @endphp
                            <td class="border-r border-slate-200 px-1.5 py-1.5 {{ !empty($day['is_today']) ? 'bg-cyan-50/60' : 'bg-white' }}">
                                @if(!empty($slotCells))
                                    <div class="space-y-1.5">
                                        @foreach($slotCells as $entry)
                                            @php
                                                $toneClass = $toneClasses[$entry['tone'] ?? 'slate'] ?? $toneClasses['slate'];
                                            @endphp
                                            <article class="rounded-xl border p-2 shadow-sm {{ $toneClass }}">
                                                <div class="flex items-start justify-between gap-1.5">
                                                    <div class="min-w-0 flex-1">
                                                        <p class="truncate text-[12px] font-semibold leading-4 text-slate-900" title="{{ $entry['title'] }}">{{ $entry['title'] }}</p>
                                                        @if(!empty($entry['subtitle']))
                                                            <p class="mt-0.5 truncate text-[10px] text-slate-500">{{ $entry['subtitle'] }}</p>
                                                        @endif
                                                        <p class="mt-0.5 text-[10px] uppercase tracking-[0.12em] text-slate-400">{{ $entry['time_label'] ?? $slot['label'] }}</p>
                                                    </div>

                                                    <div class="flex shrink-0 flex-col items-end gap-1">
                                                        @if(!empty($entry['badge']))
                                                            <span class="rounded-full px-1.5 py-0.5 text-[9px] font-semibold {{ $entry['badge_class'] ?? 'bg-slate-100 text-slate-600' }}">{{ $entry['badge'] }}</span>
                                                        @endif
                                                        <button
                                                            type="button"
                                                            class="rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-50"
                                                            @click="activeEntry = @js($entry['id'])"
                                                        >
                                                            Xem chi tiet
                                                        </button>
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex min-h-[58px] items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 px-2 py-2 text-center text-[10px] text-slate-400">
                                        -
                                    </div>
                                @endif
                            </td>
                        @endforeach

                        <th class="border-l border-slate-200 bg-slate-50 px-1.5 py-2.5 text-center text-[10px] font-semibold leading-none whitespace-nowrap text-slate-700" style="width: 68px;">
                            {{ $slot['label'] }}
                        </th>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($gridDays) + 2 }}" class="px-6 py-12 text-center text-sm text-slate-500">
                            {{ $emptyMessage ?? 'Chua co du lieu lich hoc.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @foreach($gridEntries as $entry)
        <div
            x-show="activeEntry === @js($entry['id'])"
            x-cloak
            class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/60 px-4 py-6"
            @click.self="activeEntry = null"
        >
            <div class="w-full max-w-xl rounded-3xl bg-white p-5 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-cyan-700">Chi tiet buoi hoc</p>
                        <h4 class="mt-2 text-xl font-semibold text-slate-900">{{ $entry['title'] }}</h4>
                        <p class="mt-2 text-sm text-slate-500">{{ $entry['time_label'] ?? '' }}</p>
                    </div>
                    <button type="button" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-600" @click="activeEntry = null">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <div class="mt-5 space-y-3 rounded-2xl bg-slate-50 p-4">
                    @if(!empty($entry['badge']))
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-medium text-slate-600">Trang thai</span>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $entry['badge_class'] ?? 'bg-slate-100 text-slate-600' }}">{{ $entry['badge'] }}</span>
                        </div>
                    @endif

                    @if(!empty($entry['subtitle']))
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-sm font-medium text-slate-600">Mon / lop</span>
                            <span class="text-right text-sm text-slate-800">{{ $entry['subtitle'] }}</span>
                        </div>
                    @endif

                    @if(!empty($entry['meta']))
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-sm font-medium text-slate-600">Thong tin</span>
                            <span class="text-right text-sm text-slate-800">{{ $entry['meta'] }}</span>
                        </div>
                    @endif

                    @if(!empty($entry['description']))
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-sm font-medium text-slate-600">Ghi chu</span>
                            <span class="text-right text-sm text-slate-800">{{ $entry['description'] }}</span>
                        </div>
                    @endif
                </div>

                @if(!empty($entry['url']) || !empty($entry['secondary_url']))
                    <div class="mt-5 flex flex-wrap justify-end gap-2">
                        @if(!empty($entry['url']))
                            <a href="{{ $entry['url'] }}" class="inline-flex items-center rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">
                                {{ $entry['primary_label'] ?? 'Xem chi tiet' }}
                            </a>
                        @endif
                        @if(!empty($entry['secondary_url']))
                            <a href="{{ $entry['secondary_url'] }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                {{ $entry['secondary_label'] ?? 'Lien ket' }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</section>
