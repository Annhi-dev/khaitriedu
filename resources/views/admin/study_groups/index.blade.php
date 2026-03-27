@extends('layouts.admin')
@section('title', 'Quản lý nhóm học')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 5</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Quản lý nhóm học</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Theo dõi các nhóm học lớn, kiểm soát trạng thái hiển thị và số lượng khóa học bên trong trước khi admin quản lý chi tiết từng khóa.</p>
        </div>
        <a href="{{ route('admin.categories.create-page') }}" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Tạo nhóm học mới</a>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="{{ route('admin.categories') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px_auto]">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Tìm kiếm</label>
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tên, slug, mô tả, chương trình, cấp độ" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Trạng thái</label>
                <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">Tất cả trạng thái</option>
                    <option value="{{ \App\Models\Category::STATUS_ACTIVE }}" @selected(($filters['status'] ?? '') === \App\Models\Category::STATUS_ACTIVE)>Hoạt động</option>
                    <option value="{{ \App\Models\Category::STATUS_INACTIVE }}" @selected(($filters['status'] ?? '') === \App\Models\Category::STATUS_INACTIVE)>Ngừng hoạt động</option>
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">Áp dụng</button>
                <a href="{{ route('admin.categories') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Xóa lọc</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Danh sách nhóm học</h2>
                <p class="text-sm text-slate-500">Tổng cộng {{ $categories->total() }} nhóm học phù hợp với bộ lọc hiện tại.</p>
            </div>
        </div>

        @if ($categories->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Nhóm học</th>
                            <th class="px-5 py-4">Chương trình</th>
                            <th class="px-5 py-4">Trạng thái</th>
                            <th class="px-5 py-4">Khóa học</th>
                            <th class="px-5 py-4 text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($categories as $category)
                            @php
                                $statusClasses = $category->status === \App\Models\Category::STATUS_ACTIVE
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                    : 'border-amber-200 bg-amber-50 text-amber-700';
                            @endphp
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900">{{ $category->name }}</div>
                                    <div class="mt-1 text-xs text-slate-500">/{{ $category->slug }}</div>
                                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600">{{ $category->description ?: 'Chưa có mô tả cho nhóm học này.' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <div>{{ $category->program ?: 'Chưa cấu hình chương trình' }}</div>
                                    <div class="mt-1 text-xs text-slate-500">Cấp độ: {{ $category->level ?: 'Chưa xác định' }}</div>
                                    <div class="mt-1 text-xs text-slate-500">Thứ tự hiển thị: {{ $category->order }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $category->statusLabel() }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <div>{{ $category->subjects_count }} khóa học public</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $category->courses_count }} lớp học nội bộ</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('admin.categories.show', $category) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">Xem</a>
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 px-3 py-2 text-xs font-medium text-cyan-700 hover:bg-cyan-50">Sửa</a>
                                        @if ($category->status === \App\Models\Category::STATUS_ACTIVE)
                                            <form method="post" action="{{ route('admin.categories.deactivate', $category) }}" onsubmit="return confirm('Ngừng hoạt động nhóm học này?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-600">Ngừng hoạt động</button>
                                            </form>
                                        @else
                                            <form method="post" action="{{ route('admin.categories.activate', $category) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Kích hoạt lại</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-5 py-4">
                {{ $categories->links() }}
            </div>
        @else
            <div class="px-6 py-14 text-center">
                <h3 class="text-lg font-semibold text-slate-900">Chưa có nhóm học phù hợp</h3>
                <p class="mt-2 text-sm text-slate-500">Hãy đổi bộ lọc hoặc tạo nhóm học mới để bắt đầu.</p>
            </div>
        @endif
    </div>
</div>
@endsection