<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị hệ thống') - KhaiTriEdu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="min-h-screen bg-slate-100 text-slate-800" style="font-family: 'Manrope', sans-serif;">
@php
    $adminUser = $adminAuthUser ?? (session('user_id') ? \App\Models\User::find(session('user_id')) : null);
    $pageTitle = trim($__env->yieldContent('title')) ?: 'Quản trị hệ thống';
    $menuSections = [
        [
            'label' => 'Tổng quan',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'fas fa-gauge-high', 'route' => 'admin.dashboard', 'active' => request()->routeIs('admin.dashboard')],
                ['label' => 'Báo cáo', 'icon' => 'fas fa-chart-column', 'route' => 'admin.report', 'active' => request()->routeIs('admin.report')],
            ],
        ],
        [
            'label' => 'Quản lý người dùng',
            'items' => [
                ['label' => 'Học viên', 'icon' => 'fas fa-user-graduate', 'route' => 'admin.students.index', 'active' => request()->routeIs('admin.students.*')],
                ['label' => 'Giảng viên', 'icon' => 'fas fa-chalkboard-user', 'route' => 'admin.teachers.index', 'active' => request()->routeIs('admin.teachers.*')],
                ['label' => 'Tài khoản hệ thống', 'icon' => 'fas fa-users-gear', 'route' => 'admin.users', 'active' => request()->routeIs('admin.users*') || request()->routeIs('admin.user.*')],
            ],
        ],
        [
            'label' => 'Quản lý đào tạo',
            'items' => [
                ['label' => 'Nhóm học', 'icon' => 'fas fa-layer-group', 'route' => 'admin.categories', 'active' => request()->routeIs('admin.categories*')],
                ['label' => 'Khóa học', 'icon' => 'fas fa-book-open', 'route' => 'admin.subjects', 'active' => request()->routeIs('admin.subjects*') || request()->routeIs('admin.subject.*')],
                ['label' => 'Lớp học và module', 'icon' => 'fas fa-people-group', 'route' => 'admin.courses', 'active' => request()->routeIs('admin.courses*') || request()->routeIs('admin.course.*')],
                ['label' => 'Đăng ký học', 'icon' => 'fas fa-clipboard-check', 'route' => 'admin.enrollments', 'active' => request()->routeIs('admin.enrollments*')],
                ['label' => 'Lịch học', 'icon' => 'fas fa-calendar-days', 'route' => 'admin.schedules.index', 'active' => request()->routeIs('admin.schedules.*')],
            ],
        ],
        [
            'label' => 'Vận hành',
            'items' => [
                ['label' => 'Ứng tuyển giảng viên', 'icon' => 'fas fa-file-signature', 'route' => 'admin.teacher-applications', 'active' => request()->routeIs('admin.teacher-applications*')],
                ['label' => 'Yêu cầu đổi lịch', 'icon' => 'fas fa-calendar-rotate', 'route' => 'admin.schedule-change-requests.index', 'active' => request()->routeIs('admin.schedule-change-requests.*')],
            ],
        ],
    ];
