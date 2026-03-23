@extends('layouts.app')
@section('title', $lesson->title)
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('courses.show', $course->id) }}" class="text-primary hover:underline">← Quay lại khóa học</a>
    <div class="bg-white p-6 rounded-xl shadow-sm">
        <h1 class="text-2xl font-bold mb-2">{{ $lesson->title }}</h1>
        <div class="text-gray-600 mb-4">Module: {{ $module->title }}</div>
        <p class="text-gray-700 mb-4">{{ $lesson->description }}</p>
        @if($lesson->video_url)
            <div class="mb-4">
                <iframe src="{{ $lesson->video_url }}" class="w-full h-80 rounded-lg" allowfullscreen></iframe>
            </div>
        @endif
        <div class="prose max-w-none text-gray-700">{!! nl2br(e($lesson->content)) !!}</div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm">
        <h2 class="text-xl font-semibold mb-3">Quiz liên quan</h2>
        @if($lesson->quiz)
            <a href="{{ route('courses.quiz.show', [$course->id, $lesson->quiz->id]) }}" class="inline-block bg-primary hover:bg-primary-dark text-white px-5 py-2 rounded-lg">Làm quiz: {{ $lesson->quiz->title }}</a>
        @else
            <p class="text-gray-600">Hiện chưa có quiz trong bài học này.</p>
        @endif
    </div>
</div>
@endsection