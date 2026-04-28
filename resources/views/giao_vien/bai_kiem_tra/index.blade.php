@extends('bo_cuc.giao_vien')

@section('title', 'Bài kiểm tra')
@section('eyebrow', 'Teacher Tests')

@section('content')
@php
    $statusOptions = [
        'all' => 'Tất cả',
        \App\Models\Quiz::STATUS_DRAFT => 'Nháp',
        \App\Models\Quiz::STATUS_PUBLISHED => 'Công khai',
    ];

    $selectedStatus = $filters['status'] ?? 'all';
    $selectedSearch = $filters['search'] ?? '';
@endphp

<div class="space-y-6">
    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="border-b border-slate-100 p-6 lg:border-b-0 lg:border-r lg:p-7">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">Quiz Management</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Bài kiểm tra của tôi</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">Tạo và quản lý bài kiểm tra theo lớp, khóa hoặc môn học được phân công. Học viên chỉ thấy bài đã công khai.</p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white">
                        {{ $summary['total'] }} bài kiểm tra
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700">
                        {{ $summary['questions'] }} câu hỏi
                    </span>
                </div>
            </div>

            <div class="grid gap-3 bg-slate-50 p-6 sm:grid-cols-2 lg:grid-cols-1">
                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Công khai</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $summary['published'] }}</p>
                    <p class="mt-1 text-sm text-slate-500">Đang hiển thị cho học viên</p>
                </div>
                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Nháp</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $summary['draft'] }}</p>
                    <p class="mt-1 text-sm text-slate-500">Chưa xuất bản cho lớp</p>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('teacher.tests.index') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px_auto]">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Tìm kiếm</label>
                <input type="text" name="search" value="{{ $selectedSearch }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Tên bài kiểm tra, lớp, khóa hoặc môn học">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Trạng thái</label>
                <select name="status" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">Lọc</button>
                <a href="{{ route('teacher.tests.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Xóa lọc</a>
                <a href="{{ route('teacher.tests.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">
                    <i class="fas fa-plus mr-2"></i>
                    Tạo bài kiểm tra
                </a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-4 text-left font-semibold text-slate-500">Tên bài kiểm tra</th>
                        <th class="px-5 py-4 text-left font-semibold text-slate-500">Lớp / Khóa / Môn</th>
                        <th class="px-5 py-4 text-left font-semibold text-slate-500">Câu hỏi</th>
                        <th class="px-5 py-4 text-left font-semibold text-slate-500">Thời gian</th>
                        <th class="px-5 py-4 text-left font-semibold text-slate-500">Trạng thái</th>
                        <th class="px-5 py-4 text-left font-semibold text-slate-500">Ngày tạo</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-500">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tests as $test)
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">{{ $test->title }}</div>
                                <p class="mt-1 max-w-xl text-xs leading-5 text-slate-500">{{ $test->description ?: 'Chưa có mô tả.' }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <div class="space-y-1">
                                    <p>{{ $test->targetLabel() }}</p>
                                    <p class="text-xs text-slate-400">{{ $test->course?->title ?? $test->subject?->name ?? 'Chưa xác định' }}</p>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $test->questions_count }} câu</td>
                            <td class="px-5 py-4 text-slate-600">{{ $test->durationLabel() }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $test->isPublished() ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $test->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ optional($test->created_at)->format('d/m/Y H:i') ?? 'Chưa rõ' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('teacher.tests.show', $test) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Xem</a>
                                    <a href="{{ route('teacher.tests.edit', $test) }}" class="rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-2 text-xs font-semibold text-cyan-700 hover:bg-cyan-100">Sửa</a>
                                    <form method="POST" action="{{ route('teacher.tests.destroy', $test) }}" onsubmit="return confirm('Xóa bài kiểm tra này?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-100">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="mx-auto max-w-xl">
                                    <p class="text-lg font-semibold text-slate-900">Chưa có bài kiểm tra nào.</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-500">Tạo bài kiểm tra mới để học viên xem ở phần lớp học của mình.</p>
                                    <a href="{{ route('teacher.tests.create') }}" class="mt-5 inline-flex items-center gap-2 rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">
                                        <i class="fas fa-plus"></i>
                                        Tạo bài kiểm tra đầu tiên
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
