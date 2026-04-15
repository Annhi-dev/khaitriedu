@extends('layouts.admin')

@section('title', 'Theo dõi theo slot')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Theo dõi theo slot" subtitle="Nhìn nhanh mức độ lấp đầy, số nguyện vọng chờ và tiến độ mở lớp trên từng khung giờ học.">
        <x-slot name="actions">
            <a href="{{ route('admin.slot-registrations.index') }}" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition">
                Nguyện vọng slot
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid gap-4 md:grid-cols-4">
        <x-admin.stat-card label="Slot đang mở" :value="$summary['open_slots']" icon="fas fa-door-open" color="cyan" />
        <x-admin.stat-card label="Slot đủ điều kiện" :value="$summary['ready_slots']" icon="fas fa-circle-check" color="emerald" />
        <x-admin.stat-card label="Tổng lựa chọn" :value="$summary['choices']" icon="fas fa-list-check" color="amber" />
        <x-admin.stat-card label="Nhu cầu cao nhất" :value="$summary['top_demand']" icon="fas fa-chart-line" color="slate" />
    </div>

    <x-admin.filter-bar :route="route('admin.slot-tracking.index')" :statuses="$statuses">
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
        </x-slot>
    </x-admin.filter-bar>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Slot</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Giảng viên / phòng</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Nhu cầu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Tiến độ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($timeSlots as $timeSlot)
                        @php
                            $fillRate = $timeSlot->max_students > 0 ? round(($timeSlot->registrations_count / $timeSlot->max_students) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $timeSlot->subject?->name ?? 'Không xác định' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $timeSlot->formattedWindow() }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <div>{{ $timeSlot->teacher?->displayName() ?? 'Chưa phân công' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $timeSlot->room ? $timeSlot->room->code . ' - ' . $timeSlot->room->name : 'Chưa gán phòng' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-800">{{ $timeSlot->registrations_count }} / {{ $timeSlot->max_students }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $fillRate }}% lấp đầy</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <div>{{ $timeSlot->pending_registrations_count }} chờ xử lý</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $timeSlot->scheduled_registrations_count }} đã xếp lớp</div>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">Chưa có dữ liệu theo dõi slot.</td>
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
