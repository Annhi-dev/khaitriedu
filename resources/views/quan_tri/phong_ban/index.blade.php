@extends('bo_cuc.quan_tri')
@section('title', 'Quản lý phòng ban')
@section('content')
@php
    $summaryCards = [
        [
            'label' => 'Tổng phòng ban',
            'value' => number_format($summary['total'] ?? 0),
            'icon' => 'fas fa-building',
            'note' => 'Cấu trúc phòng ban đang được quản lý trong hệ thống.',
        ],
        [
            'label' => 'Đang hoạt động',
            'value' => number_format($summary['active'] ?? 0),
            'icon' => 'fas fa-circle-check',
            'note' => 'Phòng ban sẵn sàng phân bổ giảng viên.',
        ],
        [
            'label' => 'Tạm ngưng',
            'value' => number_format($summary['inactive'] ?? 0),
            'icon' => 'fas fa-pause-circle',
            'note' => 'Các phòng ban đang tạm dừng sử dụng.',
        ],
        [
            'label' => 'Tổng giảng viên',
            'value' => number_format($summary['teachers'] ?? 0),
            'icon' => 'fas fa-chalkboard-user',
            'note' => 'Tổng số giảng viên đang thuộc các phòng ban.',
        ],
    ];
@endphp

<div class="max-w-7xl mx-auto space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] border border-cyan-100 bg-gradient-to-br from-slate-900 via-cyan-900 to-sky-700 text-white shadow-2xl">
        <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-cyan-400/20 blur-3xl"></div>
        <div class="absolute -bottom-20 right-0 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
        <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)] lg:p-8">
            <div class="relative">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-cyan-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    Phòng ban
                </div>

                <h1 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">Quản lý phòng ban</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-cyan-50/90">
                    Theo dõi cơ cấu phòng ban, trạng thái hoạt động và số giảng viên đang được phân bổ.
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('admin.departments.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-cyan-50">
                        <i class="fas fa-plus text-xs text-cyan-700"></i>
                        Thêm phòng ban
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white/90 transition hover:bg-white/15">
                        <i class="fas fa-house text-cyan-100"></i>
                        Dashboard
                    </a>
                    <span class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white/90">
                        <i class="fas fa-building text-cyan-100"></i>
                        {{ number_format($summary['total'] ?? 0) }} phòng ban
                    </span>
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white">
                        <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                        Hoạt động: {{ number_format($summary['active'] ?? 0) }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white">
                        <span class="h-2 w-2 rounded-full bg-amber-300"></span>
                        Tạm ngưng: {{ number_format($summary['inactive'] ?? 0) }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white">
                        <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                        Giảng viên: {{ number_format($summary['teachers'] ?? 0) }}
                    </span>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                @foreach ($summaryCards as $card)
                    <x-quan_tri.the_thong_ke :label="$card['label']" :value="$card['value']" :icon="$card['icon']" color="cyan" :note="$card['note']" />
                @endforeach
            </div>
        </div>
    </section>

    <x-quan_tri.thanh_loc
        :route="route('admin.departments.index')"
        searchPlaceholder="Mã phòng ban, tên phòng ban, mô tả..."
        :statuses="\App\Models\Department::statusOptions()"
    />

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Danh sách</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Phòng ban hiện có</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    Lọc theo mã, tên hoặc trạng thái để xem nhanh cơ cấu phòng ban và số giảng viên phụ trách.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700">
                    <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                    {{ number_format($departments->total()) }} kết quả
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700">
                    <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                    Trang {{ $departments->currentPage() }} / {{ $departments->lastPage() }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-[0.18em] text-slate-500">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Phòng ban</th>
                        <th class="px-6 py-4 text-left font-semibold">Mô tả</th>
                        <th class="px-6 py-4 text-left font-semibold">Số giảng viên</th>
                        <th class="px-6 py-4 text-left font-semibold">Trạng thái</th>
                        <th class="px-6 py-4 text-right font-semibold">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($departments as $department)
                        <tr class="group transition hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-50 to-sky-100 text-sm font-semibold text-cyan-700 ring-1 ring-cyan-100">
                                        {{ mb_strtoupper(mb_substr((string) $department->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-900">{{ $department->name }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $department->code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $department->description ?: 'Chưa có mô tả' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-700">
                                    <i class="fas fa-chalkboard-user text-[11px]"></i>
                                    {{ number_format($department->teachers_count) }} giảng viên
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <x-quan_tri.huy_hieu :type="$department->status === \App\Models\Department::STATUS_ACTIVE ? 'success' : 'warning'" :text="$department->statusLabel()" />
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.departments.edit', $department) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                        <i class="fas fa-pen text-[11px]"></i>
                                        Chỉnh sửa
                                    </a>
                                    @if ($department->status === \App\Models\Department::STATUS_ACTIVE)
                                        <form class="inline" method="post" action="{{ route('admin.departments.deactivate', $department) }}" onsubmit="return confirm('Chuyển phòng ban này sang tạm ngưng?')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                                <i class="fas fa-pause text-[11px]"></i>
                                                Tạm ngưng
                                            </button>
                                        </form>
                                    @else
                                        <form class="inline" method="post" action="{{ route('admin.departments.activate', $department) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                                <i class="fas fa-play text-[11px]"></i>
                                                Kích hoạt
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="mx-auto max-w-md">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <i class="fas fa-building text-xl"></i>
                                    </div>
                                    <h3 class="mt-4 text-lg font-semibold text-slate-900">Chưa có phòng ban nào</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-500">
                                        Tạo phòng ban đầu tiên để bắt đầu phân bổ giảng viên và quản lý cơ cấu.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 p-6">
            <x-quan_tri.phan_trang :paginator="$departments" label="phòng ban" />
        </div>
    </section>
</div>
@endsection
