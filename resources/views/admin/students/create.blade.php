@extends('layouts.admin')
@section('title', 'Tạo học viên')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-slate-900">Tạo học viên mới</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Admin tạo tài khoản học viên trước khi bắt đầu các bước đăng ký học, xếp lớp và quản lý học vụ.</p>
        </div>
        <a href="{{ route('admin.students.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Quay lại danh sách</a>
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
        <form method="post" action="{{ route('admin.students.store') }}" class="space-y-2">
            @csrf
            @include('admin.students._form', ['submitLabel' => 'Tạo học viên'])
        </form>
    </div>
</div>
@endsection
