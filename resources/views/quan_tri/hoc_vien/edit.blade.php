@extends('bo_cuc.quan_tri')
@section('title', 'Cập nhật học viên')
@section('content')
<div class="max-w-4xl mx-auto">
    <x-quan_tri.tieu_de_trang title="Cập nhật học viên" subtitle="Chỉnh sửa thông tin học viên" />
    @if ($errors->any())
        <div class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700">
            <ul class="list-disc pl-5"><li>{{ implode('</li><li>', $errors->all()) }}</li></ul>
        </div>
    @endif
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <form method="post" action="{{ route('admin.students.update', $student) }}">
            @csrf
            @include('quan_tri.hoc_vien._form', ['submitLabel' => 'Lưu thay đổi', 'student' => $student])
        </form>
    </div>
</div>
@endsection