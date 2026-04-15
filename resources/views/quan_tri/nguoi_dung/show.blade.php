@extends('bo_cuc.quan_tri')
@section('title', 'Chi tiết người dùng')
@section('content')
@php
    $roleBadge = match ($target->getRoleName()) {
        \App\Models\User::ROLE_ADMIN => ['type' => 'danger', 'text' => 'Admin'],
        \App\Models\User::ROLE_TEACHER => ['type' => 'info', 'text' => 'Giảng viên'],
        \App\Models\User::ROLE_STUDENT => ['type' => 'success', 'text' => 'Học viên'],
        default => ['type' => 'default', 'text' => $target->roleLabel()],
    };
    $statusBadge = match ($target->status) {
        \App\Models\User::STATUS_ACTIVE => ['type' => 'success', 'text' => 'Hoạt động'],
        \App\Models\User::STATUS_LOCKED => ['type' => 'danger', 'text' => 'Đã khóa'],
        \App\Models\User::STATUS_INACTIVE => ['type' => 'warning', 'text' => 'Tạm dừng'],
        default => ['type' => 'default', 'text' => $target->statusLabel()],
    };
    $initial = mb_strtoupper(mb_substr((string) $target->name, 0, 1));
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] border border-cyan-100 bg-gradient-to-br from-slate-900 via-cyan-900 to-sky-700 text-white shadow-2xl">
        <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-cyan-400/20 blur-3xl"></div>
        <div class="absolute -bottom-20 right-0 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
        <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1.2fr)_280px] lg:p-8">
            <div>
                <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-50/95 hover:text-white">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại danh sách
                </a>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-50">
                        Hồ sơ tài khoản
                    </span>
                    <x-quan_tri.huy_hieu :type="$roleBadge['type']" :text="$roleBadge['text']" />
                    <x-quan_tri.huy_hieu :type="$statusBadge['type']" :text="$statusBadge['text']" />
                </div>

                <h1 class="mt-4 text-3xl font-bold tracking-tight sm:text-4xl">{{ $target->name }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-cyan-50/90">
                    Cập nhật tên và vai trò của tài khoản này ngay tại đây.
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <span class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white/90">
                        <i class="fas fa-user text-cyan-100"></i>
                        {{ $target->username }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white/90">
                        <i class="fas fa-envelope text-cyan-100"></i>
                        {{ $target->email }}
                    </span>
                </div>
            </div>

            <div class="rounded-3xl border border-white/15 bg-white/10 p-5 backdrop-blur-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-3xl bg-white/10 text-2xl font-semibold text-white ring-1 ring-white/15">
                        {{ $initial }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-[0.2em] text-cyan-100/80">ID tài khoản</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $target->id }}</p>
                        <p class="mt-1 text-sm text-cyan-50/80">{{ $target->roleLabel() }}</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="rounded-2xl bg-white/10 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-cyan-100/80">Vai trò</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $target->roleLabel() }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-cyan-100/80">Trạng thái</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $target->statusLabel() }}</p>
                    </div>
                </div>
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

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Chỉnh sửa</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Thông tin người dùng</h2>
                </div>
                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">Cập nhật nhanh</span>
            </div>

            <form method="post" action="{{ route('admin.users.update', $target->id) }}" class="mt-6 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Tên</label>
                        <input name="name" value="{{ old('name', $target->name) }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Vai trò</label>
                        <select name="role" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                            <option value="student" @selected(old('role', $target->getRoleName()) === 'student')>Học viên</option>
                            <option value="teacher" @selected(old('role', $target->getRoleName()) === 'teacher')>Giảng viên</option>
                            <option value="admin" @selected(old('role', $target->getRoleName()) === 'admin')>Admin</option>
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Tên đăng nhập</label>
                        <input value="{{ $target->username }}" readonly class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:outline-none" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                        <input value="{{ $target->email }}" readonly class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:outline-none" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('admin.users') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Hủy
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        </section>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thông tin nhanh</h2>
                <div class="mt-5 space-y-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Người dùng</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $target->name }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Vai trò</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $target->roleLabel() }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Trạng thái</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $target->statusLabel() }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Email xác thực</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            {{ $target->email_verified_at ? $target->email_verified_at->format('d/m/Y H:i') : 'Chưa xác thực' }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-rose-200 bg-rose-50/80 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Xóa tài khoản</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Thao tác này sẽ xóa người dùng khỏi hệ thống. Hãy kiểm tra kỹ trước khi thực hiện.
                </p>
                <form method="post" action="{{ route('admin.users.delete', $target->id) }}" onsubmit="return confirm('Xóa người dùng này?');" class="mt-5">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white hover:bg-rose-700">
                        Xóa người dùng
                    </button>
                </form>
            </section>
        </aside>
    </div>
</div>
@endsection
