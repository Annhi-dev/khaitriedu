@extends('layouts.app')
@section('title', 'Chi tiết môn học')
@section('content')
<div class="max-w-4xl mx-auto mt-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $subject->name }}</h1>
            <p class="text-gray-600">Chi tiết và khóa học liên kết.</p>
        </div>
        <a href="{{ route('admin.subjects') }}" class="btn border border-gray-300 px-3 py-2 rounded">Quay lại</a>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm">
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                @if($subject->image)
                    <img src="{{ asset('storage/' . $subject->image) }}" class="w-full h-48 object-cover rounded" />
                @else
                    <div class="w-full h-48 bg-gray-200 rounded flex items-center justify-center text-gray-500">Chưa có ảnh</div>
                @endif
            </div>
            <div class="md:col-span-2">
                <p class="text-sm font-medium text-gray-500">Giá: <span class="text-primary-dark font-semibold">{{ number_format($subject->price, 0, ',', '.') }} VND</span></p>
                <p class="mt-2 text-gray-700">{{ $subject->description ?? 'Không có mô tả' }}</p>
                <p class="mt-3 text-xs text-gray-500">Tạo: {{ $subject->created_at->format('d/m/Y H:i') }} &middot; Cập nhật: {{ $subject->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        <div class="mt-4">
            <h3 class="font-semibold">Cập nhật môn học</h3>
            <form method="post" action="{{ route('admin.subjects.update', $subject->id) }}" enctype="multipart/form-data" class="grid gap-2 mt-2">
                @csrf
                <div class="grid sm:grid-cols-2 gap-2">
                    <input name="name" value="{{ $subject->name }}" class="border rounded px-2 py-2" required />
                    <input name="price" type="number" step="0.01" value="{{ $subject->price }}" class="border rounded px-2 py-2" required />
                </div>
                <textarea name="description" class="border rounded px-2 py-2" rows="3">{{ $subject->description }}</textarea>
                <div class="grid sm:grid-cols-2 gap-2">
                    <input type="file" name="image" accept="image/*" class="border rounded px-2 py-2" />
                    <button class="btn bg-blue-600 text-white rounded px-3 py-2">Lưu thay đổi</button>
                </div>
            </form>
            <form method="post" action="{{ route('admin.subjects.delete', $subject->id) }}" class="mt-2" onsubmit="return confirm('Xác nhận xóa môn học này?');">
                @csrf
                <button class="btn bg-red-500 text-white rounded px-3 py-2">Xóa môn học</button>
            </form>
        </div>
        <div class="mt-4">
            <h3 class="font-semibold">Khóa học liên quan</h3>
            @if($subject->courses->isEmpty())
                <p class="text-gray-500 text-sm">Chưa có khóa học.</p>
            @else
                <ul class="list-disc pl-5 mt-2">
                @foreach($subject->courses as $course)
                    <li class="py-1"><span class="font-medium">{{ $course->title }}</span> - {{ Str::limit($course->description, 80) }}</li>
                @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection