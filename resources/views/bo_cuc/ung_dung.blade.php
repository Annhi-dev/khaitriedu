<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KhaiTriEdu')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="font-sans antialiased bg-gradient-to-br from-blue-50 via-white to-indigo-50 text-gray-800 min-h-screen flex flex-col">

    
    <nav class="bg-white/80 backdrop-blur-md border-b border-white/20 sticky top-0 z-50 shadow-sm" x-data="{ mobileOpen: false }">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                @php
                    $user = Auth::user();
                    $profileRoute = null;

                    if ($user) {
                        $profileRoute = $user->isAdmin()
                            ? route('admin.profile.show')
                            : ($user->isTeacher() ? route('teacher.profile.show') : route('student.profile.show'));
                    }
                @endphp
                
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('images/LOGO.png') }}" alt="KhaiTriEdu Logo" class="h-10 w-auto" />
                    <span class="text-2xl font-bold bg-gradient-to-r from-primary to-primary-dark bg-clip-text text-transparent">KhaiTri Edu</span>
                </a>

                
                <div class="hidden md:flex items-center space-x-8">
                    @if($user && $user->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link text-gray-700 hover:text-primary transition font-medium">Dashboard</a>
                        <a href="{{ route('admin.categories') }}" class="nav-link text-gray-700 hover:text-primary transition font-medium">Quản lý nhóm học</a>
                        <a href="{{ route('admin.subjects') }}" class="nav-link text-gray-700 hover:text-primary transition font-medium">Quản lý khóa học</a>
                        <a href="{{ route('admin.courses') }}" class="nav-link text-gray-700 hover:text-primary transition font-medium">Quản lý lớp học</a>
                        <a href="{{ route('admin.users') }}" class="nav-link text-gray-700 hover:text-primary transition font-medium">Quản lý người dùng</a>
                    @else
                        <a href="{{ route('home') }}" class="nav-link text-gray-700 hover:text-primary transition font-medium">Trang chủ</a>
                        <a href="{{ route('courses.index') }}" class="nav-link text-gray-700 hover:text-primary transition font-medium">Khóa học</a>
                        <a href="{{ route('about') }}" class="nav-link text-gray-700 hover:text-primary transition font-medium">Giới thiệu</a>
                        <a href="{{ route('contact') }}" class="nav-link text-gray-700 hover:text-primary transition font-medium">Liên hệ</a>
                        <a href="{{ route('blog') }}" class="nav-link text-gray-700 hover:text-primary transition font-medium">Blog</a>
                    @endif
                </div>

                
                <div class="hidden md:flex items-center space-x-4">
                    @if(!$user)
                        <a href="{{ route('login') }}" class="btn px-5 py-2.5 bg-primary text-white rounded-xl shadow-md hover:bg-primary-dark transition text-sm font-medium flex items-center gap-2">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </a>
                        <a href="{{ route('register') }}" class="btn px-5 py-2.5 border-2 border-primary text-primary rounded-xl hover:bg-primary-light/20 transition text-sm font-medium flex items-center gap-2">
                            <i class="fas fa-user-plus"></i> Đăng ký
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn px-5 py-2.5 bg-primary text-white rounded-xl shadow-md hover:bg-primary-dark transition text-sm font-medium flex items-center gap-2">
                            <i class="fas fa-user-circle"></i> Về trang cá nhân
                        </a>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none group">
                                @if($user->avatarUrl())
                                    <img src="{{ $user->avatarUrl() }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover ring-2 ring-primary-light/60">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center text-primary-dark font-semibold">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="text-gray-700 font-medium group-hover:text-primary">{{ $user->name }}</span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform" :class="{ 'rotate-180': open }"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-2 border border-gray-100 z-10">
                                @if($user->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-light/30 transition"><i class="fas fa-tachometer-alt w-5 mr-2"></i>Admin Dashboard</a>
                                    <a href="{{ route('admin.categories') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-light/30 transition"><i class="fas fa-list-alt w-5 mr-2"></i>Quản lý nhóm học</a>
                                @else
                                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-light/30 transition"><i class="fas fa-tachometer-alt w-5 mr-2"></i>Dashboard</a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-light/30 transition"><i class="fas fa-book-open w-5 mr-2"></i>Đăng ký khóa học</a>
                                @endif
                                <a href="{{ $profileRoute ?? '#' }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-light/30 transition"><i class="fas fa-user-circle w-5 mr-2"></i>Thông tin cá nhân</a>
                                <hr class="my-1 border-gray-200">
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition"><i class="fas fa-sign-out-alt w-5 mr-2"></i>Đăng xuất</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>

                
                <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>

        
        <div x-show="mobileOpen" x-cloak class="md:hidden bg-white border-t px-4 py-3 space-y-2">
            @if($user && $user->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-tachometer-alt w-6 mr-2"></i>Admin Dashboard</a>
                <a href="{{ route('admin.categories') }}" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-list-alt w-6 mr-2"></i>Quản lý nhóm học</a>
                <a href="{{ route('admin.subjects') }}" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-book w-6 mr-2"></i>Quản lý khóa học</a>
                <a href="{{ route('admin.courses') }}" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-graduation-cap w-6 mr-2"></i>Quản lý lớp học</a>
                <a href="{{ route('admin.users') }}" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-users w-6 mr-2"></i>Quản lý người dùng</a>
            @else
                <a href="{{ route('home') }}" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-home w-6 mr-2"></i>Trang chủ</a>
                <a href="{{ route('courses.index') }}" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-book w-6 mr-2"></i>Khóa học</a>
                <a href="{{ route('about') }}" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-info-circle w-6 mr-2"></i>Giới thiệu</a>
                <a href="{{ route('contact') }}" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-envelope w-6 mr-2"></i>Liên hệ</a>
                <a href="{{ route('blog') }}" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-blog w-6 mr-2"></i>Blog</a>
            @endif
            @if(!$user)
                <div class="pt-2 flex flex-col space-y-2">
                    <a href="{{ route('login') }}" class="btn bg-primary text-white px-4 py-2 rounded-xl text-center"><i class="fas fa-sign-in-alt mr-2"></i>Đăng nhập</a>
                    <a href="{{ route('register') }}" class="btn border-2 border-primary text-primary px-4 py-2 rounded-xl text-center"><i class="fas fa-user-plus mr-2"></i>Đăng ký</a>
                </div>
            @else
                <div class="pt-2 border-t border-gray-200">
                    <div class="font-medium text-gray-800 mb-2 flex items-center">
                        @if($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover ring-2 ring-primary-light/60 mr-2">
                        @else
                            <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center text-primary-dark font-semibold mr-2">{{ substr($user->name, 0, 1) }}</div>
                        @endif
                        {{ $user->name }}
                    </div>
                    <a href="{{ route('dashboard') }}" class="block py-2 text-sm text-gray-600 hover:text-primary"><i class="fas fa-user-circle w-6 mr-2"></i>Về trang cá nhân</a>
                    @if($user->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="block py-2 text-sm text-gray-600 hover:text-primary"><i class="fas fa-tachometer-alt w-6 mr-2"></i>Admin Dashboard</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="block py-2 text-sm text-gray-600 hover:text-primary"><i class="fas fa-tachometer-alt w-6 mr-2"></i>Dashboard</a>
                    @endif
                    <a href="{{ $profileRoute ?? '#' }}" class="block py-2 text-sm text-gray-600 hover:text-primary"><i class="fas fa-user-circle w-6 mr-2"></i>Thông tin cá nhân</a>
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left py-2 text-sm text-red-500 hover:text-red-700"><i class="fas fa-sign-out-alt w-6 mr-2"></i>Đăng xuất</button>
                    </form>
                </div>
            @endif
        </div>
    </nav>

    
    <main class="flex-1 container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @yield('content')
    </main>

    
    <footer class="bg-white border-t border-gray-200 mt-auto">
        @php $socialLinks = config('site.social_links'); @endphp
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-graduation-cap text-primary text-2xl"></i>
                        <span class="text-xl font-bold text-primary-dark">KhaiTri Edu</span>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">Nền tảng đào tạo trực tuyến hàng đầu, kết nối tri thức và cộng đồng học tập.</p>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-800 mb-4">Liên kết</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('about') }}" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Về chúng tôi</a></li>
                        <li><a href="{{ route('courses.index') }}" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Khóa học</a></li>
                        <li><a href="{{ route('teachers') }}" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Giảng viên</a></li>
                        <li><a href="{{ route('careers') }}" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Tuyển dụng</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-800 mb-4">Hỗ trợ</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('help') }}" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Trung tâm trợ giúp</a></li>
                        <li><a href="{{ route('contact') }}" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Liên hệ</a></li>
                        <li><a href="{{ route('terms') }}" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Điều khoản dịch vụ</a></li>
                        <li><a href="{{ route('privacy') }}" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Chính sách bảo mật</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-800 mb-4">Kết nối</h4>
                    <div class="flex space-x-4 mb-4">
                        <a href="{{ $socialLinks['facebook'] }}" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-primary transition text-xl"><i class="fab fa-facebook"></i></a>
                        <a href="{{ $socialLinks['youtube'] }}" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-primary transition text-xl"><i class="fab fa-youtube"></i></a>
                        <a href="{{ $socialLinks['zalo'] }}" target="_blank" rel="noopener noreferrer" class="hover:opacity-75 transition">
                            <img src="{{ asset('images/zalo.png') }}" alt="Zalo" class="w-6 h-6">
                        </a>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">Nhận thông tin khóa học mới:</p>
                    <div class="flex">
                        <input type="email" placeholder="Email của bạn" class="px-3 py-2 border rounded-l-lg text-sm w-full focus:outline-none focus:ring-2 focus:ring-primary">
                        <button class="bg-primary text-white px-4 rounded-r-lg hover:bg-primary-dark transition"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200 mt-8 pt-6 text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} KhaiTri Edu. Mọi quyền được bảo lưu.
            </div>
        </div>
    </footer>

    
    <script>
        document.addEventListener('click', function (e) {
            const target = e.target.closest('.btn, .nav-link, .card, .stat-card');
            if (!target) return;

            const ripple = document.createElement('span');
            const rect = target.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.className = 'ripple';

            target.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    </script>
</body>
</html>
