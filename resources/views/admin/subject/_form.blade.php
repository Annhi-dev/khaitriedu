@php
    $subject = $subject ?? null;
    $selectedCategory = $selectedCategory ?? null;
    $statusValue = old('status', $subject->status ?? \App\Models\Subject::STATUS_OPEN);
    $selectedCategoryId = (string) old('category_id', $subject->category_id ?? $selectedCategory?->id ?? request('category_id', ''));
    $returnToCategoryId = old('return_to_category_id', $returnToCategoryId ?? null);
@endphp
@if ($returnToCategoryId)
    <input type="hidden" name="return_to_category_id" value="{{ $returnToCategoryId }}" />
@endif
<div class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(320px,1fr)]">
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Thong tin khoa hoc</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Ten khoa hoc</label>
                    <input id="name" name="name" value="{{ old('name', $subject->name ?? '') }}" placeholder="Vi du: Tin hoc van phong" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    @error('name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="category_id" class="mb-2 block text-sm font-medium text-slate-700">Nhom hoc cha</label>
                    <select id="category_id" name="category_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                        <option value="">Chua gan nhom hoc</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($selectedCategoryId === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @if ($selectedCategory)
                        <p class="mt-2 text-xs text-cyan-600">Dang tao khoa hoc trong nhom {{ $selectedCategory->name }}.</p>
                    @endif
                    @error('category_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-medium text-slate-700">Trang thai</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                        <option value="{{ \App\Models\Subject::STATUS_DRAFT }}" @selected($statusValue === \App\Models\Subject::STATUS_DRAFT)>Nhap</option>
                        <option value="{{ \App\Models\Subject::STATUS_OPEN }}" @selected($statusValue === \App\Models\Subject::STATUS_OPEN)>Dang mo</option>
                        <option value="{{ \App\Models\Subject::STATUS_CLOSED }}" @selected($statusValue === \App\Models\Subject::STATUS_CLOSED)>Dong dang ky</option>
                        <option value="{{ \App\Models\Subject::STATUS_ARCHIVED }}" @selected($statusValue === \App\Models\Subject::STATUS_ARCHIVED)>Luu tru</option>
                    </select>
                    @error('status')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="price" class="mb-2 block text-sm font-medium text-slate-700">Hoc phi tham khao</label>
                    <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $subject->price ?? 0) }}" placeholder="Vi du: 1500000" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    @error('price')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="duration" class="mb-2 block text-sm font-medium text-slate-700">Thoi gian hoc (du kien)</label>
                    <input id="duration" name="duration" type="number" min="1" max="120" value="{{ old('duration', $subject->duration ?? '') }}" placeholder="Vi du: 36" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    <p class="mt-2 text-xs text-slate-500">Nhap tong so thang du kien. Form ho tro cac moc linh hoat nhu 36, 42 hoac 48 thang.</p>
                    @error('duration')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Mo ta</label>
                    <textarea id="description" name="description" rows="6" placeholder="Mo ta gia tri khoa hoc, doi tuong hoc vien, ket qua dau ra" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">{{ old('description', $subject->description ?? '') }}</textarea>
                    @error('description')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Hien thi</h2>
            <div class="mt-5 space-y-4">
                <div>
                    <label for="image" class="mb-2 block text-sm font-medium text-slate-700">Anh dai dien</label>
                    <input id="image" name="image" type="file" accept="image/*" class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-cyan-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-cyan-700" />
                    @if (! empty($subject?->image))
                        <p class="mt-2 text-xs text-slate-500">Khoa hoc nay da co anh dai dien hien tai.</p>
                    @endif
                    @error('image')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Luu y nghiep vu</h2>
            <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-600">
                <p>Khoa hoc o day la man public de hoc vien dang ky.</p>
                <p class="mt-2">Giang vien chinh thuc van duoc phan o cap lop hoc noi bo de admin chu dong xep lich va phan lop o cac phase sau.</p>
            </div>
        </div>
    </div>
</div>
