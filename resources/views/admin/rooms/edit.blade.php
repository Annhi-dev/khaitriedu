@extends('layouts.admin')

@section('title', 'Chỉnh sửa phòng học')

@section('content')
<div class="max-w-3xl space-y-6">
    <x-admin.page-header title="Chỉnh sửa phòng học" subtitle="Cập nhật thông tin phòng để lịch học và sức chứa luôn chính xác.">
        <x-slot name="actions">
            <a href="{{ route('admin.rooms.index') }}" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition">
                Danh sách phòng học
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <form method="post" action="{{ route('admin.rooms.update', $room) }}">
            @csrf
            @include('admin.rooms._form', ['submitLabel' => 'Lưu thay đổi'])
        </form>
    </div>
</div>
@endsection
