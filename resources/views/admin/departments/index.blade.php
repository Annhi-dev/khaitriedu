@extends('layouts.admin')
@section('title', 'Quản lý phòng ban')
@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Quản lý phòng ban" subtitle="Quản lý cơ cấu phòng ban và phân bổ giảng viên trong hệ thống">
        <a href="{{ route('admin.departments.create') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
            <i class="fas fa-plus mr-1"></i> Thêm phòng ban
        </a>
    </x-admin.page-header>

    <div class="grid gap-4 md:grid-cols-4">
        <x-admin.stat-card label="Tổng phòng ban" :value="$summary['total']" icon="fas fa-building" color="cyan" />
        <x-admin.stat-card label="Đang hoạt động" :value="$summary['active']" icon="fas fa-circle-check" color="emerald" />
        <x-admin.stat-card label="Tạm ngưng" :value="$summary['inactive']" icon="fas fa-pause-circle" color="amber" />
        <x-admin.stat-card label="Tổng giảng viên" :value="$summary['teachers']" icon="fas fa-chalkboard-user" color="slate" />
    </div>

    <x-admin.filter-bar :route="route('admin.departments.index')" searchPlaceholder="Mã phòng ban, tên phòng ban, mô tả..." :statuses="\App\Models\Department::statusOptions()" />

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Phòng ban</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Mô tả</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Số giảng viên</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trạng thái</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($departments as $department)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $department->name }}</div>
                            <div class="text-xs text-slate-500">{{ $department->code }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $department->description ?: 'Chưa có mô tả' }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ $department->teachers_count }}</td>
                        <td class="px-6 py-4">
                            <x-admin.badge :type="$department->status === \App\Models\Department::STATUS_ACTIVE ? 'success' : 'warning'" :text="$department->statusLabel()" />
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.departments.edit', $department) }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Chỉnh sửa
                            </a>
                            @if($department->status === \App\Models\Department::STATUS_ACTIVE)
                                <form class="inline" method="post" action="{{ route('admin.departments.deactivate', $department) }}" onsubmit="return confirm('Chuyển phòng ban này sang tạm ngưng?')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700 hover:bg-amber-100">
                                        Tạm ngưng
                                    </button>
                                </form>
                            @else
                                <form class="inline" method="post" action="{{ route('admin.departments.activate', $department) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100">
                                        Kích hoạt
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">Chưa có phòng ban nào</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $departments->links() }}
        </div>
    </div>
</div>
@endsection
