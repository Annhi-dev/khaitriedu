<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Khu vực giảng viên') | KhaiTriEdu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900" x-data="{ mobileNavOpen: false }">
    @php $teacherUser = $current ?? Auth::user(); @endphp

    <div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
        <div x-show="mobileNavOpen" x-cloak class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" @click="mobileNavOpen = false"></div>

        <aside class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full overflow-y-auto border-r border-slate-800 bg-slate-950 px-5 py-6 text-slate-100 transition duration-200 lg:static lg:w-auto lg:translate-x-0"
            :class="{ 'translate-x-0': mobileNavOpen }">
            <div class="flex items-center justify-between">
                <a href="{{ route('teacher.dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-cyan-400/15 text-cyan-300">
                        <i class="fas fa-chalkboard-teacher text-lg"></i>
                    </span>
                    <div>
                        <p class="text-xs uppercase tracking-[0.28em] text-cyan-300/80">Teacher Hub</p>
                        <p class="text-lg font-semibold">KhaiTriEdu</p>
                    </div>
                </a>
                <button type="button" class="rounded-xl border border-slate-800 px-3 py-2 text-sm lg:hidden" @click="mobileNavOpen = false">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="mt-8 rounded-3xl border border-slate-800 bg-slate-900/70 p-4">
                <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Giảng viên</p>
                <p class="mt-3 text-lg font-semibold">{{ $teacherUser?->name }}</p>
                <p class="mt-1 text-sm text-slate-400">{{ $teacherUser?->email }}</p>
            </div>

            <nav class="mt-8 space-y-2">
                @php
                    $navItems = [
                        ['route' => 'teacher.dashboard', 'label' => 'Dashboard', 'icon' => 'fa-gauge-high'],
                        ['route' => 'teacher.schedules.index', 'label' => 'Lịch giảng dạy', 'icon' => 'fa-calendar-week'],
                        ['route' => 'teacher.classes.index', 'label' => 'Lớp học của tôi', 'icon' => 'fa-users-rectangle'],
                        ['route' => 'teacher.schedule-change-requests.index', 'label' => 'Yêu cầu đổi lịch', 'icon' => 'fa-arrows-rotate'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @php
                        $isActive = request()->routeIs($item['route'])
                            || ($item['route'] === 'teacher.classes.index' && request()->routeIs('teacher.classes.*'))
                            || ($item['route'] === 'teacher.schedule-change-requests.index' && request()->routeIs('teacher.schedule-change-requests.*'));
                    @endphp
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $isActive ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-900/25' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i class="fas {{ $item['icon'] }} w-5 text-center"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-10 rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950 p-4 text-sm text-slate-300">
                <p class="font-semibold text-white">Nhịp dạy trong tuần</p>
                <p class="mt-2 leading-6 text-slate-400">Theo dõi lịch giảng, cập nhật điểm danh, bảng điểm và phản hồi học viên trong cùng một không gian làm việc.</p>
            </div>

            <div class="mt-8 space-y-2">
                <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm text-slate-300 hover:bg-slate-900 hover:text-white">
                    <i class="fas fa-house w-5 text-center"></i>
                    <span>Về website</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-sm text-rose-300 hover:bg-rose-500/10 hover:text-rose-200">
                        <i class="fas fa-right-from-bracket w-5 text-center"></i>
                        <span>Đăng xuất</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="min-w-0">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 text-slate-700 lg:hidden" @click="mobileNavOpen = true">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div>
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-400">@yield('eyebrow', 'Teacher Workspace')</p>
                            <h1 class="text-xl font-semibold text-slate-900">@yield('title', 'Khu vực giảng viên')</h1>
                        </div>
                    </div>

                    <div class="hidden items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600 md:flex">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-cyan-500/10 font-semibold text-cyan-700">
                            {{ strtoupper(substr($teacherUser?->name ?? 'T', 0, 1)) }}
                        </span>
                        <div>
                            <p class="font-medium text-slate-800">{{ $teacherUser?->name }}</p>
                            <p class="text-xs text-slate-500">{{ $teacherUser?->roleLabel() }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
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

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
