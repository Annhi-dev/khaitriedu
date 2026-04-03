<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Khu vực học viên') | KhaiTriEdu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900">
    @php
        $studentUser = $current ?? $user ?? Auth::user() ?? (session('user_id') ? \App\Models\User::with('role')->find(session('user_id')) : null);
        $pageTitle = trim($__env->yieldContent('title')) ?: 'Khu vực học viên';
        $navItems = [
            ['route' => 'student.dashboard', 'label' => 'Tổng quan', 'icon' => 'fa-house'],
            ['route' => 'student.enroll.index', 'label' => 'Đăng ký khóa học', 'icon' => 'fa-book-open'],
            ['route' => 'student.enroll.my-classes', 'label' => 'Lớp của tôi', 'icon' => 'fa-users'],
            ['route' => 'student.schedule', 'label' => 'Thời khóa biểu', 'icon' => 'fa-calendar-days'],
            ['route' => 'student.grades', 'label' => 'Kết quả học tập', 'icon' => 'fa-square-poll-horizontal'],
        ];
    @endphp

    <div
        x-data="studentLayout()"
        x-init="init()"
        class="min-h-screen lg:grid"
        id="student-shell"
    >
        <div
            x-show="mobileNavOpen"
            x-cloak
            class="fixed inset-0 z-40 bg-slate-950/45 lg:hidden"
            @click="mobileNavOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-[280px] -translate-x-full flex-col overflow-y-auto border-r border-cyan-950/10 bg-gradient-to-b from-slate-950 via-slate-900 to-cyan-950 px-5 py-5 text-slate-100 transition duration-300 lg:static lg:w-auto lg:translate-x-0"
            :class="{ 'translate-x-0': mobileNavOpen }"
        >
            <div class="flex items-center justify-between">
                <a href="{{ route('student.dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-cyan-400/15 text-cyan-300">
                        <i class="fas fa-user-graduate text-lg"></i>
                    </span>
                    <div class="student-brand-copy min-w-0">
                        <p class="truncate text-xs uppercase tracking-[0.28em] text-cyan-200/75">Student Hub</p>
                        <p class="truncate text-lg font-semibold text-white">KhaiTriEdu</p>
                    </div>
                </a>

                <button
                    type="button"
                    class="rounded-xl border border-slate-800 px-3 py-2 text-sm text-slate-300 lg:hidden"
                    @click="mobileNavOpen = false"
                >
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="student-user-panel mt-7 flex items-center gap-3 rounded-3xl border border-white/10 bg-white/5 p-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-cyan-400/15 text-lg font-semibold text-cyan-200">
                    {{ strtoupper(substr($studentUser?->name ?? 'H', 0, 1)) }}
                </span>
                <div class="student-user-copy min-w-0">
                    <p class="truncate text-sm font-semibold text-white">{{ $studentUser?->name ?? 'Học viên' }}</p>
                    <p class="truncate text-xs text-slate-400">{{ $studentUser?->email ?? 'student@khaitriedu.local' }}</p>
                    <p class="mt-1 inline-flex rounded-full bg-emerald-500/10 px-2 py-1 text-[11px] font-medium text-emerald-200">
                        {{ $studentUser?->roleLabel() ?? 'Học viên' }}
                    </p>
                </div>
            </div>

            <nav class="mt-8 space-y-2">
                @foreach ($navItems as $item)
                    @php
                        $isActive = request()->routeIs($item['route'])
                            || ($item['route'] === 'student.enroll.index' && request()->routeIs('student.enroll.select'))
                            || ($item['route'] === 'student.enroll.index' && request()->routeIs('student.enroll.request-form'))
                            || ($item['route'] === 'student.enroll.my-classes' && request()->routeIs('student.enroll.my-classes'));
                    @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        class="student-nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $isActive ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-950/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
                        title="{{ $item['label'] }}"
                    >
                        <i class="student-nav-icon fas {{ $item['icon'] }} w-5 shrink-0 text-center"></i>
                        <span class="student-nav-text truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="student-sidebar-note mt-8 rounded-3xl border border-cyan-400/10 bg-cyan-400/5 p-4 text-sm text-slate-300">
                <p class="font-semibold text-white">Góc học tập cá nhân</p>
                <p class="mt-2 leading-6 text-slate-400">Theo dõi đăng ký, lịch học và kết quả ngay trong một giao diện cố định, gọn và dễ dùng.</p>
            </div>

            <div class="mt-auto pt-8">
                <div class="student-sidebar-footer mb-3 text-[11px] uppercase tracking-[0.24em] text-slate-500">
                    Điều hướng nhanh
                </div>

                <div class="space-y-2">
                    <a
                        href="{{ route('home') }}"
                        class="student-sidebar-action flex items-center gap-3 rounded-2xl px-4 py-3 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white"
                        title="Về website"
                    >
                        <i class="fas fa-house w-5 text-center"></i>
                        <span class="student-sidebar-footer">Về website</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="student-sidebar-action flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-sm text-rose-300 transition hover:bg-rose-500/10 hover:text-rose-200"
                            title="Đăng xuất"
                        >
                            <i class="fas fa-right-from-bracket w-5 text-center"></i>
                            <span class="student-sidebar-footer">Đăng xuất</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="min-w-0">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 text-slate-700 lg:hidden"
                            @click="mobileNavOpen = true"
                        >
                            <i class="fas fa-bars"></i>
                        </button>

                        <button
                            type="button"
                            class="hidden h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700 lg:inline-flex"
                            @click="toggleDesktopSidebar()"
                            title="Thu gọn / mở rộng menu"
                        >
                            <i class="fas fa-bars-staggered"></i>
                        </button>

                        <div class="min-w-0">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-400">@yield('eyebrow', 'Student Workspace')</p>
                            <h1 class="truncate text-xl font-semibold text-slate-900">{{ $pageTitle }}</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <a
                            href="{{ route('home') }}"
                            class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700"
                        >
                            <i class="fas fa-house"></i>
                            <span class="hidden sm:inline">Trang chủ</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50 hover:text-rose-700"
                            >
                                <i class="fas fa-right-from-bracket"></i>
                                <span class="hidden sm:inline">Đăng xuất</span>
                            </button>
                        </form>

                        @hasSection('header_actions')
                            <div class="hidden items-center gap-3 lg:flex">
                                @yield('header_actions')
                            </div>
                        @endif

                        <div class="hidden items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600 md:flex">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-cyan-500/10 font-semibold text-cyan-700">
                                {{ strtoupper(substr($studentUser?->name ?? 'H', 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-800">{{ $studentUser?->name ?? 'Học viên' }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $studentUser?->roleLabel() ?? 'Học viên' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        function studentLayout() {
            return {
                mobileNavOpen: false,
                init() {
                    const shell = document.getElementById('student-shell');
                    if (!shell) {
                        return;
                    }

                    const savedState = localStorage.getItem('studentSidebarState');
                    if (savedState === 'collapsed' && window.innerWidth >= 1024) {
                        shell.classList.add('student-sidebar-collapsed');
                    }
                },
                toggleDesktopSidebar() {
                    const shell = document.getElementById('student-shell');
                    if (!shell) {
                        return;
                    }

                    shell.classList.toggle('student-sidebar-collapsed');
                    localStorage.setItem(
                        'studentSidebarState',
                        shell.classList.contains('student-sidebar-collapsed') ? 'collapsed' : 'expanded'
                    );
                },
            };
        }
    </script>
</body>
</html>
