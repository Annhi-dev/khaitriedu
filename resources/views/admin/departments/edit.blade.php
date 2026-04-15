@extends('layouts.admin')
@section('title', 'Cập nhật phòng ban')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-slate-900">Cập nhật phòng ban</h1>
        </div>
        <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Danh sách phòng ban</a>
    </div>

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="post" action="{{ route('admin.departments.update', $department) }}" class="space-y-2">
            @csrf
            @include('admin.departments._form', ['submitLabel' => 'Lưu thay đổi', 'department' => $department])
        </form>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Giảng viên trong phòng ban</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $teachers->count() }} giảng viên</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($teachers as $teacher)
                    <div class="rounded-2xl border border-slate-200 px-4 py-3">
                        <p class="text-sm font-semibold text-slate-900">{{ $teacher->displayName() }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $teacher->email }} · {{ $teacher->username }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        Chưa có giảng viên nào trong phòng ban này.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Thêm giảng viên vào phòng ban</h2>
            <form method="post" action="{{ route('admin.departments.teachers.assign', $department) }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Chọn giảng viên</label>
                    <select name="teacher_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                        <option value="">-- Chọn giảng viên --</option>
                        @foreach ($assignableTeachers as $teacherOption)
                            <option value="{{ $teacherOption->id }}" @selected((string) old('teacher_id') === (string) $teacherOption->id)>
                                {{ $teacherOption->displayName() }}
                                @if($teacherOption->department?->name)
                                    (Đang thuộc: {{ $teacherOption->department->name }})
                                @else
                                    (Chưa gán phòng ban)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                    @if($assignableTeachers->isEmpty())
                        <p class="mt-2 text-xs text-slate-500">Không còn giảng viên nào để thêm/chuyển vào phòng ban này.</p>
                    @endif
                </div>

                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-50" @disabled($assignableTeachers->isEmpty())>
                    Thêm vào phòng ban
                </button>
            </form>
        </section>
    </div>
</div>
@endsection
