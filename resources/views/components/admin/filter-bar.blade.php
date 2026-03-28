@props(['route', 'searchPlaceholder' => 'Tìm kiếm...', 'statuses' => [], 'additionalFilters' => null])

<form method="get" action="{{ $route }}" class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tìm kiếm</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ $searchPlaceholder }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
        </div>
        @if(count($statuses))
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái</label>
            <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
                <option value="">Tất cả</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') == $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @endif
        @if($additionalFilters)
            {{ $additionalFilters }}
        @endif
        <div class="flex items-end gap-2">
            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Lọc</button>
            <a href="{{ $route }}" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition">Xóa lọc</a>
        </div>
    </div>
</form>