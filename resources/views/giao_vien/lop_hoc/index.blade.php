@extends('bo_cuc.giao_vien')

@section('title', 'Lớp Học Của Tôi')
@section('eyebrow', 'Assigned Classes')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-700">Classroom Management</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Danh sách lớp được phân công</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">Mỗi lớp đều dẫn tới khu vực quản lý học viên, điểm danh, bảng điểm và đánh giá theo đúng vai trò giảng viên.</p>
            </div>
            <a href="{{ route('teacher.schedules.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                Xem lịch giảng dạy
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <section class="grid gap-5 lg:grid-cols-2 2xl:grid-cols-3">
        @forelse ($classes as $classRoom)
            <article class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Lớp #{{ $classRoom->id }}</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ $classRoom->displayName() }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ $classRoom->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                    </div>
                    <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $classRoom->students_count ?? 0 }} học viên</span>
                </div>

                <dl class="mt-6 grid gap-3 text-sm text-slate-600">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500">Môn học</dt>
                        <dd class="text-right font-medium text-slate-900">{{ $classRoom->subject?->name ?? 'Chưa gắn môn học' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500">Phòng</dt>
                        <dd class="text-right font-medium text-slate-900">{{ $classRoom->room?->name ?? 'Chưa phân phòng' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500">Lịch</dt>
                        <dd class="max-w-[60%] text-right font-medium text-slate-900">{{ $classRoom->scheduleSummary() }}</dd>
                    </div>
                </dl>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('teacher.classes.show', $classRoom) }}" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">
                        Mở lớp
                    </a>
                    <a href="{{ route('teacher.classes.show', ['classRoom' => $classRoom->id, 'tab' => 'attendance']) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Điểm danh
                    </a>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center text-sm text-slate-500 lg:col-span-2 2xl:col-span-3">
                Chưa có lớp nội bộ nào được phân công cho giảng viên này.
            </div>
        @endforelse
    </section>
</div>
@endsection
