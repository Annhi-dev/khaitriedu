@extends('bo_cuc.quan_tri')

@section('title', 'Chỉnh sửa khung giờ học')

@section('content')
<div class="max-w-4xl space-y-6">
    <x-quan_tri.tieu_de_trang title="Chỉnh sửa khung giờ học" subtitle="Cập nhật slot khi đổi giảng viên, phòng học hoặc số lượng học viên mục tiêu.">
        <x-slot name="actions">
            <a href="{{ route('admin.course-time-slots.index') }}" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition">
                Danh sách khung giờ
            </a>
        </x-slot>
    </x-quan_tri.tieu_de_trang>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <form method="post" action="{{ route('admin.course-time-slots.update', $courseTimeSlot) }}">
            @csrf
            @include('quan_tri.khung_gio_khoa_hoc._form', ['submitLabel' => 'Lưu thay đổi'])
        </form>
    </div>
</div>
@endsection
