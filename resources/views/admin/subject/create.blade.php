@extends('layouts.admin')
@section('title', 'Tạo khóa học')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 6</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Tạo khóa học mới</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                @if ($selectedCategory)
                    Bạn đang thêm khóa học mới trong nhóm {{ $selectedCategory->name }}. Sau khi lưu, hệ thống sẽ quay lại hồ sơ nhóm học để bạn tiếp tục quản lý.
                @else
                    Tạo khóa học public để học viên nhìn thấy, gửi yêu cầu đăng ký và chờ admin phân lớp ở các phase tiếp theo.
                @endif
            </p>
        </div>
        <a href="{{ $selectedCategory ? route('admin.categories.show', $selectedCategory) : route('admin.subjects') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
            {{ $selectedCategory ? 'Quay lại nhóm học' : 'Quay lại danh sách' }}
        </a>
    </div>

    @if ($selectedCategory)
        <div class="rounded-3xl border border-cyan-200 bg-cyan-50 px-5 py-4 text-sm text-cyan-800">
            <p class="font-semibold">Nhóm học hiện tại: {{ $selectedCategory->name }}</p>
            <p class="mt-1">Nhóm này hiện có {{ $selectedCategory->subjects_count }} khóa public và {{ $selectedCategory->courses_count }} lớp nội bộ.</p>
        </div>
    @endif

    <form method="post" action="{{ route('admin.subjects.create') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.subject._form')

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ $selectedCategory ? route('admin.categories.show', $selectedCategory) : route('admin.subjects') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Hủy</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Lưu khóa học</button>
        </div>
    </form>
</div>
@endsection