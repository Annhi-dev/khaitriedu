@extends('bo_cuc.quan_tri')

@section('title', 'Quản lý nguyện vọng slot')

@section('content')
<div class="space-y-6">
    <x-quan_tri.tieu_de_trang title="Quản lý nguyện vọng slot" subtitle="Theo dõi nhu cầu học theo khung giờ để admin duyệt và gom lớp chính xác hơn.">
        <x-slot name="actions">
            <a href="{{ route('admin.slot-tracking.index') }}" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition">
                Theo dõi theo slot
            </a>
        </x-slot>
    </x-quan_tri.tieu_de_trang>

    <div class="grid gap-4 md:grid-cols-4">
        <x-quan_tri.the_thong_ke label="Tổng nguyện vọng" :value="$summary['total']" icon="fas fa-list-check" color="cyan" />
        <x-quan_tri.the_thong_ke label="Chờ xử lý" :value="$summary['pending']" icon="fas fa-hourglass-half" color="amber" />
        <x-quan_tri.the_thong_ke label="Đã xếp lớp" :value="$summary['scheduled']" icon="fas fa-graduation-cap" color="emerald" />
        <x-quan_tri.the_thong_ke label="Cần chọn lại" :value="$summary['needs_reselect']" icon="fas fa-rotate" color="rose" />
    </div>

    <x-quan_tri.thanh_loc :route="route('admin.slot-registrations.index')" searchPlaceholder="Học viên, khóa học hoặc ghi chú..." :statuses="$statuses">
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
    </x-quan_tri.thanh_loc>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Học viên</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Khóa học</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Nguyện vọng</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Người xử lý</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($registrations as $registration)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $registration->student?->name ?? 'Không xác định' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $registration->created_at?->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-700">{{ $registration->subject?->name ?? 'Không xác định' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $registration->subject?->category?->name ?? 'Chưa phân nhóm học' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ $registration->choices_count }} lựa chọn
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $type = match ($registration->status) {
                                        \App\Models\SlotRegistration::STATUS_SCHEDULED,
                                        \App\Models\SlotRegistration::STATUS_RECORDED => 'success',
                                        \App\Models\SlotRegistration::STATUS_NEEDS_RESELECT => 'warning',
                                        \App\Models\SlotRegistration::STATUS_REJECTED => 'danger',
                                        default => 'info',
                                    };
                                @endphp
                                <x-quan_tri.huy_hieu :type="$type" :text="$registration->statusLabel()" />
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ $registration->reviewer?->name ?? 'Chưa xử lý' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.slot-registrations.show', $registration) }}" class="inline-flex items-center rounded-xl border border-cyan-200 px-3 py-2 text-sm font-medium text-cyan-700 hover:bg-cyan-50">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">Chưa có nguyện vọng slot nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $registrations->links() }}
        </div>
    </div>
</div>
@endsection
