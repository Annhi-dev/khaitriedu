@extends('bo_cuc.giao_vien')

@section('title', 'Lớp Học Của Tôi')
@section('eyebrow', 'Assigned Classes')

@section('content')
<div class="space-y-6">
    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_300px]">
            <div class="border-b border-slate-100 p-6 lg:border-b-0 lg:border-r lg:p-7">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">Classroom Management</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Danh sách lớp được phân công</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">Mỗi lớp đều dẫn tới khu vực quản lý học viên, điểm danh, bảng điểm và đánh giá theo đúng vai trò giảng viên.</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white">
                        {{ $classes->count() }} lớp
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700">
                        Lớp phụ trách
                    </span>
                </div>
            </div>
            <div class="grid gap-3 bg-slate-50 p-6 sm:grid-cols-2 lg:grid-cols-1">
                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Đang hoạt động</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $classes->count() }}</p>
                    <p class="mt-1 text-sm text-slate-500">Lớp được gán cho giảng viên</p>
                </div>
                <a href="{{ route('teacher.schedules.index') }}" class="rounded-2xl border border-white bg-white p-4 shadow-sm transition hover:border-cyan-200 hover:bg-cyan-50/60">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Lịch giảng dạy</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">Mở lịch tuần</p>
                    <p class="mt-1 text-sm text-slate-500">Xem toàn bộ lịch đang phân công</p>
                </a>
            </div>
        </div>
    </section>

    <section class="grid gap-5 lg:grid-cols-2 2xl:grid-cols-3">
        @forelse ($classes as $classRoom)
            <article class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="border-b border-slate-100 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">Lớp #{{ $classRoom->id }}</p>
                            <h3 class="mt-2 text-xl font-semibold tracking-tight text-slate-950">{{ $classRoom->displayName() }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $classRoom->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                        </div>
                        <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $classRoom->students_count ?? 0 }} học viên</span>
                    </div>
                </div>

                <div class="space-y-3 p-6 text-sm">
                    <div>
                        <p class="text-slate-500">Môn học</p>
                        <p class="mt-1 font-medium text-slate-900">{{ $classRoom->subject?->name ?? 'Chưa gắn môn học' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Phòng học</p>
                        <p class="mt-1 font-medium text-slate-900">{{ $classRoom->room?->name ?? 'Chưa phân phòng' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Lịch</p>
                        <p class="mt-1 font-medium text-slate-900">{{ $classRoom->scheduleSummary() }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 border-t border-slate-100 px-6 py-5">
                    <a href="{{ route('teacher.classes.show', $classRoom) }}" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700">
                        Mở lớp
                    </a>
                    <a href="{{ route('teacher.classes.show', ['classRoom' => $classRoom->id, 'tab' => 'attendance']) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        Điểm danh
                    </a>
                </div>
            </article>
        @empty
            <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white px-6 py-16 text-center text-sm text-slate-500 lg:col-span-2 2xl:col-span-3">
                Chưa có khóa học triển khai nào được phân công cho giảng viên này.
            </div>
        @endforelse
    </section>
</div>
@endsection
