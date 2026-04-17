@extends('bo_cuc.quan_tri')

@section('title', 'Điểm số toàn hệ thống')

@section('content')
@php
    $gradeList = method_exists($grades, 'getCollection') ? $grades->getCollection() : collect($grades);
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Bảng điểm</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Điểm số toàn hệ thống</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Theo dõi toàn bộ điểm số theo học viên, lớp học, khóa học và môn học. Đây là màn đọc chi tiết để admin kiểm tra dữ liệu gốc phía sau các thống kê.
                </p>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                <i class="fas fa-gauge-high"></i>
                Quay lại tổng quan
            </a>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <x-quan_tri.the_thong_ke label="Tổng bản ghi" :value="number_format($summary['totalGrades'] ?? 0)" icon="fas fa-clipboard-list" color="cyan" trend="Tất cả điểm đang hiển thị theo bộ lọc hiện tại" />
            <x-quan_tri.the_thong_ke label="Học viên có điểm" :value="number_format($summary['uniqueStudents'] ?? 0)" icon="fas fa-user-graduate" color="emerald" trend="Số học viên khác nhau có ít nhất một điểm" />
            <x-quan_tri.the_thong_ke label="Lớp có điểm" :value="number_format($summary['uniqueClasses'] ?? 0)" icon="fas fa-people-group" color="amber" trend="Số lớp học có dữ liệu điểm" />
            <x-quan_tri.the_thong_ke label="Điểm trung bình" :value="($summary['averageScore'] ?? null) !== null ? number_format((float) $summary['averageScore'], 1) : '--'" icon="fas fa-chart-line" color="violet" trend="Tính trên các bản ghi có score hợp lệ" />
        </div>
    </section>

    <x-quan_tri.thanh_loc route="{{ route('admin.grades.index') }}" searchPlaceholder="Học viên, lớp, môn, bài kiểm tra...">
        <x-slot name="additionalFilters">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Học viên</label>
                    <select name="student_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
                        <option value="">Tất cả học viên</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected((string) request('student_id') === (string) $student->id)>{{ $student->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Môn học</label>
                    <select name="subject_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
                        <option value="">Tất cả môn học</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string) request('subject_id') === (string) $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Khóa học</label>
                    <select name="course_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
                        <option value="">Tất cả khóa học</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected((string) request('course_id') === (string) $course->id)>{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Lớp học</label>
                    <select name="class_room_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
                        <option value="">Tất cả lớp học</option>
                        @foreach ($classRooms as $classRoom)
                            <option value="{{ $classRoom->id }}" @selected((string) request('class_room_id') === (string) $classRoom->id)>{{ $classRoom->displayName() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-slot>
    </x-quan_tri.thanh_loc>

    <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Danh sách điểm</h3>
                <p class="mt-1 text-sm text-slate-500">Hiển thị {{ number_format($grades->total()) }} bản ghi phù hợp với bộ lọc hiện tại.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $gradeList->count() }} dòng trên trang này</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium">Học viên</th>
                        <th class="px-6 py-3 text-left font-medium">Lớp / Khóa</th>
                        <th class="px-6 py-3 text-left font-medium">Môn / Bài kiểm tra</th>
                        <th class="px-6 py-3 text-right font-medium">Điểm</th>
                        <th class="px-6 py-3 text-right font-medium">Hệ số</th>
                        <th class="px-6 py-3 text-left font-medium">Giảng viên</th>
                        <th class="px-6 py-3 text-left font-medium">Cập nhật</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($gradeList as $grade)
                        @php
                            $student = $grade->student;
                            $classRoom = $grade->classRoom ?? $grade->enrollment?->classRoom;
                            $course = $grade->enrollment?->course ?? $classRoom?->course;
                            $subject = $classRoom?->subject ?? $course?->subject ?? $grade->enrollment?->course?->subject;
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $student?->name ?? 'Học viên' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $student?->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $classRoom?->displayName() ?? 'Chưa xếp lớp' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $course?->title ?? 'Chưa xác định khóa học' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $subject?->name ?? 'Chưa xác định môn' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $grade->module?->title ?? $grade->test_name ?? 'Chưa có bài kiểm tra' }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $grade->score ?? '--' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right text-slate-700">{{ $grade->weight ?? 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $grade->teacher?->displayName() ?? 'Chưa rõ' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $grade->grade ?? 'Chưa xếp loại' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $grade->updated_at?->format('d/m/Y H:i') ?? 'Chưa rõ' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                Chưa có điểm số nào phù hợp với bộ lọc hiện tại.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <x-quan_tri.phan_trang :paginator="$grades" label="bản ghi điểm" />
</div>
@endsection
