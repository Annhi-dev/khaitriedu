<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Khu vực giảng viên') - KhaiTriEdu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800">
@php
    $teacherUser = $current ?? Auth::user();
    $pageTitle = trim($__env->yieldContent('title')) ?: 'Khu vực giảng viên';
    $pageEyebrow = trim($__env->yieldContent('eyebrow'));
    $navSections = [
        [
            'label' => 'Tổng quan',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'fas fa-gauge-high', 'route' => 'teacher.dashboard', 'active' => request()->routeIs('teacher.dashboard')],
                ['label' => 'Lịch giảng dạy', 'icon' => 'fas fa-calendar-days', 'route' => 'teacher.schedules.index', 'active' => request()->routeIs('teacher.schedules.*')],
            ],
        ],
        [
            'label' => 'Giảng dạy',
            'items' => [
                ['label' => 'Lớp học của tôi', 'icon' => 'fas fa-users-rectangle', 'route' => 'teacher.classes.index', 'active' => request()->routeIs('teacher.classes.*')],
                ['label' => 'Yêu cầu dời buổi', 'icon' => 'fas fa-calendar-rotate', 'route' => 'teacher.schedule-change-requests.index', 'active' => request()->routeIs('teacher.schedule-change-requests.*')],
                ['label' => 'Thông tin cá nhân', 'icon' => 'fas fa-user-gear', 'route' => 'teacher.profile.show', 'active' => request()->routeIs('teacher.profile.*')],
            ],
        ],
    ];
@endphp

<div x-data="{ mobileNavOpen: false }" class="relative min-h-screen">
    <div class="lg:grid lg:grid-cols-[280px_minmax(0,1fr)]">
        <div x-show="mobileNavOpen" x-cloak class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden" @click="mobileNavOpen = false"></div>

        <aside class="fixed inset-y-0 left-0 z-50 w-80 -translate-x-full overflow-y-auto border-r border-slate-200 bg-white transition duration-200 lg:static lg:w-auto lg:translate-x-0"
            :class="{ 'translate-x-0': mobileNavOpen }">
            <div class="border-b border-slate-100 px-6 py-6">
                <a href="{{ route('teacher.dashboard') }}" class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700">
                        <i class="fas fa-chalkboard-user"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.28em] text-cyan-700/80">Teacher Console</p>
                        <p class="text-lg font-bold text-slate-900">KhaiTriEdu</p>
                    </div>
                </a>
            </div>

            <div class="px-4 py-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Giảng viên</p>
                    <p class="mt-3 text-lg font-semibold text-slate-900">{{ $teacherUser?->displayName() }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $teacherUser?->email }}</p>
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
                                    <i class="{{ $item['icon'] }} w-5 text-center text-sm {{ $item['active'] ? 'text-cyan-600' : 'text-slate-400' }}"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-900">Nhịp dạy trong tuần</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Theo dõi lịch giảng, điểm danh và gửi yêu cầu dời buổi từ một nơi duy nhất.</p>
                </div>

                <div class="space-y-1 pt-1">
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
                    <div class="flex items-center gap-3">
                        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-slate-700 lg:hidden" @click="mobileNavOpen = true">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div>
                            @if($pageEyebrow)
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">{{ $pageEyebrow }}</p>
                            @endif
                            <h1 class="mt-1 text-xl font-semibold text-slate-900">@yield('title', 'Khu vực giảng viên')</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                            <i class="fas fa-house"></i>
                            <span class="hidden sm:inline">Trang chủ</span>
                        </a>

                        <a href="{{ route('teacher.profile.show') }}" class="hidden items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600 md:flex">
                            @if ($teacherUser?->avatarUrl())
                                <img src="{{ $teacherUser->avatarUrl() }}" alt="Avatar" class="h-9 w-9 rounded-full object-cover ring-2 ring-cyan-100">
                            @else
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-cyan-500/10 font-semibold text-cyan-700">
                                    {{ strtoupper(substr($teacherUser?->displayName() ?? 'T', 0, 1)) }}
                                </span>
                            @endif
                            <div>
                                <p class="font-medium text-slate-800">{{ $teacherUser?->displayName() }}</p>
                                <p class="text-xs text-slate-500">{{ $teacherUser?->roleLabel() }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </header>

            @if (!request()->routeIs('teacher.dashboard'))
                <div class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-500 sm:px-6 lg:px-8">
                    <a href="{{ route('teacher.dashboard') }}" class="hover:text-cyan-700">Giảng viên</a>
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

            <footer class="border-t border-slate-200 py-4 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} KhaiTriEdu Teacher Console
            </footer>
        </main>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {});
    </script>
</div>
</body>
</html>
