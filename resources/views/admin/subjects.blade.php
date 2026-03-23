@extends('layouts.app')
@section('title', 'Quản lý môn học')
@section('content')
<div class="max-w-6xl mx-auto">
  <div class="flex justify-between items-center mb-4">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Quản lý môn học</h1>
      <p class="text-gray-600">Mỗi môn học có thể có nhiều khóa học.</p>
    </div>
    <div class="space-x-2">
      <a href="{{ route('admin.courses') }}" class="btn rounded-lg border border-primary text-primary px-3 py-2">Quản lý khóa học</a>
      <a href="{{ route('admin.dashboard') }}" class="btn rounded-lg border border-gray-300 px-3 py-2">Dashboard</a>
    </div>
  </div>

  @if(session('status'))<div class="alert alert-success mb-3">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger mb-3">{{ session('error') }}</div>@endif

  <div class="grid lg:grid-cols-2 gap-4 mb-4">
    <div class="card bg-white p-4 rounded-xl shadow-sm">
      <h3 class="font-semibold mb-2">Thêm môn học</h3>
      <form method="post" action="{{ route('admin.subjects.create') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-2"><input name="name" placeholder="Tên môn học" required class="w-full border rounded-md px-3 py-2" /></div>
        <div class="mb-2"><input name="price" type="number" step="0.01" placeholder="Giá (VND)" class="w-full border rounded-md px-3 py-2" /></div>
        <div class="mb-2"><textarea name="description" placeholder="Mô tả" class="w-full border rounded-md px-3 py-2"></textarea></div>
        <div class="mb-2"><input name="image" type="file" accept="image/*" class="w-full border rounded-md px-3 py-2" /></div>
        <button class="btn bg-primary text-white rounded-xl px-3 py-2">Thêm môn học</button>
      </form>
    </div>
  </div>

  <div class="space-y-3">
    @foreach($subjects as $subject)
    <div class="card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
      <div class="flex justify-between items-start gap-2 mb-2">
        <div>
          <h3 class="font-semibold text-lg">{{ $subject->name }}</h3>
          <p class="text-gray-600 text-sm">Giá: {{ number_format($subject->price ?? 0, 0, ',', '.') }} VND</p>
          <p class="text-gray-600 text-sm">{{ $subject->description ?? 'Không có mô tả' }}</p>
        </div>
        <div class="flex gap-2">
          <a href="{{ route('admin.subject.show', $subject->id) }}" class="px-3 py-1 text-xs bg-blue-600 text-white rounded shadow">Sửa</a>
          <form method="post" action="{{ route('admin.subjects.delete', $subject->id) }}" onsubmit="return confirm('Xóa môn học và tất cả khóa học?');">
            @csrf
            <button class="px-3 py-1 text-xs bg-blue-600 text-white rounded shadow">Xóa</button>
          </form>
        </div>
      </div>

    </div>
    @endforeach
  </div>
</div>
@endsection