@props(['route', 'searchPlaceholder' => 'Tìm kiếm...', 'statuses' => [], 'additionalFilters' => null])
@php
    $hasStatuses = count($statuses) > 0;
    $hasAdditionalFilters = isset($additionalFilters) && trim((string) $additionalFilters) !== '';
@endphp

<form method="get" action="{{ $route }}">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Bộ lọc</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Tìm nhanh dữ liệu</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Lọc theo từ khóa, trạng thái và các tiêu chí bổ sung để xử lý nhanh hơn.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ $route }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    Xóa lọc
                </a>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Áp dụng
                </button>
            </div>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Tìm kiếm</label>
                <div class="relative">
                    <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ $searchPlaceholder }}"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 pl-11 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                    >
                </div>
            </div>

            @if ($hasStatuses)
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Trạng thái</label>
                    <select
                        name="status"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                    >
                        <option value="">Tất cả</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if ($hasAdditionalFilters)
                <div class="lg:col-span-2">
                    {{ $additionalFilters }}
                </div>
            @endif
        </div>
    </section>
</form>
