@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        <div class="font-semibold">Du lieu chua hop le.</div>
        <ul class="mt-2 list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Ma phong</label>
        <input type="text" name="code" value="{{ old('code', $room->code) }}" placeholder="Vi du: P203" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-cyan-500 focus:ring-cyan-500">
        <p class="mt-1 text-xs text-slate-500">Neu de trong, he thong se tu sinh ma phong.</p>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Trang thai</label>
        <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-cyan-500 focus:ring-cyan-500">
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $room->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Loai phong</label>
        <select name="type" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-cyan-500 focus:ring-cyan-500">
            <option value="theory" @selected(old('type', $room->type) === 'theory')>Phong ly thuyet</option>
            <option value="practice" @selected(old('type', $room->type) === 'practice')>Phong thuc hanh</option>
        </select>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Ten phong</label>
        <input type="text" name="name" value="{{ old('name', $room->name) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-cyan-500 focus:ring-cyan-500" required>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Vi tri</label>
        <input type="text" name="location" value="{{ old('location', $room->location) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-cyan-500 focus:ring-cyan-500">
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Suc chua</label>
        <input type="number" min="1" name="capacity" value="{{ old('capacity', $room->capacity) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-cyan-500 focus:ring-cyan-500" required>
    </div>

    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">Ghi chu</label>
        <textarea name="note" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-cyan-500 focus:ring-cyan-500">{{ old('note', $room->note) }}</textarea>
    </div>
</div>

<div class="mt-6 flex flex-wrap items-center justify-end gap-3">
    <a href="{{ route('admin.rooms.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium transition hover:bg-slate-50">Quay lai</a>
    <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
        {{ $submitLabel }}
    </button>
</div>
