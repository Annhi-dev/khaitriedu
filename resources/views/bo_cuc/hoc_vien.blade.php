<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Khu vực học viên') - KhaiTriEdu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800">
@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $studentUser = $current ?? $user ?? Auth::user() ?? (session('user_id') ? \App\Models\NguoiDung::with('role')->find(session('user_id')) : null);
    $pageTitle = trim($__env->yieldContent('title')) ?: 'Khu vực học viên';
    $pageEyebrow = trim($__env->yieldContent('eyebrow'));
    $studentNotificationItems = $studentUser ? $studentUser->notifications()->latest('id')->take(5)->get() : collect();
    $studentUnreadNotifications = $studentUser ? $studentUser->notifications()->where('is_read', false)->count() : 0;
    $navSections = [
        [
            'label' => 'Tổng quan',
            'items' => [
                ['route' => 'student.dashboard', 'label' => 'Tổng quan', 'icon' => 'fa-house', 'active' => request()->routeIs('student.dashboard')],
                ['route' => 'student.schedule', 'label' => 'Thời khóa biểu', 'icon' => 'fa-calendar-days', 'active' => request()->routeIs('student.schedule')],
            ],
        ],
        [
            'label' => 'Học tập',
            'items' => [
                ['route' => 'student.enroll.index', 'label' => 'Đăng ký học', 'icon' => 'fa-book-open', 'active' => request()->routeIs('student.enroll.*')],
                ['route' => 'student.classes.index', 'label' => 'Lớp học của tôi', 'icon' => 'fa-users-rectangle', 'active' => request()->routeIs('student.classes.*')],
                ['route' => 'student.leave-requests.index', 'label' => 'Xin phép nghỉ', 'icon' => 'fa-file-circle-exclamation', 'active' => request()->routeIs('student.leave-requests.*')],
                ['route' => 'student.grades', 'label' => 'Kết quả học tập', 'icon' => 'fa-square-poll-horizontal', 'active' => request()->routeIs('student.grades')],
            ],
        ],
        [
            'label' => 'Tài khoản',
            'items' => [
                ['route' => 'student.profile.show', 'label' => 'Thông tin cá nhân', 'icon' => 'fa-user-gear', 'active' => request()->routeIs('student.profile.*')],
            ],
        ],
    ];
@endphp

<div x-data="{ sidebarOpen: false }" class="relative min-h-screen">
    <div id="student-shell" class="lg:grid lg:grid-cols-[280px_minmax(0,1fr)] transition-all duration-300">
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden" @click="sidebarOpen = false"></div>

        <aside id="student-sidebar" class="fixed inset-y-0 left-0 z-50 w-80 -translate-x-full overflow-y-auto border-r border-slate-200 bg-white transition duration-300 lg:static lg:w-auto lg:translate-x-0"
            :class="{ 'translate-x-0': sidebarOpen }">
            <div class="border-b border-slate-100 px-6 py-6">
                <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="student-brand-copy">
                        <p class="text-xs uppercase tracking-[0.28em] text-cyan-700/80">Cổng học viên</p>
                        <p class="text-lg font-bold text-slate-900">KhaiTriEdu</p>
                    </div>
                </a>
            </div>

            <div class="px-4 py-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Học viên</p>
                    <p class="mt-3 text-lg font-semibold text-slate-900">{{ $studentUser?->name ?? 'Học viên' }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $studentUser?->email ?? 'student@khaitriedu.local' }}</p>
                </div>
            </div>

            <nav class="px-4 pb-6 space-y-5">
                @foreach ($navSections as $section)
                    <div>
                        <p class="px-3 text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-400">{{ $section['label'] }}</p>
                        <div class="mt-2 space-y-1">
                            @foreach ($section['items'] as $item)
                                <a href="{{ route($item['route']) }}"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $item['active'] ? 'bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100' : 'text-slate-600 hover:bg-slate-50 hover:text-cyan-700' }}">
                                    <i class="fas {{ $item['icon'] }} w-5 text-center text-sm {{ $item['active'] ? 'text-cyan-600' : 'text-slate-400' }}"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-900">Góc học tập</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Theo dõi lịch học, đăng ký lớp và xem kết quả trong cùng một nơi.</p>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-cyan-700">
                        <i class="fas fa-house w-5 text-center text-slate-400"></i>
                        <span>Về website</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 hover:bg-rose-50">
                            <i class="fas fa-right-from-bracket w-5 text-center text-rose-400"></i>
                            <span>Đăng xuất</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        <main class="min-w-0">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-slate-700 lg:hidden" @click="sidebarOpen = true">
                            <i class="fas fa-bars"></i>
                        </button>

                        <button type="button" class="hidden h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700 lg:inline-flex" @click="toggleSidebar()" title="Thu gọn / mở rộng menu">
                            <i class="fas fa-bars-staggered"></i>
                        </button>

                        <div class="min-w-0">
                            @if($pageEyebrow)
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">{{ $pageEyebrow }}</p>
                            @endif
                            <h1 class="mt-1 truncate text-xl font-semibold text-slate-900">@yield('title', 'Khu vực học viên')</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div x-data="{ notificationsOpen: false }" class="relative" data-notification-poller data-notification-poller-url="{{ route('student.notifications.poll') }}">
                            <button type="button" @click="notificationsOpen = !notificationsOpen" class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700" title="Thông báo">
                                <i class="fas fa-bell"></i>
                                @if ($studentUnreadNotifications > 0)
                                    <span data-notification-badge class="absolute -right-1 -top-1 min-w-[18px] rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[10px] font-semibold leading-none text-white">
                                        {{ $studentUnreadNotifications > 99 ? '99+' : $studentUnreadNotifications }}
                                    </span>
                                @else
                                    <span data-notification-badge class="absolute -right-1 -top-1 hidden min-w-[18px] rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[10px] font-semibold leading-none text-white"></span>
                                @endif
                            </button>

                            <div x-show="notificationsOpen" x-cloak @click.away="notificationsOpen = false" class="absolute right-0 z-50 mt-3 w-96 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
                                <div class="border-b border-slate-100 px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-900">Thông báo</p>
                                    <p class="mt-1 text-xs text-slate-500"><span data-notification-unread-count>{{ $studentUnreadNotifications }}</span> chưa đọc</p>
                                </div>

                                <div class="max-h-96 overflow-y-auto" data-notification-list>
                                    @forelse ($studentNotificationItems as $notification)
                                        <a href="{{ route('student.notifications.show', $notification) }}" class="block border-b border-slate-100 px-5 py-4 transition hover:bg-slate-50">
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

                                <a href="{{ route('student.notifications.index') }}" class="block border-t border-slate-100 px-5 py-3 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-50">
                                    Mở hộp thông báo
                                </a>
                            </div>
                        </div>

                        @hasSection('header_actions')
                            <div class="hidden items-center gap-3 md:flex">
                                @yield('header_actions')
                            </div>
                        @endif

                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                            <i class="fas fa-house"></i>
                            <span class="hidden sm:inline">Trang chủ</span>
                        </a>

                        <a href="{{ route('student.profile.show') }}" class="hidden items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600 md:flex">
                            @if ($studentUser?->avatarUrl())
                                <img src="{{ $studentUser->avatarUrl() }}" alt="Avatar" class="h-9 w-9 rounded-full object-cover ring-2 ring-cyan-100">
                            @else
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-cyan-500/10 font-semibold text-cyan-700">
                                    {{ strtoupper(substr($studentUser?->name ?? 'H', 0, 1)) }}
                                </span>
                            @endif
                            <div>
                                <p class="font-medium text-slate-800">{{ $studentUser?->name ?? 'Học viên' }}</p>
                                <p class="text-xs text-slate-500">{{ $studentUser?->roleLabel() ?? 'Học viên' }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </header>

            @if (!request()->routeIs('student.dashboard'))
                <div class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-500 sm:px-6 lg:px-8">
                <a href="{{ route('student.dashboard') }}" class="hover:text-cyan-700">Tổng quan</a>
                    <span class="mx-1">/</span>
                    <span class="text-slate-800">{{ $pageTitle }}</span>
                </div>
            @endif

            <div class="px-4 py-4 sm:px-6 lg:px-8">
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
</div>

<script>
    function toggleSidebar() {
        const shell = document.getElementById('student-shell');
        if (shell) {
            shell.classList.toggle('student-sidebar-collapsed');
            localStorage.setItem('studentSidebarState', shell.classList.contains('student-sidebar-collapsed') ? 'collapsed' : 'expanded');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const shell = document.getElementById('student-shell');
        if (shell && localStorage.getItem('studentSidebarState') === 'collapsed') {
            shell.classList.add('student-sidebar-collapsed');
        }
    });
</script>
</body>
</html>
