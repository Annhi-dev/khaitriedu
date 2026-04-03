@extends('layouts.admin')
@section('title', 'Quản lý giảng viên')
@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Quản lý giảng viên" subtitle="Theo dõi và quản lý tài khoản giảng viên">
        <a href="{{ route('admin.teachers.create') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
            <i class="fas fa-plus mr-1"></i> Thêm giảng viên
        </a>
    </x-admin.page-header>

    <x-admin.filter-bar route="{{ route('admin.teachers.index') }}" searchPlaceholder="Tên, email, số điện thoại" :statuses="['active'=>'Hoạt động', 'inactive'=>'Tạm dừng', 'locked'=>'Khóa']">
        <x-slot name="additionalFilters">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Phòng ban</label>
                <select name="department_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">Tất cả</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot>
    </x-admin.filter-bar>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Giảng viên</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Phòng ban</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Liên hệ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Lớp phụ trách</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Yêu cầu đổi lịch</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teachers as $teacher)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $teacher->name }}</div>
                            <div class="text-xs text-slate-500">{{ $teacher->username }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">
                            {{ $teacher->department?->name ?: 'Chưa gán phòng ban' }}
                        </td>
                        <td class="px-6 py-4">
                            <div>{{ $teacher->email }}</div>
                            <div class="text-xs text-slate-500">{{ $teacher->phone ?: 'Chưa có số' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <x-admin.badge :type="match($teacher->status) {'active'=>'success','inactive'=>'warning','locked'=>'danger', default=>'default'}" :text="$teacher->statusLabel()" />
                        </td>
                        <td class="px-6 py-4">{{ $teacher->taught_courses_count }}</td>
                        <td class="px-6 py-4">{{ $teacher->schedule_change_requests_count }}</td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.teachers.show', $teacher) }}" class="text-cyan-600 hover:text-cyan-800"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.teachers.edit', $teacher) }}" class="text-slate-600 hover:text-slate-800"><i class="fas fa-edit"></i></a>
                            @if($teacher->isLocked())
                                <form class="inline" method="post" action="{{ route('admin.teachers.unlock', $teacher) }}">
                                    @csrf
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800"><i class="fas fa-unlock-alt"></i></button>
                                </form>
                            @else
                                <form class="inline" method="post" action="{{ route('admin.teachers.lock', $teacher) }}" onsubmit="return confirm('Khóa tài khoản này?')">
                                    @csrf
                                    <button type="submit" class="text-rose-600 hover:text-rose-800"><i class="fas fa-lock"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500">Chưa có giảng viên nào</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $teachers->links() }}
        </div>
    </div>
</div>
@endsection
