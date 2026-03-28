<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị hệ thống') - KhaiTriEdu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .transition-smooth { transition: all 0.2s ease-in-out; }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-down { animation: fadeInDown 0.3s ease-out; }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800">
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
                ['label' => 'Lớp học', 'icon' => 'fas fa-people-group', 'route' => 'admin.courses', 'active' => request()->routeIs('admin.courses*') || request()->routeIs('admin.course.*')],
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
<div x-data="{ sidebarOpen: false }" class="relative min-h-screen">
    <div class="lg:grid lg:grid-cols-[280px_minmax(0,1fr)]">
        <!-- Sidebar Desktop -->
        <aside class="hidden lg:block bg-white border-r border-slate-200 shadow-sm h-screen sticky top-0 overflow-y-auto">
            <div class="px-6 py-6 border-b border-slate-100">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-cyan-100 flex items-center justify-center text-cyan-700">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-slate-800">KhaiTriEdu</h1>
                        <p class="text-xs text-slate-500">Admin Console</p>
                    </div>
                </a>
            </div>
            <nav class="px-4 py-6 space-y-6">
                @foreach ($menuSections as $section)
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 px-3">{{ $section['label'] }}</p>
                        <div class="mt-2 space-y-1">
                            @foreach ($section['items'] as $item)
                                @php
                                    $active = $item['active'];
                                    $classes = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200';
                                    $classes .= $active ? ' bg-cyan-50 text-cyan-700' : ' text-slate-600 hover:bg-slate-50 hover:text-cyan-600';
                                @endphp
                                <a href="{{ route($item['route']) }}" class="{{ $classes }}">
                                    <i class="{{ $item['icon'] }} w-5 text-sm {{ $active ? 'text-cyan-600' : 'text-slate-400' }}"></i>
                                    <span>{{ $item['label'] }}</span>
                                    @if ($active)
                                        <span class="ml-auto h-2 w-2 rounded-full bg-cyan-500"></span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
        </aside>

        <!-- Main content -->
        <div class="min-w-0">
            <!-- Topbar -->
            <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-slate-200 px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg hover:bg-slate-100">
                            <i class="fas fa-bars text-slate-600"></i>
                        </button>
                        <div>
                            <h2 class="text-xl font-semibold text-slate-800">{{ $pageTitle }}</h2>
                            <p class="text-xs text-slate-500">Chào mừng trở lại, {{ $adminUser?->name ?? 'Admin' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <button class="p-2 rounded-full hover:bg-slate-100">
                                <i class="fas fa-bell text-slate-500"></i>
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 rounded-full bg-cyan-100 flex items-center justify-center text-cyan-700">
                                <span class="text-sm font-semibold">{{ substr($adminUser?->name ?? 'A', 0, 1) }}</span>
                            </div>
                            <span class="hidden sm:inline text-sm font-medium text-slate-700">{{ $adminUser?->name ?? 'Admin' }}</span>
                            <a href="{{ route('logout') }}" class="ml-2 p-2 rounded-lg hover:bg-slate-100">
                                <i class="fas fa-sign-out-alt text-slate-500"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Breadcrumb (optional) -->
            @if (!request()->routeIs('admin.dashboard'))
                <div class="px-4 py-2 text-sm text-slate-500 bg-slate-50 border-b border-slate-200">
                    <div class="container mx-auto">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-cyan-600">Admin</a>
                        <span class="mx-1">/</span>
                        <span class="text-slate-800">{{ $pageTitle }}</span>
                    </div>
                </div>
            @endif

            <!-- Flash messages -->
            <div class="px-4 py-4 sm:px-6 lg:px-8">
                @include('components.admin.alert', ['session' => session()])
            </div>

            <!-- Main content -->
            <main class="px-4 pb-10 sm:px-6 lg:px-8">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="border-t border-slate-200 py-4 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} KhaiTriEdu Admin Console
            </footer>
        </div>
    </div>

    <!-- Mobile sidebar -->
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-50 bg-black/30 lg:hidden" @click="sidebarOpen = false"></div>
    <aside x-show="sidebarOpen" x-cloak class="fixed inset-y-0 left-0 z-50 w-80 bg-white border-r border-slate-200 shadow-lg lg:hidden overflow-y-auto">
        <div class="flex items-center justify-between p-4 border-b">
            <h2 class="text-lg font-bold text-slate-800">KhaiTriEdu</h2>
            <button @click="sidebarOpen = false" class="p-2 rounded-lg hover:bg-slate-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="p-4 space-y-6">
            @foreach ($menuSections as $section)
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400">{{ $section['label'] }}</p>
                    <div class="mt-2 space-y-1">
                        @foreach ($section['items'] as $item)
                            @php $active = $item['active']; @endphp
                            <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ $active ? 'bg-cyan-50 text-cyan-700' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="{{ $item['icon'] }} w-5"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>
    </aside>
</div>
</body>
</html>