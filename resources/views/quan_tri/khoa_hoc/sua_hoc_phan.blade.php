@extends('bo_cuc.quan_tri')
@section('title', 'Cập nhật module')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Lộ trình học</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Cập nhật module</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Điều chỉnh mục tiêu học tập, thứ tự và thời lượng của module trong lớp <strong>{{ $course->title }}</strong> để giữ lộ trình học rõ ràng và đúng thứ tự.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.courses.modules.index', $course) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Về quản lý module</a>
            <a href="{{ route('admin.course.show', $course->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Chi tiết lớp học</a>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="post" action="{{ route('admin.courses.modules.update', [$course, $module]) }}" class="space-y-4">
            @csrf
            @include('quan_tri.khoa_hoc._mau_hoc_phan', ['module' => $module])
            <div class="flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('admin.courses.modules.index', $course) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Hủy</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
@endsection
