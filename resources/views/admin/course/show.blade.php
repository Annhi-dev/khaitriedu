@extends('layouts.app')
@section('title', 'Chỉnh sửa lớp học')
@section('content')
<div class="max-w-5xl mx-auto mt-6">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $course->title }}</h1>
            <p class="text-gray-600">Cập nhật lớp học để admin có thể xếp học viên đúng lớp sau khi duyệt yêu cầu.</p>
        </div>
        <a href="{{ route('admin.courses') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary transition">Quay lại</a>
    </div>

    @if(session('status'))<div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('status') }}</div>@endif
    @if(session('error'))<div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>@endif

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-gray-500">Khóa học học viên đăng ký</div>
            <div class="mt-2 text-xl font-bold text-gray-900">{{ $course->subject?->name ?? 'Chưa gắn khóa học công khai' }}</div>
            <p class="mt-2 text-sm text-gray-500">{{ $course->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-gray-500">Học viên đã xếp</div>
            <div class="mt-2 text-xl font-bold text-primary">{{ $course->enrollments_count ?? 0 }}</div>
            <p class="mt-2 text-sm text-gray-500">Con số này tăng khi admin xác nhận và xếp lớp cho học viên.</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-gray-500">Số module</div>
            <div class="mt-2 text-xl font-bold text-gray-900">{{ $course->modules->count() }}</div>
            <p class="mt-2 text-sm text-gray-500">Bạn có thể thêm module từ màn hình quản lý nội dung lớp học hiện có.</p>
        </div>
    </div>

    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="post" action="{{ route('admin.courses.update', $course->id) }}" class="grid gap-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tên lớp học</label>
                    <input name="title" value="{{ $course->title }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" required />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Thuộc khóa học</label>
                    <select name="subject_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" required>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($course->subject_id == $subject->id)>
                                {{ $subject->name }}{{ $subject->category ? ' - ' . $subject->category->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Giảng viên</label>
                    <select name="teacher_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5">
                        <option value="">Chưa phân công</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected($course->teacher_id == $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Lịch học</label>
                    <input name="schedule" value="{{ $course->schedule }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5" placeholder="Ví dụ: T2-T4-T6, 18:00-20:00" />
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Mô tả lớp học</label>
                <textarea name="description" rows="4" class="w-full rounded-xl border border-gray-300 px-3 py-2.5">{{ $course->description }}</textarea>
            </div>
            <div>
                <button class="rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark transition">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
@endsection
