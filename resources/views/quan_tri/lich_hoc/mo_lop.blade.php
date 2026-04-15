@extends('bo_cuc.quan_tri')
@section('title', 'Mo lop cho')
@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
            <i class="fas fa-arrow-left"></i>
            Quay lai lich hoc toan he thong
        </a>
        <h1 class="mt-3 text-3xl font-semibold text-slate-900">Mo lop cho {{ $course->title }}</h1>
        <p class="mt-2 text-sm text-slate-500">Luu y kiem tra thong tin truoc khi chot lop.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(360px,0.9fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $course->subject?->category?->name ?? 'Chua phan nhom' }}</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $course->title }}</h2>
                </div>
                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">{{ $course->statusLabel() }}</span>
            </div>

            <div class="mt-5 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                <p><strong>Giang vien:</strong> {{ $course->teacher?->displayName() ?? 'Chua phan cong' }}</p>
                <p><strong>Lich hoc du kien:</strong> {{ $course->meetingDaysLabel() }} | {{ $course->start_time }} - {{ $course->end_time }}</p>
                <p><strong>Si so hien tai:</strong> {{ $course->scheduled_students_count }}/{{ $minimumStudentsToOpen }} hoc vien toi thieu</p>
                <p><strong>Suc chua:</strong> {{ $course->capacity ?? 20 }}</p>
            </div>

            @if ($studentsNeeded > 0)
                <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                    Lop nay con thieu {{ $studentsNeeded }} hoc vien nua moi du dieu kien mo lop.
                </div>
            @else
                <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-800">
                    Lop da du toi thieu {{ $minimumStudentsToOpen }} hoc vien. Ban co the chot ngay khai giang ngay bay gio.
                </div>
            @endif

        </section>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="post" action="{{ route('admin.schedules.courses.open.store', $course) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-medium text-slate-700">Phong hoc chinh thuc</label>
                    <select name="room_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        <option value="">Chon phong hoc</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" @selected((string) old('room_id') === (string) $room->id)>
                                {{ $room->name }}{{ $room->code ? ' (' . $room->code . ')' : '' }} - suc chua {{ $room->capacity }}
                            </option>
                        @endforeach
                    </select>
                    @error('room_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Ngay bat dau</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                    @error('start_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Ngay ket thuc</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                    @error('end_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white hover:bg-cyan-700" @disabled($studentsNeeded > 0)>
                    Chot ngay va mo lop
                </button>
            </form>
        </aside>
    </div>
</div>
@endsection
