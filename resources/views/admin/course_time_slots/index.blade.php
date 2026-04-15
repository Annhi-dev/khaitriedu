@extends('layouts.admin')

@section('title', 'Quản lý khung giờ học')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Quản lý khung giờ học" subtitle="Theo dõi cấu hình mở slot, giảng viên, phòng học và số lượng nguyện vọng trên từng khung giờ.">
        <x-slot name="actions">
            <a href="{{ route('admin.course-time-slots.create') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
                <i class="fas fa-plus mr-1"></i> Thêm khung giờ
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid gap-4 md:grid-cols-4">
        <x-admin.stat-card label="Đã cấu hình" :value="$summary['configured']" icon="fas fa-clock" color="cyan" />
        <x-admin.stat-card label="Đang mở đăng ký" :value="$summary['open']" icon="fas fa-door-open" color="emerald" />
        <x-admin.stat-card label="Đủ điều kiện mở lớp" :value="$summary['ready']" icon="fas fa-circle-check" color="amber" />
        <x-admin.stat-card label="Đã mở lớp" :value="$summary['opened']" icon="fas fa-graduation-cap" color="slate" />
    </div>

    <x-admin.filter-bar :route="route('admin.course-time-slots.index')" searchPlaceholder="Khóa học, giảng viên, phòng học..." :statuses="$statuses">
        <x-slot name="additionalFilters">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Khóa học</label>
                <select name="subject_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">Tất cả</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string) request('subject_id') === (string) $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Giảng viên</label>
                <select name="teacher_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">Tất cả</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((string) request('teacher_id') === (string) $teacher->id)>{{ $teacher->displayName() }}</option>
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
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Khóa học</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Lịch học</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Giảng viên / phòng</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Chỉ tiêu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Nguyện vọng</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Trạng thái</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($timeSlots as $timeSlot)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $timeSlot->subject?->name ?? 'Không xác định' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $timeSlot->subject?->category?->name ?? 'Chưa phân nhóm học' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-700">{{ $timeSlot->formattedWindow() }}</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    @if ($timeSlot->registration_open_at || $timeSlot->registration_close_at)
                                        {{ optional($timeSlot->registration_open_at)->format('d/m H:i') ?: '...' }} - {{ optional($timeSlot->registration_close_at)->format('d/m H:i') ?: '...' }}
                                    @else
                                        Chưa mở khung đăng ký
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">
                                <div>{{ $timeSlot->teacher?->displayName() ?? 'Chưa phân công' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $timeSlot->room ? $timeSlot->room->code . ' - ' . $timeSlot->room->name : 'Chưa gán phòng' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ $timeSlot->min_students }} - {{ $timeSlot->max_students }} học viên
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ $timeSlot->registrations_count }} nguyện vọng
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $type = match ($timeSlot->status) {
                                        \App\Models\CourseTimeSlot::STATUS_OPEN_FOR_REGISTRATION,
                                        \App\Models\CourseTimeSlot::STATUS_READY_TO_OPEN_CLASS,
                                        \App\Models\CourseTimeSlot::STATUS_CLASS_OPENED => 'success',
                                        \App\Models\CourseTimeSlot::STATUS_CANCELLED => 'danger',
                                        default => 'warning',
                                    };
                                @endphp
                                <x-admin.badge :type="$type" :text="$timeSlot->statusLabel()" />
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.course-time-slots.edit', $timeSlot) }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    Chỉnh sửa
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">Chưa có khung giờ học nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $timeSlots->links() }}
        </div>
    </div>
</div>
@endsection
