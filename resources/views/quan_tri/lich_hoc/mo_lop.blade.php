@extends('bo_cuc.quan_tri')
@section('title', 'Mo lop cho')
@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
            <i class="fas fa-arrow-left"></i>
            Quay lai lich hoc toan he thong
        </a>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Mo lop cho {{ $course->title }}</h1>
        <p class="mt-2 text-sm text-slate-500">Lưu ý kiểm tra thông tin trước khi chốt lớp.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(360px,0.9fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ $course->subject?->category?->name ?? 'Chua phan nhom' }}</p>
                    <h2 class="mt-2 text-xl font-semibold tracking-tight text-slate-950">{{ $course->title }}</h2>
                </div>
                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">{{ $course->statusLabel() }}</span>
            </div>

            <div class="mt-5 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Giảng viên</p>
                    <p class="mt-1 font-medium text-slate-700">{{ $course->teacher?->displayName() ?? 'Chua phan cong' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Lịch học dự kiến</p>
                    <p class="mt-1 font-medium text-slate-700">{{ $course->meetingDaysLabel() }} | {{ $course->start_time }} - {{ $course->end_time }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Sĩ số hiện tại</p>
                    <p class="mt-1 font-medium text-slate-700">{{ $course->scheduled_students_count }}/{{ $minimumStudentsToOpen }} hoc vien toi thieu</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Sức chứa</p>
                    <p class="mt-1 font-medium text-slate-700">{{ $course->capacity ?? 20 }}</p>
                </div>
            </div>

            @if ($studentsNeeded > 0)
                <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm leading-6 text-amber-800">
                    Lop nay con thieu {{ $studentsNeeded }} hoc vien nua moi du dieu kien mo lop.
                </div>
            @else
                <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm leading-6 text-emerald-800">
                    Lop da du toi thieu {{ $minimumStudentsToOpen }} hoc vien. Ban co the chot ngay khai giang ngay bay gio.
                </div>
            @endif

        </section>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="post" action="{{ route('admin.schedules.courses.open.store', $course) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-medium text-slate-700">Phòng học chính thức</label>
                    <select name="room_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        <option value="">Chọn phòng học</option>
                        @forelse ($rooms as $room)
                            <option value="{{ $room->id }}" @selected((string) old('room_id') === (string) $room->id)>
                                {{ $room->name }}{{ $room->code ? ' (' . $room->code . ')' : '' }} - sức chứa {{ $room->capacity }}
                            </option>
                        @empty
                            <option value="" disabled>Không có phòng trống phù hợp lịch này</option>
                        @endforelse
                    </select>
                    @if (($availableRoomsCount ?? 0) === 0)
                        <p class="mt-2 text-sm text-amber-700">Không có phòng nào trống cho lịch học hiện tại. Hãy đổi ngày hoặc giờ học.</p>
                    @endif
                    @error('room_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Ngày bắt đầu</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                    @error('start_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Ngày kết thúc</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                    @error('end_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white hover:bg-cyan-700" @disabled($studentsNeeded > 0)>
                    Chốt ngày và mở lớp
                </button>
            </form>
        </aside>
    </div>
</div>
@endsection
