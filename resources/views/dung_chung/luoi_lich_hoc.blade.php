@php
    $toneClasses = [
        'cyan' => [
            'card' => 'border-cyan-200 bg-cyan-50/40 text-slate-950',
            'stripe' => 'bg-cyan-500',
            'badge' => 'bg-cyan-50 text-cyan-700',
        ],
        'emerald' => [
            'card' => 'border-emerald-200 bg-emerald-50/40 text-slate-950',
            'stripe' => 'bg-emerald-500',
            'badge' => 'bg-emerald-50 text-emerald-700',
        ],
        'amber' => [
            'card' => 'border-amber-200 bg-amber-50/40 text-slate-950',
            'stripe' => 'bg-amber-500',
            'badge' => 'bg-amber-50 text-amber-700',
        ],
        'rose' => [
            'card' => 'border-rose-200 bg-rose-50/40 text-slate-950',
            'stripe' => 'bg-rose-500',
            'badge' => 'bg-rose-50 text-rose-700',
        ],
        'violet' => [
            'card' => 'border-violet-200 bg-violet-50/40 text-slate-950',
            'stripe' => 'bg-violet-500',
            'badge' => 'bg-violet-50 text-violet-700',
        ],
        'slate' => [
            'card' => 'border-slate-200 bg-slate-50/50 text-slate-950',
            'stripe' => 'bg-slate-500',
            'badge' => 'bg-slate-100 text-slate-600',
        ],
    ];

    $dayAccents = [
        ['border' => 'border-cyan-200', 'pill' => 'bg-cyan-50 text-cyan-700', 'marker' => 'bg-cyan-500', 'cardTop' => 'border-t-cyan-500'],
        ['border' => 'border-slate-200', 'pill' => 'bg-slate-50 text-slate-700', 'marker' => 'bg-slate-400', 'cardTop' => 'border-t-slate-400'],
        ['border' => 'border-emerald-200', 'pill' => 'bg-emerald-50 text-emerald-700', 'marker' => 'bg-emerald-500', 'cardTop' => 'border-t-emerald-500'],
        ['border' => 'border-amber-200', 'pill' => 'bg-amber-50 text-amber-700', 'marker' => 'bg-amber-500', 'cardTop' => 'border-t-amber-500'],
        ['border' => 'border-violet-200', 'pill' => 'bg-violet-50 text-violet-700', 'marker' => 'bg-violet-500', 'cardTop' => 'border-t-violet-500'],
        ['border' => 'border-rose-200', 'pill' => 'bg-rose-50 text-rose-700', 'marker' => 'bg-rose-500', 'cardTop' => 'border-t-rose-500'],
        ['border' => 'border-sky-200', 'pill' => 'bg-sky-50 text-sky-700', 'marker' => 'bg-sky-500', 'cardTop' => 'border-t-sky-500'],
    ];

    $gridDays = $grid['days'] ?? [];
    $gridSlots = $grid['slots'] ?? [];
    $gridMatrix = $grid['matrix'] ?? [];
    $gridEntries = $grid['entries'] ?? [];
    $weekLabel = $grid['weekLabel'] ?? null;
@endphp

