@extends('layouts.admin')
@section('title', 'Quản lý học viên')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 2</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Quản lý học viên</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Theo dõi danh sách học viên, tìm kiếm nhanh theo thông tin liên hệ và quản lý trạng thái tài khoản trước khi admin duyệt các nghiệp vụ học tập tiếp theo.</p>
        </div>
        <a href="{{ route('admin.students.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Thêm học viên mới</a>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="{{ route('admin.students.index') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_240px_auto]">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Tìm kiếm</label>
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tên, email hoặc số điện thoại" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Lọc trạng thái</label>
                <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">Tất cả trạng thái</option>
                    <option value="{{ \App\Models\User::STATUS_ACTIVE }}" @selected(($filters['status'] ?? '') === \App\Models\User::STATUS_ACTIVE)>Hoạt động</option>
                    <option value="{{ \App\Models\User::STATUS_INACTIVE }}" @selected(($filters['status'] ?? '') === \App\Models\User::STATUS_INACTIVE)>Tạm dừng</option>
                    <option value="{{ \App\Models\User::STATUS_LOCKED }}" @selected(($filters['status'] ?? '') === \App\Models\User::STATUS_LOCKED)>Đã khóa</option>
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">Áp dụng</button>
                <a href="{{ route('admin.students.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Xóa lọc</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Danh sách học viên</h2>
                <p class="text-sm text-slate-500">Tổng cộng {{ $students->total() }} học viên phù hợp với bộ lọc hiện tại.</p>
            </div>
        </div>

        @if ($students->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Học viên</th>
                            <th class="px-5 py-4">Liên hệ</th>
                            <th class="px-5 py-4">Trạng thái</th>
                            <th class="px-5 py-4">Đăng ký học</th>
                            <th class="px-5 py-4 text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($students as $student)
                            @php
                                $statusClasses = match ($student->status) {
                                    \App\Models\User::STATUS_ACTIVE => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    \App\Models\User::STATUS_INACTIVE => 'border-amber-200 bg-amber-50 text-amber-700',
                                    \App\Models\User::STATUS_LOCKED => 'border-rose-200 bg-rose-50 text-rose-700',
                                    default => 'border-slate-200 bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900">{{ $student->name }}</div>
                                    <div class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ $student->username }}</div>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <div>{{ $student->email }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $student->phone ?: 'Chưa cập nhật số điện thoại' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $student->statusLabel() }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $student->enrollments_count }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('admin.students.show', $student) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">Xem</a>
                                        <a href="{{ route('admin.students.edit', $student) }}" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 px-3 py-2 text-xs font-medium text-cyan-700 hover:bg-cyan-50">Sửa</a>
                                        @if ($student->isLocked())
                                            <form method="post" action="{{ route('admin.students.unlock', $student) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Mở khóa</button>
                                            </form>
                                        @else
                                            <form method="post" action="{{ route('admin.students.lock', $student) }}" onsubmit="return confirm('Khóa tài khoản học viên này?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">Khóa</button>
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
                {{ $students->links() }}
            </div>
        @else
            <div class="px-6 py-14 text-center">
                <h3 class="text-lg font-semibold text-slate-900">Chưa có học viên phù hợp</h3>
                <p class="mt-2 text-sm text-slate-500">Hãy đổi bộ lọc hoặc tạo học viên mới để bắt đầu quản lý.</p>
            </div>
        @endif
    </div>
</div>
@endsection
