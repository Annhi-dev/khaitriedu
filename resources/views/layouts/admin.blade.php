<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị hệ thống') - KhaiTriEdu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
@php
    $adminUser = $adminAuthUser ?? (session('user_id') ? \App\Models\User::find(session('user_id')) : null);
    $pageTitle = trim($__env->yieldContent('title')) ?: 'Quản trị hệ thống';
    $menuItems = [
        ['label' => 'Tổng quan', 'icon' => 'fas fa-gauge-high', 'route' => 'admin.dashboard', 'active' => request()->routeIs('admin.dashboard')],
        ['label' => 'Học viên', 'icon' => 'fas fa-user-graduate', 'route' => 'admin.students.index', 'active' => request()->routeIs('admin.students.*')],
        ['label' => 'Giảng viên', 'icon' => 'fas fa-chalkboard-user', 'route' => 'admin.teachers.index', 'active' => request()->routeIs('admin.teachers.*')],
        ['label' => 'Ứng tuyển giảng viên', 'icon' => 'fas fa-file-signature', 'route' => 'admin.teacher-applications', 'active' => request()->routeIs('admin.teacher-applications*')],
        ['label' => 'Nhóm học', 'icon' => 'fas fa-layer-group', 'route' => 'admin.categories', 'active' => request()->routeIs('admin.categories*')],
        ['label' => 'Khóa học', 'icon' => 'fas fa-book-open', 'route' => 'admin.subjects', 'active' => request()->routeIs('admin.subjects*') || request()->routeIs('admin.subject.show')],
        ['label' => 'Lớp học và module', 'icon' => 'fas fa-people-group', 'route' => 'admin.courses', 'active' => request()->routeIs('admin.courses*') || request()->routeIs('admin.course.show')],
        ['label' => 'Đăng ký học', 'icon' => 'fas fa-clipboard-check', 'route' => 'admin.enrollments', 'active' => request()->routeIs('admin.enrollments*')],
        ['label' => 'Lịch học', 'icon' => 'fas fa-calendar-days', 'route' => 'admin.schedules.index', 'active' => request()->routeIs('admin.schedules.*')],
        ['label' => 'Yêu cầu đổi lịch', 'icon' => 'fas fa-calendar-rotate', 'route' => 'admin.schedule-change-requests.index', 'active' => request()->routeIs('admin.schedule-change-requests.*')],
        ['label' => 'Báo cáo', 'icon' => 'fas fa-chart-column', 'route' => 'admin.report', 'active' => request()->routeIs('admin.report')],
    ];
@endphp
<div class="min-h-screen lg:grid lg:grid-cols-[280px_minmax(0,1fr)]" x-data="{ sidebarOpen: false }">
    <aside class="hidden border-r border-slate-200 bg-slate-950 text-slate-100 lg:flex lg:flex-col">
        <div class="border-b border-white/10 px-6 py-6">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-500/15 text-cyan-300">
                    <i class="fas fa-shield-halved text-lg"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-cyan-300/80">Admin Area</p>
                    <h1 class="text-xl font-semibold">KhaiTri Edu</h1>
                </div>
            </a>
        </div>
        <div class="px-4 py-5">
            <p class="px-3 text-xs uppercase tracking-[0.25em] text-slate-400">Điều hướng</p>
            <nav class="mt-3 space-y-1">
                @foreach ($menuItems as $item)
                    @php
                        $classes = 'flex items-center justify-between gap-3 rounded-2xl px-3 py-3 text-sm font-medium transition';
                        $classes .= !empty($item['active']) ? ' bg-cyan-500/15 text-white shadow-inner shadow-cyan-500/10' : ' text-slate-300 hover:bg-white/5 hover:text-white';
                    @endphp
                    @if (!empty($item['disabled']))
                        <div class="{{ $classes }} cursor-not-allowed opacity-60">
                            <span class="flex items-center gap-3">
                                <i class="{{ $item['icon'] }} w-5 text-center"></i>
                                <span>{{ $item['label'] }}</span>
                            </span>
                            <span class="rounded-full border border-white/10 px-2 py-0.5 text-[11px] uppercase tracking-wide text-slate-400">{{ $item['note'] }}</span>
                        </div>
                    @else
                        <a href="{{ route($item['route']) }}" class="{{ $classes }}">
                            <span class="flex items-center gap-3">
                                <i class="{{ $item['icon'] }} w-5 text-center"></i>
                                <span>{{ $item['label'] }}</span>
                            </span>
                            @if (!empty($item['active']))
                                <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </nav>
        </div>
        <div class="mt-auto px-6 py-6">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300">
                <p class="font-semibold text-white">Nguyên tắc hệ thống</p>
                <p class="mt-2 leading-6">Mọi nghiệp vụ quan trọng đều qua admin phê duyệt trước khi hệ thống cập nhật chính thức.</p>
            </div>
        </div>
    </aside>

    <div class="min-w-0">
        <header class="border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 lg:hidden">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Quản trị</p>
                        <h2 class="text-xl font-semibold text-slate-900">{{ $pageTitle }}</h2>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-right sm:block">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Phiên đăng nhập</p>
                        <p class="text-sm font-medium text-slate-700">{{ $adminUser?->name ?? 'Admin' }}</p>
                    </div>
                    <a href="{{ route('logout') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                        <i class="fas fa-right-from-bracket"></i>
                        <span>Đăng xuất</span>
                    </a>
                </div>
            </div>
            <div class="border-t border-slate-100 px-4 py-3 text-sm text-slate-500 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.dashboard') }}" class="font-medium text-slate-600 hover:text-slate-900">Dashboard</a>
                    @unless (request()->routeIs('admin.dashboard'))
                        <span>/</span>
                        <span>{{ $pageTitle }}</span>
                    @endunless
                </div>
            </div>
        </header>

        <main class="px-4 py-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden"></div>
    <aside x-show="sidebarOpen" x-cloak class="fixed inset-y-0 left-0 z-50 w-72 overflow-y-auto bg-slate-950 px-4 py-5 text-slate-100 shadow-2xl lg:hidden">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-cyan-300/80">Admin Area</p>
                <h2 class="mt-1 text-lg font-semibold">KhaiTri Edu</h2>
            </div>
            <button @click="sidebarOpen = false" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-white/10 text-slate-300">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <nav class="space-y-1">
            @foreach ($menuItems as $item)
                @php
                    $classes = 'flex items-center justify-between gap-3 rounded-2xl px-3 py-3 text-sm font-medium transition';
                    $classes .= !empty($item['active']) ? ' bg-cyan-500/15 text-white' : ' text-slate-300 hover:bg-white/5 hover:text-white';
                @endphp
                @if (!empty($item['disabled']))
                    <div class="{{ $classes }} cursor-not-allowed opacity-60">
                        <span class="flex items-center gap-3">
                            <i class="{{ $item['icon'] }} w-5 text-center"></i>
                            <span>{{ $item['label'] }}</span>
                        </span>
                        <span class="rounded-full border border-white/10 px-2 py-0.5 text-[11px] uppercase tracking-wide text-slate-400">{{ $item['note'] }}</span>
                    </div>
                @else
                    <a href="{{ route($item['route']) }}" class="{{ $classes }}">
                        <span class="flex items-center gap-3">
                            <i class="{{ $item['icon'] }} w-5 text-center"></i>
                            <span>{{ $item['label'] }}</span>
                        </span>
                    </a>
                @endif
            @endforeach
        </nav>
    </aside>
</div>
<style>
    [x-cloak] { display: none !important; }
</style>
</body>
</html>