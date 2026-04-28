@extends('bo_cuc.giao_vien')

@section('title', 'Chi Tiết Lớp')

@section('content')
@php
    $attendanceStatuses = \App\Models\AttendanceRecord::statusOptions();
    $timeRanges = $classRoom->schedules
        ->map(fn ($schedule) => substr((string) $schedule->start_time, 0, 5) . ' - ' . substr((string) $schedule->end_time, 0, 5))
        ->unique()
        ->values();
@endphp

<div class="space-y-6" x-data="{ tab: '{{ $activeTab }}' }">
    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <a href="{{ route('teacher.classes.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại danh sách lớp
                </a>
                <p class="mt-4 text-xs uppercase tracking-[0.22em] text-slate-400">Class #{{ $classRoom->id }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-slate-900">{{ $classRoom->displayName() }}</h2>
                @if ($classRoom->note)
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">{{ $classRoom->note }}</p>
                @endif
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <a href="{{ route('teacher.tests.create', ['lop_hoc_id' => $classRoom->id]) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700 sm:col-span-2">
                    <i class="fas fa-plus"></i>
                    Tạo bài kiểm tra cho lớp này
                </a>
                <div class="rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-600">
                    <p><strong>Môn học:</strong> {{ $classRoom->subject?->name ?? 'Chưa xác định' }}</p>
                    <p class="mt-1"><strong>Nhóm học:</strong> {{ $classRoom->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                    <p class="mt-1"><strong>Phòng:</strong> {{ $classRoom->room?->name ?? 'Chưa phân phòng' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-600">
                    <p><strong>Lịch lớp:</strong> {{ $classRoom->scheduleSummary() }}</p>
                    <p class="mt-1"><strong>Thời gian học:</strong> {{ $timeRanges->isNotEmpty() ? $timeRanges->implode(' | ') : 'Chưa chốt' }}</p>
                    <p class="mt-1"><strong>Số học viên:</strong> {{ $enrollments->count() }}</p>
                    <p class="mt-1"><strong>Bắt đầu:</strong> {{ $classRoom->start_date?->format('d/m/Y') ?? 'Chưa chốt' }}</p>
                    <p class="mt-1"><strong>Kết thúc:</strong> {{ $classRoom->scheduleRangeEnd()?->format('d/m/Y') ?? 'Chưa chốt' }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-2 shadow-sm ring-1 ring-slate-200">
        <div class="grid gap-2 md:grid-cols-4">
            @php
                $tabs = [
                    'students' => ['label' => 'Danh sách học viên', 'icon' => 'fa-user-group'],
                    'attendance' => ['label' => 'Điểm danh', 'icon' => 'fa-clipboard-check'],
                    'grades' => ['label' => 'Bảng điểm', 'icon' => 'fa-square-poll-vertical'],
                    'evaluations' => ['label' => 'Đánh giá', 'icon' => 'fa-comments'],
                ];
            @endphp

            @foreach ($tabs as $key => $item)
                <button type="button"
                    class="flex items-center justify-center gap-2 rounded-[1.25rem] px-4 py-3 text-sm font-medium transition"
                    :class="tab === '{{ $key }}' ? 'bg-slate-950 text-white shadow-lg shadow-slate-900/15' : 'text-slate-600 hover:bg-slate-50'"
                    @click="tab = '{{ $key }}'">
                    <i class="fas {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </button>
            @endforeach
        </div>
    </section>

    <section x-show="tab === 'students'" x-cloak class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-2xl font-semibold text-slate-900">Danh sách học viên</h3>
            </div>
            <span class="rounded-full bg-cyan-50 px-4 py-2 text-sm font-medium text-cyan-700">{{ $enrollments->count() }} học viên</span>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Học viên</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Email</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Lịch đã chốt</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($enrollments as $enrollment)
                        <tr>
                            <td class="px-4 py-4 font-medium text-slate-900">{{ $enrollment->user?->name }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $enrollment->user?->email }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $enrollment->schedule ?: $classRoom->scheduleSummary() }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $enrollment->displayStatusLabel() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-slate-500">Chưa có học viên nào trong lớp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section x-show="tab === 'attendance'" x-cloak class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-slate-900">Điểm danh theo buổi</h3>
            </div>
            <form method="GET" action="{{ route('teacher.classes.show', $classRoom) }}" class="grid gap-3 sm:grid-cols-[220px_180px_auto]">
                <input type="hidden" name="tab" value="attendance">
                <select name="schedule_id" class="rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                    @foreach ($classRoom->schedules as $schedule)
                        <option value="{{ $schedule->id }}" @selected(optional($selectedSchedule)->id === $schedule->id)>{{ $schedule->label() }}</option>
                    @endforeach
                </select>
                <input type="date" name="date" value="{{ $selectedDate }}" class="rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                <button type="submit" class="rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Tải buổi học</button>
            </form>
        </div>

        @if ($selectedSchedule)
            <form method="POST" action="{{ route('teacher.classes.attendance.store', $classRoom) }}" class="mt-6 space-y-5">
                @csrf
                <input type="hidden" name="class_schedule_id" value="{{ $selectedSchedule->id }}">
                <input type="hidden" name="attendance_date" value="{{ $selectedDate }}">

                <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    <strong>Buổi đang chọn:</strong> {{ $selectedSchedule->label() }} • {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-slate-500">Học viên</th>
                                <th class="px-4 py-3 text-left font-medium text-slate-500">Trạng thái</th>
                                <th class="px-4 py-3 text-left font-medium text-slate-500">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($enrollments as $enrollment)
                                @php $record = $attendanceMap->get($enrollment->user_id); @endphp
                                <tr>
                                    <td class="px-4 py-4 font-medium text-slate-900">{{ $enrollment->user?->name }}</td>
                                    <td class="px-4 py-4">
                                        <select name="attendance[{{ $enrollment->user_id }}][status]" class="w-full rounded-2xl border border-slate-300 px-3 py-2 text-sm focus:border-cyan-500 focus:outline-none">
                                            @foreach ($attendanceStatuses as $status => $label)
                                                <option value="{{ $status }}" @selected(($record->status ?? \App\Models\AttendanceRecord::STATUS_PRESENT) === $status)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-4">
                                        <input type="text" name="attendance[{{ $enrollment->user_id }}][note]" value="{{ $record->note ?? '' }}" class="w-full rounded-2xl border border-slate-300 px-3 py-2 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Ghi chú">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Lưu điểm danh</button>
                </div>
            </form>
        @else
            <div class="mt-6 rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                Lớp này chưa có lịch học nên chưa thể điểm danh.
            </div>
        @endif
    </section>

    <section x-show="tab === 'grades'" x-cloak class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div>
            <h3 class="text-2xl font-semibold text-slate-900">Bảng điểm theo lần kiểm tra</h3>
        </div>

        <form method="POST" action="{{ route('teacher.classes.grades.store', $classRoom) }}" class="mt-6 space-y-5">
            @csrf

            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                <strong>Số lần kiểm tra theo môn:</strong> {{ $gradeColumns->count() }} lần.
                <span class="ml-2 text-slate-500">TB được tính theo công thức: tổng (điểm x hệ số) / tổng hệ số.</span>
            </div>

            @unless ($gradeWeightsSupported ?? true)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Cảnh báo: cơ sở dữ liệu hiện chưa sẵn sàng cho cấu hình hệ số, nên hệ số từng cột đang tạm dùng mặc định <strong>1</strong>.
                    Sau khi admin chạy migrate, hệ số sẽ được lưu ở màn quản trị lớp học.
                </div>
            @endunless

            <div class="rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-4 text-sm text-cyan-900">
                <div class="flex flex-wrap items-center gap-4">
                    <span class="font-semibold">Hệ số từng cột:</span>
                    <span class="text-cyan-700">Chỉ admin mới được chỉnh.</span>
                    @foreach ($gradeColumns as $column)
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 text-xs font-medium text-cyan-800 ring-1 ring-cyan-100">
                            <span>KT {{ $column['index'] }}</span>
                            <span class="rounded-full bg-cyan-50 px-2 py-1 font-semibold text-cyan-700">x{{ $column['weight'] ?? 1 }}</span>
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-slate-500">Học viên</th>
                            @foreach ($gradeColumns as $column)
                                <th class="px-4 py-3 text-center font-medium text-slate-500" title="{{ $column['name'] }}">KT {{ $column['index'] }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-center font-medium text-slate-500">TB</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($enrollments as $enrollment)
                            <tr>
                                <td class="px-4 py-4 font-medium text-slate-900">{{ $enrollment->user?->name }}</td>
                                @foreach ($gradeColumns as $column)
                                    @php
                                        $grade = $gradeMap->get($enrollment->user_id . '|' . $column['name']);
                                        $oldKey = 'scores.' . $enrollment->user_id . '.' . $column['index'];
                                    @endphp
                                    <td class="px-4 py-4 text-center">
                                        <input
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            name="scores[{{ $enrollment->user_id }}][{{ $column['index'] }}]"
                                            value="{{ old($oldKey, $grade?->score) }}"
                                            class="w-24 rounded-2xl border border-slate-300 px-3 py-2 text-center text-sm focus:border-cyan-500 focus:outline-none"
                                        >
                                    </td>
                                @endforeach
                                <td class="px-4 py-4 text-center">
                                    @php $avg = $averageScoreMap->get($enrollment->user_id); @endphp
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                        {{ $avg !== null ? rtrim(rtrim(number_format((float) $avg, 2, '.', ''), '0'), '.') : '—' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($errors->has('scores') || $errors->has('scores.*.*'))
                <p class="text-sm text-rose-600">{{ $errors->first('scores') ?: $errors->first('scores.*.*') }}</p>
            @endif

            <div class="flex justify-end">
                <button type="submit" class="rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Lưu bảng điểm</button>
            </div>
        </form>
    </section>

    <section x-show="tab === 'evaluations'" x-cloak class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <article class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h3 class="text-2xl font-semibold text-slate-900">Đánh giá học viên</h3>
                </div>
                <form method="GET" action="{{ route('teacher.classes.show', $classRoom) }}" class="grid gap-3 sm:grid-cols-[260px_auto]">
                    <input type="hidden" name="tab" value="evaluations">
                    <select name="student_id" class="rounded-2xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none">
                        @php
                            $activeStudentIds = $enrollments->pluck('user_id')->map(fn ($id) => (int) $id)->all();
                        @endphp
                        @foreach ($evaluationStudentOptions as $studentOption)
                            @php
                                $isHistoricalOnly = ! in_array((int) $studentOption->id, $activeStudentIds, true);
                            @endphp
                            <option value="{{ $studentOption->id }}" @selected($selectedStudentId === (int) $studentOption->id)>
                                {{ $studentOption->name }}{{ $isHistoricalOnly ? ' (lịch sử)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Tải đánh giá</button>
                </form>
            </div>

            @if ($selectedStudentId)
                <form method="POST" action="{{ route('teacher.classes.evaluations.store', $classRoom) }}" class="mt-6 space-y-5">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $selectedStudentId }}">
                    @php
                        $selectedRating = (int) old('rating', $currentEvaluation?->rating ?? 0);
                    @endphp

                    <div>
                        <label class="text-sm font-medium text-slate-700">Điểm thái độ học tập</label>
                        <div class="mt-3 grid grid-cols-5 gap-2">
                            @for ($rating = 1; $rating <= 5; $rating++)
                                <label class="cursor-pointer">
                                    <input type="radio" class="peer sr-only" name="rating" value="{{ $rating }}" @checked($selectedRating === $rating)>
                                    <span class="block rounded-2xl border border-slate-200 px-3 py-3 text-center text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 peer-checked:border-cyan-500 peer-checked:bg-cyan-50 peer-checked:text-cyan-700">
                                        {{ $rating }}/5
                                    </span>
                                </label>
                            @endfor
                        </div>
                        @error('rating')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Nhận xét</label>
                        <textarea name="comments" rows="8" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Nhận xét">{{ old('comments', $currentEvaluation?->comments) }}</textarea>
                        @error('comments')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Lưu đánh giá</button>
                    </div>
                </form>
            @else
                <div class="mt-6 rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                    Lớp chưa có học viên để đánh giá.
                </div>
            @endif
        </article>

        <article class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-2xl font-semibold text-slate-900">Lịch sử đánh giá</h3>
                </div>
                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">{{ $evaluationHistory->count() }} bản ghi</span>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($evaluationHistory as $evaluation)
                    <div class="rounded-3xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-medium text-slate-900">{{ $evaluation->student?->name ?? 'Học viên' }}</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-400">{{ optional($evaluation->updated_at)->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $evaluation->rating }}/5</span>
                                <a
                                    href="{{ route('teacher.classes.show', ['classRoom' => $classRoom->id, 'tab' => 'evaluations', 'student_id' => $evaluation->student_id]) }}"
                                    class="rounded-full border border-slate-300 px-3 py-1 text-xs font-medium text-slate-600 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700"
                                >
                                    Chỉnh sửa
                                </a>
                            </div>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $evaluation->comments ?: 'Chưa có nhận xét chi tiết.' }}</p>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                        Chưa có đánh giá nào được lưu cho lớp này.
                    </div>
                @endforelse
            </div>
        </article>
    </section>
</div>
@endsection
