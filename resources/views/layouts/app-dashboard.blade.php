<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KhaiTriEdu Dashboard')</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="font-sans antialiased text-gray-800" x-data="{ mobileMenuOpen: false }" :class="{'sidebar-open': mobileMenuOpen}">

    <div class="dashboard-wrapper">
        
        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay" @click="mobileMenuOpen = false"></div>

        <!-- Sidebar Section -->
        <aside class="dashboard-sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-graduation-cap text-blue-600 mr-2"></i> KhaiTriEdu
            </div>
            
            <nav class="sidebar-nav">
                @php $user = Auth::user(); @endphp
                
                @if($user)
                    <!-- Admin Menu -->
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
                    
                    <!-- Teacher Menu -->
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

                    <!-- Student Menu -->
                    @else
                        <div class="px-6 py-2 text-xs font-semibold text-gray-400 pl-6 uppercase tracking-wider mb-1 mt-2">Học Viên</div>
                        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-gauge-high nav-icon"></i> Bảng điều khiển
                        </a>
                        <a href="{{ route('courses.index') }}" class="nav-item {{ request()->routeIs('courses.index') ? 'active' : '' }}">
                            <i class="fas fa-search nav-icon"></i> Khám phá
                        </a>
                        <a href="{{ route('student.enroll.index') }}" class="nav-item {{ request()->routeIs('student.enroll.index') || request()->routeIs('student.enroll.select') ? 'active' : '' }}">
                            <i class="fas fa-search-plus nav-icon"></i> Đăng ký lớp
                        </a>
                        <a href="{{ route('student.enroll.my-classes') }}" class="nav-item {{ request()->routeIs('student.enroll.my-classes') ? 'active' : '' }}">
                            <i class="fas fa-calendar-check nav-icon"></i> Lớp của tôi (Thực tế)
                        </a>
                        <a href="{{ route('student.schedule') }}" class="nav-item {{ request()->routeIs('student.schedule') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt nav-icon"></i> Lịch học
                        </a>
                        <a href="#" class="nav-item">
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

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Top Navbar -->
            <header class="dashboard-header">
                <div class="flex items-center">
                    <button class="md:hidden text-gray-500 hover:text-blue-600 focus:outline-none mr-4" @click="mobileMenuOpen = !mobileMenuOpen">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-800 hidden sm:block">@yield('title', 'Bảng điều khiển')</h2>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Notifications -->
                    <button class="text-gray-400 hover:text-blue-600 rounded-full p-2 transition">
                        <i class="fas fa-bell"></i>
                    </button>
                    
                    <!-- Profile Dropdown -->
                    @if(Auth::check())
                        <div class="relative" x-data="{ profileOpen: false }" @click.away="profileOpen = false">
                            <button @click="profileOpen = !profileOpen" class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-full p-1 border border-gray-200 hover:bg-gray-50 transition">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span class="text-sm font-medium text-gray-700 hidden md:block px-1">{{ Auth::user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs text-gray-400 pr-1 hidden md:block"></i>
                            </button>
                            
                            <!-- Dropdown menu -->
                            <div x-show="profileOpen" x-transition x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                                <div class="px-4 py-2 border-b border-gray-100 mb-1">
                                    <p class="text-sm text-gray-900 font-bold truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                                </div>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600"><i class="fas fa-user mr-2 w-4 text-center text-gray-400"></i> Hồ sơ</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt mr-2 w-4 text-center"></i> Đăng xuất</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </header>

            <!-- Render the content -->
            <div class="dashboard-content">
                <!-- Safe Wrapper for existing content -->
                <div class="content-card-wrapper">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
</body>
</html>
