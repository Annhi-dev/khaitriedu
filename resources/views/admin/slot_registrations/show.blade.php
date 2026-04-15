@extends('layouts.admin')

@section('title', 'Chi tiết nguyện vọng slot')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Chi tiết nguyện vọng slot" subtitle="Xem đầy đủ học viên đã chọn những slot nào và thứ tự ưu tiên của từng lựa chọn.">
        <x-slot name="actions">
            <a href="{{ route('admin.slot-registrations.index') }}" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition">
                Quay lại danh sách
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,1fr)]">
        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-slate-800">Các lựa chọn đã đăng ký</h2>
            <div class="mt-5 space-y-4">
                @forelse ($choices as $choice)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-800">Ưu tiên {{ $choice->priority }}</div>
                                <div class="mt-1 text-sm text-slate-600">
                                    {{ $choice->courseTimeSlot?->subject?->name ?? 'Không xác định' }}
                                </div>
                            </div>
                            <x-admin.badge type="info" :text="$choice->courseTimeSlot?->statusLabel() ?? 'Chưa cấu hình'" />
                        </div>
                        <div class="mt-4 grid gap-3 md:grid-cols-3 text-sm text-slate-600">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-400">Khung giờ</div>
                                <div class="mt-1">{{ $choice->courseTimeSlot?->formattedWindow() ?? 'Chưa cấu hình' }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-400">Giảng viên</div>
                                <div class="mt-1">{{ $choice->courseTimeSlot?->teacher?->displayName() ?? 'Chưa phân công' }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-400">Phòng học</div>
                                <div class="mt-1">
                                    @if ($choice->courseTimeSlot?->room)
                                        {{ $choice->courseTimeSlot->room->code }} - {{ $choice->courseTimeSlot->room->name }}
                                    @else
                                        Chưa gán phòng
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        Học viên này chưa chọn slot nào.
                    </div>
                @endforelse
            </div>
        </section>

        <aside class="space-y-6">
            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-slate-800">Thông tin chung</h2>
                <div class="mt-4 space-y-4 text-sm text-slate-600">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-400">Học viên</div>
                        <div class="mt-1 font-medium text-slate-800">{{ $slotRegistration->student?->name ?? 'Không xác định' }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-400">Khóa học</div>
                        <div class="mt-1 font-medium text-slate-800">{{ $slotRegistration->subject?->name ?? 'Không xác định' }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-400">Trạng thái</div>
                        <div class="mt-1">
                            <x-admin.badge type="info" :text="$slotRegistration->statusLabel()" />
                        </div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-400">Người xử lý</div>
                        <div class="mt-1">{{ $slotRegistration->reviewer?->name ?? 'Chưa xử lý' }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-400">Ghi chú</div>
                        <div class="mt-1 leading-6">{{ $slotRegistration->note ?: 'Không có ghi chú.' }}</div>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
