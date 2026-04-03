@extends('layouts.admin')
@section('title', 'Quản lý module')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Phase 7</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Quản lý module cho lớp học</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Sắp xếp nội dung thành từng module cho lớp <strong>{{ $course->title }}</strong>, kiểm soát trạng thái hiển thị và chuẩn bị cho phase bài học/quiz tiếp theo.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.course.show', $course->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Về chi tiết lớp học</a>
            <a href="{{ route('admin.courses') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Danh sách lớp học</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(360px,1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Danh sách module</h2>
                    <p class="text-sm text-slate-500">Khóa học public: {{ $course->subject?->name ?? 'Chưa gắn khóa học' }}</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $modules->count() }} module</span>
            </div>

            @if ($modules->count())
                <form method="post" action="{{ route('admin.courses.modules.reorder', $course) }}" class="mt-5 space-y-4">
                    @csrf
                    @foreach ($modules as $listedModule)
                        @php
                            $statusClasses = $listedModule->status === \App\Models\Module::STATUS_PUBLISHED
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                : 'border-amber-200 bg-amber-50 text-amber-700';
                        @endphp
                        <div class="rounded-2xl border border-slate-200 px-4 py-4">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-sm font-semibold text-slate-900">{{ $listedModule->title }}</h3>
                                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $listedModule->statusLabel() }}</span>
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $listedModule->content ?: 'Chưa có mô tả cho module này.' }}</p>
                                    <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-slate-500">
                                        <span>{{ $listedModule->durationLabel() }}</span>
                                        <span>{{ $listedModule->lessons_count }} bài học</span>
                                        <span>{{ $listedModule->quizzes_count }} quiz</span>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Thứ tự</label>
                                        <input type="number" min="1" name="positions[{{ $listedModule->id }}]" value="{{ $listedModule->position }}" class="w-24 rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                                    </div>
                                    <a href="{{ route('admin.courses.modules.edit', [$course, $listedModule]) }}" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 px-3 py-2 text-xs font-medium text-cyan-700 hover:bg-cyan-50">Sửa</a>
                                    <form method="post" action="{{ route('admin.courses.modules.delete', [$course, $listedModule]) }}" onsubmit="return confirm('Xóa module này? Nếu đang có bài học hoặc quiz thì hệ thống sẽ chuyển sang ẩn thay vì xóa cứng.');">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">Xóa</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">Cập nhật thứ tự module</button>
                    </div>
                </form>
            @else
                <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-10 text-center text-sm text-slate-500">Lớp học này chưa có module nào. Hãy tạo module đầu tiên ở khung bên cạnh.</div>
            @endif
        </section>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thêm module mới</h2>
                <form method="post" action="{{ route('admin.courses.modules.create', $course) }}" class="mt-5 space-y-4">
                    @csrf
                    @include('admin.course._module_form', ['module' => null, 'defaultPosition' => ((int) $modules->max('position')) + 1])
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Lưu module</button>
                </form>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Lưu ý</h2>
                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-600">
                    <p>Module ở trạng thái ẩn sẽ không hiển thị cho học viên trong màn học.</p>
                    <p class="mt-2">Nếu module đã có bài học hoặc quiz, thao tác xóa sẽ chuyển module sang ẩn để tránh mất dữ liệu học tập.</p>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection

