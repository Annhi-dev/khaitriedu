@props(['paginator', 'label' => 'kết quả'])

@if ($paginator->hasPages())
    <div class="rounded-3xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Phân trang</p>
                <p class="mt-2 text-sm text-slate-600">
                    Hiển thị
                    <span class="font-semibold text-slate-900">{{ number_format($paginator->firstItem() ?? 0) }}</span>
                    -
                    <span class="font-semibold text-slate-900">{{ number_format($paginator->lastItem() ?? 0) }}</span>
                    trên
                    <span class="font-semibold text-slate-900">{{ number_format($paginator->total()) }}</span>
                    {{ $label }}
                </p>
            </div>

            <div class="overflow-x-auto">
                {{ $paginator->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
@endif
