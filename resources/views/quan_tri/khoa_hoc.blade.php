@extends('bo_cuc.quan_tri')
@section('title', 'Quản lý khóa học')
@section('content')
@php
  $selectedCategoryId = old('category_id', $selectedCategory?->id ?? '');
  $selectedSubjectId = old('subject_id', $selectedSubject?->id ?? '');
  $selectedSubjectName = old('subject_name', $selectedSubject?->name ?? '');
  $selectedSubjectDuration = old('subject_duration', $selectedSubject?->duration ?? 12);
@endphp
<div class="max-w-6xl mx-auto space-y-6">
  <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-primary-dark">{{ $selectedCategory ? 'Khóa học trong nhóm ' . $selectedCategory->name : 'Quản lý khóa học' }}</h1>
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
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-gray-900">{{ $selectedCategory ? 'Tạo khóa học mới trong nhóm' : 'Tạo khóa học mới' }}</h2>
      </div>
    </div>

    @if ($selectedCategory && $subjects->isEmpty())
      <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <a href="{{ route('admin.subjects.create-page', ['category_id' => $selectedCategory->id, 'return_to_category_id' => $selectedCategory->id]) }}" class="font-semibold underline underline-offset-2">Tạo môn học trước</a>
      </div>
    @endif

    <form method="post" action="{{ route('admin.courses.create') }}" class="mt-4 grid gap-4 lg:grid-cols-2">
      @csrf
      @if ($returnToCategoryId)
        <input type="hidden" name="return_to_category_id" value="{{ $returnToCategoryId }}" />
      @endif

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Nhóm học</label>
        <select id="category_select" name="category_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5">
          <option value="">Chọn nhóm học...</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected((string) $selectedCategoryId === (string) $cat->id)>{{ $cat->name }}</option>
          @endforeach
        </select>
        @error('category_id')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">
          Môn học
        </label>
        <input id="subject_name" name="subject_name" value="{{ $selectedSubjectName }}" list="subject_options" placeholder="Môn học" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 disabled:opacity-50" disabled />
        <input type="hidden" id="subject_id" name="subject_id" value="{{ $selectedSubjectId }}" />
        <datalist id="subject_options"></datalist>
        @error('subject_name')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('subject_id')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Tên khóa học</label>
        <input id="course_title" name="title" value="{{ old('title') }}" placeholder="Tên khóa học" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
        @error('title')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">
          Giá khóa học
        </label>
        <div class="relative">
          <input id="course_price" type="number" name="price" value="{{ old('price', 0) }}" min="0" placeholder="Giá" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 pr-12" />
          <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-500">VNĐ</span>
        </div>
        @error('price')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">
          Thời lượng môn học (tháng)
        </label>
        <input id="subject_duration" type="number" name="subject_duration" value="{{ $selectedSubjectDuration }}" min="1" max="120" placeholder="Thời lượng" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" />
        @error('subject_duration')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div class="lg:col-span-2">
        <label class="mb-1 block text-sm font-medium text-gray-700">
          Mô tả
        </label>
        <textarea id="course_description" name="description" rows="3" placeholder="Mô tả" class="w-full rounded-xl border border-gray-300 px-3 py-2.5">{{ old('description') }}</textarea>
        @error('description')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div class="lg:col-span-2">
        <button class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark transition">
          <i class="fas fa-plus"></i>
          Tạo khóa học
        </button>
      </div>
    </form>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category_select');
    const subjectInput = document.getElementById('subject_name');
    const subjectIdInput = document.getElementById('subject_id');
    const subjectOptions = document.getElementById('subject_options');
    const titleEl = document.getElementById('course_title');
    const priceEl = document.getElementById('course_price');
    const descEl = document.getElementById('course_description');
    const durationEl = document.getElementById('subject_duration');
    const apiBase = '{{ url("/admin/api/categories") }}';
    const nextBatch = @json($nextBatch);

    if (!categorySelect || !subjectInput || !subjectIdInput || !subjectOptions) {
      return;
    }

    let subjectCatalog = @json($subjectSuggestions);

    function normalize(value) {
      return (value || '').trim().toLowerCase();
    }

    function markAuto(el, isAuto) {
      if (el) {
        el.dataset.autofilled = isAuto ? '1' : '0';
      }
    }

    function isAuto(el) {
      return el && el.dataset.autofilled === '1';
    }

    function watchManualInput(el) {
      if (!el) {
        return;
      }

      el.addEventListener('input', function () {
        markAuto(el, false);
      });
    }

    function renderSubjectOptions(subjects) {
      subjectOptions.innerHTML = '';

      subjects.forEach(function (subject) {
        const option = document.createElement('option');
        option.value = subject.name;
        subjectOptions.appendChild(option);
      });
    }

    function setTitleFromSubject(subjectName) {
      if (!titleEl) {
        return;
      }

      if (!titleEl.value || isAuto(titleEl)) {
        titleEl.value = 'Khóa ' + nextBatch + ' - ' + subjectName;
        markAuto(titleEl, true);
      }
    }

    function applySubject(subject) {
      if (!subject) {
        return;
      }

      subjectIdInput.value = subject.id;
      subjectInput.value = subject.name;
      setTitleFromSubject(subject.name);

      if (priceEl) {
        priceEl.value = subject.price ?? 0;
        markAuto(priceEl, true);
      }

      if (descEl) {
        descEl.value = subject.description ?? '';
        markAuto(descEl, true);
      }

      if (durationEl) {
        durationEl.value = subject.duration ?? 12;
        markAuto(durationEl, true);
      }
    }

    function clearAutoValues() {
      if (titleEl && isAuto(titleEl)) {
        titleEl.value = '';
      }

      if (priceEl && isAuto(priceEl)) {
        priceEl.value = 0;
      }

      if (descEl && isAuto(descEl)) {
        descEl.value = '';
      }

      if (durationEl && isAuto(durationEl)) {
        durationEl.value = 12;
      }
    }

    function syncSubjectState() {
      const rawName = subjectInput.value;
      const normalizedName = normalize(rawName);

      if (!normalizedName) {
        subjectIdInput.value = '';
        clearAutoValues();
        return;
      }

      const matchedSubject = subjectCatalog.find(function (subject) {
        return normalize(subject.name) === normalizedName;
      });

      if (matchedSubject) {
        applySubject(matchedSubject);
        return;
      }

      const hadMatchedSubject = Boolean(subjectIdInput.value);
      subjectIdInput.value = '';

      setTitleFromSubject(rawName.trim());

      if (hadMatchedSubject) {
        clearAutoValues();
      }

      if (priceEl && (priceEl.value === '' || priceEl.value === '0' || isAuto(priceEl))) {
        priceEl.value = 0;
        markAuto(priceEl, true);
      }

      if (descEl && (descEl.value === '' || isAuto(descEl))) {
        descEl.value = '';
        markAuto(descEl, true);
      }

      if (durationEl && (durationEl.value === '' || durationEl.value === '12' || isAuto(durationEl))) {
        durationEl.value = 12;
        markAuto(durationEl, true);
      }
    }

    function loadSubjectsForCategory(categoryId) {
      subjectInput.disabled = true;
      subjectInput.placeholder = 'Môn học';
      subjectCatalog = [];
      renderSubjectOptions([]);

      if (!categoryId) {
        subjectInput.value = '';
        subjectIdInput.value = '';
        clearAutoValues();
        subjectInput.placeholder = 'Môn học';
        return Promise.resolve();
      }

      return fetch(apiBase + '/' + categoryId + '/subjects', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Unable to load subjects');
        }

        return response.json();
      })
      .then(function (subjects) {
        subjectCatalog = subjects || [];
        renderSubjectOptions(subjectCatalog);
        subjectInput.disabled = false;
        subjectInput.placeholder = 'Môn học';
        syncSubjectState();
      })
      .catch(function () {
        subjectInput.disabled = false;
        subjectInput.placeholder = 'Môn học';
        renderSubjectOptions([]);
      });
    }

    function resetDependentFields() {
      subjectInput.value = '';
      subjectIdInput.value = '';

      if (titleEl) {
        titleEl.value = '';
        markAuto(titleEl, true);
      }

      if (priceEl) {
        priceEl.value = 0;
        markAuto(priceEl, true);
      }

      if (descEl) {
        descEl.value = '';
        markAuto(descEl, true);
      }

      if (durationEl) {
        durationEl.value = 12;
        markAuto(durationEl, true);
      }
    }

    watchManualInput(titleEl);
    watchManualInput(priceEl);
    watchManualInput(descEl);
    watchManualInput(durationEl);

    categorySelect.addEventListener('change', function () {
      resetDependentFields();
      loadSubjectsForCategory(this.value);
    });

    subjectInput.addEventListener('input', syncSubjectState);
    subjectInput.addEventListener('change', syncSubjectState);
    subjectInput.addEventListener('blur', syncSubjectState);

    const initialCategoryId = categorySelect.value;
    if (initialCategoryId) {
      loadSubjectsForCategory(initialCategoryId).then(function () {
        syncSubjectState();
      });
    } else {
      subjectInput.disabled = true;
      subjectInput.placeholder = 'Môn học';
    }

    if (subjectInput.value) {
      syncSubjectState();
    }
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
              <p><strong>Giảng viên:</strong> {{ $course->teacher?->displayName() ?? 'Chưa phân công' }}</p>
              <p><strong>Học viên đã xếp:</strong> {{ $course->enrollments_count ?? 0 }}</p>
            </div>
            <p class="mt-3 text-sm leading-6 text-gray-600">{{ $course->description ?? 'Chưa có mô tả cho khóa học này.' }}</p>
          </div>
          <div class="flex flex-wrap gap-2 mt-3">
            <a href="{{ route('admin.classes.create', ['subject_id' => $course->subject_id, 'course_id' => $course->id]) }}"
               class="inline-flex items-center gap-1.5 rounded-xl bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700 transition">
              <i class="fas fa-calendar-plus text-xs"></i>
              Tạo lớp
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
        {{ $selectedCategory ? 'Nhóm học này chưa có khóa học nào.' : 'Chưa có khóa học nào.' }}
      </div>
    @endforelse
  </div>
</div>
@endsection
