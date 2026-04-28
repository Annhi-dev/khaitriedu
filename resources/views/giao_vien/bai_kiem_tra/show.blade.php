@extends('bo_cuc.giao_vien')

@section('title', 'Chi tiết bài kiểm tra')
@section('eyebrow', 'Teacher Tests')

@section('content')
@php
    $questions = $quiz->questions ?? collect();
    $answersCount = $quiz->answers?->count() ?? 0;
    $studentCount = $quiz->answers?->pluck('user_id')->unique()->count() ?? 0;
    $quizReport = $quizReport ?? null;
@endphp

<div class="space-y-6">
    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="border-b border-slate-100 p-6 lg:border-b-0 lg:border-r lg:p-7">
                <a href="{{ route('teacher.tests.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại danh sách bài kiểm tra
                </a>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $quiz->title }}</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">{{ $quiz->description ?: 'Chưa có mô tả.' }}</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white">{{ $quiz->statusLabel() }}</span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700">{{ $questions->count() }} câu hỏi</span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700">{{ $studentCount }} học viên đã làm</span>
                </div>
            </div>
            <div class="grid gap-3 bg-slate-50 p-6 sm:grid-cols-2 lg:grid-cols-1">
                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Đối tượng</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $quiz->targetLabel() }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $quiz->course?->title ?? $quiz->subject?->name ?? 'Chưa xác định' }}</p>
                </div>
                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Thời gian / điểm</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $quiz->durationLabel() }}</p>
                    <p class="mt-1 text-sm text-slate-500">Điểm tối đa: {{ $quiz->totalScoreLabel() }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Khóa học</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $quiz->course?->title ?? 'Chưa xác định' }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Môn học</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $quiz->subject?->name ?? 'Chưa xác định' }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Lớp học</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $quiz->classRoom?->displayName() ?? 'Chưa gán lớp' }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Cập nhật</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ optional($quiz->updated_at)->format('d/m/Y H:i') ?? 'Chưa rõ' }}</p>
        </div>
    </section>

    @if($quizReport)
        <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-2xl font-semibold text-slate-900">Kết quả làm bài</h3>
                    <p class="mt-1 text-sm text-slate-500">Tổng hợp nhanh số lượt làm và chất lượng bài làm của học viên.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Lượt làm</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quizReport['totalAttempts'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Học viên</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quizReport['studentCount'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Điểm TB</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quizReport['averageScore'] !== null ? number_format((float) $quizReport['averageScore'], 2) . '%' : '--' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Tỉ lệ đạt</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quizReport['passRate'] !== null ? $quizReport['passRate'] . '%' : '--' }}</p>
                </div>
            </div>

            @if(!empty($quizReport['recentAttempts']))
                <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Học viên</th>
                                <th class="px-4 py-3">Lần làm</th>
                                <th class="px-4 py-3">Điểm gần nhất</th>
                                <th class="px-4 py-3">Kết quả</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($quizReport['recentAttempts'] as $attemptRow)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $attemptRow['student']?->displayName() ?? 'Học viên' }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $attemptRow['attemptCount'] }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $attemptRow['latestAttempt']['score'] !== null ? number_format((float) $attemptRow['latestAttempt']['score'], 2) . '%' : '--' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ ($attemptRow['passed'] ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ ($attemptRow['passed'] ?? false) ? 'Đạt' : 'Chưa đạt' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif

    <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-slate-900">Câu hỏi</h3>
                <p class="mt-1 text-sm text-slate-500">Danh sách câu hỏi và lựa chọn của bài kiểm tra.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('teacher.tests.edit', $quiz) }}" class="rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Chỉnh sửa</a>
                <form method="POST" action="{{ route('teacher.tests.destroy', $quiz) }}" onsubmit="return confirm('Xóa bài kiểm tra này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-600 hover:bg-rose-100">Xóa</button>
                </form>
            </div>
        </div>

        <div class="mt-6 space-y-4">
            @forelse($questions as $question)
                <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">Câu hỏi {{ $loop->iteration }}</p>
                            <h4 class="mt-2 text-lg font-semibold text-slate-900">{{ $question->question }}</h4>
                            @if($question->description)
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $question->description }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                            <span class="rounded-full bg-white px-3 py-1">Điểm: {{ $question->points }}</span>
                            <span class="rounded-full bg-white px-3 py-1">Đáp án: {{ $question->options->firstWhere('is_correct', true)?->option_text ?? 'Chưa rõ' }}</span>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        @foreach($question->options as $option)
                            <div class="rounded-2xl border border-white bg-white p-4 shadow-sm {{ $option->is_correct ? 'ring-2 ring-emerald-200' : '' }}">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] {{ $option->is_correct ? 'text-emerald-700' : 'text-slate-400' }}">
                                    {{ chr(64 + $option->order) }}{{ $option->is_correct ? ' - Đúng' : '' }}
                                </p>
                                <p class="mt-2 text-sm leading-6 text-slate-700">{{ $option->option_text }}</p>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                    Bài kiểm tra này chưa có câu hỏi nào.
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
