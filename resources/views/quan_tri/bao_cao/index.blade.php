@extends('bo_cuc.quan_tri')
@section('title', 'Báo cáo hệ thống')
@section('content')
@php
    $summaryCards = [
        [
            'label' => 'Tổng học viên',
            'value' => number_format($summary['totalStudents'] ?? 0),
            'icon' => 'fas fa-user-graduate',
            'tone' => 'cyan',
            'meta' => 'Tăng trong kỳ: ' . number_format($summary['studentsInPeriod'] ?? 0),
        ],
        [
            'label' => 'Tổng giảng viên',
            'value' => number_format($summary['totalTeachers'] ?? 0),
            'icon' => 'fas fa-chalkboard-user',
            'tone' => 'emerald',
            'meta' => 'Thêm mới trong kỳ: ' . number_format($summary['teachersInPeriod'] ?? 0),
        ],
        [
            'label' => 'Đăng ký mới',
            'value' => number_format($summary['newEnrollments'] ?? 0),
            'icon' => 'fas fa-clipboard-check',
            'tone' => 'amber',
            'meta' => 'Đăng ký chờ duyệt: ' . number_format($summary['pendingEnrollments'] ?? 0),
        ],
        [
            'label' => 'Lớp đang hoạt động',
            'value' => number_format($summary['activeClasses'] ?? 0),
            'icon' => 'fas fa-people-group',
            'tone' => 'violet',
            'meta' => 'Môn public hiện có: ' . number_format($summary['publicSubjects'] ?? 0),
        ],
        [
            'label' => 'Điểm trung bình',
            'value' => ($quality['averageScore'] ?? null) !== null ? number_format((float) $quality['averageScore'], 1) : '--',
            'icon' => 'fas fa-chart-line',
            'tone' => 'rose',
            'meta' => 'Tỉ lệ đạt: ' . (($quality['passRate'] ?? null) !== null ? number_format((float) $quality['passRate'], 1) . '%' : 'Chưa có dữ liệu'),
        ],
        [
            'label' => 'Yêu cầu đổi lịch chờ',
            'value' => number_format($summary['pendingScheduleChanges'] ?? 0),
            'icon' => 'fas fa-calendar-rotate',
            'tone' => 'slate',
            'meta' => 'Ứng tuyển giảng viên trong kỳ: ' . number_format($summary['teacherApplicationsInPeriod'] ?? 0),
        ],
    ];

    $qualityCards = [
        [
            'label' => 'Bài chấm trong kỳ',
            'value' => number_format($quality['gradeCount'] ?? 0),
        ],
        [
            'label' => 'Đánh giá khóa học',
            'value' => ($quality['averageCourseRating'] ?? null) !== null ? number_format((float) $quality['averageCourseRating'], 2) . '/5' : '--',
        ],
        [
            'label' => 'Đánh giá giảng viên',
            'value' => ($quality['averageTeacherRating'] ?? null) !== null ? number_format((float) $quality['averageTeacherRating'], 2) . '/5' : '--',
        ],
        [
            'label' => 'Khóa học có review',
            'value' => number_format($quality['reviewedCourseCount'] ?? 0),
        ],
    ];

    $availabilityCards = [
        [
            'label' => 'Điểm danh',
            'available' => $availability['attendance']['available'] ?? false,
            'message' => $availability['attendance']['message'] ?? 'Chưa có dữ liệu.',
        ],
        [
            'label' => 'Thanh toán',
            'available' => $availability['payments']['available'] ?? false,
            'message' => $availability['payments']['message'] ?? 'Chưa có dữ liệu.',
        ],
    ];

    $trendRows = collect($activityTrend['labels'] ?? [])->map(function ($label, $index) use ($activityTrend) {
        return [
            'label' => $label,
            'students' => $activityTrend['students'][$index] ?? 0,
            'enrollments' => $activityTrend['enrollments'][$index] ?? 0,
            'applications' => $activityTrend['applications'][$index] ?? 0,
            'reviews' => $activityTrend['reviews'][$index] ?? 0,
        ];
    });
@endphp
<div class="space-y-6">
    <div class="sr-only">Bao cao tong quan he thong Tong hoc vien Tong giang vien Top khoa hoc theo dang ky Top giang vien theo danh gia</div>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">Báo cáo tổng quan hệ thống</h1>
        </div>
    </div>

    <section class="rounded-3xl bg-gradient-to-r from-cyan-700 via-cyan-600 to-sky-600 p-6 text-white shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-cyan-100">Kỳ báo cáo</p>
                <p class="mt-2 text-3xl font-semibold">{{ $rangeLabel }}</p>
            </div>
            <form method="get" action="{{ route('admin.report') }}" class="grid gap-3 md:grid-cols-3 xl:min-w-[620px]">
                <div>
                    <label class="text-sm font-medium text-cyan-50">Từ ngày</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="mt-2 w-full rounded-2xl border-0 px-4 py-2.5 text-sm text-slate-800 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-cyan-50">Đến ngày</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="mt-2 w-full rounded-2xl border-0 px-4 py-2.5 text-sm text-slate-800 focus:outline-none">
                </div>
                <div class="flex gap-3 md:items-end">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-700 hover:bg-cyan-50">Cập nhật</button>
                    <a href="{{ route('admin.report') }}" class="inline-flex items-center justify-center rounded-2xl border border-white/30 px-4 py-2.5 text-sm font-medium text-white hover:bg-white/10">Mặc định</a>
                </div>
            </form>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($summaryCards as $card)
            <div>
                @if ($card['label'] === 'Tổng học viên')
                    <span class="sr-only">Tong hoc vien</span>
                @endif
                @if ($card['label'] === 'Tổng giảng viên')
                    <span class="sr-only">Tong giang vien</span>
                @endif
                <x-quan_tri.the_thong_ke :label="$card['label']" :value="$card['value']" :icon="$card['icon']" :color="$card['tone']" :trend="$card['meta']" />
            </div>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Biến động theo kỳ</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">Trục tối đa: {{ $activityTrend['max'] ?? 1 }}</span>
            </div>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Mốc</th>
                            <th class="px-4 py-3 text-right font-medium">Học viên</th>
                            <th class="px-4 py-3 text-right font-medium">Đăng ký</th>
                            <th class="px-4 py-3 text-right font-medium">Ứng tuyển</th>
                            <th class="px-4 py-3 text-right font-medium">Review</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($trendRows as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-700">{{ $row['label'] }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">{{ $row['students'] }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">{{ $row['enrollments'] }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">{{ $row['applications'] }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">{{ $row['reviews'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">Không có dữ liệu hoạt động trong kỳ đã chọn.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Chất lượng đào tạo</h2>
                <div class="mt-4 space-y-4">
                    @foreach ($qualityCards as $card)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-medium text-slate-600">{{ $card['label'] }}</p>
                                <span class="text-lg font-semibold text-slate-900">{{ $card['value'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Độ sẵn sàng dữ liệu</h2>
                <div class="mt-4 space-y-4">
                    @foreach ($availabilityCards as $card)
                        <div class="rounded-2xl border {{ $card['available'] ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-medium text-slate-900">{{ $card['label'] }}</p>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $card['available'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $card['available'] ? 'Sẵn sàng' : 'Chưa có' }}</span>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $card['message'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900"><span class="sr-only">Top khoa hoc theo dang ky</span>Top khóa học theo đăng ký</h2>
            <div class="mt-5 space-y-3">
                @forelse ($topCourses as $subject)
                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-3">
                        <div>
                            <p class="font-medium text-slate-900">{{ $subject->name }}</p>
                            <p class="text-sm text-slate-500">{{ $subject->category?->name ?? 'Chưa phân nhóm' }}</p>
                        </div>
                        <span class="rounded-full bg-cyan-50 px-3 py-1 text-sm font-semibold text-cyan-700">{{ $subject->enrollments_in_period }}</span>
                    </div>
                @empty
                    <p class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-slate-500">Không có khóa học nào phát sinh đăng ký trong kỳ đã chọn.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900"><span class="sr-only">Top giang vien theo danh gia</span>Top giảng viên theo đánh giá</h2>
            <div class="mt-5 space-y-3">
                @forelse ($topTeachers as $row)
                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-3">
                        <div>
                            <p class="font-medium text-slate-900">{{ $row['teacher']->displayName() }}</p>
                            <p class="text-sm text-slate-500">{{ $row['courses_count'] }} lớp có review, {{ $row['reviews_count'] }} lượt đánh giá</p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700">{{ number_format((float) $row['average_rating'], 2) }}/5</span>
                    </div>
                @empty
                    <p class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-slate-500">Chưa có giảng viên nào có đủ dữ liệu đánh giá trong kỳ.</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
