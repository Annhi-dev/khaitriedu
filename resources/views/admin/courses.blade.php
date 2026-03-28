@extends('layouts.admin')
@section('title', 'Quản lý khóa học')
@section('content')
@php
  $subjectOptions = $subjects->map(function ($subject) {
      return [
          'id' => $subject->id,
          'label' => $subject->name . ($subject->category ? ' - ' . $subject->category->name : ''),
      ];
  });
@endphp
<div class="max-w-6xl mx-auto space-y-6">
  <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">{{ $selectedCategory ? 'Khóa học trong nhóm ' . $selectedCategory->name : 'Quản lý khóa học' }}</h1>
      <p class="text-gray-600">
        @if ($selectedCategory)
          Trang này chỉ hiển thị các khóa học thực tế thuộc nhóm học này. Khi lưu, hệ thống sẽ quay lại đúng hồ sơ nhóm để bạn tiếp tục quản lý.
        @else
          Mỗi dòng bên dưới là một khóa học hoặc lớp thực tế mà admin có thể phân công giảng viên và xếp học viên.
        @endif
      </p>
    </div>
    <div class="flex flex-wrap gap-2">
      @if ($selectedCategory)
        <a href="{{ route('admin.courses') }}" class="rounded-lg border border-primary px-3 py-2 text-sm font-medium text-primary hover:bg-primary-light/20 transition">Tất cả khóa học</a>
        <a href="{{ route('admin.categories.show', $selectedCategory) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Quay lại nhóm học</a>
      @else
        <a href="{{ route('admin.subjects') }}" class="rounded-lg border border-primary px-3 py-2 text-sm font-medium text-primary hover:bg-primary-light/20 transition">Khóa gốc</a>
        <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Dashboard</a>
      @endif
    </div>
  </div>

  @if(session('status'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>@endif

  <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-gray-900">{{ $selectedCategory ? 'Tạo khóa học mới trong nhóm' : 'Tạo khóa học mới' }}</h2>
    @if ($selectedCategory)
      <p class="mt-2 text-sm text-gray-600">Nhóm {{ $selectedCategory->name }} hiện có {{ $courses->count() }} khóa học. Form bên dưới sẽ chỉ cho phép tạo khóa thuộc đúng nhóm này.</p>
    @endif

    @if ($selectedCategory && $subjects->isEmpty())
      <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Nhóm học này chưa có cấu hình khóa gốc để gắn khóa học mới.
        <a href="{{ route('admin.subjects.create-page', ['category_id' => $selectedCategory->id, 'return_to_category_id' => $selectedCategory->id]) }}" class="font-semibold underline underline-offset-2">Tạo cấu hình trước</a>
      </div>
    @else
      <form method="post" action="{{ route('admin.courses.create') }}" class="mt-4 grid gap-4 lg:grid-cols-2">
        @csrf
        @if ($returnToCategoryId)
          <input type="hidden" name="return_to_category_id" value="{{ $returnToCategoryId }}" />
        @endif
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Thuộc khóa gốc</label>
          <select name="subject_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5">
            <option value="">Chọn khóa gốc</option>
            @foreach($subjectOptions as $subjectOption)
              <option value="{{ $subjectOption['id'] }}" @selected((string) old('subject_id', $selectedSubject?->id) === (string) $subjectOption['id'])>{{ $subjectOption['label'] }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Tên khóa học</label>
          <input name="title" value="{{ old('title') }}" required placeholder="Ví dụ: Tin học văn phòng" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Lịch dự kiến</label>
          <input name="schedule" value="{{ old('schedule') }}" placeholder="Ví dụ: T2-T4-T6, 18:00-20:00" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
        </div>
        <div class="lg:col-span-2">
          <label class="mb-1 block text-sm font-medium text-gray-700">Mô tả</label>
          <textarea name="description" rows="3" placeholder="Ghi chú ngắn về khóa học này" class="w-full rounded-xl border border-gray-300 px-3 py-2.5">{{ old('description') }}</textarea>
        </div>
        <div class="lg:col-span-2">
          <button class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark transition">
            <i class="fas fa-plus"></i>
            Tạo khóa học
          </button>
        </div>
      </form>
    @endif
  </div>

  <div class="grid gap-4 lg:grid-cols-2">
    @forelse($courses as $course)
      <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="text-lg font-semibold text-gray-900">{{ $course->title }}</h3>
              <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $course->subject?->name ?? 'Chưa gắn khóa gốc' }}</span>
            </div>
            <div class="mt-2 space-y-1 text-sm text-gray-600">
              <p><strong>Nhóm học:</strong> {{ $course->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
              <p><strong>Lịch:</strong> {{ $course->formattedSchedule() }}</p>
              <p><strong>Giảng viên:</strong> {{ $course->teacher?->name ?? 'Chưa phân công' }}</p>
              <p><strong>Học viên đã xếp:</strong> {{ $course->enrollments_count ?? 0 }}</p>
            </div>
            <p class="mt-3 text-sm leading-6 text-gray-600">{{ $course->description ?? 'Chưa có mô tả cho khóa học này.' }}</p>
          </div>
          <div class="flex gap-2">
            <a href="{{ route('admin.course.show', $course->id) }}" class="rounded-xl bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">Chỉnh sửa</a>
            <form method="post" action="{{ route('admin.courses.delete', $course->id) }}" onsubmit="return confirm('Xóa khóa học này?');">
              @csrf
              <button class="rounded-xl bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 transition">Xóa</button>
            </form>
          </div>
        </div>
      </div>
    @empty
      <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500 lg:col-span-2">
        {{ $selectedCategory ? 'Nhóm học này chưa có khóa học nào. Tạo mới ở form phía trên.' : 'Chưa có khóa học nào. Tạo khóa mới để admin có thể phân công giảng viên và xếp học viên.' }}
      </div>
    @endforelse
  </div>
</div>
@endsection