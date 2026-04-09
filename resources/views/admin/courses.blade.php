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
          Trang này chỉ hiển thị các khóa học thuộc nhóm học này. Khi lưu, hệ thống sẽ quay lại đúng hồ sơ nhóm để bạn tiếp tục quản lý.
        @else
          Mỗi dòng bên dưới là một khóa học mở ra từ danh mục để admin có thể phân công giảng viên và xếp học viên vào các lớp.
        @endif
      </p>
    </div>
    <div class="flex flex-wrap gap-2">
      @if ($selectedCategory)
        <a href="{{ route('admin.courses') }}" class="rounded-lg border border-primary px-3 py-2 text-sm font-medium text-primary hover:bg-primary-light/20 transition">Tất cả khóa học</a>
        <a href="{{ route('admin.categories.show', $selectedCategory) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Quay lại nhóm học</a>
      @else
        <a href="{{ route('admin.subjects') }}" class="rounded-lg border border-primary px-3 py-2 text-sm font-medium text-primary hover:bg-primary-light/20 transition">Danh mục</a>
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
        Nhóm học này chưa có cấu hình danh mục để gắn khóa học mới.
        <a href="{{ route('admin.subjects.create-page', ['category_id' => $selectedCategory->id, 'return_to_category_id' => $selectedCategory->id]) }}" class="font-semibold underline underline-offset-2">Tạo cấu hình trước</a>
      </div>
    @else
      <form method="post" action="{{ route('admin.courses.create') }}" class="mt-4 grid gap-4 lg:grid-cols-2">
        @csrf
        @if ($returnToCategoryId)
          <input type="hidden" name="return_to_category_id" value="{{ $returnToCategoryId }}" />
        @endif

        {{-- Bước 1: Chọn Nhóm học --}}
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Nhóm học</label>
          <select id="category_select" class="w-full rounded-xl border border-gray-300 px-3 py-2.5">
            <option value="">Chọn nhóm học...</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" @selected($selectedSubject?->category_id == $cat->id)>{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Bước 2: Chọn Môn học (load theo nhóm) --}}
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Môn học</label>
          <select id="subject_id_select" name="subject_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 disabled:opacity-50" disabled>
            <option value="">-- Chọn nhóm học trước --</option>
          </select>
          <p class="mt-1 text-xs text-blue-600">⚡ Chọn môn học để tự động điền tên, giá và mô tả</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Tên khóa học</label>
          <input name="title" value="{{ old('title') }}" required placeholder="Ví dụ: Tin học văn phòng" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">
            Giá khóa học
            <span id="price_autofill_badge" class="hidden ml-2 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700">Tự động điền</span>
          </label>
          <div class="relative">
            <input id="course_price" type="number" name="price" value="{{ old('price', 0) }}" min="0" placeholder="Nhập giá" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 pr-12" />
            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-500">VNĐ</span>
          </div>
        </div>

        <div class="lg:col-span-2">
          <label class="mb-1 block text-sm font-medium text-gray-700">
            Mô tả
            <span id="desc_autofill_badge" class="hidden ml-2 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700">Tự động điền</span>
          </label>
          <textarea id="course_description" name="description" rows="3" placeholder="Ghi chú ngắn về khóa học này" class="w-full rounded-xl border border-gray-300 px-3 py-2.5">{{ old('description') }}</textarea>
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

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category_select');
    const subjectSelect  = document.getElementById('subject_id_select');
    const titleEl        = document.querySelector('input[name="title"]');
    const priceEl        = document.getElementById('course_price');
    const descEl         = document.getElementById('course_description');
    const priceBadge     = document.getElementById('price_autofill_badge');
    const descBadge      = document.getElementById('desc_autofill_badge');

    if (!categorySelect || !subjectSelect) return;

    const hasOldInput   = priceEl && priceEl.getAttribute('value') !== '0';
    const apiBase       = '{{ url("/admin/api/subjects") }}';
    const categoriesApi = '{{ url("/admin/api/categories") }}';

    // --- Bước 1: Chọn Nhóm học => load lại Danh sách môn học ---
    categorySelect.addEventListener('change', function () {
      const catId = this.value;

      // Reset
      subjectSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
      subjectSelect.disabled = true;
      if (titleEl) titleEl.value = '';
      if (priceEl) priceEl.value = 0;
      if (descEl)  descEl.value  = '';
      if (priceBadge) priceBadge.classList.add('hidden');
      if (descBadge)  descBadge.classList.add('hidden');

      if (!catId) {
        subjectSelect.innerHTML = '<option value="">-- Chọn nhóm học trước --</option>';
        return;
      }

      fetch(categoriesApi + '/' + catId + '/subjects', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
      .then(res => res.ok ? res.json() : Promise.reject())
      .then(function(subjects) {
        subjectSelect.innerHTML = '<option value="">Chọn môn học...</option>';
        subjects.forEach(function(s) {
          const opt = document.createElement('option');
          opt.value = s.id;
          opt.textContent = s.name;
          subjectSelect.appendChild(opt);
        });
        subjectSelect.disabled = false;
      })
      .catch(function() {
        subjectSelect.innerHTML = '<option value="">Lỗi tải môn học</option>';
      });
    });

    // --- Bước 2: Chọn Môn học => Auto-fill tên, giá, mô tả ---
    function fetchAndFill(subjectId) {
      if (!subjectId) return;
      fetch(apiBase + '/' + subjectId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
      .then(res => res.ok ? res.json() : Promise.reject())
      .then(function(data) {
        if (titleEl && data.name && !titleEl.value) {
          const batch = data.next_batch ? parseInt(data.next_batch) : ((new Date().getFullYear()) % 100);
          titleEl.value = 'Khóa ' + batch + ' - ' + data.name;
        }
        if (priceEl && data.price !== null && data.price !== undefined) {
          priceEl.value = parseFloat(data.price) || 0;
          if (priceBadge) priceBadge.classList.remove('hidden');
        }
        if (descEl && data.description) {
          descEl.value = data.description;
          if (descBadge) descBadge.classList.remove('hidden');
        }
      })
      .catch(err => console.warn('[KhaiTriEdu] Auto-fill lỗi:', err));
    }

    subjectSelect.addEventListener('change', function () {
      if (priceBadge) priceBadge.classList.add('hidden');
      if (descBadge)  descBadge.classList.add('hidden');
      if (titleEl) titleEl.value = '';
      fetchAndFill(this.value);
    });

    // Nếu đang có old-input sau lỗi: kích hoạt sẵn nhóm và môn tương ứng
    @if(old('subject_id') || $selectedSubject)
    (function() {
      const preSubjectId = '{{ old('subject_id', $selectedSubject?->id) }}';
      const preCatId     = '{{ $selectedSubject?->category_id }}';
      if (preCatId && categorySelect) {
        categorySelect.value = preCatId;
        categorySelect.dispatchEvent(new Event('change'));
        // Đợi subjects load xong rồi mới set
        setTimeout(function() {
          if (subjectSelect && preSubjectId) {
            subjectSelect.value = preSubjectId;
            subjectSelect.dispatchEvent(new Event('change'));
          }
        }, 800);
      }
    })();
    @endif
  });
  </script>

  <div class="grid gap-4 lg:grid-cols-2">
    @forelse($courses as $course)
      <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="text-lg font-semibold text-gray-900">{{ $course->title }}</h3>
              <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $course->subject?->name ?? 'Chưa gắn danh mục' }}</span>
            </div>
            <div class="mt-2 space-y-1 text-sm text-gray-600">
              <p><strong>Nhóm học:</strong> {{ $course->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
              <p><strong>Giá:</strong> {{ $course->price == 0 ? 'Miễn phí' : number_format($course->price) . ' VNĐ' }}</p>
              <p><strong>Lịch:</strong> {{ $course->formattedSchedule() }}</p>
              <p><strong>Giảng viên:</strong> {{ $course->teacher?->name ?? 'Chưa phân công' }}</p>
              <p><strong>Học viên đã xếp:</strong> {{ $course->enrollments_count ?? 0 }}</p>
            </div>
            <p class="mt-3 text-sm leading-6 text-gray-600">{{ $course->description ?? 'Chưa có mô tả cho khóa học này.' }}</p>
          </div>
          <div class="flex flex-wrap gap-2 mt-3">
            {{-- Nút thiết lập lịch → chuyển thành lớp học --}}
            <a href="{{ route('admin.classes.create', ['subject_id' => $course->subject_id, 'course_id' => $course->id]) }}"
               class="inline-flex items-center gap-1.5 rounded-xl bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700 transition">
              <i class="fas fa-calendar-plus text-xs"></i>
              Thiết lập lịch → Tạo lớp
            </a>
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
