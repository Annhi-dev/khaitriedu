@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        <div class="font-semibold">Dữ liệu chưa hợp lệ.</div>
        <ul class="mt-2 list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Mã phòng</label>
        <input type="text" name="code" value="{{ old('code', $room->code) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Tên phòng</label>
        <input type="text" name="name" value="{{ old('name', $room->name) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Vị trí</label>
        <input type="text" name="location" value="{{ old('location', $room->location) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sức chứa</label>
        <input type="number" min="1" name="capacity" value="{{ old('capacity', $room->capacity) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái</label>
        <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $room->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
        <textarea name="note" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">{{ old('note', $room->note) }}</textarea>
    </div>
</div>

<div class="mt-6 flex flex-wrap items-center justify-end gap-3">
    <a href="{{ route('admin.rooms.index') }}" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition">Quay lại</a>
    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
        {{ $submitLabel }}
    </button>
</div>
