@extends('layouts.admin')
@section('title', 'Tạo nhóm học')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 5</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Tạo nhóm học mới</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Tạo lớp logic cha để gom các khóa học cùng hướng đào tạo, ví dụ Ngoại ngữ - Tin học hoặc Kỹ năng thiếu nhi.</p>
        </div>
        <a href="{{ route('admin.categories') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Quay lại danh sách</a>
    </div>

    <form method="post" action="{{ route('admin.categories.create') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.study_groups._form')

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('admin.categories') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Hủy</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Lưu nhóm học</button>
        </div>
    </form>
</div>
@endsection