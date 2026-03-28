@extends('layouts.admin')
@section('title', 'Quản lý khóa học')
@section('content')
@php
    $createRouteParams = [];

    if (! empty($filters['category_id'])) {
        $createRouteParams['category_id'] = $filters['category_id'];
        $createRouteParams['return_to_category_id'] = $filters['category_id'];
    }
@endphp
<div class="space-y-6">
    <x-admin.page-header title="Quản lý khóa học" subtitle="Các khóa học public để học viên đăng ký">
        <a href="{{ route('admin.subjects.create-page', $createRouteParams) }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
            <i class="fas fa-plus mr-1"></i> Thêm khóa học
        </a>
    </x-admin.page-header>

    <form method="get" action="{{ route('admin.subjects') }}" class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tìm kiếm</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tên khóa học" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nhóm học</label>
                <select name="category_id" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                    <option value="">Tất cả</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái</label>
                <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                    <option value="">Tất cả</option>
                    @foreach(['draft' => 'Nháp', 'open' => 'Đang mở', 'closed' => 'Đóng đăng ký', 'archived' => 'Lưu trữ'] as $val => $label)
                        <option value="{{ $val }}" @selected(($filters['status'] ?? '') == $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-xl text-sm font-semibold">Lọc</button>
                <a href="{{ route('admin.subjects') }}" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium">Xóa lọc</a>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Khóa học</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Nhóm học</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Quy mô</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subjects as $subject)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $subject->name }}</div>
                            <div class="mt-2 text-sm text-slate-600">Học phí: {{ number_format($subject->price, 0, ',', '.') }}đ</div>
                            <div class="text-xs text-slate-500">{{ $subject->durationLabel() }}</div>
                        </td>
                        <td class="px-6 py-4">{{ $subject->category?->name ?? 'Chưa gắn' }}</td>
                        <td class="px-6 py-4">
                            <x-admin.badge :type="match($subject->status) {'open' => 'success', 'draft' => 'default', 'closed' => 'warning', 'archived' => 'danger', default => 'default'}" :text="$subject->statusLabel()" />
                        </td>
                        <td class="px-6 py-4">
                            <div>{{ $subject->courses_count }} lớp</div>
                            <div class="text-xs text-slate-500">{{ $subject->enrollments_count }} đăng ký</div>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.subject.show', $subject) }}" class="text-cyan-600 hover:text-cyan-800"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.subjects.edit', $subject) }}" class="text-slate-600 hover:text-slate-800"><i class="fas fa-edit"></i></a>
                            @if($subject->status !== 'archived')
                                <form class="inline" method="post" action="{{ route('admin.subjects.archive', $subject) }}" onsubmit="return confirm('Chuyển sang lưu trữ?')">
                                    @csrf
                                    <button type="submit" class="text-rose-600 hover:text-rose-800"><i class="fas fa-archive"></i></button>
                                </form>
                            @else
                                <form class="inline" method="post" action="{{ route('admin.subjects.reopen', $subject) }}">
                                    @csrf
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800"><i class="fas fa-undo-alt"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">Chưa có khóa học nào</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $subjects->links() }}
        </div>
    </div>
</div>
@endsection