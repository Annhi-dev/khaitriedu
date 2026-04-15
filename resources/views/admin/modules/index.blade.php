@extends('layouts.admin')

@section('title', 'Quản lý module')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Quản lý module" subtitle="Tổng hợp module theo từng lớp học, số buổi và trạng thái hiển thị.">
        <x-slot name="actions">
            <a href="{{ route('admin.courses') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
                <i class="fas fa-people-group mr-1"></i> Danh sách lớp học
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid gap-4 md:grid-cols-3">
        <x-admin.stat-card label="Lớp học có module" :value="$summary['course_count']" icon="fas fa-people-group" color="cyan" />
        <x-admin.stat-card label="Tổng module" :value="$summary['module_count']" icon="fas fa-cubes-stacked" color="emerald" />
        <x-admin.stat-card label="Module đang hiển thị" :value="$summary['published_module_count']" icon="fas fa-eye" color="amber" />
    </div>

    <x-admin.filter-bar :route="route('admin.modules.index')" searchPlaceholder="Tên lớp học, khóa học hoặc nhóm học..." />

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Lớp học</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Khóa học public</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Giảng viên</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Thống kê module</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($courses as $course)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $course->title }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $course->formattedSchedule() }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-700">{{ $course->subject?->name ?? 'Chưa gắn khóa học public' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $course->subject?->category?->name ?? 'Chưa phân nhóm học' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ $course->teacher?->displayName() ?? 'Chưa phân công' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-800">{{ $course->modules_count }} module</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $course->published_modules_count }} đang hiển thị · {{ $course->active_students_count }} học viên đang theo học
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <form method="post" action="{{ route('admin.courses.modules.sync-template', $course) }}" onsubmit="return confirm('Sinh curriculum mẫu cho lớp này? Các module placeholder sẽ được điền lại theo template chuẩn.');">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center rounded-xl border border-emerald-200 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-50">
                                            Sinh mẫu
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.courses.modules.index', $course) }}" class="inline-flex items-center rounded-xl border border-cyan-200 px-3 py-2 text-sm font-medium text-cyan-700 hover:bg-cyan-50">
                                        Mở module
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">Chưa có lớp học nào để quản lý module.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $courses->links() }}
        </div>
    </div>
</div>
@endsection
