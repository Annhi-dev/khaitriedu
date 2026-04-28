@php
    $quizLayout = 'bo_cuc.ung_dung';

    if ($user?->isStudent()) {
        $quizLayout = 'bo_cuc.hoc_vien';
    } elseif ($user?->isTeacher()) {
        $quizLayout = 'bo_cuc.giao_vien';
    } elseif ($user?->isAdmin()) {
        $quizLayout = 'bo_cuc.quan_tri';
    }

    $quizProgress = $quizProgress ?? null;
    $quizReport = $quizReport ?? null;
    $canSubmit = $user?->isStudent() ? ($quizProgress['canAttempt'] ?? true) : false;
    $latestAttempt = $quizProgress['latestAttempt'] ?? null;
    $attempts = collect($quizProgress['attempts'] ?? []);
    $latestAttemptScore = $latestAttempt['score'] ?? null;
    $latestAttemptPassed = $latestAttempt['passed'] ?? false;
@endphp
@extends($quizLayout)
@section('title', 'Quiz: ' . $quiz->title)
@section('eyebrow', 'Bài kiểm tra')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('courses.show', $course->id) }}" class="text-primary hover:underline">← Quay lại lớp học</a>
    <div class="bg-white p-6 rounded-xl shadow-sm">
        <h1 class="text-2xl font-bold mb-1">{{ $quiz->title }}</h1>
        <p class="text-gray-600 mb-4">{{ $quiz->description }}</p>

        @if(session('status'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if($user?->isStudent() && $quizProgress)
            <div class="mb-6 grid gap-3 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Lần làm</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quizProgress['attemptCount'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Điểm gần nhất</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $latestAttemptScore !== null ? number_format((float) $latestAttemptScore, 2) . '%' : 'Chưa làm' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Còn lại</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quizProgress['remainingAttempts'] === null ? '∞' : $quizProgress['remainingAttempts'] }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Kết quả</p>
                    <p class="mt-2 text-2xl font-semibold {{ $latestAttemptPassed ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $latestAttempt ? ($latestAttemptPassed ? 'Đạt' : 'Chưa đạt') : 'Chưa có' }}
                    </p>
                </div>
            </div>
        @endif

        @if($user?->isStudent() && $attempts->isNotEmpty())
            <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Lần</th>
                            <th class="px-4 py-3">Điểm</th>
                            <th class="px-4 py-3">Đúng</th>
                            <th class="px-4 py-3">Nộp lúc</th>
                            <th class="px-4 py-3">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($attempts as $attempt)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $attempt['attempt'] }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ number_format((float) $attempt['score'], 2) }}%</td>
                                <td class="px-4 py-3 text-slate-700">{{ $attempt['correctCount'] }}/{{ $attempt['totalQuestions'] }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $attempt['submitted_at'] ?? 'Chưa rõ' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $attempt['passed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $attempt['passed'] ? 'Đạt' : 'Chưa đạt' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($quizReport && ($user?->isTeacher() || $user?->isAdmin()))
            <div class="mb-6 grid gap-3 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Lượt làm</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quizReport['totalAttempts'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Học viên</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quizReport['studentCount'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Điểm TB</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quizReport['averageScore'] !== null ? number_format((float) $quizReport['averageScore'], 2) . '%' : '--' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Tỉ lệ đạt</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $quizReport['passRate'] !== null ? $quizReport['passRate'] . '%' : '--' }}</p>
                </div>
            </div>
        @endif

        <form method="post" action="{{ route('courses.quiz.submit', [$course->id, $quiz->id]) }}" class="space-y-6">
            @csrf
            @foreach($quiz->questions as $question)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="font-semibold">{{ $loop->iteration }}. {{ $question->question }}</div>
                    <div class="text-sm text-gray-500 mb-3">Loại: {{ ucfirst(str_replace('_', ' ', $question->type)) }}</div>

                    @if($question->type === 'multiple_choice' || $question->type === 'true_false')
                        @foreach($question->options as $option)
                            <label class="flex items-center gap-2 text-gray-700">
                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" required class="text-primary focus:ring-primary" />
                                {{ $option->option_text }}
                            </label>
                        @endforeach
                    @else
                        <textarea name="answers[{{ $question->id }}]" rows="3" class="w-full border rounded p-2" placeholder="Trả lời ngắn"></textarea>
                    @endif
                </div>
            @endforeach
            @if($user?->isStudent() && ! $canSubmit)
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Bạn đã đạt số lần làm tối đa cho bài kiểm tra này.
                </div>
            @elseif(! $user?->isStudent())
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    Đây là chế độ xem trước, chỉ học viên mới có thể nộp bài.
                </div>
            @endif
            <button type="submit" @disabled(! $canSubmit) class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark disabled:cursor-not-allowed disabled:opacity-60">Nộp bài</button>
        </form>
    </div>
</div>
@endsection
