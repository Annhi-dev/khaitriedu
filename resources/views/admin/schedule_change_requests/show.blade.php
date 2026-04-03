@extends('layouts.admin')
@section('title', 'Chi tiet yeu cau doi lich')
@section('content')
@php
    $badgeClasses = match ($scheduleChangeRequest->status) {
        \App\Models\ScheduleChangeRequest::STATUS_APPROVED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        \App\Models\ScheduleChangeRequest::STATUS_REJECTED => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-amber-200 bg-amber-50 text-amber-700',
    };
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('admin.schedule-change-requests.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                <i class="fas fa-arrow-left"></i>
                Quay lai danh sach yeu cau doi lich
            </a>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Chi tiet yeu cau doi lich</h1>
            <p class="mt-2 text-sm text-slate-600">Admin danh gia de xuat doi lich cua giang vien va quyet dinh cap nhat lich hoc.</p>
        </div>
        <span class="inline-flex rounded-full border px-4 py-2 text-sm font-semibold {{ $badgeClasses }}">{{ $scheduleChangeRequest->statusLabel() }}</span>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)]">
        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thong tin giang vien va lop hoc</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2 text-sm text-slate-600">
                    <p><strong>Giang vien:</strong> {{ $scheduleChangeRequest->teacher?->name ?? 'Khong co du lieu' }}</p>
                    <p><strong>Email:</strong> {{ $scheduleChangeRequest->teacher?->email ?? 'Khong co du lieu' }}</p>
                    <p><strong>Lop hoc:</strong> {{ $scheduleChangeRequest->targetTitle() }}</p>
                    <p><strong>Khoa hoc:</strong> {{ $scheduleChangeRequest->subjectName() }}</p>
                    <p class="md:col-span-2"><strong>Nhom hoc:</strong> {{ $scheduleChangeRequest->categoryName() }}</p>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thong tin doi lich</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2 text-sm text-slate-600">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Lich hien tai</p>
                        <p class="mt-1 font-medium text-slate-900">{{ $scheduleChangeRequest->currentScheduleLabel() }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Lich de xuat</p>
                        <p class="mt-1 font-medium text-slate-900">{{ $scheduleChangeRequest->requestedScheduleLabel() }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Phong hien tai</p>
                        <p class="mt-1 font-medium text-slate-900">
                            {{ $scheduleChangeRequest->isClassScheduleRequest() ? $scheduleChangeRequest->currentRoomLabel() : 'Khong ap dung' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Phong de xuat</p>
                        <p class="mt-1 font-medium text-slate-900">
                            {{ $scheduleChangeRequest->isClassScheduleRequest() ? $scheduleChangeRequest->requestedRoomLabel() : 'Khong ap dung' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ngay gui</p>
                        <p class="mt-1 font-medium text-slate-900">{{ optional($scheduleChangeRequest->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Nguoi duyet gan nhat</p>
                        <p class="mt-1 font-medium text-slate-900">{{ $scheduleChangeRequest->reviewer?->name ?? 'Chua co' }}</p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                    <p class="font-medium text-slate-800">Ly do giang vien</p>
                    <p class="mt-2 leading-6">{{ $scheduleChangeRequest->reason }}</p>
                </div>

                @if ($scheduleChangeRequest->admin_note)
                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-600">
                        <p class="font-medium text-slate-800">Ghi chu admin</p>
                        <p class="mt-2 leading-6">{{ $scheduleChangeRequest->admin_note }}</p>
                    </div>
                @endif
            </section>
        </div>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Xu ly yeu cau</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Neu duyet, he thong se cap nhat lich lop hoc va dong bo lich moi cho hoc vien. Neu tu choi, lich hien tai duoc giu nguyen.</p>

            @if ($scheduleChangeRequest->isPending())
                <form method="post" action="{{ route('admin.schedule-change-requests.review', $scheduleChangeRequest) }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm font-medium text-slate-700">Ghi chu admin</label>
                        <textarea name="admin_note" rows="5" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Dung cho ghi chu duyet hoac ly do tu choi.">{{ old('admin_note', $scheduleChangeRequest->admin_note) }}</textarea>
                        @error('admin_note')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    @error('action')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror

                    <div class="grid gap-3 sm:grid-cols-2">
                        <button name="action" value="approve" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Duyet va cap nhat lich</button>
                        <button name="action" value="reject" class="inline-flex items-center justify-center rounded-2xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 hover:bg-rose-100">Tu choi yeu cau</button>
                    </div>
                </form>
            @else
                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                    <p class="font-medium text-slate-800">Yeu cau da duoc xu ly</p>
                    <p class="mt-2 leading-6">{{ $scheduleChangeRequest->reviewed_at ? 'Xu ly luc ' . $scheduleChangeRequest->reviewed_at->format('d/m/Y H:i') : 'Khong co moc thoi gian xu ly.' }}</p>
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection
