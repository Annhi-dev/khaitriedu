@extends('bo_cuc.quan_tri')
@section('title', 'Quản lý học viên')
@section('content')
@php
    $summaryCards = [
        [
            'label' => 'Tổng học viên',
            'value' => number_format($summary['total'] ?? 0),
            'icon' => 'fas fa-user-graduate',
            'note' => 'Tất cả tài khoản học viên đang có trong hệ thống.',
        ],
        [
            'label' => 'Đang hoạt động',
            'value' => number_format($summary['active'] ?? 0),
            'icon' => 'fas fa-circle-check',
            'note' => 'Sẵn sàng đăng nhập và sử dụng ngay.',
        ],
        [
            'label' => 'Tài khoản khóa',
            'value' => number_format($summary['locked'] ?? 0),
            'icon' => 'fas fa-lock',
            'note' => 'Cần mở khóa trước khi tiếp tục truy cập.',
        ],
        [
            'label' => 'Đã đăng ký học',
            'value' => number_format($summary['enrolled'] ?? 0),
            'icon' => 'fas fa-book-open',
            'note' => 'Học viên đã có ít nhất một đăng ký.',
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
                    Học viên
                </div>

                <h1 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">Quản lý học viên</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-cyan-50/90">
                    Theo dõi trạng thái, thông tin liên hệ và lịch đăng ký của từng học viên trong một giao diện rõ ràng hơn.
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('admin.students.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-cyan-50">
                        <i class="fas fa-user-plus text-xs text-cyan-700"></i>
                        Thêm học viên
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white/90 transition hover:bg-white/15">
                        <i class="fas fa-house text-cyan-100"></i>
                        Dashboard
                    </a>
                    <span class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white/90">
                        <i class="fas fa-users text-cyan-100"></i>
                        {{ number_format($summary['total'] ?? 0) }} hồ sơ
                    </span>
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white">
                        <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                        Hoạt động: {{ number_format($summary['active'] ?? 0) }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white">
                        <span class="h-2 w-2 rounded-full bg-rose-300"></span>
                        Khóa: {{ number_format($summary['locked'] ?? 0) }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white">
                        <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                        Đã đăng ký: {{ number_format($summary['enrolled'] ?? 0) }}
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
        :route="route('admin.students.index')"
        searchPlaceholder="Tên, email, số điện thoại"
        :statuses="['active' => 'Hoạt động', 'inactive' => 'Tạm dừng', 'locked' => 'Khóa']"
    />

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Danh sách</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Học viên hiện có</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    Tìm nhanh học viên theo tên, email hoặc số điện thoại. Mỗi hàng có trạng thái và thao tác ngay bên phải.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700">
                    <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                    {{ number_format($students->total()) }} kết quả
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700">
                    <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                    Trang {{ $students->currentPage() }} / {{ $students->lastPage() }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-[0.18em] text-slate-500">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Học viên</th>
                        <th class="px-6 py-4 text-left font-semibold">Liên hệ</th>
                        <th class="px-6 py-4 text-left font-semibold">Trạng thái</th>
                        <th class="px-6 py-4 text-left font-semibold">Đăng ký</th>
                        <th class="px-6 py-4 text-right font-semibold">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($students as $student)
                        <tr class="group transition hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-50 to-sky-100 text-sm font-semibold text-cyan-700 ring-1 ring-cyan-100">
                                        {{ mb_strtoupper(mb_substr((string) $student->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-900">{{ $student->name }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $student->username }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                <div class="font-medium text-slate-900">{{ $student->email }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $student->phone ?: 'Chưa có số điện thoại' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <x-quan_tri.huy_hieu :type="match ($student->status) {
                                    \App\Models\NguoiDung::STATUS_ACTIVE => 'success',
                                    \App\Models\NguoiDung::STATUS_INACTIVE => 'warning',
                                    \App\Models\NguoiDung::STATUS_LOCKED => 'danger',
                                    default => 'default',
                                }" :text="$student->statusLabel()" />
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-700">
                                    <i class="fas fa-book-open text-[11px]"></i>
                                    {{ number_format($student->enrollments_count) }} đăng ký
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.students.show', $student) }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-2 text-xs font-semibold text-cyan-700 transition hover:bg-cyan-100">
                                        <i class="fas fa-eye text-[11px]"></i>
                                        Xem
                                    </a>
                                    <a href="{{ route('admin.students.edit', $student) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                        <i class="fas fa-pen text-[11px]"></i>
                                        Sửa
                                    </a>
                                    @if ($student->isLocked())
                                        <form class="inline" method="post" action="{{ route('admin.students.unlock', $student) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                                <i class="fas fa-unlock text-[11px]"></i>
                                                Mở khóa
                                            </button>
                                        </form>
                                    @else
                                        <form class="inline" method="post" action="{{ route('admin.students.lock', $student) }}" onsubmit="return confirm('Khóa tài khoản này?')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                <i class="fas fa-lock text-[11px]"></i>
                                                Khóa
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
                                        <i class="fas fa-user-graduate text-xl"></i>
                                    </div>
                                    <h3 class="mt-4 text-lg font-semibold text-slate-900">Chưa có học viên nào</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-500">
                                        Tạo hồ sơ học viên đầu tiên để bắt đầu quản lý đăng ký và lịch học.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 p-6">
            <x-quan_tri.phan_trang :paginator="$students" label="học viên" />
        </div>
    </section>
</div>
@endsection
