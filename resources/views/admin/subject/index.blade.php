@extends('layouts.admin')
@section('title', 'Quản lý khóa học')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 6</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Quản lý khóa học</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Quản lý các khóa học public nằm trong nhóm học, kiểm soát học phí, thời lượng, trạng thái mở đăng ký và theo dõi số lớp nội bộ, số học viên quan tâm.</p>
        </div>
        <a href="{{ route('admin.subjects.create-page') }}" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Tạo khóa học mới</a>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="{{ route('admin.subjects') }}" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_260px_220px_auto]">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Tìm kiếm</label>
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tên khóa học hoặc mô tả" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Nhóm học</label>
                <select name="category_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">Tất cả nhóm học</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Trạng thái</label>
                <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">Tất cả trạng thái</option>
                    <option value="{{ \App\Models\Subject::STATUS_DRAFT }}" @selected(($filters['status'] ?? '') === \App\Models\Subject::STATUS_DRAFT)>Nháp</option>
                    <option value="{{ \App\Models\Subject::STATUS_OPEN }}" @selected(($filters['status'] ?? '') === \App\Models\Subject::STATUS_OPEN)>Đang mở</option>
                    <option value="{{ \App\Models\Subject::STATUS_CLOSED }}" @selected(($filters['status'] ?? '') === \App\Models\Subject::STATUS_CLOSED)>Đóng đăng ký</option>
                    <option value="{{ \App\Models\Subject::STATUS_ARCHIVED }}" @selected(($filters['status'] ?? '') === \App\Models\Subject::STATUS_ARCHIVED)>Lưu trữ</option>
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">Áp dụng</button>
                <a href="{{ route('admin.subjects') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Xóa lọc</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Danh sách khóa học</h2>
                <p class="text-sm text-slate-500">Tổng cộng {{ $subjects->total() }} khóa học phù hợp với bộ lọc hiện tại.</p>
            </div>
        </div>

        @if ($subjects->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Khóa học</th>
                            <th class="px-5 py-4">Nhóm học</th>
                            <th class="px-5 py-4">Trạng thái</th>
                            <th class="px-5 py-4">Quy mô</th>
                            <th class="px-5 py-4 text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($subjects as $subject)
                            @php
                                $statusClasses = match ($subject->status) {
                                    \App\Models\Subject::STATUS_OPEN => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    \App\Models\Subject::STATUS_DRAFT => 'border-slate-200 bg-slate-100 text-slate-700',
                                    \App\Models\Subject::STATUS_CLOSED => 'border-amber-200 bg-amber-50 text-amber-700',
                                    \App\Models\Subject::STATUS_ARCHIVED => 'border-rose-200 bg-rose-50 text-rose-700',
                                    default => 'border-slate-200 bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900">{{ $subject->name }}</div>
                                    <div class="mt-2 grid gap-1 text-sm text-slate-600">
                                        <p>Học phí: {{ number_format((float) $subject->price, 0, ',', '.') }} đ</p>
                                        <p>Thời lượng: {{ $subject->durationLabel() }}</p>
                                    </div>
                                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600">{{ $subject->description ?: 'Chưa có mô tả cho khóa học này.' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <div>{{ $subject->category?->name ?? 'Chưa gắn nhóm học' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $subject->statusLabel() }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <div>{{ $subject->courses_count }} lớp học nội bộ</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $subject->modules_count }} module hiện có</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $subject->enrollments_count }} lượt đăng ký</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('admin.subject.show', $subject) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">Xem</a>
                                        <a href="{{ route('admin.subjects.edit', $subject) }}" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 px-3 py-2 text-xs font-medium text-cyan-700 hover:bg-cyan-50">Sửa</a>
                                        @if ($subject->status === \App\Models\Subject::STATUS_ARCHIVED)
                                            <form method="post" action="{{ route('admin.subjects.reopen', $subject) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Mở lại</button>
                                            </form>
                                        @else
                                            <form method="post" action="{{ route('admin.subjects.archive', $subject) }}" onsubmit="return confirm('Chuyển khóa học này sang trạng thái lưu trữ?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">Lưu trữ</button>
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
                {{ $subjects->links() }}
            </div>
        @else
            <div class="px-6 py-14 text-center">
                <h3 class="text-lg font-semibold text-slate-900">Chưa có khóa học phù hợp</h3>
                <p class="mt-2 text-sm text-slate-500">Hãy đổi bộ lọc hoặc tạo khóa học mới để bắt đầu.</p>
            </div>
        @endif
    </div>
</div>
@endsection