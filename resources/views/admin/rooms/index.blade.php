@extends('layouts.admin')

@section('title', 'Quản lý phòng học')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Quản lý phòng học" subtitle="Quản lý danh sách phòng, sức chứa và tình trạng sẵn sàng cho việc mở lớp theo slot.">
        <x-slot name="actions">
            <a href="{{ route('admin.rooms.create') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
                <i class="fas fa-plus mr-1"></i> Thêm phòng học
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid gap-4 md:grid-cols-4">
        <x-admin.stat-card label="Tổng phòng" :value="$summary['total']" icon="fas fa-door-open" color="cyan" />
        <x-admin.stat-card label="Đang hoạt động" :value="$summary['active']" icon="fas fa-circle-check" color="emerald" />
        <x-admin.stat-card label="Bảo trì" :value="$summary['maintenance']" icon="fas fa-screwdriver-wrench" color="amber" />
        <x-admin.stat-card label="Tổng sức chứa" :value="$summary['capacity']" icon="fas fa-users" color="slate" />
    </div>

    <x-admin.filter-bar :route="route('admin.rooms.index')" searchPlaceholder="Mã phòng, tên phòng hoặc vị trí..." :statuses="\App\Models\Room::statusOptions()" />

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Phòng học</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Vị trí</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Sức chứa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Khai thác slot</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Trạng thái</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rooms as $room)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $room->code }} - {{ $room->name }}</div>
                                <div class="mt-1 text-sm text-slate-600">
                                    @if($room->type == 'theory')
                                        Phòng lý thuyết
                                    @else
                                        Phòng thực hành
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $room->location ?: 'Chưa cấu hình' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ $room->capacity }} chỗ
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-800">{{ $room->time_slots_count }} khung giờ</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $room->open_time_slots_count }} đang mở đăng ký</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $type = match ($room->status) {
                                        \App\Models\Room::STATUS_ACTIVE => 'success',
                                        \App\Models\Room::STATUS_MAINTENANCE => 'warning',
                                        default => 'default',
                                    };
                                @endphp
                                <x-admin.badge :type="$type" :text="$room->statusLabel()" />
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.rooms.edit', $room) }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    Chỉnh sửa
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">Chưa có phòng học nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $rooms->links() }}
        </div>
    </div>
</div>
@endsection
