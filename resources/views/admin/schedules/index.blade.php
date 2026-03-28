@extends('layouts.admin')
@section('title', 'Lịch học toàn hệ thống')
@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Lịch học toàn hệ thống" subtitle="Theo dõi các lớp đã xếp lịch">
        <a href="{{ route('admin.schedules.queue') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Hàng chờ xếp lịch</a>
    </x-admin.page-header>

    <form method="get" action="{{ route('admin.schedules.index') }}" class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Giảng viên</label><select name="teacher_id" class="w-full rounded-xl border px-3 py-2"><option value="">Tất cả</option>@foreach($teachers as $t)<option value="{{ $t->id }}" @selected(request('teacher_id')==$t->id)>{{ $t->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700">Học viên</label><select name="student_id" class="w-full rounded-xl border px-3 py-2"><option value="">Tất cả</option>@foreach($students as $s)<option value="{{ $s->id }}" @selected(request('student_id')==$s->id)>{{ $s->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700">Lớp học</label><select name="course_id" class="w-full rounded-xl border px-3 py-2"><option value="">Tất cả</option>@foreach($courses as $c)<option value="{{ $c->id }}" @selected(request('course_id')==$c->id)>{{ $c->title }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700">Ngày học</label><input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-xl border px-3 py-2"></div>
            <div class="flex items-end gap-2"><button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-xl">Lọc</button><a href="{{ route('admin.schedules.index') }}" class="border px-4 py-2 rounded-xl">Xóa</a></div>
        </div>
    </form>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($schedules as $course)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex justify-between">
                <div>
                    <p class="text-xs text-slate-500">{{ $course->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                    <h3 class="font-semibold text-lg">{{ $course->title }}</h3>
                </div>
                <x-admin.badge :type="'info'" :text="$course->statusLabel()" />
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                <p><span class="text-slate-500">Giảng viên:</span> {{ $course->teacher?->name ?? 'Chưa' }}</p>
                <p><span class="text-slate-500">Lịch:</span> {{ $course->formattedSchedule() }}</p>
                <p><span class="text-slate-500">Sĩ số:</span> {{ $course->scheduled_students_count }}/{{ $course->capacity ?? 20 }}</p>
                <p><span class="text-slate-500">Ngày học:</span> {{ $course->dayLabel() }}</p>
            </div>
            <div class="mt-4 p-3 bg-slate-50 rounded-xl text-sm">
                <p class="font-medium">Học viên:</p>
                <div class="flex flex-wrap gap-1 mt-1">
                    @forelse($course->enrollments->whereIn('status', \App\Models\Enrollment::courseAccessStatuses()) as $enrollment)
                        <span class="px-2 py-1 bg-white rounded-full text-xs border">{{ $enrollment->user?->name }}</span>
                    @empty
                        <span class="text-xs text-slate-500">Chưa có</span>
                    @endforelse
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-2 text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300">Chưa có lớp học nào được xếp lịch chính thức</div>
        @endforelse
    </div>
    @if($schedules->hasPages()) <div>{{ $schedules->links() }}</div> @endif
</div>
@endsection