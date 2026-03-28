@extends('layouts.admin')

@section('title', 'Thêm phòng học')

@section('content')
<div class="max-w-3xl space-y-6">
    <x-admin.page-header title="Thêm phòng học" subtitle="Tạo phòng học mới để admin có thể gán vào khung giờ học.">
        <x-slot name="actions">
            <a href="{{ route('admin.rooms.index') }}" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition">
                Danh sách phòng học
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <form method="post" action="{{ route('admin.rooms.store') }}">
            @csrf
            @include('admin.rooms._form', ['submitLabel' => 'Lưu phòng học'])
        </form>
    </div>
</div>
@endsection
