@extends('layouts.admin')
@section('title', 'Quản lý đăng ký học')
@section('content')
@php
    $pendingCount = $enrollments->getCollection()->where('status', \App\Models\Enrollment::STATUS_PENDING)->count();
    $dayLabels = [
        'Monday' => 'T2',
        'Tuesday' => 'T3',
        'Wednesday' => 'T4',
        'Thursday' => 'T5',
        'Friday' => 'T6',
        'Saturday' => 'T7',
        'Sunday' => 'CN',
    ];
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 8</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Quản lý đăng ký học</h1>
            <p class="mt-2 text-sm text-slate-600">Theo dõi yêu cầu học viên gửi lên, duyệt đăng ký, từ chối hoặc chuyển sang bước xếp lớp.</p>
            @if ($pendingCount > 0)
                <p class="mt-2 text-sm font-medium text-amber-700">Hiện có {{ $pendingCount }} đăng ký đang chờ admin xử lý.</p>
            @endif
        </div>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Về dashboard</a>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="{{ route('admin.enrollments') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1.6fr)_220px_auto] lg:items-end">
            <div>
                <label class="text-sm font-medium text-slate-700">Tìm kiếm</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Tên học viên, email, khóa học..."
                    class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none"
                >
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Trạng thái</label>
                <select name="status" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                    <option value="">Tất cả trạng thái</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                            {{ \App\Models\Enrollment::make(['status' => $status])->statusLabel() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Lọc dữ liệu</button>
                <a href="{{ route('admin.enrollments') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Học viên</th>
                        <th class="px-5 py-4 font-semibold">Khóa học</th>
                        <th class="px-5 py-4 font-semibold">Khung giờ mong muốn</th>
                        <th class="px-5 py-4 font-semibold">Xếp lớp hiện tại</th>
                        <th class="px-5 py-4 font-semibold">Trạng thái</th>
                        <th class="px-5 py-4 font-semibold">Xử lý</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($enrollments as $enrollment)
                        @php
                            $subjectName = $enrollment->subject->name ?? $enrollment->course->subject->name ?? 'Chưa xác định';
                            $categoryName = $enrollment->subject->category->name ?? $enrollment->course->subject->category->name ?? 'Chưa phân nhóm';
                            $normalizedStatus = $enrollment->normalizedStatus();
                            $statusClasses = match ($normalizedStatus) {
                                \App\Models\Enrollment::STATUS_APPROVED => 'border-cyan-200 bg-cyan-50 text-cyan-700',
                                \App\Models\Enrollment::STATUS_REJECTED => 'border-rose-200 bg-rose-50 text-rose-700',
                                \App\Models\Enrollment::STATUS_SCHEDULED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                \App\Models\Enrollment::STATUS_ACTIVE => 'border-violet-200 bg-violet-50 text-violet-700',
                                \App\Models\Enrollment::STATUS_COMPLETED => 'border-slate-300 bg-slate-100 text-slate-700',
                                default => 'border-amber-200 bg-amber-50 text-amber-700',
                            };
                            $selectedDays = $enrollment->preferred_days ? (json_decode($enrollment->preferred_days, true) ?: []) : [];
                        @endphp
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">{{ $enrollment->user?->name ?? 'Không có dữ liệu' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $enrollment->user?->email }}</div>
                                <div class="mt-2 text-xs text-slate-400">Mã đăng ký #{{ $enrollment->id }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">{{ $subjectName }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $categoryName }}</div>
                                @if ($enrollment->submitted_at)
                                    <div class="mt-2 text-xs text-slate-400">Gửi lúc {{ $enrollment->submitted_at->format('d/m/Y H:i') }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <div>{{ $enrollment->start_time ?: '--:--' }} - {{ $enrollment->end_time ?: '--:--' }}</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $selectedDays ? implode(', ', array_map(fn ($day) => $dayLabels[$day] ?? $day, $selectedDays)) : 'Chưa chọn ngày học' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <div class="font-medium text-slate-800">{{ $enrollment->course?->title ?? 'Chưa xếp lớp' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $enrollment->schedule ?: 'Chưa có lịch chính thức' }}</div>
                                <div class="mt-2 text-xs text-slate-500">Giảng viên: {{ $enrollment->assignedTeacher?->name ?? 'Chưa phân công' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $enrollment->statusLabel() }}</span>
                                @if ($enrollment->note)
                                    <p class="mt-3 rounded-2xl bg-slate-50 px-3 py-2 text-xs leading-5 text-slate-600">{{ $enrollment->note }}</p>
                                @endif
                                @if ($enrollment->reviewer)
                                    <p class="mt-2 text-xs text-slate-400">Duyệt bởi {{ $enrollment->reviewer->name }}{{ $enrollment->reviewed_at ? ' lúc ' . $enrollment->reviewed_at->format('d/m/Y H:i') : '' }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="inline-flex items-center justify-center rounded-2xl border border-cyan-200 px-4 py-2 text-sm font-medium text-cyan-700 hover:bg-cyan-50">Xem chi tiết</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">Chưa có đăng ký học nào phù hợp với bộ lọc hiện tại.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($enrollments->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $enrollments->links() }}
            </div>
        @endif
    </section>
</div>
@endsection