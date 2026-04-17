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
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800">
@php
    $adminUser = $adminAuthUser ?? (session('user_id') ? \App\Models\User::find(session('user_id')) : null);
    $pageTitle = trim($__env->yieldContent('title')) ?: 'Quản trị hệ thống';
    $sidebarBadges = $adminSidebarBadges ?? [];
    $adminNotificationItems = $adminUser ? $adminUser->notifications()->latest('id')->take(5)->get() : collect();
    $adminUnreadNotifications = $adminUser ? $adminUser->notifications()->where('is_read', false)->count() : 0;
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
                ['label' => 'Phòng ban', 'icon' => 'fas fa-building', 'route' => 'admin.departments.index', 'active' => request()->routeIs('admin.departments.*')],
                ['label' => 'Tài khoản hệ thống', 'icon' => 'fas fa-users-gear', 'route' => 'admin.users', 'active' => request()->routeIs('admin.users*') || request()->routeIs('admin.user.*')],
                [
                    'label' => 'Ứng tuyển giảng viên',
                    'icon' => 'fas fa-file-signature',
                    'route' => 'admin.teacher-applications',
                    'active' => request()->routeIs('admin.teacher-applications*'),
                    'badge' => ($sidebarBadges['teacher_applications_pending'] ?? 0) ?: null,
                ],
            ],
        ],
        [
            'label' => 'Quản lý đào tạo',
            'items' => [
                ['label' => 'Nhóm học', 'icon' => 'fas fa-layer-group', 'route' => 'admin.categories', 'active' => request()->routeIs('admin.categories*')],

                ['label' => 'Khóa học công khai', 'icon' => 'fas fa-book-open', 'route' => 'admin.subjects', 'active' => request()->routeIs('admin.subjects*') || request()->routeIs('admin.subject.*')],
                ['label' => 'Khóa học triển khai', 'icon' => 'fas fa-laptop-code', 'route' => 'admin.courses', 'active' => request()->routeIs('admin.courses*') || request()->routeIs('admin.course.*')],
                ['label' => 'Lớp học', 'icon' => 'fas fa-people-group', 'route' => 'admin.classes.index', 'active' => request()->routeIs('admin.classes.*')],
                ['label' => 'Module', 'icon' => 'fas fa-cubes-stacked', 'route' => 'admin.modules.index', 'active' => request()->routeIs('admin.modules.*') || request()->routeIs('admin.courses.modules.*')],
                ['label' => 'Phòng học', 'icon' => 'fas fa-door-open', 'route' => 'admin.rooms.index', 'active' => request()->routeIs('admin.rooms.*')],
                [
                    'label' => 'Đăng ký học',
                    'icon' => 'fas fa-clipboard-check',
                    'route' => 'admin.enrollments',
                    'active' => request()->routeIs('admin.enrollments*'),
                    'badge' => ($sidebarBadges['enrollments_pending'] ?? 0) ?: null,
                ],
                ['label' => 'Lịch học', 'icon' => 'fas fa-calendar-days', 'route' => 'admin.schedules.index', 'active' => request()->routeIs('admin.schedules.*')],
                [
                    'label' => 'Kiểm tra xung đột',
                    'icon' => 'fas fa-triangle-exclamation',
                    'route' => 'admin.schedules.conflicts',
                    'active' => request()->routeIs('admin.schedules.conflicts'),
                    'badge' => ($sidebarBadges['schedule_conflicts'] ?? 0) ?: null,
                ],
                [
                    'label' => 'Yêu cầu dời buổi',
                    'icon' => 'fas fa-calendar-rotate',
                    'route' => 'admin.schedule-change-requests.index',
                    'active' => request()->routeIs('admin.schedule-change-requests.*'),
                    'badge' => ($sidebarBadges['schedule_change_requests_pending'] ?? 0) ?: null,
                ],
            ],
        ],
    ];
