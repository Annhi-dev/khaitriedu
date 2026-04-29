<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KhaiTriEdu Dashboard')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="font-sans antialiased text-gray-800" x-data="{ mobileMenuOpen: false }" :class="{'sidebar-open': mobileMenuOpen}">

    <div class="dashboard-wrapper">
        
        
        <div class="sidebar-overlay" @click="mobileMenuOpen = false"></div>

        
        <aside class="dashboard-sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-graduation-cap text-blue-600 mr-2"></i> KhaiTriEdu
            </div>
            
            <nav class="sidebar-nav">
                @php
                    $user = Auth::user();
                    $profileRoute = '#';
                    $dashboardNotifications = $user ? $user->notifications()->latest('id')->take(5)->get() : collect();
                    $dashboardUnreadNotifications = $user ? $user->notifications()->where('is_read', false)->count() : 0;
                    $dashboardNotificationIndexRoute = $user ? ($user->isAdmin() ? route('admin.notifications.index') : ($user->isTeacher() ? route('teacher.notifications.index') : route('student.notifications.index'))) : '#';
                    $dashboardNotificationPollRoute = $user ? ($user->isAdmin() ? route('admin.notifications.poll') : ($user->isTeacher() ? route('teacher.notifications.poll') : route('student.notifications.poll'))) : '#';
                    $dashboardNotificationShowRoute = $user ? ($user->isAdmin() ? 'admin.notifications.show' : ($user->isTeacher() ? 'teacher.notifications.show' : 'student.notifications.show')) : null;

                    if ($user) {
                        $profileRoute = $user->isAdmin()
                            ? route('admin.profile.show')
                            : ($user->isTeacher() ? route('teacher.profile.show') : route('student.profile.show'));
                    }
                @endphp
                
                @if($user)
                    
                    @if($user->isAdmin())
                        <div class="px-6 py-2 text-xs font-semibold text-gray-400 pl-6 uppercase tracking-wider mb-1 mt-2">Admin Portal</div>
                        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-gauge-high nav-icon"></i> Dashboard
                        </a>
                        <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                            <i class="fas fa-users nav-icon"></i> Người dùng
                        </a>
                        <a href="{{ route('admin.courses') }}" class="nav-item {{ request()->routeIs('admin.courses') ? 'active' : '' }}">
                            <i class="fas fa-book nav-icon"></i> Các Lớp học
                        </a>
                        <a href="{{ route('admin.report') ?? '#' }}" class="nav-item">
                            <i class="fas fa-chart-bar nav-icon"></i> Báo cáo
                        </a>
                    
                    
                    @elseif($user->isTeacher())
                        <div class="px-6 py-2 text-xs font-semibold text-gray-400 pl-6 uppercase tracking-wider mb-1 mt-2">Giảng Viên</div>
                        <a href="{{ route('teacher.dashboard') }}" class="nav-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-gauge-high nav-icon"></i> Bảng điều khiển
                        </a>
                        <a href="{{ route('teacher.courses') }}" class="nav-item {{ request()->routeIs('teacher.courses') ? 'active' : '' }}">
                            <i class="fas fa-chalkboard nav-icon"></i> Lớp phụ trách
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-user-graduate nav-icon"></i> Học viên
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-clipboard-check nav-icon"></i> Điểm danh
                        </a>

                    
                    @else
                        <div class="px-6 py-2 text-xs font-semibold text-gray-400 pl-6 uppercase tracking-wider mb-1 mt-2">Học Viên</div>
                        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-gauge-high nav-icon"></i> Bảng điều khiển
                        </a>
                        <a href="{{ route('courses.index') }}" class="nav-item {{ request()->routeIs('courses.index') ? 'active' : '' }}">
                            <i class="fas fa-search nav-icon"></i> Khám phá
                        </a>
                        <a href="{{ route('student.enroll.index') }}" class="nav-item {{ request()->routeIs('student.enroll.index') || request()->routeIs('student.enroll.select') ? 'active' : '' }}">
                            <i class="fas fa-search-plus nav-icon"></i> Đăng ký học
                        </a>
                        <a href="{{ route('student.classes.index') }}" class="nav-item {{ request()->routeIs('student.classes.*') ? 'active' : '' }}">
                            <i class="fas fa-calendar-check nav-icon"></i> Lớp học của tôi
                        </a>
                        <a href="{{ route('student.schedule') }}" class="nav-item {{ request()->routeIs('student.schedule') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt nav-icon"></i> Lịch học
                        </a>
                        <a href="{{ $profileRoute }}" class="nav-item">
                            <i class="fas fa-user nav-icon"></i> Cá nhân
                        </a>
                    @endif
                @endif
                
                <div class="px-6 py-2 text-xs font-semibold text-gray-400 pl-6 uppercase tracking-wider mb-1 mt-6">Hệ thống</div>
                <a href="{{ route('home') }}" class="nav-item">
                    <i class="fas fa-home nav-icon"></i> Trang chủ Website
                </a>
            </nav>
        </aside>

        
        <main class="dashboard-main">
            
            <header class="dashboard-header">
                <div class="flex items-center">
                    <button class="md:hidden text-gray-500 hover:text-blue-600 focus:outline-none mr-4" @click="mobileMenuOpen = !mobileMenuOpen">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-800 hidden sm:block">@yield('title', 'Bảng điều khiển')</h2>
                </div>

                    <div class="flex items-center gap-4">
                    @if($user)
                        <div x-data="{ notificationsOpen: false }" class="relative" data-notification-poller data-notification-poller-url="{{ $dashboardNotificationPollRoute }}">
                            <button @click="notificationsOpen = !notificationsOpen" class="relative rounded-full p-2 text-gray-400 transition hover:text-blue-600 hover:bg-gray-100" title="Thông báo">
                                <i class="fas fa-bell"></i>
                                @if ($dashboardUnreadNotifications > 0)
                                    <span data-notification-badge class="absolute -right-1 -top-1 min-w-[18px] rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[10px] font-semibold leading-none text-white">
                                        {{ $dashboardUnreadNotifications > 99 ? '99+' : $dashboardUnreadNotifications }}
                                    </span>
                                @else
                                    <span data-notification-badge class="absolute -right-1 -top-1 hidden min-w-[18px] rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[10px] font-semibold leading-none text-white"></span>
                                @endif
                            </button>

                            <div x-show="notificationsOpen" x-cloak @click.away="notificationsOpen = false" class="absolute right-0 z-50 mt-3 w-96 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl">
                                <div class="border-b border-gray-100 px-5 py-4">
                                    <p class="text-sm font-semibold text-gray-900">Thông báo</p>
                                    <p class="mt-1 text-xs text-gray-500"><span data-notification-unread-count>{{ $dashboardUnreadNotifications }}</span> chưa đọc</p>
                                </div>

                                <div class="max-h-96 overflow-y-auto" data-notification-list>
                                    @forelse ($dashboardNotifications as $notification)
                                        <a href="{{ route($dashboardNotificationShowRoute, $notification) }}" class="block border-b border-gray-100 px-5 py-4 transition hover:bg-gray-50">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="font-medium text-gray-900">{{ $notification->title }}</p>
                                                    <p class="mt-1 text-sm leading-6 text-gray-500">{{ $notification->message }}</p>
                                                </div>
                                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $notification->is_read ? 'bg-gray-300' : 'bg-blue-500' }}"></span>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="px-5 py-8 text-center text-sm text-gray-500" data-notification-empty>
                                            Chưa có thông báo nào.
                                        </div>
                                    @endforelse
                                </div>

                                <a href="{{ $dashboardNotificationIndexRoute }}" class="block border-t border-gray-100 px-5 py-3 text-sm font-semibold text-blue-600 transition hover:bg-blue-50">
                                    Mở hộp thông báo
                                </a>
                            </div>
                        </div>
                    @endif
                    
                    
                    @if(Auth::check())
                        <div class="relative" x-data="{ profileOpen: false }" @click.away="profileOpen = false">
                            <button @click="profileOpen = !profileOpen" class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-full p-1 border border-gray-200 hover:bg-gray-50 transition">
                                @if (Auth::user()->avatarUrl())
                                    <img src="{{ Auth::user()->avatarUrl() }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover ring-2 ring-blue-100">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="text-sm font-medium text-gray-700 hidden md:block px-1">{{ Auth::user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs text-gray-400 pr-1 hidden md:block"></i>
                            </button>
                            
                            
                            <div x-show="profileOpen" x-transition x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                                <div class="px-4 py-2 border-b border-gray-100 mb-1">
                                    <p class="text-sm text-gray-900 font-bold truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                                </div>
                                <a href="{{ $profileRoute }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600"><i class="fas fa-user mr-2 w-4 text-center text-gray-400"></i> Hồ sơ</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt mr-2 w-4 text-center"></i> Đăng xuất</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </header>

            
            <div class="dashboard-content">
                
                <div class="content-card-wrapper">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
</body>
</html>
