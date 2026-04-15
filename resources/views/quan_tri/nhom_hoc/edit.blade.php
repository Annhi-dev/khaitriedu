@extends('bo_cuc.quan_tri')
@section('title', 'Cập nhật nhóm học')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Cập nhật nhóm học</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.categories.show', $category) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Xem chi tiết</a>
            <a href="{{ route('admin.categories') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Danh sách nhóm học</a>
        </div>
    </div>

    <form method="post" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('quan_tri.nhom_hoc._form', ['category' => $category])

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('admin.categories.show', $category) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Hủy</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Lưu thay đổi</button>
        </div>
    </form>
</div>
@endsection
