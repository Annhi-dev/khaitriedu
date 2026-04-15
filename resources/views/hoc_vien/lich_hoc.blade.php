@extends('bo_cuc.hoc_vien')
@section('title', 'Lich hoc cua toi')
@section('eyebrow', 'Student Schedule')
@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-dark">Lich hoc cua toi</h1>
        </div>
    <a href="{{ route('student.dashboard') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-primary hover:text-primary">Quay lai dashboard</a>
  </div>

  @include('dung_chung.luoi_lich_hoc', [
      'grid' => $weeklyTimetable,
      'sectionEyebrow' => 'Student Schedule',
      'sectionTitle' => 'Lich hoc theo tuan',
      'sectionSubtitle' => 'Moi o la mot khung gio thuc te trong tuan hien tai. Bam vao tung o de xem chi tiet.',
      'emptyMessage' => 'Ban chua co buoi hoc nao trong tuan nay.',
  ])

  <div class="grid gap-4">
    @forelse($enrollments as $enrollment)
      @php
        $course = $enrollment->course;
        $waitingOpen = $course?->isPendingOpen();
        $classRoom = $enrollment->classRoom;
        $attendanceSummary = $classRoom ? ($attendanceSummaries->get($classRoom->id) ?? null) : null;
        $classmates = $classRoom
            ? $classRoom->enrollments
                ->filter(fn ($classEnrollment) => (int) $classEnrollment->user_id !== (int) $enrollment->user_id && $classEnrollment->user !== null)
                ->map(fn ($classEnrollment) => $classEnrollment->user)
                ->values()
            : collect();
      @endphp
      <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <div class="text-sm font-semibold uppercase tracking-wide text-primary">Lop hoc</div>
            <div class="mt-2 flex flex-wrap items-center gap-2">
              <h2 class="text-xl font-bold text-gray-900">{{ $course?->title }}</h2>
              <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $waitingOpen ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                {{ $waitingOpen ? $course->statusLabel() : $enrollment->statusLabel() }}
              </span>
            </div>
            <div class="mt-3 grid gap-2 text-sm text-gray-600 md:grid-cols-2">
              <p><strong>Khoa hoc:</strong> {{ $course?->subject?->name ?? 'Chua xac dinh' }}</p>
              <p><strong>Giang vien:</strong> {{ $enrollment->assignedTeacher->name ?? 'Chua phan cong' }}</p>
              <p><strong>Lich hoc:</strong> {{ $course?->formattedSchedule() ?? 'Chua co lich' }}</p>
              <p><strong>Trang thai:</strong> <span class="font-semibold {{ $waitingOpen ? 'text-amber-700' : 'text-green-700' }}">{{ $waitingOpen ? 'Dang cho mo lop' : $enrollment->statusLabel() }}</span></p>
            </div>

            @if ($waitingOpen)
              <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Lớp đang chờ mở chính thức.
              </div>
            @endif

            @if (! $waitingOpen && $classRoom)
              <div class="mt-4 grid gap-4 xl:grid-cols-2">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                  <p class="text-sm font-semibold text-emerald-800">Thong ke diem danh</p>
                  @if ($attendanceSummary)
                    <div class="mt-3 grid grid-cols-2 gap-2 text-sm text-emerald-900">
                      <p><strong>Tong buoi:</strong> {{ $attendanceSummary['total'] }}</p>
                      <p><strong>Ty le co mat:</strong> {{ $attendanceSummary['present_rate'] }}%</p>
                      <p><strong>Co mat:</strong> {{ $attendanceSummary['present'] }}</p>
                      <p><strong>Di tre:</strong> {{ $attendanceSummary['late'] }}</p>
                      <p><strong>Co phep:</strong> {{ $attendanceSummary['excused'] }}</p>
                      <p><strong>Vang:</strong> {{ $attendanceSummary['absent'] }}</p>
                    </div>

                    <div class="mt-3 border-t border-emerald-200 pt-3">
                      <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Lan diem danh gan day</p>
                      <ul class="mt-2 space-y-1 text-xs text-emerald-900">
                        @foreach ($attendanceSummary['recent'] as $attendanceItem)
                          <li>
                            {{ $attendanceItem->attendance_date?->format('d/m/Y') ?? 'Chua ro ngay' }}:
                            {{ $attendanceItem->statusLabel() }}
                          </li>
                        @endforeach
                      </ul>
                    </div>
                  @else
                    <p class="mt-2 text-sm text-emerald-800">Giang vien chua diem danh lop nay.</p>
                  @endif
                </div>

                <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4">
                  <p class="text-sm font-semibold text-sky-800">Ban hoc cung lop</p>
                  @if ($classmates->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-2">
                      @foreach ($classmates->take(8) as $classmate)
                        <span class="rounded-full border border-sky-200 bg-white px-3 py-1 text-xs font-medium text-sky-800">
                          {{ $classmate->name }}
                        </span>
                      @endforeach
                    </div>
                    @if ($classmates->count() > 8)
                      <p class="mt-2 text-xs text-sky-700">Va {{ $classmates->count() - 8 }} ban khac trong lop.</p>
                    @endif
                  @else
                    <p class="mt-2 text-sm text-sky-800">Hien chua co them hoc vien nao khac trong lop nay.</p>
                  @endif
                </div>
              </div>
            @endif
          </div>

          <div class="flex items-center gap-3">
            @if (! $waitingOpen)
              <a href="{{ route('courses.show', $course->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 font-semibold text-white transition hover:bg-primary-dark">
                <i class="fas fa-arrow-right"></i>
                Vao lop hoc
              </a>
            @else
              <span class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700">
                <i class="fas fa-hourglass-half"></i>
                Dang cho mo lop
              </span>
            @endif
          </div>
        </div>
      </div>
    @empty
      <div class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
        <p class="text-lg font-semibold text-gray-700">Ban chua duoc xep vao lop hoc nao.</p>
        <p class="mt-2 text-gray-500">Hãy chọn khóa học và gửi yêu cầu lịch học phù hợp.</p>
        <a href="{{ route('student.enroll.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 font-semibold text-white transition hover:bg-primary-dark">
          <i class="fas fa-book-open"></i>
          Xem khoa hoc
        </a>
      </div>
    @endforelse
  </div>
</div>
@endsection
