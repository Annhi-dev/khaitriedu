@extends('layouts.admin')
@section('title', 'Quản lý lớp học')
@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Quản lý lớp học" subtitle="Lớp học nội bộ để xếp học viên">
        <a href="{{ route('admin.courses.create-page') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
            <i class="fas fa-plus mr-1"></i> Tạo lớp mới
        </a>
    </x-admin.page-header>

    @if(session('status')) <x-admin.alert :session="session()" /> @endif
    @if(session('error')) <x-admin.alert :session="session()" /> @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($courses as $course)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">{{ $course->title }}</h3>
                    <p class="text-sm text-slate-500">{{ $course->subject?->name ?? 'Chưa gắn khóa học' }}</p>
                </div>
                <x-admin.badge :type="'info'" :text="$course->statusLabel()" />
            </div>
            <div class="mt-4 space-y-2 text-sm text-slate-600">
                <p><i class="fas fa-calendar-alt w-5"></i> Lịch: {{ $course->schedule ?: 'Chưa chốt' }}</p>
                <p><i class="fas fa-chalkboard-user w-5"></i> Giảng viên: {{ $course->teacher?->displayName() ?? 'Chưa phân công' }}</p>
                <p><i class="fas fa-users w-5"></i> Học viên: {{ $course->enrollments_count ?? 0 }}</p>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <a href="{{ route('admin.course.show', $course->id) }}" class="border border-cyan-200 text-cyan-700 hover:bg-cyan-50 px-4 py-2 rounded-xl text-sm font-medium transition">Chi tiết</a>
                <a href="{{ route('admin.courses.modules.index', $course) }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Quản lý module</a>
            </div>
        </div>
        @empty
        <div class="col-span-2 text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300">
            <p class="text-slate-500">Chưa có lớp học nào. Hãy tạo lớp mới để xếp học viên.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection