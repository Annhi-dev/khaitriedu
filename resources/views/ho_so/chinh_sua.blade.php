@extends($layout)

@section('title', 'Thong tin ca nhan')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    @if ($user->avatarUrl())
                        <img src="{{ $user->avatarUrl() }}" alt="Anh dai dien" class="h-14 w-14 rounded-2xl object-cover ring-2 ring-cyan-100">
                    @else
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-100 text-xl font-semibold text-cyan-700">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </span>
                    @endif
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Ho so tai khoan</h2>
                        <p class="mt-1 text-sm text-slate-500">Cập nhật thông tin để sử dụng hệ thống ổn định hơn.</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700">
                    Vai tro: {{ $user->roleLabel() }}
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <form method="POST" action="{{ route($updateRoute) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="space-y-2 sm:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Anh dai dien</span>
                        <div class="flex items-center gap-4">
                            @if ($user->avatarUrl())
                                <img src="{{ $user->avatarUrl() }}" alt="Anh dai dien hien tai" class="h-16 w-16 rounded-2xl object-cover ring-2 ring-slate-100">
                            @else
                                <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                    <i class="fas fa-user text-xl"></i>
                                </span>
                            @endif
                            <div class="flex-1">
                                <input
                                    type="file"
                                    name="avatar"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                                >
                                <p class="mt-1 text-xs text-slate-500">Ho tro JPG, PNG, WEBP. Kich thuoc toi da 2MB.</p>
                            </div>
                        </div>
                        @error('avatar')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">Ho va ten</span>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                        >
                        @error('name')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">Ten dang nhap</span>
                        <input
                            type="text"
                            name="username"
                            value="{{ old('username', $user->username) }}"
                            required
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                        >
                        @error('username')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">Email</span>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                        >
                        @error('email')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">So dien thoai</span>
                        <input
                            type="text"
                            name="phone"
                            value="{{ old('phone', $user->phone) }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                        >
                        @error('phone')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </label>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Doi mat khau (tuy chon)</h3>
                    <p class="mt-1 text-sm text-slate-500">Bo trong neu ban khong muon doi mat khau.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-slate-700">Mat khau hien tai</span>
                            <input
                                type="password"
                                name="current_password"
                                autocomplete="current-password"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                            >
                            @error('current_password')
                                <p class="text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </label>

                        <label class="space-y-2">
                            <span class="text-sm font-medium text-slate-700">Mat khau moi</span>
                            <input
                                type="password"
                                name="new_password"
                                autocomplete="new-password"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                            >
                            @error('new_password')
                                <p class="text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </label>

                        <label class="space-y-2">
                            <span class="text-sm font-medium text-slate-700">Nhap lai mat khau moi</span>
                            <input
                                type="password"
                                name="new_password_confirmation"
                                autocomplete="new-password"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                            >
                        </label>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                        <i class="fas fa-save"></i>
                        <span>Luu thay doi</span>
                    </button>

                    <a href="{{ route($backRoute) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                        <i class="fas fa-arrow-left"></i>
                        <span>Quay lai</span>
                    </a>
                </div>
            </form>
        </section>
    </div>
@endsection
