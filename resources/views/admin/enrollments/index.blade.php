@extends('layouts.admin')
@section('title', 'Quản lý đăng ký học')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 8</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Quản lý đăng ký học</h1>
            <p class="mt-2 text-sm text-slate-600">Xử lý yêu cầu đăng ký của học viên, rà soát nhu cầu học và chuyển hồ sơ sang bước duyệt hoặc xếp lớp.</p>
        </div>
    </div>

    <x-admin.filter-bar route="{{ route('admin.enrollments') }}" searchPlaceholder="Tên học viên, email, khóa học..." :statuses="$statusOptions">
        <x-slot:additionalFilters>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Loại hồ sơ</label>
                <select name="request_source" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">Tất cả</option>
                    @foreach($requestSourceOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('request_source') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:additionalFilters>
    </x-admin.filter-bar>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Học viên</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Khóa học</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Khung giờ mong muốn</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Xếp lớp hiện tại</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trạng thái</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-500 uppercase">Xử lý</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($enrollments as $enrollment)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-4">
                            <div class="font-medium">{{ $enrollment->user?->name }}</div>
                            <div class="text-xs text-slate-500">{{ $enrollment->user?->email }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-medium text-slate-900">{{ $enrollment->subject?->name ?? 'Chưa xác định' }}</div>
                            <div class="mt-2">
                                <x-admin.badge :type="$enrollment->requestSourceBadgeType()" :text="$enrollment->requestSourceLabel()" />
                            </div>
                        </td>
                        <td class="px-5 py-4">{{ $enrollment->start_time ?: '--' }} - {{ $enrollment->end_time ?: '--' }}</td>
                        <td class="px-5 py-4">{{ $enrollment->course?->title ?? 'Chưa xếp lớp' }}</td>
                        <td class="px-5 py-4">
                            <x-admin.badge :type="match($enrollment->status) {'pending' => 'warning', 'approved' => 'info', 'scheduled' => 'success', 'active' => 'success', 'completed' => 'default', 'rejected' => 'danger', default => 'default'}" :text="$enrollment->statusLabel()" />
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="inline-flex items-center px-3 py-1 rounded-xl bg-cyan-50 text-cyan-700 text-xs font-medium hover:bg-cyan-100 transition">Chi tiết</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">Không có đăng ký học nào phù hợp</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-200">
            {{ $enrollments->links() }}
        </div>
    </div>
</div>
@endsection