@endphp
<div x-data="{ sidebarOpen: false }" class="relative min-h-screen">
    <div id="app-wrapper" class="lg:grid lg:grid-cols-[280px_minmax(0,1fr)] transition-all duration-300">
        <aside id="main-sidebar" class="hidden lg:block bg-white border-r border-slate-200 shadow-sm h-screen sticky top-0 overflow-x-hidden overflow-y-auto transition-all duration-300">
            <div class="px-6 py-6 border-b border-slate-100">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <div class="h-10 w-10 shrink-0 rounded-xl bg-cyan-100 flex items-center justify-center text-cyan-700">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div class="sidebar-header-text whitespace-nowrap">
                        <h1 class="text-lg font-bold text-slate-800">KhaiTriEdu</h1>
                        <p class="text-xs text-slate-500">Admin Console</p>
                    </div>
                </a>
            </div>
            <nav class="px-4 py-6 space-y-6">
                @foreach ($menuSections as $section)
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 px-3 sidebar-text whitespace-nowrap">{{ $section['label'] }}</p>
                        <div class="mt-2 space-y-1">
                            @foreach ($section['items'] as $item)
                                @php
                                    $active = $item['active'];
                                    $isPlaceholder = empty($item['route']);
                                    $classes = 'sidebar-menu-item flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200';
                                    $classes .= $active
                                        ? ' bg-cyan-50 text-cyan-700'
                                        : ($isPlaceholder ? ' text-slate-400 bg-slate-50/80 cursor-not-allowed' : ' text-slate-600 hover:bg-slate-50 hover:text-cyan-600');
                                @endphp
                                @if ($isPlaceholder)
                                    <div class="{{ $classes }}" title="{{ $item['label'] }}">
                                        <i class="{{ $item['icon'] }} w-5 text-center text-sm text-slate-300 shrink-0 sidebar-icon overflow-visible"></i>
                                        <span class="sidebar-text truncate whitespace-nowrap">{{ $item['label'] }}</span>
                                        @if (! empty($item['badge']))
                                            <span class="ml-auto rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-500 sidebar-badge">{{ $item['badge'] }}</span>
                                        @endif
                                    </div>
                                @else
                                    <a href="{{ route($item['route']) }}" class="{{ $classes }}" title="{{ $item['label'] }}">
                                        <i class="{{ $item['icon'] }} w-5 text-center text-sm {{ $active ? 'text-cyan-600' : 'text-slate-400' }} shrink-0 sidebar-icon overflow-visible"></i>
                                        <span class="sidebar-text truncate whitespace-nowrap">{{ $item['label'] }}</span>
                                        @if (! empty($item['badge']))
                                            <span class="ml-auto rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500 sidebar-badge">{{ $item['badge'] }}</span>
                                        @elseif ($active)
                                            <span class="ml-auto h-2 w-2 rounded-full shadow bg-cyan-500 shrink-0 sidebar-badge"></span>
                                        @endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0">
            <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-slate-200 px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg hover:bg-slate-100">
                            <i class="fas fa-bars text-slate-600"></i>
                        </button>
                        
                        <button onclick="toggleSidebar()" class="hidden lg:block p-2 rounded-lg hover:bg-slate-100" title="Thu gọn/Mở rộng menu">
                            <i class="fas fa-bars text-slate-600"></i>
                        </button>
                        <div>
                            <h2 class="text-xl font-semibold text-slate-800">{{ $pageTitle }}</h2>
                            <p class="text-xs text-slate-500">Chào mừng trở lại, {{ $adminUser?->name ?? 'Admin' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                            <i class="fas fa-house"></i>
                            <span class="hidden sm:inline">Trang chủ</span>
                        </a>
                        <div x-data="{ notificationsOpen: false }" class="relative" data-notification-poller data-notification-poller-url="{{ route('admin.notifications.poll') }}">
                            <button @click="notificationsOpen = !notificationsOpen" class="relative rounded-full p-2 text-slate-500 transition hover:bg-slate-100 hover:text-cyan-700" title="Thông báo">
                                <i class="fas fa-bell"></i>
                                @if ($adminUnreadNotifications > 0)
                                    <span data-notification-badge class="absolute -right-1 -top-1 min-w-[18px] rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[10px] font-semibold leading-none text-white">
                                        {{ $adminUnreadNotifications > 99 ? '99+' : $adminUnreadNotifications }}
                                    </span>
                                @else
                                    <span data-notification-badge class="absolute -right-1 -top-1 hidden min-w-[18px] rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[10px] font-semibold leading-none text-white"></span>
                                @endif
                            </button>

                            <div x-show="notificationsOpen" x-cloak @click.away="notificationsOpen = false" class="absolute right-0 z-50 mt-3 w-96 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
                                <div class="border-b border-slate-100 px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-900">Thông báo</p>
                                    <p class="mt-1 text-xs text-slate-500"><span data-notification-unread-count>{{ $adminUnreadNotifications }}</span> chưa đọc</p>
                                </div>

                                <div class="max-h-96 overflow-y-auto" data-notification-list>
                                    @forelse ($adminNotificationItems as $notification)
                                        <a href="{{ route('admin.notifications.show', $notification) }}" class="block border-b border-slate-100 px-5 py-4 transition hover:bg-slate-50">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="font-medium text-slate-900">{{ $notification->title }}</p>
                                                    <p class="mt-1 text-sm leading-6 text-slate-500">{{ $notification->message }}</p>
                                                </div>
                                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $notification->is_read ? 'bg-slate-300' : 'bg-cyan-500' }}"></span>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="px-5 py-8 text-center text-sm text-slate-500" data-notification-empty>
                                            Chưa có thông báo nào.
                                        </div>
                                    @endforelse
                                </div>

                                <a href="{{ route('admin.notifications.index') }}" class="block border-t border-slate-100 px-5 py-3 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-50">
                                    Mở hộp thông báo
                                </a>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.profile.show') }}" class="flex items-center gap-2 rounded-xl px-2 py-1 transition hover:bg-slate-100" title="Thong tin ca nhan">
                                @if ($adminUser?->avatarUrl())
                                    <img src="{{ $adminUser->avatarUrl() }}" alt="Avatar" class="h-8 w-8 rounded-full object-cover ring-2 ring-cyan-100">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-cyan-100 flex items-center justify-center text-cyan-700">
                                        <span class="text-sm font-semibold">{{ substr($adminUser?->name ?? 'A', 0, 1) }}</span>
                                    </div>
                                @endif
                                <span class="hidden sm:inline text-sm font-medium text-slate-700">{{ $adminUser?->name ?? 'Admin' }}</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="ml-2 p-2 rounded-lg hover:bg-slate-100 hover:bg-red-50 cursor-pointer" title="Đăng xuất">
                                    <i class="fas fa-sign-out-alt text-slate-500 hover:text-red-500"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            @if (!request()->routeIs('admin.dashboard'))
                <div class="px-4 py-2 text-sm text-slate-500 bg-slate-50 border-b border-slate-200">
                    <div class="container mx-auto">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-cyan-600">Admin</a>
                        <span class="mx-1">/</span>
                        <span class="text-slate-800">{{ $pageTitle }}</span>
                    </div>
                </div>
            @endif

            <div class="px-4 py-4 sm:px-6 lg:px-8">
                @include('components.quan_tri.thong_bao', ['session' => session()])
            </div>

            <main class="px-4 pb-10 sm:px-6 lg:px-8">
                @yield('content')
            </main>

            <footer class="border-t border-slate-200 py-4 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} KhaiTriEdu Admin Console
            </footer>
        </div>
    </div>

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
                            @php
                                $active = $item['active'];
                                $isPlaceholder = empty($item['route']);
                            @endphp
                            @if ($isPlaceholder)
                                <div class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400 bg-slate-50/80 cursor-not-allowed">
                                    <i class="{{ $item['icon'] }} w-5"></i>
                                    <span>{{ $item['label'] }}</span>
                                    @if (! empty($item['badge']))
                                        <span class="ml-auto rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-500">{{ $item['badge'] }}</span>
                                    @endif
                                </div>
                            @else
                                <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ $active ? 'bg-cyan-50 text-cyan-700' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <i class="{{ $item['icon'] }} w-5"></i>
                                    <span>{{ $item['label'] }}</span>
                                    @if (! empty($item['badge']))
                                        <span class="ml-auto rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">{{ $item['badge'] }}</span>
                                    @endif
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>
    </aside>
</div>

<script>
    function toggleSidebar() {
        const wrapper = document.getElementById('app-wrapper');
        if(wrapper) {
            wrapper.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarState', wrapper.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        if(localStorage.getItem('sidebarState') === 'collapsed') {
            const wrapper = document.getElementById('app-wrapper');
            if(wrapper) wrapper.classList.add('sidebar-collapsed');
        }
    });
</script>
</body>
</html>
