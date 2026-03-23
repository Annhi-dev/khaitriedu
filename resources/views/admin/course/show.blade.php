@extends('layouts.app')
@section('title', 'Chỉnh sửa khóa học')
@section('content')
<div class="max-w-4xl mx-auto mt-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold">Chỉnh sửa khóa học: {{ $course->title }}</h1>
            <p class="text-gray-600">Cập nhật thông tin khóa học.</p>
        </div>
        <a href="{{ route('admin.courses') }}" class="btn border border-gray-300 px-3 py-2 rounded">Quay lại danh sách</a>
    </div>
    @if(session('status'))<div class="bg-green-100 text-green-800 p-3 rounded mb-3">{{ session('status') }}</div>@endif
    @if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mb-3">{{ session('error') }}</div>@endif
    <div class="card bg-white rounded-xl shadow p-4">
        <form method="post" action="{{ route('admin.courses.update', $course->id) }}" class="grid gap-3">
            @csrf
            <div class="grid sm:grid-cols-2 gap-2">
                <div>
                    <label class="block text-sm font-medium">Tiêu đề</label>
                    <input name="title" value="{{ $course->title }}" class="w-full border rounded p-2" required />
                </div>
                <div>
                    <label class="block text-sm font-medium">Môn học</label>
                    <select name="subject_id" class="w-full border rounded p-2" required>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @if($course->subject_id == $subject->id) selected @endif>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-2">
                <div>
                    <label class="block text-sm font-medium">Giảng viên</label>
                    <select name="teacher_id" class="w-full border rounded p-2">
                        <option value="">Không gán</option>
                        @foreach($teachers as $t)
                        <option value="{{ $t->id }}" @if($course->teacher_id == $t->id) selected @endif>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Lịch học</label>
                    <input name="schedule" value="{{ $course->schedule }}" class="w-full border rounded p-2" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">Mô tả</label>
                <textarea name="description" class="w-full border rounded p-2" rows="3">{{ $course->description }}</textarea>
            </div>
            <button class="btn bg-blue-600 text-white px-3 py-2 rounded">Lưu thay đổi</button>
        </form>
    </div>
</div>
@endsection