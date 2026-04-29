@php
    $category = $category ?? null;
    $statusValue = old('status', $category->status ?? \App\Models\NhomHoc::STATUS_ACTIVE);
@endphp
<div class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(280px,1fr)]">
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Thông tin nhóm học</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Tên nhóm học</label>
                    <input id="name" name="name" value="{{ old('name', $category->name ?? '') }}" placeholder="Tên nhóm học" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    @error('name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="slug" class="mb-2 block text-sm font-medium text-slate-700">Slug</label>
                    <input id="slug" name="slug" value="{{ old('slug', $category->slug ?? '') }}" placeholder="Slug" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    @error('slug')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="program" class="mb-2 block text-sm font-medium text-slate-700">Chương trình</label>
                    <input id="program" name="program" value="{{ old('program', $category->program ?? '') }}" placeholder="Chương trình" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    @error('program')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="level" class="mb-2 block text-sm font-medium text-slate-700">Cấp độ</label>
                    <input id="level" name="level" value="{{ old('level', $category->level ?? '') }}" placeholder="Cấp độ" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    @error('level')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Mô tả</label>
                    <textarea id="description" name="description" rows="6" placeholder="Mô tả" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">{{ old('description', $category->description ?? '') }}</textarea>
                    @error('description')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Hiển thị và trạng thái</h2>
            <div class="mt-5 space-y-4">
                <div>
                    <label for="status" class="mb-2 block text-sm font-medium text-slate-700">Trạng thái</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                        <option value="{{ \App\Models\NhomHoc::STATUS_ACTIVE }}" @selected($statusValue === \App\Models\NhomHoc::STATUS_ACTIVE)>Hoạt động</option>
                        <option value="{{ \App\Models\NhomHoc::STATUS_INACTIVE }}" @selected($statusValue === \App\Models\NhomHoc::STATUS_INACTIVE)>Ngừng hoạt động</option>
                    </select>
                    @error('status')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="order" class="mb-2 block text-sm font-medium text-slate-700">Thứ tự hiển thị</label>
                    <input id="order" name="order" type="number" min="0" value="{{ old('order', $category->order ?? 0) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    @error('order')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="image" class="mb-2 block text-sm font-medium text-slate-700">Ảnh đại diện</label>
                    <input id="image" name="image" type="file" accept="image/*" class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-cyan-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-cyan-700" />
                    @error('image')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
