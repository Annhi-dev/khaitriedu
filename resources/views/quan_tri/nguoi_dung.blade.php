@extends('bo_cuc.quan_tri')
@section('title', 'Quản lý người dùng')
@section('content')
@php
    $totalUsers = $users->count();
    $adminCount = $users->filter(fn ($item) => $item->getRoleName() === \App\Models\User::ROLE_ADMIN)->count();
    $teacherCount = $users->filter(fn ($item) => $item->getRoleName() === \App\Models\User::ROLE_TEACHER)->count();
    $studentCount = $users->filter(fn ($item) => $item->getRoleName() === \App\Models\User::ROLE_STUDENT)->count();
    $activeCount = $users->filter(fn ($item) => $item->status === \App\Models\User::STATUS_ACTIVE)->count();
    $lockedCount = $users->filter(fn ($item) => $item->status === \App\Models\User::STATUS_LOCKED)->count();

    $summaryCards = [
        [
            'label' => 'Tổng tài khoản',
            'value' => number_format($totalUsers),
            'icon' => 'fas fa-users',
            'note' => 'Toàn bộ tài khoản trong hệ thống',
        ],
        [
            'label' => 'Đang hoạt động',
            'value' => number_format($activeCount),
            'icon' => 'fas fa-circle-check',
            'note' => 'Có thể đăng nhập và sử dụng ngay',
        ],
        [
            'label' => 'Tài khoản khóa',
            'value' => number_format($lockedCount),
            'icon' => 'fas fa-lock',
            'note' => 'Cần mở khóa nếu muốn truy cập lại',
        ],
        [
            'label' => 'Quản trị viên',
            'value' => number_format($adminCount),
            'icon' => 'fas fa-shield-halved',
            'note' => 'Nhóm có quyền quản lý cao nhất',
        ],
    ];

    $rolePills = [
        ['label' => 'Học viên', 'count' => $studentCount, 'dot' => 'bg-emerald-500'],
        ['label' => 'Giảng viên', 'count' => $teacherCount, 'dot' => 'bg-cyan-500'],
        ['label' => 'Admin', 'count' => $adminCount, 'dot' => 'bg-rose-500'],
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
                    Tài khoản hệ thống
                </div>

                <h1 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">Quản lý người dùng</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-cyan-50/90">
                    Tạo mới, cập nhật và phân quyền học viên, giảng viên, admin trong cùng một màn hình gọn gàng.
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-cyan-50">
                        <i class="fas fa-arrow-left text-xs text-cyan-700"></i>
                        Quay lại dashboard
                    </a>
                    <span class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white/90">
                        <i class="fas fa-users text-cyan-100"></i>
                        {{ number_format($totalUsers) }} tài khoản
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white/90">
                        <i class="fas fa-circle-check text-cyan-100"></i>
                        {{ number_format($activeCount) }} đang hoạt động
                    </span>
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach ($rolePills as $pill)
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white">
                            <span class="h-2 w-2 rounded-full {{ $pill['dot'] }}"></span>
                            {{ $pill['label'] }}: {{ number_format($pill['count']) }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                @foreach ($summaryCards as $card)
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-cyan-100/80">{{ $card['label'] }}</p>
                                <p class="mt-2 text-2xl font-semibold">{{ $card['value'] }}</p>
                            </div>
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/10">
                                <i class="{{ $card['icon'] }} text-lg"></i>
                            </div>
                        </div>
                        <p class="mt-3 text-xs leading-5 text-cyan-50/75">{{ $card['note'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if (isset($errors) && $errors->any())
        <div class="rounded-3xl border border-rose-200 bg-rose-50/90 p-4 text-rose-700 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div>
                    <p class="font-semibold">Có vài trường chưa đúng</p>
                    <ul class="mt-2 space-y-1 text-sm leading-6">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-24">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Tạo mới</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Thêm người dùng</h2>
                </div>
                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">Nhanh</span>
            </div>
            <p class="mt-3 text-sm leading-6 text-slate-500">
                Nhập thông tin cơ bản, chọn vai trò và tạo tài khoản mới ngay tại đây.
            </p>

            <form method="post" action="{{ route('admin.users.create') }}" class="mt-6 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Tên</label>
                        <input name="name" value="{{ old('name') }}" placeholder="Nhập tên đầy đủ" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Tên đăng nhập</label>
                        <input name="username" value="{{ old('username') }}" placeholder="Tên đăng nhập" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="name@example.com" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Mật khẩu</label>
                        <input name="password" placeholder="Mật khẩu tạm" type="password" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Vai trò</label>
                        <select name="role" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                            <option value="student" @selected(old('role', 'student') === 'student')>Học viên</option>
                            <option value="teacher" @selected(old('role') === 'teacher')>Giảng viên</option>
                            <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                        </select>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-slate-800">
                    <i class="fas fa-user-plus"></i>
                    Tạo tài khoản
                </button>
            </form>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Danh sách</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Người dùng hiện có</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Xem nhanh tên, email, vai trò và trạng thái. Nhấn sửa để mở chi tiết tài khoản.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach ($rolePills as $pill)
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700">
                            <span class="h-2 w-2 rounded-full {{ $pill['dot'] }}"></span>
                            {{ $pill['label'] }}: {{ number_format($pill['count']) }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Người dùng</th>
                            <th class="px-6 py-4 text-left font-semibold">Email</th>
                            <th class="px-6 py-4 text-left font-semibold">Vai trò</th>
                            <th class="px-6 py-4 text-left font-semibold">Trạng thái</th>
                            <th class="px-6 py-4 text-right font-semibold">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $u)
                            @php
                                $roleBadge = match ($u->getRoleName()) {
                                    \App\Models\User::ROLE_ADMIN => ['type' => 'danger', 'text' => 'Admin'],
                                    \App\Models\User::ROLE_TEACHER => ['type' => 'info', 'text' => 'Giảng viên'],
                                    \App\Models\User::ROLE_STUDENT => ['type' => 'success', 'text' => 'Học viên'],
                                    default => ['type' => 'default', 'text' => $u->roleLabel()],
                                };
                                $statusBadge = match ($u->status) {
                                    \App\Models\User::STATUS_ACTIVE => ['type' => 'success', 'text' => 'Hoạt động'],
                                    \App\Models\User::STATUS_LOCKED => ['type' => 'danger', 'text' => 'Đã khóa'],
                                    \App\Models\User::STATUS_INACTIVE => ['type' => 'warning', 'text' => 'Tạm dừng'],
                                    default => ['type' => 'default', 'text' => $u->statusLabel()],
                                };
                            @endphp
                            <tr class="group transition hover:bg-slate-50/80">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-50 to-sky-100 text-sm font-semibold text-cyan-700 ring-1 ring-cyan-100">
                                            {{ mb_strtoupper(mb_substr((string) $u->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-900">{{ $u->name }}</p>
                                            <p class="truncate text-xs text-slate-500">{{ $u->username }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $u->email }}</td>
                                <td class="px-6 py-4">
                                    <x-quan_tri.huy_hieu :type="$roleBadge['type']" :text="$roleBadge['text']" />
                                </td>
                                <td class="px-6 py-4">
                                    <x-quan_tri.huy_hieu :type="$statusBadge['type']" :text="$statusBadge['text']" />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.user.show', $u->id) }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-2 text-xs font-semibold text-cyan-700 transition hover:bg-cyan-100">
                                            <i class="fas fa-pen text-[11px]"></i>
                                            Sửa
                                        </a>
                                        <form method="post" action="{{ route('admin.users.delete', $u->id) }}" onsubmit="return confirm('Xóa người dùng?');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                <i class="fas fa-trash text-[11px]"></i>
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="mx-auto max-w-md">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <i class="fas fa-users text-xl"></i>
                                        </div>
                                        <h3 class="mt-4 text-lg font-semibold text-slate-900">Chưa có người dùng nào</h3>
                                        <p class="mt-2 text-sm leading-6 text-slate-500">
                                            Tạo tài khoản đầu tiên để bắt đầu quản lý học viên, giảng viên và admin.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
