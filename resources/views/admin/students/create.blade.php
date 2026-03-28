@extends('layouts.admin')
@section('title', 'Tạo học viên')
@section('content')
<div class="max-w-4xl mx-auto">
    <x-admin.page-header title="Tạo học viên mới" subtitle="Nhập thông tin tài khoản học viên" />
    @if ($errors->any())
        <div class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700">
            <ul class="list-disc pl-5"><li>{{ implode('</li><li>', $errors->all()) }}</li></ul>
        </div>
    @endif
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <form method="post" action="{{ route('admin.students.store') }}">
            @csrf
            @include('admin.students._form', ['submitLabel' => 'Tạo học viên'])
        </form>
    </div>
</div>
@endsection