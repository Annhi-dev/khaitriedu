@extends('layouts.app')
@section('title', 'Quản lý khóa học')
@section('content')
<div class="max-w-6xl mx-auto">
  <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">Quản lý khóa học</h1>
      <p class="text-gray-600">Đây là các khóa học học viên sẽ nhìn thấy và gửi yêu cầu đăng ký.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('admin.categories') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Nhóm ngành</a>
      <a href="{{ route('admin.courses') }}" class="rounded-lg border border-primary px-3 py-2 text-sm font-medium text-primary hover:bg-primary-light/20 transition">Lớp học</a>
      <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Dashboard</a>
    </div>
  </div>

  @if(session('status'))<div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>@endif

  <div class="mb-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-gray-900">Thêm khóa học mới</h2>
    <form method="post" action="{{ route('admin.subjects.create') }}" enctype="multipart/form-data" class="mt-4 grid gap-4 lg:grid-cols-2">
      @csrf
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Nhóm ngành</label>
        <select name="category_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5">
          <option value="">Chọn nhóm ngành</option>
          @foreach($categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Tên khóa học</label>
        <input name="name" placeholder="Ví dụ: Tin học văn phòng" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Học phí tham khảo</label>
        <input name="price" type="number" step="0.01" placeholder="Ví dụ: 1500000" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Ảnh đại diện</label>
        <input name="image" type="file" accept="image/*" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
      </div>
      <div class="lg:col-span-2">
        <label class="mb-1 block text-sm font-medium text-gray-700">Mô tả</label>
        <textarea name="description" rows="4" placeholder="Mô tả ngắn về khóa học" class="w-full rounded-xl border border-gray-300 px-3 py-2.5"></textarea>
      </div>
      <div class="lg:col-span-2">
        <button class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark transition">
          <i class="fas fa-plus"></i>
          Thêm khóa học
        </button>
      </div>
    </form>
  </div>

  <div class="grid gap-4 lg:grid-cols-2">
    @forelse($subjects as $subject)
      <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="text-lg font-semibold text-gray-900">{{ $subject->name }}</h3>
              <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $subject->category?->name ?? 'Chưa phân nhóm' }}</span>
            </div>
            <div class="mt-2 space-y-1 text-sm text-gray-600">
              <p><strong>Học phí:</strong> {{ number_format($subject->price ?? 0, 0, ',', '.') }}đ</p>
              <p><strong>Số lớp hiện có:</strong> {{ $subject->courses_count ?? 0 }}</p>
            </div>
            <p class="mt-3 text-sm leading-6 text-gray-600">{{ $subject->description ?? 'Chưa có mô tả cho khóa học này.' }}</p>
          </div>
          <div class="flex gap-2">
            <a href="{{ route('admin.subject.show', $subject->id) }}" class="rounded-xl bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">Chỉnh sửa</a>
            <form method="post" action="{{ route('admin.subjects.delete', $subject->id) }}" onsubmit="return confirm('Xóa khóa học này và toàn bộ lớp học bên trong?');">
              @csrf
              <button class="rounded-xl bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 transition">Xóa</button>
            </form>
          </div>
        </div>
      </div>
    @empty
      <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500 lg:col-span-2">
        Chưa có khóa học nào. Hãy tạo khóa học đầu tiên cho học viên đăng ký.
      </div>
    @endforelse
  </div>
</div>
@endsection