@extends('layouts.student')

@section('title', 'Tong quan hoc vien')
@section('eyebrow', 'Student Dashboard')

@section('header_actions')
    <a href="{{ route('student.enroll.index') }}" class="inline-flex items-center gap-2 rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
        <i class="fas fa-book-open"></i>
        <span>Dang ky khoa hoc</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <section class="rounded-[2rem] bg-gradient-to-r from-cyan-600 via-sky-600 to-blue-700 p-6 text-white shadow-lg shadow-cyan-900/20">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-cyan-100/80">Khu vuc hoc vien</p>
                <h2 class="mt-3 text-3xl font-bold leading-tight">Xin chao, {{ $user->name }}.</h2>
                <p class="mt-3 text-sm leading-6 text-cyan-50/90">
                    Tu day ban co the theo doi lop da dang ky, lop dang cho mo va thong bao moi nhat tu admin trong cung mot giao dien.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-xs uppercase tracking-[0.24em] text-cyan-100/80">Vai tro</p>
                    <p class="mt-2 text-lg font-semibold">{{ $user->roleLabel() }}</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-xs uppercase tracking-[0.24em] text-cyan-100/80">Trang thai</p>
                    <p class="mt-2 text-lg font-semibold">{{ $user->statusLabel() }}</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-xs uppercase tracking-[0.24em] text-cyan-100/80">Thong bao moi</p>
                    <p class="mt-2 text-lg font-semibold">{{ ($notifications ?? collect())->count() }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('student.enroll.index') }}" class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-cyan-200 hover:shadow-lg">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700">
                <i class="fas fa-book-open"></i>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Dang ky khoa hoc</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">Xem cac khoa dang mo va gui yeu cau dang ky lop phu hop.</p>
        </a>

        <a href="{{ route('student.enroll.my-classes') }}" class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-cyan-200 hover:shadow-lg">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-700">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Lop cua toi</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">Theo doi danh sach lop da dang ky va tinh trang cho mo lop.</p>
        </a>

        <a href="{{ route('student.schedule') }}" class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-cyan-200 hover:shadow-lg">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                <i class="fas fa-calendar-days"></i>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Thoi khoa bieu</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">Xem lich hoc da duoc mo chinh thuc va cac lop dang cho khai giang.</p>
        </a>

        <a href="{{ route('student.grades') }}" class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-cyan-200 hover:shadow-lg">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
                <i class="fas fa-square-poll-horizontal"></i>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Ket qua hoc tap</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">Tra cuu diem so hien co va theo doi tien do hoc tap cua ban.</p>
        </a>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Loi tat thuong dung</h3>
            <div class="mt-5 grid gap-3 md:grid-cols-2">
                <a href="{{ route('student.enroll.index') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    <i class="fas fa-compass mr-2"></i>
                    Mo danh sach khoa hoc
                </a>
                <a href="{{ route('student.enroll.my-classes') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    <i class="fas fa-layer-group mr-2"></i>
                    Theo doi lop dang cho mo
                </a>
                <a href="{{ route('student.schedule') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    <i class="fas fa-calendar-check mr-2"></i>
                    Xem lich hoc hien tai
                </a>
                <a href="{{ route('home') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    <i class="fas fa-house mr-2"></i>
                    Quay ve website
                </a>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Thong bao gan day</h3>
            <div class="mt-4 space-y-3">
                @forelse (($notifications ?? collect()) as $notification)
                    <div class="rounded-2xl border {{ $notification->is_read ? 'border-slate-200 bg-slate-50' : 'border-cyan-200 bg-cyan-50/60' }} px-4 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-medium text-slate-900">{{ $notification->title }}</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">{{ $notification->message }}</p>
                            </div>
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $notification->is_read ? 'bg-slate-300' : 'bg-cyan-500' }}"></span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500">
                        Chua co thong bao nao gui den ban.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
