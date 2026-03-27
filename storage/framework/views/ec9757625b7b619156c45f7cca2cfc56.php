<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'KhaiTriEdu'); ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
    <!-- Vite -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Font Awesome (icon) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="font-sans antialiased bg-gradient-to-br from-blue-50 via-white to-indigo-50 text-gray-800 min-h-screen flex flex-col">

    <!-- Navbar với hiệu ứng kính mờ -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-white/20 sticky top-0 z-50 shadow-sm" x-data="{ mobileOpen: false }">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <?php $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null; ?>
                <!-- Logo + icon -->
                <a href="<?php echo e(route('home')); ?>" class="flex items-center space-x-2">
                    <img src="<?php echo e(asset('hinh/LOGO.png')); ?>" alt="KhaiTriEdu Logo" class="h-10 w-auto" />
                    <span class="text-2xl font-bold bg-gradient-to-r from-primary to-primary-dark bg-clip-text text-transparent">KhaiTri Edu</span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <?php if($user && $user->role === 'admin'): ?>
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link text-gray-700 hover:text-primary transition font-medium">Dashboard</a>
                        <a href="<?php echo e(route('admin.categories')); ?>" class="nav-link text-gray-700 hover:text-primary transition font-medium">Quản lý nhóm học</a>
                        <a href="<?php echo e(route('admin.subjects')); ?>" class="nav-link text-gray-700 hover:text-primary transition font-medium">Quản lý khóa học</a>
                        <a href="<?php echo e(route('admin.courses')); ?>" class="nav-link text-gray-700 hover:text-primary transition font-medium">Quản lý lớp học</a>
                        <a href="<?php echo e(route('admin.users')); ?>" class="nav-link text-gray-700 hover:text-primary transition font-medium">Quản lý người dùng</a>
                    <?php else: ?>
                        <a href="<?php echo e(route('home')); ?>" class="nav-link text-gray-700 hover:text-primary transition font-medium">Trang chủ</a>
                        <a href="<?php echo e(route('courses.index')); ?>" class="nav-link text-gray-700 hover:text-primary transition font-medium">Khóa học</a>
                        <a href="<?php echo e(route('about')); ?>" class="nav-link text-gray-700 hover:text-primary transition font-medium">Giới thiệu</a>
                        <a href="<?php echo e(route('contact')); ?>" class="nav-link text-gray-700 hover:text-primary transition font-medium">Liên hệ</a>
                        <a href="<?php echo e(route('blog')); ?>" class="nav-link text-gray-700 hover:text-primary transition font-medium">Blog</a>
                    <?php endif; ?>
                </div>

                <!-- Right side -->
                <div class="hidden md:flex items-center space-x-4">
                    <?php if(!$user): ?>
                        <a href="<?php echo e(route('login')); ?>" class="btn px-5 py-2.5 bg-primary text-white rounded-xl shadow-md hover:bg-primary-dark transition text-sm font-medium flex items-center gap-2">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </a>
                        <a href="<?php echo e(route('register')); ?>" class="btn px-5 py-2.5 border-2 border-primary text-primary rounded-xl hover:bg-primary-light/20 transition text-sm font-medium flex items-center gap-2">
                            <i class="fas fa-user-plus"></i> Đăng ký
                        </a>
                    <?php else: ?>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none group">
                                <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center text-primary-dark font-semibold">
                                    <?php echo e(substr($user->name, 0, 1)); ?>

                                </div>
                                <span class="text-gray-700 font-medium group-hover:text-primary"><?php echo e($user->name); ?></span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform" :class="{ 'rotate-180': open }"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-2 border border-gray-100 z-10">
                                <?php if($user->role === 'admin'): ?>
                                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-light/30 transition"><i class="fas fa-tachometer-alt w-5 mr-2"></i>Admin Dashboard</a>
                                    <a href="<?php echo e(route('admin.categories')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-light/30 transition"><i class="fas fa-list-alt w-5 mr-2"></i>Quản lý nhóm học</a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('dashboard')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-light/30 transition"><i class="fas fa-tachometer-alt w-5 mr-2"></i>Dashboard</a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-light/30 transition"><i class="fas fa-book-open w-5 mr-2"></i>Đăng ký khóa học</a>
                                <?php endif; ?>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-light/30 transition"><i class="fas fa-user-circle w-5 mr-2"></i>Thông tin cá nhân</a>
                                <hr class="my-1 border-gray-200">
                                <a href="<?php echo e(route('logout')); ?>" class="block px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition"><i class="fas fa-sign-out-alt w-5 mr-2"></i>Đăng xuất</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileOpen" x-cloak class="md:hidden bg-white border-t px-4 py-3 space-y-2">
            <?php if($user && $user->role === 'admin'): ?>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-tachometer-alt w-6 mr-2"></i>Admin Dashboard</a>
                <a href="<?php echo e(route('admin.categories')); ?>" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-list-alt w-6 mr-2"></i>Quản lý nhóm học</a>
                <a href="<?php echo e(route('admin.subjects')); ?>" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-book w-6 mr-2"></i>Quản lý khóa học</a>
                <a href="<?php echo e(route('admin.courses')); ?>" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-graduation-cap w-6 mr-2"></i>Quản lý lớp học</a>
                <a href="<?php echo e(route('admin.users')); ?>" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-users w-6 mr-2"></i>Quản lý người dùng</a>
            <?php else: ?>
                <a href="<?php echo e(route('home')); ?>" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-home w-6 mr-2"></i>Trang chủ</a>
                <a href="<?php echo e(route('courses.index')); ?>" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-book w-6 mr-2"></i>Khóa học</a>
                <a href="<?php echo e(route('about')); ?>" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-info-circle w-6 mr-2"></i>Giới thiệu</a>
                <a href="<?php echo e(route('contact')); ?>" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-envelope w-6 mr-2"></i>Liên hệ</a>
                <a href="<?php echo e(route('blog')); ?>" class="block nav-link py-2 text-gray-700 hover:text-primary"><i class="fas fa-blog w-6 mr-2"></i>Blog</a>
            <?php endif; ?>
            <?php if(!$user): ?>
                <div class="pt-2 flex flex-col space-y-2">
                    <a href="<?php echo e(route('login')); ?>" class="btn bg-primary text-white px-4 py-2 rounded-xl text-center"><i class="fas fa-sign-in-alt mr-2"></i>Đăng nhập</a>
                    <a href="<?php echo e(route('register')); ?>" class="btn border-2 border-primary text-primary px-4 py-2 rounded-xl text-center"><i class="fas fa-user-plus mr-2"></i>Đăng ký</a>
                </div>
            <?php else: ?>
                <div class="pt-2 border-t border-gray-200">
                    <div class="font-medium text-gray-800 mb-2 flex items-center">
                        <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center text-primary-dark font-semibold mr-2"><?php echo e(substr($user->name, 0, 1)); ?></div>
                        <?php echo e($user->name); ?>

                    </div>
                    <?php if($user->role === 'admin'): ?>
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="block py-2 text-sm text-gray-600 hover:text-primary"><i class="fas fa-tachometer-alt w-6 mr-2"></i>Admin Dashboard</a>
                    <?php else: ?>
                        <a href="<?php echo e(route('dashboard')); ?>" class="block py-2 text-sm text-gray-600 hover:text-primary"><i class="fas fa-tachometer-alt w-6 mr-2"></i>Dashboard</a>
                    <?php endif; ?>
                    <a href="#" class="block py-2 text-sm text-gray-600 hover:text-primary"><i class="fas fa-user-circle w-6 mr-2"></i>Thông tin cá nhân</a>
                    <a href="<?php echo e(route('logout')); ?>" class="block py-2 text-sm text-red-500 hover:text-red-700"><i class="fas fa-sign-out-alt w-6 mr-2"></i>Đăng xuất</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Main content -->
    <main class="flex-1 container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Footer hiện đại -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Cột 1 -->
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-graduation-cap text-primary text-2xl"></i>
                        <span class="text-xl font-bold text-primary-dark">KhaiTri Edu</span>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">Nền tảng đào tạo trực tuyến hàng đầu, kết nối tri thức và cộng đồng học tập.</p>
                </div>
                <!-- Cột 2 -->
                <div>
                    <h4 class="font-semibold text-gray-800 mb-4">Liên kết</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?php echo e(route('about')); ?>" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Về chúng tôi</a></li>
                        <li><a href="<?php echo e(route('courses.index')); ?>" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Khóa học</a></li>
                        <li><a href="<?php echo e(route('teachers')); ?>" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Giảng viên</a></li>
                        <li><a href="<?php echo e(route('careers')); ?>" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Tuyển dụng</a></li>
                    </ul>
                </div>
                <!-- Cột 3 -->
                <div>
                    <h4 class="font-semibold text-gray-800 mb-4">Hỗ trợ</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?php echo e(route('help')); ?>" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Trung tâm trợ giúp</a></li>
                        <li><a href="<?php echo e(route('contact')); ?>" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Liên hệ</a></li>
                        <li><a href="<?php echo e(route('terms')); ?>" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Điều khoản dịch vụ</a></li>
                        <li><a href="<?php echo e(route('privacy')); ?>" class="text-gray-600 hover:text-primary transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i>Chính sách bảo mật</a></li>
                    </ul>
                </div>
                <!-- Cột 4: Mạng xã hội + Đăng ký nhận tin -->
                <div>
                    <h4 class="font-semibold text-gray-800 mb-4">Kết nối</h4>
                    <div class="flex space-x-4 mb-4">
                        <a href="https://www.facebook.com/profile.php?id=61575515763147" target="_blank" class="text-gray-500 hover:text-primary transition text-xl"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.youtube.com/channel/UCPrE7RBNFZHZAxJzvCxvMSg" target="_blank" class="text-gray-500 hover:text-primary transition text-xl"><i class="fab fa-youtube"></i></a>
                        <a href="https://zalo.me/84867852853" target="_blank" class="hover:opacity-75 transition">
                            <img src="<?php echo e(asset('hinh/zalo.png')); ?>" alt="Zalo" class="w-6 h-6">
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
                &copy; <?php echo e(date('Y')); ?> KhaiTri Edu. Mọi quyền được bảo lưu.
            </div>
        </div>
    </footer>

    <!-- Alpine x-cloak style -->
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <!-- Ripple effect script (giữ nguyên) -->
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
<?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/layouts/app.blade.php ENDPATH**/ ?>