@extends('layouts.admin')
@section('title', 'Tạo giảng viên')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-slate-900">Tạo giảng viên mới</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Admin tạo tài khoản giảng viên để phục vụ phân công lớp, lịch dạy và các phase quản lý sau.</p>
        </div>
        <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Quay lại danh sách</a>
    </div>

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="post" action="{{ route('admin.teachers.store') }}" class="space-y-2">
            @csrf
            @include('admin.teachers._form', ['submitLabel' => 'Tạo giảng viên'])
        </form>
    </div>
</div>
@endsection