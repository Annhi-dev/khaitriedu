@php
  $courseLayout = 'layouts.app';

  if ($user?->isStudent()) {
      $courseLayout = 'layouts.student';
  } elseif ($user?->isTeacher()) {
      $courseLayout = 'layouts.teacher';
  } elseif ($user?->isAdmin()) {
      $courseLayout = 'layouts.admin';
  }
@endphp
@extends($courseLayout)
@section('title', $course->title)
@section('eyebrow', 'Lớp học nội bộ')
@section('content')
@php
  $backUrl = route('dashboard');
  $backLabel = 'Quay lại dashboard';

  if ($user && $user->isTeacher()) {
      $backUrl = route('teacher.courses');
      $backLabel = 'Quay lại lớp học phụ trách';
  } elseif ($user && $user->isAdmin()) {
      $backUrl = route('admin.courses');
      $backLabel = 'Quay lại quản lý lớp học';
  } elseif ($user && $user->isStudent()) {
      $backUrl = route('student.schedule');
      $backLabel = 'Quay lại lịch học';
  }

  $averageRating = $reviews->count() ? number_format($reviews->avg('rating'), 1) : null;
@endphp
<div class="max-w-5xl mx-auto space-y-6">
  <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-primary hover:gap-3 transition font-semibold">
    <i class="fas fa-arrow-left"></i>
    {{ $backLabel }}
  </a>

  @if($courseLayout === 'layouts.app' && session('status'))
    <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('status') }}</div>
  @endif

  @if($courseLayout === 'layouts.app' && session('error'))
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
  @endif

  <section class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-1 text-sm font-semibold text-blue-700">
          <i class="fas fa-users-rectangle"></i>
          Lớp học nội bộ
        </span>
        <h1 class="mt-4 text-3xl font-bold text-gray-900">{{ $course->title }}</h1>
        <p class="mt-3 max-w-3xl text-gray-600">
          {{ $course->description ?: 'Đây là lớp học mà admin đã xếp cho học viên sau khi duyệt yêu cầu đăng ký khóa học.' }}
        </p>
      </div>

      @if($averageRating)
        <div class="rounded-2xl bg-amber-50 px-5 py-4 text-center text-amber-800 shadow-sm">
          <div class="text-sm font-semibold uppercase tracking-wide">Đánh giá trung bình</div>
          <div class="mt-2 text-3xl font-black">{{ $averageRating }}</div>
          <div class="mt-1 text-sm">{{ $reviews->count() }} đánh giá</div>
        </div>
      @endif
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <div class="rounded-2xl bg-slate-50 p-4">
        <div class="text-sm text-gray-500">Khóa học học viên đã đăng ký</div>
        <div class="mt-2 text-lg font-bold text-gray-900">{{ $course->subject?->name ?? 'Chưa gắn khóa học' }}</div>
      </div>
      <div class="rounded-2xl bg-slate-50 p-4">
        <div class="text-sm text-gray-500">Nhóm ngành</div>
        <div class="mt-2 text-lg font-bold text-gray-900">{{ $course->subject?->category?->name ?? 'Chưa phân nhóm' }}</div>
      </div>
      <div class="rounded-2xl bg-slate-50 p-4">
        <div class="text-sm text-gray-500">Giảng viên phụ trách</div>
        <div class="mt-2 text-lg font-bold text-gray-900">{{ $course->teacher?->displayName() ?? 'Chưa phân công' }}</div>
      </div>
      <div class="rounded-2xl bg-slate-50 p-4">
        <div class="text-sm text-gray-500">Lịch lớp</div>
        <div class="mt-2 text-lg font-bold text-gray-900">{{ $course->schedule ?: ($enrollment->schedule ?? 'Chưa chốt lịch') }}</div>
      </div>
    </div>

    @if($user && $user->isStudent() && $enrollment)
      <div class="mt-6 rounded-2xl border border-primary/20 bg-primary-light/10 p-5">
        <h2 class="text-lg font-semibold text-primary-dark">Thông tin xếp lớp của bạn</h2>
        <div class="mt-3 grid gap-4 md:grid-cols-3 text-sm text-gray-700">
          <div>
            <div class="font-medium text-gray-500">Trạng thái</div>
            <div class="mt-1 font-semibold text-green-700">Đã được xếp lớp</div>
          </div>
          <div>
            <div class="font-medium text-gray-500">Lịch đã chốt</div>
            <div class="mt-1 font-semibold text-gray-900">{{ $enrollment->schedule ?: $course->schedule ?: 'Đang cập nhật' }}</div>
          </div>
          <div>
            <div class="font-medium text-gray-500">Giảng viên</div>
            <div class="mt-1 font-semibold text-gray-900">{{ $enrollment->assignedTeacher?->displayName() ?? $course->teacher?->displayName() ?? 'Chưa phân công' }}</div>
          </div>
        </div>
      </div>
    @endif
  </section>

  <section class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Lộ trình trong lớp học</h2>
        <p class="text-gray-600">Danh sách module và buổi học bạn có thể theo dõi trong lớp này.</p>
      </div>
      @php
        $totalSessions = $course->modules->sum(fn ($module) => $module->plannedSessionCount());
      @endphp
      <div class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">
        {{ $course->modules->count() }} module · {{ $totalSessions }} buổi
      </div>
    </div>

    @php
      $completedLessonLookup = array_flip($completedLessonIds ?? []);
    @endphp

    @if($user && $user->isStudent() && $enrollment && $courseProgress)
      <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <div class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Tiến độ học tập của bạn</div>
            <p class="mt-1 text-sm text-emerald-800">Đã hoàn thành {{ $courseProgress['completed'] }}/{{ $courseProgress['total'] }} buổi.</p>
          </div>
          <div class="text-3xl font-black text-emerald-700">{{ $courseProgress['percent'] }}%</div>
        </div>
      </div>
    @endif

    @forelse($course->modules as $module)
      @php
        $moduleStat = $moduleProgress[$module->id] ?? [
          'completed' => 0,
          'total' => $module->lessons->count(),
          'percent' => 0,
        ];
      @endphp
        <div class="mb-4 rounded-2xl border border-gray-200 p-5 last:mb-0">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
          <div>
            <div class="text-sm font-semibold uppercase tracking-wide text-primary">Module {{ $module->position ?? $loop->iteration }}</div>
            <h3 class="mt-1 text-xl font-bold text-gray-900">{{ $module->title }}</h3>
            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-primary">Học viên sẽ học gì?</p>
            <p class="mt-1 text-gray-600">{{ $module->learningSummary() }}</p>
          </div>
          @if($user && $user->isStudent() && $enrollment)
            <div class="rounded-2xl bg-emerald-50 px-4 py-2 text-right text-sm font-semibold text-emerald-700">
              <div>{{ $moduleStat['completed'] }}/{{ $moduleStat['total'] }} buổi</div>
              <div>{{ $moduleStat['percent'] }}% hoàn thành</div>
            </div>
          @else
            <div class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">
              {{ $module->sessionCountLabel() }}
            </div>
          @endif
        </div>

        @if($module->lessons->isEmpty())
          <div class="mt-4 rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-500">
            Chưa có buổi học nào trong module này.
          </div>
        @else
          <div class="mt-4 grid gap-3">
            @foreach($module->lessons as $lesson)
              <a href="{{ route('courses.lesson.show', [$course->id, $module->id, $lesson->id]) }}" class="flex flex-col gap-3 rounded-2xl border border-gray-200 px-4 py-4 transition hover:border-primary hover:bg-primary-light/10 md:flex-row md:items-center md:justify-between">
                <div>
                  <div class="text-sm font-semibold text-primary">Buổi {{ $lesson->order ?? $loop->iteration }}</div>
                  <div class="mt-1 text-lg font-semibold text-gray-900">{{ $lesson->title }}</div>
                  <p class="mt-1 text-sm text-gray-600">{{ $lesson->description ?: 'Mở buổi học để xem nội dung chi tiết.' }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                  @if($lesson->duration)
                    <span class="rounded-full bg-slate-100 px-3 py-1">{{ $lesson->duration }} phút</span>
                  @endif
                  @if($lesson->quiz)
                    <span class="rounded-full bg-amber-100 px-3 py-1 font-semibold text-amber-800">Có quiz</span>
                  @endif
                  @if($user && $user->isStudent() && $enrollment)
                    @if(isset($completedLessonLookup[$lesson->id]))
                      <span class="rounded-full bg-emerald-100 px-3 py-1 font-semibold text-emerald-700">Đã học</span>
                    @else
                      <span class="rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-600">Chưa học</span>
                    @endif
                  @endif
                  <span class="font-semibold text-primary">Vào buổi học</span>
                </div>
              </a>
            @endforeach
          </div>
        @endif
      </div>
    @empty
      <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-gray-500">
        Lớp học này chưa có module. Admin hoặc giảng viên sẽ cập nhật nội dung sớm.
      </div>
    @endforelse
  </section>

  @if($user && $user->isStudent() && $enrollment)
    <section class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Đánh giá lớp học</h2>
        <p class="text-gray-600">Chia sẻ trải nghiệm sau khi bạn đã được xếp và học trong lớp này.</p>
      </div>

      @if($review)
        <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5">
          <div class="text-sm font-semibold text-amber-800">Đánh giá của bạn</div>
          <div class="mt-2 text-lg font-bold text-gray-900">{{ str_repeat('★', $review->rating) }} <span class="text-sm font-medium text-gray-500">({{ $review->rating }}/5)</span></div>
          @if($review->comment)
            <p class="mt-3 text-gray-700">{{ $review->comment }}</p>
          @endif
        </div>
      @else
        <form method="post" action="{{ route('courses.review', $course->id) }}" class="mt-5 grid gap-4">
          @csrf
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Số sao</label>
            <select name="rating" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5">
              <option value="">Chọn số sao</option>
              <option value="5">5 sao</option>
              <option value="4">4 sao</option>
              <option value="3">3 sao</option>
              <option value="2">2 sao</option>
              <option value="1">1 sao</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Nhận xét</label>
            <textarea name="comment" rows="4" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" placeholder="Điều gì hữu ích nhất trong lớp này?"></textarea>
          </div>
          <div>
            <button type="submit" class="rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark transition">Gửi đánh giá</button>
          </div>
        </form>
      @endif
    </section>
  @endif

  <section class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Phản hồi từ học viên</h2>
        <p class="text-gray-600">Những chia sẻ gần đây của học viên đang theo lớp này.</p>
      </div>
      <div class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">
        {{ $reviews->count() }} phản hồi
      </div>
    </div>

    @if($reviews->isEmpty())
      <div class="mt-5 rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-gray-500">
        Chưa có đánh giá nào cho lớp học này.
      </div>
    @else
      <div class="mt-5 space-y-4">
        @foreach($reviews as $item)
          <div class="rounded-2xl border border-gray-200 p-5">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
              <div>
                <div class="text-lg font-semibold text-gray-900">{{ $item->user?->name ?? 'Học viên' }}</div>
                <div class="mt-1 text-sm font-semibold text-amber-600">{{ str_repeat('★', $item->rating) }} <span class="text-gray-500">({{ $item->rating }}/5)</span></div>
              </div>
              <div class="text-sm text-gray-500">{{ $item->created_at?->format('d/m/Y') }}</div>
            </div>
            @if($item->comment)
              <p class="mt-3 text-gray-700">{{ $item->comment }}</p>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </section>
</div>
@endsection