<section class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm" x-data="{ activeEntry: null }">
    <div class="flex flex-col gap-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 via-white to-white p-5 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">{{ $sectionEyebrow ?? 'Lịch' }}</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{{ $sectionTitle ?? 'Lịch tuần' }}</h2>
            <p class="mt-2 text-sm font-medium text-slate-600">Khung giờ được nhóm theo từng ngày để dễ xem trên màn hình nhỏ.</p>
            @if($weekLabel)
                <p class="mt-3 inline-flex rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">Tuần {{ $weekLabel }}</p>
            @endif
            @if(!empty($sectionSubtitle))
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">{{ $sectionSubtitle }}</p>
            @endif
        </div>

        <div class="flex flex-wrap gap-2 text-xs text-slate-500">
            <span class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1 font-medium text-cyan-700">
                <span class="h-2.5 w-2.5 rounded-full bg-cyan-500"></span>
                Lịch đã xếp
            </span>
            <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-700">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                Đang chờ mở lớp
            </span>
        </div>
    </div>

    @if(!empty($grid['hasConflicts']))
        <div class="mx-5 mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <div class="flex items-start gap-3">
                <i class="fas fa-triangle-exclamation mt-0.5 text-rose-500"></i>
                <div>
                    <p class="font-semibold">
                        Phát hiện {{ $grid['conflictCount'] ?? 0 }} buổi bị trùng lịch trong dữ liệu hiện tại.
                    </p>
                    <p class="mt-1 leading-6">
                        Các buổi trùng được gắn nhãn đỏ để bạn nhận diện nhanh và xử lý lại nếu cần.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="p-5">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($gridDays as $day)
                @php
                    $dayAccent = $dayAccents[$loop->index % count($dayAccents)];
                    $daySlots = [];

                    foreach ($gridSlots as $slot) {
                        $slotCells = $gridMatrix[$slot['key']][$day['key']] ?? [];
                        if (!empty($slotCells)) {
                            $daySlots[] = [
                                'slot' => $slot,
                                'entries' => $slotCells,
                            ];
                        }
                    }

                    $occupiedCount = count($daySlots);
                    $emptyCount = max(count($gridSlots) - $occupiedCount, 0);
                @endphp

                <article class="flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ !empty($day['is_today']) ? 'ring-2 ring-cyan-200 ring-inset' : '' }}">
                    <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Ngày</p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $dayAccent['marker'] }}"></span>
                                    <h3 class="text-base font-semibold text-slate-900">{{ $day['label'] }}</h3>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">{{ $day['date_label'] }}</p>
                            </div>

                            <div class="flex flex-col items-end gap-2 text-right">
                                @if(!empty($day['is_today']))
                                    <span class="rounded-full bg-cyan-50 px-3 py-1 text-[11px] font-semibold text-cyan-700">Hôm nay</span>
                                @endif
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $dayAccent['pill'] }}">
                                    {{ $occupiedCount }} buổi
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 space-y-3 p-4">
                        @forelse($daySlots as $daySlot)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-slate-800">{{ $daySlot['slot']['label'] }}</p>
                                    <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-medium text-slate-500 ring-1 ring-slate-200">
                                        {{ count($daySlot['entries']) }} mục
                                    </span>
                                </div>

                                <div class="mt-3 space-y-2">
                                    @foreach($daySlot['entries'] as $entry)
                                        @php
                                            $toneClass = $toneClasses[$entry['tone'] ?? 'slate'] ?? $toneClasses['slate'];
                                        @endphp
                                        <article class="group relative overflow-hidden rounded-2xl border p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ !empty($entry['conflict']) ? 'border-rose-300 bg-rose-50/70 text-slate-950' : $toneClass['card'] }}">
                                            <div class="absolute inset-y-0 left-0 w-1.5 {{ $toneClass['stripe'] }}"></div>
                                            <div class="flex items-start justify-between gap-2 pl-2">
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-semibold leading-5 text-current" title="{{ $entry['title'] }}">{{ $entry['title'] }}</p>
                                                    @if(!empty($entry['subtitle']))
                                                        <p class="mt-1 truncate text-xs opacity-80">{{ $entry['subtitle'] }}</p>
                                                    @endif
                                                    <p class="mt-1 text-[11px] uppercase tracking-[0.12em] opacity-70">{{ $entry['time_label'] ?? $daySlot['slot']['label'] }}</p>
                                                </div>

                                                <div class="flex shrink-0 flex-col items-end gap-1">
                                                    @if(!empty($entry['badge']))
                                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $entry['badge_class'] ?? $toneClass['badge'] }}">{{ $entry['badge'] }}</span>
                                                    @endif
                                                    @if(!empty($entry['conflict']))
                                                        <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700">Trùng lịch</span>
                                                    @endif
                                                    <button
                                                        type="button"
                                                        class="rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-semibold text-slate-700 ring-1 ring-white/70 transition hover:bg-white hover:text-cyan-700"
                                                        @click="activeEntry = @js($entry['id'])"
                                                    >
                                                        Xem chi tiết
                                                    </button>
                                                </div>
                                            </div>
                                            @if(!empty($entry['conflict_note']))
                                                <p class="mt-2 pl-2 text-[11px] leading-5 {{ !empty($entry['conflict']) ? 'text-rose-700' : 'opacity-75' }}">
                                                    {{ $entry['conflict_note'] }}
                                                </p>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                Chưa có buổi học nào trong ngày này.
                            </div>
                        @endforelse
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50/60 px-4 py-3 text-xs text-slate-500">
                        {{ $occupiedCount }} khung giờ có buổi học, {{ $emptyCount }} khung giờ trống.
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-sm text-slate-500">
                    {{ $emptyMessage ?? 'Chưa có dữ liệu lịch học.' }}
                </div>
            @endforelse
        </div>
    </div>

    @foreach($gridEntries as $entry)
        <div
            x-show="activeEntry === @js($entry['id'])"
            x-cloak
            class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/60 px-4 py-6"
            @click.self="activeEntry = null"
        >
            <div class="w-full max-w-xl overflow-hidden rounded-[2rem] bg-white shadow-2xl ring-1 ring-black/5">
                <div class="border-b border-slate-100 bg-gradient-to-r from-cyan-50 via-white to-slate-50 p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">Chi tiết buổi học</p>
                            <h4 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{{ $entry['title'] }}</h4>
                            <p class="mt-2 text-sm text-slate-500">{{ $entry['time_label'] ?? '' }}</p>
                        </div>
                        <button type="button" class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-50" @click="activeEntry = null">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-3 p-5 sm:p-6">
                    <div class="grid gap-3 sm:grid-cols-2">
                        @if(!empty($entry['badge']))
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Trạng thái</span>
                                <div class="mt-2">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $entry['badge_class'] ?? 'bg-slate-100 text-slate-600' }}">{{ $entry['badge'] }}</span>
                                </div>
                            </div>
                        @endif

                        @if(!empty($entry['subtitle']))
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Lớp / môn</span>
                                <p class="mt-2 text-sm font-medium text-slate-800">{{ $entry['subtitle'] }}</p>
                            </div>
                        @endif

                        @if(!empty($entry['meta']))
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Thông tin</span>
                                <p class="mt-2 text-sm font-medium text-slate-800">{{ $entry['meta'] }}</p>
                            </div>
                        @endif

                        @if(!empty($entry['description']))
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Ghi chú</span>
                                <p class="mt-2 text-sm leading-6 text-slate-800">{{ $entry['description'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                @if(!empty($entry['url']) || !empty($entry['secondary_url']))
                    <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 px-5 py-4 sm:px-6">
                        @if(!empty($entry['url']))
                            <a href="{{ $entry['url'] }}" class="inline-flex items-center rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">
                                {{ $entry['primary_label'] ?? 'Xem chi tiết' }}
                            </a>
                        @endif
                        @if(!empty($entry['secondary_url']))
                            <a href="{{ $entry['secondary_url'] }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                {{ $entry['secondary_label'] ?? 'Liên kết' }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</section>
