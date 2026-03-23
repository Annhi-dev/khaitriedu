@extends('layouts.app')
@section('title', 'Quiz: ' . $quiz->title)
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('courses.show', $course->id) }}" class="text-primary hover:underline">← Quay lại khóa học</a>
    <div class="bg-white p-6 rounded-xl shadow-sm">
        <h1 class="text-2xl font-bold mb-1">{{ $quiz->title }}</h1>
        <p class="text-gray-600 mb-4">{{ $quiz->description }}</p>
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
            <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark">Nộp bài</button>
        </form>
    </div>
</div>
@endsection