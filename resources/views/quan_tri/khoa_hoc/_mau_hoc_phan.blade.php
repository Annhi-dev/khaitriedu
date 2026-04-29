@php
    $module = $module ?? null;
    $defaultPosition = $defaultPosition ?? null;
    $statusValue = old('status', $module->status ?? \App\Models\HocPhan::STATUS_PUBLISHED);
    $positionValue = old('position', $module->position ?? $defaultPosition ?? '');
    $sessionCountValue = old('session_count', $module->session_count ?? ($module->lessons_count ?? ''));
@endphp
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    <div class="md:col-span-2">
        <label for="title" class="mb-2 block text-sm font-medium text-slate-700">Tên module / kỹ năng</label>
        <input id="title" name="title" value="{{ old('title', $module->title ?? '') }}" placeholder="Tên module" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
        @error('title')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="position" class="mb-2 block text-sm font-medium text-slate-700">Thứ tự</label>
        <input id="position" name="position" type="number" min="1" value="{{ $positionValue }}" placeholder="Thu tu" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
        @error('position')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="session_count" class="mb-2 block text-sm font-medium text-slate-700">Số buổi dự kiến</label>
        <input id="session_count" name="session_count" type="number" min="1" value="{{ $sessionCountValue }}" placeholder="So buoi" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
        @error('session_count')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="duration" class="mb-2 block text-sm font-medium text-slate-700">Thời lượng dự kiến (phút)</label>
        <input id="duration" name="duration" type="number" min="1" value="{{ old('duration', $module->duration ?? '') }}" placeholder="Số phút dự kiến" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
        @error('duration')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label for="status" class="mb-2 block text-sm font-medium text-slate-700">Trạng thái hiển thị</label>
        <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            <option value="{{ \App\Models\HocPhan::STATUS_PUBLISHED }}" @selected($statusValue === \App\Models\HocPhan::STATUS_PUBLISHED)>Đang hiển thị</option>
            <option value="{{ \App\Models\HocPhan::STATUS_UNPUBLISHED }}" @selected($statusValue === \App\Models\HocPhan::STATUS_UNPUBLISHED)>Đang ẩn</option>
        </select>
        @error('status')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label for="content" class="mb-2 block text-sm font-medium text-slate-700">Học viên sẽ học gì?</label>
        <textarea id="content" name="content" rows="5" placeholder="Noi dung module" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">{{ old('content', $module->content ?? '') }}</textarea>
        @error('content')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>
