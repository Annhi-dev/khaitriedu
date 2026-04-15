@php
    $lessonLayout = 'layouts.app';

    if ($user?->isStudent()) {
        $lessonLayout = 'layouts.student';
    } elseif ($user?->isTeacher()) {
        $lessonLayout = 'layouts.teacher';
    } elseif ($user?->isAdmin()) {
        $lessonLayout = 'layouts.admin';
    }
@endphp
@extends($lessonLayout)
@section('title', $lesson->title)
@section('eyebrow', 'Buổi học trong lớp')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('courses.show', $course->id) }}" class="text-primary hover:underline">← Quay lại lớp học</a>
    <div class="bg-white p-6 rounded-xl shadow-sm">
        <h1 class="text-2xl font-bold mb-2">{{ $lesson->title }}</h1>
        <div class="text-gray-600 mb-4">Module: {{ $module->title }}</div>
        @if($user && $user->isStudent())
            <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
                <i class="fas fa-circle-check"></i>
                {{ $lessonProgress?->is_completed ? 'Buổi học này đã được ghi nhận hoàn thành.' : 'Buổi học này chưa hoàn thành.' }}
            </div>
        @endif
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
            <p class="text-gray-600">Hiện chưa có quiz trong buổi học này.</p>
        @endif
    </div>
</div>
@endsection