@endphp
<div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.15),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(16,185,129,0.12),_transparent_28%),linear-gradient(180deg,_#f8fafc_0%,_#eef2ff_100%)]" x-data="{ sidebarOpen: false }">
    <div class="absolute inset-x-0 top-0 h-64 bg-[linear-gradient(135deg,_rgba(15,23,42,0.96),_rgba(15,118,110,0.82))]"></div>
    <div class="relative min-h-screen lg:grid lg:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="hidden min-h-screen border-r border-white/10 bg-slate-950 text-slate-100 lg:flex lg:flex-col">
            <div class="border-b border-white/10 px-7 py-7">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-cyan-400/15 text-cyan-300 ring-1 ring-cyan-300/20">
                        <i class="fas fa-shield-halved text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.35em] text-cyan-300/80">Enterprise Admin</p>
                        <h1 class="mt-1 text-xl font-extrabold text-white">KhaiTriEdu</h1>
                        <p class="mt-1 text-sm text-slate-400">Điều phối đào tạo tập trung</p>
                    </div>
                </a>
            </div>
            <div class="flex-1 overflow-y-auto px-4 py-6">
                @foreach ($menuSections as $section)
                    <div class="mb-6 last:mb-0">
                        <p class="px-3 text-[11px] uppercase tracking-[0.3em] text-slate-500">{{ $section['label'] }}</p>
                        <nav class="mt-3 space-y-1.5">
                            @foreach ($section['items'] as $item)
                                @php
                                    $classes = 'group flex items-center justify-between gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition';
                                    $classes .= $item['active']
                                        ? ' bg-white/10 text-white ring-1 ring-white/10 shadow-[0_10px_30px_rgba(2,6,23,0.25)]'
                                        : ' text-slate-300 hover:bg-white/5 hover:text-white';
                                @endphp
                                <a href="{{ route($item['route']) }}" class="{{ $classes }}">
                                    <span class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl {{ $item['active'] ? 'bg-cyan-400/15 text-cyan-300' : 'bg-white/5 text-slate-400 group-hover:text-cyan-300' }}">
                                            <i class="{{ $item['icon'] }} text-sm"></i>
                                        </span>
                                        <span>{{ $item['label'] }}</span>
                                    </span>
                                    @if ($item['active'])
                                        <span class="h-2.5 w-2.5 rounded-full bg-cyan-300"></span>
                                    @endif
                                </a>
                            @endforeach
                        </nav>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-white/10 px-6 py-6">
                <div class="rounded-3xl border border-white/10 bg-white/5 p-5 text-sm text-slate-300">
                    <p class="font-semibold text-white">Nguyên tắc vận hành</p>
                    <p class="mt-2 leading-6">Mọi quyết định quan trọng như duyệt học viên, xếp lớp và đổi lịch đều phải đi qua admin để giữ chất lượng đào tạo ổn định.</p>
                </div>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="px-4 pt-4 sm:px-6 lg:px-8 lg:pt-6">
                <div class="overflow-hidden rounded-[32px] border border-white/50 bg-white/75 shadow-[0_20px_80px_rgba(15,23,42,0.08)] backdrop-blur-xl">
                    <div class="flex flex-col gap-4 border-b border-slate-200/80 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                        <div class="flex items-center gap-3">
                            <button @click="sidebarOpen = true" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 lg:hidden">
                                <i class="fas fa-bars"></i>
                            </button>
                            <div>
                                <p class="text-[11px] uppercase tracking-[0.35em] text-slate-400">Admin Workspace</p>
                                <h2 class="mt-1 text-2xl font-extrabold text-slate-950">{{ $pageTitle }}</h2>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="rounded-2xl bg-slate-950 px-4 py-3 text-sm text-slate-200 shadow-lg shadow-slate-950/10">
                                <p class="text-[11px] uppercase tracking-[0.25em] text-slate-400">Phiên đăng nhập</p>
                                <p class="mt-1 font-semibold text-white">{{ $adminUser?->name ?? 'Admin' }}</p>
                            </div>
                            <a href="{{ route('logout') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                <i class="fas fa-right-from-bracket"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 px-4 py-3 text-sm text-slate-500 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="font-semibold text-slate-700 hover:text-slate-950">Admin</a>
                            @unless (request()->routeIs('admin.dashboard'))
                                <span>/</span>
                                <span class="text-slate-600">{{ $pageTitle }}</span>
                            @endunless
                        </div>
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">{{ now()->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <div class="space-y-4">
                    @if (session('status'))
                        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800 shadow-sm">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-circle-check mt-0.5"></i>
                                <span>{{ session('status') }}</span>
                            </div>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="rounded-3xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800 shadow-sm">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-circle-exclamation mt-0.5"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800 shadow-sm">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-triangle-exclamation mt-0.5"></i>
                                <span>{{ session('warning') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="mt-6">
                    @yield('content')
                </div>
                <footer class="mt-8 px-1 text-xs uppercase tracking-[0.25em] text-slate-400">
                    KhaiTriEdu Admin Console
                </footer>
            </main>
        </div>

        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden"></div>
        <aside x-show="sidebarOpen" x-cloak class="fixed inset-y-0 left-0 z-50 w-80 overflow-y-auto bg-slate-950 px-4 py-5 text-slate-100 shadow-2xl lg:hidden">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.35em] text-cyan-300/80">Enterprise Admin</p>
                    <h2 class="mt-1 text-lg font-extrabold">KhaiTriEdu</h2>
                </div>
                <button @click="sidebarOpen = false" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-white/10 text-slate-300">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            @foreach ($menuSections as $section)
                <div class="mb-6 last:mb-0">
                    <p class="px-3 text-[11px] uppercase tracking-[0.3em] text-slate-500">{{ $section['label'] }}</p>
                    <nav class="mt-3 space-y-1.5">
                        @foreach ($section['items'] as $item)
                            <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold {{ $item['active'] ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                                <span class="flex h-10 w-10 items-center justify-center rounded-2xl {{ $item['active'] ? 'bg-cyan-400/15 text-cyan-300' : 'bg-white/5 text-slate-400' }}">
                                    <i class="{{ $item['icon'] }} text-sm"></i>
                                </span>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            @endforeach
        </aside>
    </div>
</div>
<style>
    [x-cloak] { display: none !important; }
</style>
</body>
</html>