@extends('layouts.app')
@section('title', 'Chi tiết người dùng')
@section('content')
<div class="max-w-3xl mx-auto">
  <div class="flex justify-between items-center mb-4">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Chi tiết người dùng</h1>
      <p class="text-gray-600">Sửa thông tin người dùng.</p>
    </div>
    <a href="{{ route('admin.users') }}" class="btn rounded-lg border border-gray-300 px-4 py-2">Quay lại danh sách</a>
  </div>

  @if(session('status'))<div class="alert alert-success mb-3">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger mb-3">{{ session('error') }}</div>@endif

  <div class="card bg-white p-4 rounded-xl shadow-sm">
    <form method="post" action="{{ route('admin.users.update', $target->id) }}">
      @csrf
      <div class="mb-3">
        <label class="block text-sm font-medium">Tên</label>
        <input name="name" value="{{ old('name', $target->name) }}" required class="w-full border rounded-md px-3 py-2" />
      </div>
      <div class="mb-3">
        <label class="block text-sm font-medium">Email</label>
        <input value="{{ $target->email }}" disabled class="w-full border rounded-md px-3 py-2 bg-gray-100" />
      </div>
      <div class="mb-3">
        <label class="block text-sm font-medium">Role</label>
        <select name="role" required class="w-full border rounded-md px-3 py-2">
          <option value="hoc_vien" @if($target->role=='hoc_vien') selected @endif>Học viên</option>
          <option value="giang_vien" @if($target->role=='giang_vien') selected @endif>Giảng viên</option>
          <option value="admin" @if($target->role=='admin') selected @endif>Admin</option>
        </select>
      </div>
      <button type="submit" class="btn bg-blue-600 text-white rounded-xl px-3 py-2">Cập nhật</button>
    </form>
    <form method="post" action="{{ route('admin.users.delete', $target->id) }}" onsubmit="return confirm('Xóa người dùng này?');" class="mt-3">
      @csrf
      <button type="submit" class="btn bg-red-600 text-white rounded-xl px-3 py-2">Xóa người dùng</button>
    </form>
  </div>
</div>
@endsection