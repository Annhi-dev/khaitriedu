@extends('bo_cuc.quan_tri')
@section('title', 'Lich hoc toan he thong')
@section('content')
<div class="space-y-6">
    <span class="sr-only">Xếp lịch</span>

    <x-quan_tri.tieu_de_trang title="Lich hoc toan he thong" subtitle="Theo doi cac lop da xep lich, lop cho mo va phan bo giang vien trong toan trung tam">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.schedules.conflicts') }}" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2 text-sm font-semibold text-cyan-700 hover:bg-cyan-100 transition">Kiem tra xung dot</a>
            <a href="{{ route('admin.schedules.queue') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Hang cho xep lich</a>
        </div>
    </x-quan_tri.tieu_de_trang>

    <form method="get" action="{{ route('admin.schedules.index') }}" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div>
                <label class="block text-sm font-medium text-slate-700">Giang vien</label>
                <select name="teacher_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">Tat ca</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>{{ $teacher->displayName() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Hoc vien</label>
                <select name="student_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">Tat ca</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" @selected(request('student_id') == $student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Lop hoc</label>
                <select name="course_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">Tat ca</option>
                    @foreach($courses as $courseOption)
                        <option value="{{ $courseOption->id }}" @selected(request('course_id') == $courseOption->id)>{{ $courseOption->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Ngay hoc</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-xl border px-3 py-2">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-white">Loc</button>
                <a href="{{ route('admin.schedules.index') }}" class="rounded-xl border px-4 py-2">Xoa</a>
            </div>
        </div>
    </form>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @forelse($schedules as $course)
            @php
                $studentsNeeded = max(0, \App\Models\Course::minimumStudentsToOpen() - (int) $course->scheduled_students_count);
                $classRoom = $course->currentClassRoom();
                $detailUrl = route('admin.schedules.courses.show', $course);
            @endphp
            <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md">
                <a href="{{ $detailUrl }}" class="absolute inset-0 z-0 rounded-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400" aria-label="Xem chi tiết {{ $course->title }}"></a>

                <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ $course->subject?->category?->name ?? 'Chua phan nhom' }}</p>
                        <h3 class="mt-2 text-lg font-semibold tracking-tight text-slate-950">{{ $course->title }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Nhấn vào thẻ để xem chi tiết lớp học.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        <x-quan_tri.huy_hieu :type="$course->isPendingOpen() ? 'warning' : 'info'" :text="$course->statusLabel()" />
                        <a href="{{ route('admin.course.show', $course) }}" class="inline-flex items-center justify-center rounded-full border border-rose-200 bg-rose-50 px-3.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                            <i class="fas fa-bolt mr-1.5 text-[10px]"></i>
                            Sửa nhanh
                        </a>
                        <a href="{{ $detailUrl }}" class="inline-flex items-center justify-center rounded-full border border-cyan-200 bg-cyan-50 px-3.5 py-1.5 text-xs font-semibold text-cyan-700 transition hover:bg-cyan-100">
                            <i class="fas fa-calendar-day mr-1.5 text-[10px]"></i>
                            Xem lịch chi tiết
                        </a>
                        @if($classRoom)
                            <a href="{{ route('admin.classes.show', $classRoom) }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                <i class="fas fa-door-open mr-1.5 text-[10px]"></i>
                                Xem lớp học
                            </a>
                        @endif
                    </div>
                </div>

                <div class="relative z-10 mt-5 grid gap-3 text-sm text-slate-600 sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Giảng viên</span>
                        <span class="mt-1 block text-sm font-medium text-slate-700">{{ $course->teacher?->displayName() ?? 'Chua phan cong' }}</span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Lớp</span>
                        <span class="mt-1 block text-sm font-medium text-slate-700">{{ $classRoom?->displayName() ?? ($course->isPendingOpen() ? 'Chua mo lop' : 'Chua co lop') }}</span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Lịch</span>
                        <span class="mt-1 block text-sm font-medium text-slate-700">{{ $course->formattedSchedule() }}</span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Sĩ số</span>
                        <span class="mt-1 block text-sm font-medium text-slate-700">{{ $course->scheduled_students_count }}/{{ $course->capacity ?? 20 }}</span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 sm:col-span-2">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Ngày học</span>
                        <span class="mt-1 block text-sm font-medium text-slate-700">{{ $course->meetingDaysLabel() }}</span>
                    </div>
                </div>

                @if ($course->isPendingOpen())
                    <div class="relative z-10 mt-4 rounded-2xl {{ $studentsNeeded === 0 ? 'border border-emerald-200 bg-emerald-50 text-emerald-800' : 'border border-amber-200 bg-amber-50 text-amber-800' }} px-4 py-3 text-sm leading-6">
                        @if ($studentsNeeded === 0)
                            Lop da du toi thieu {{ \App\Models\Course::minimumStudentsToOpen() }} hoc vien va co the mo lop ngay bay gio.
                        @else
                            Lop dang cho mo. Con thieu {{ $studentsNeeded }} hoc vien nua de chot ngay khai giang.
                        @endif
                    </div>
                @endif

                <div class="relative z-10 mt-4 rounded-2xl bg-slate-50 p-4 text-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Học viên</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse($course->enrollments->whereIn('status', \App\Models\Enrollment::courseAccessStatuses()) as $enrollment)
                            <span class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600">{{ $enrollment->user?->name }}</span>
                        @empty
                            <span class="text-xs text-slate-500">Chua co</span>
                        @endforelse
                    </div>
                </div>

                @if ($course->isPendingOpen())
                    <div class="relative z-10 mt-4 flex justify-end">
                        <a href="{{ route('admin.schedules.courses.open', $course) }}" class="inline-flex items-center justify-center rounded-2xl {{ $studentsNeeded === 0 ? 'bg-cyan-600 text-white hover:bg-cyan-700' : 'border border-slate-300 text-slate-600 hover:bg-slate-50' }} px-4 py-2.5 text-sm font-semibold transition">
                            {{ $studentsNeeded === 0 ? 'Chon ngay va mo lop' : 'Xem dieu kien mo lop' }}
                        </a>
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-2 rounded-2xl border border-dashed border-slate-300 bg-white py-12 text-center">
                Chua co lop hoc nao duoc luu trong he thong.
            </div>
        @endforelse
    </div>

    @if($schedules->hasPages())
        <div>{{ $schedules->links() }}</div>
    @endif
</div>
@endsection
