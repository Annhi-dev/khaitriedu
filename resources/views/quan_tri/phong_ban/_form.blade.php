@php
    $statusValue = old('status', $department->status ?? \App\Models\Department::STATUS_ACTIVE);
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Mã phòng ban</label>
        <input name="code" value="{{ old('code', $department->code ?? '') }}" required maxlength="30" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 uppercase focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Tên phòng ban</label>
        <input name="name" value="{{ old('name', $department->name ?? '') }}" required maxlength="150" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
    </div>
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-slate-700">Mô tả</label>
        <textarea name="description" rows="4" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">{{ old('description', $department->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Trạng thái</label>
        <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            @foreach (\App\Models\Department::statusOptions() as $value => $label)
                <option value="{{ $value }}" @selected($statusValue === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    @if(isset($department) && $department->exists)
        <div class="rounded-3xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
            <p class="font-semibold">Giảng viên đang thuộc phòng ban</p>
            <p class="mt-2 leading-6">{{ $department->teachers_count ?? 0 }} giảng viên.</p>
        </div>
    @endif
</div>

<div class="mt-6 flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
    <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Quay lại danh sách</a>
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">{{ $submitLabel }}</button>
</div>
