@extends('layouts.admin')
@section('title', 'Chi tiết nhóm học')
@section('content')
@php
    $statusClasses = $category->status === \App\Models\Category::STATUS_ACTIVE
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-amber-200 bg-amber-50 text-amber-700';
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Hồ sơ nhóm học</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">{{ $category->name }}</h1>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                <span>/{{ $category->slug }}</span>
                <span class="text-slate-300">|</span>
                <span>{{ $category->program ?: 'Chưa cấu hình chương trình' }}</span>
                <span class="text-slate-300">|</span>
                <span>{{ $category->level ?: 'Chưa xác định cấp độ' }}</span>
                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $category->statusLabel() }}</span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.categories') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Danh sách nhóm học</a>
            <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex items-center justify-center rounded-2xl border border-cyan-200 px-4 py-2.5 text-sm font-medium text-cyan-700 hover:bg-cyan-50">Sửa thông tin</a>
            @if ($category->status === \App\Models\Category::STATUS_ACTIVE)
                <form method="post" action="{{ route('admin.categories.deactivate', $category) }}" onsubmit="return confirm('Ngừng hoạt động nhóm học này?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-600">Ngừng hoạt động</button>
                </form>
            @else
                <form method="post" action="{{ route('admin.categories.activate', $category) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Kích hoạt lại</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Thông tin tổng quan</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Thứ tự hiển thị {{ $category->order }}</span>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Tên nhóm học</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $category->name }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Trạng thái</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $category->statusLabel() }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Chương trình</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $category->program ?: 'Chưa cấu hình' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Cấp độ</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $category->level ?: 'Chưa cấu hình' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Mô tả</p>
                    <p class="mt-1 text-sm leading-6 text-slate-700">{{ $category->description ?: 'Chưa có mô tả cho nhóm học này.' }}</p>
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thống kê nhanh</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Khóa học public</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $category->subjects_count }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Lớp học nội bộ</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $category->courses_count }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Ảnh đại diện</h2>
                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4">
                    @if ($category->image_path)
                        <img src="{{ asset('storage/' . $category->image_path) }}" alt="{{ $category->name }}" class="h-48 w-full rounded-2xl object-cover" />
                    @else
                        <div class="flex h-48 items-center justify-center rounded-2xl bg-slate-100 text-sm text-slate-500">Chưa có ảnh đại diện</div>
                    @endif
                </div>
            </section>
        </aside>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Khóa học bên trong nhóm</h2>
                <p class="text-sm text-slate-500">Hiển thị các khóa học public đang nằm trong nhóm học này để admin đi tiếp sang phase quản lý khóa học.</p>
            </div>
            <a href="{{ route('admin.subjects') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Mở quản lý khóa học</a>
        </div>

        <div class="mt-5 grid gap-4">
            @forelse ($subjects as $subject)
                <div class="rounded-2xl border border-slate-200 px-4 py-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $subject->name }}</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $subject->description ?: 'Chưa có mô tả cho khóa học này.' }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-500">
                                <span>Giá tham khảo: {{ number_format((float) $subject->price, 0, ',', '.') }} đ</span>
                                <span>{{ $subject->courses_count }} lớp học nội bộ</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.subject.show', $subject->id) }}" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 px-3 py-2 text-xs font-medium text-cyan-700 hover:bg-cyan-50">Xem khóa học</a>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Nhóm học này chưa có khóa học public nào được liên kết.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection