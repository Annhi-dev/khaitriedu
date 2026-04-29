@extends('bo_cuc.quan_tri')
@section('title', 'Quản lý lớp học')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Quản lý lớp học</h1>
            <p class="mt-1 text-sm text-slate-500">Tạo và quản lý các lớp học thực tế với phòng học và giảng viên.</p>
        </div>
        <a href="{{ route('admin.classes.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700 transition">
            <i class="fas fa-plus"></i> Tạo lớp mới
        </a>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <ul class="space-y-1 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    
    <form method="GET" class="flex flex-wrap gap-3">
        <select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tất cả trạng thái</option>
            <option value="open" @selected(request('status') === 'open')>Đang mở</option>
            <option value="full" @selected(request('status') === 'full')>Đủ chỗ</option>
            <option value="closed" @selected(request('status') === 'closed')>Đã đóng</option>
            <option value="completed" @selected(request('status') === 'completed')>Hoàn thành</option>
        </select>
        <select name="subject_id" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tất cả môn học</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-xl bg-slate-700 px-4 py-2 text-sm text-white hover:bg-slate-800">Lọc</button>
        <a href="{{ route('admin.classes.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Xóa lọc</a>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Môn học / Khóa học</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Giảng viên</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Phòng</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Lịch học</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Học viên</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Trạng thái</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($classes as $class)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-800">{{ $class->course->title ?? 'Chưa gắn khóa học' }}</div>
                        <div class="text-xs text-slate-500">{{ $class->subject->name ?? 'Chưa gắn môn học' }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $class->teacher?->displayName() ?? 'Chưa phân công' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $class->room->name ?? 'Chưa chọn' }}</td>
                    <td class="px-4 py-3">
                        @foreach($class->schedules as $s)
                            <span class="block text-xs text-slate-600">{{ \App\Models\LichHoc::$dayOptions[$s->day_of_week] ?? $s->day_of_week }}: {{ $s->start_time }}–{{ $s->end_time }}</span>
                        @endforeach
                        @if($class->schedules->isEmpty()) <span class="text-xs text-slate-400">Chưa có lịch</span> @endif
                    </td>
                    <td class="px-4 py-3 text-slate-700">
                        {{ $class->enrollments_count }}
                        @if($class->room) / {{ $class->room->capacity }} @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $badge = match($class->status) {
                                'open'      => 'bg-green-100 text-green-700',
                                'full'      => 'bg-amber-100 text-amber-700',
                                'closed'    => 'bg-slate-100 text-slate-600',
                                'completed' => 'bg-blue-100 text-blue-700',
                                default     => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">{{ $class->statusLabel() }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.classes.show', $class) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">Chi tiết</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-slate-400">Chưa có lớp học nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $classes->links() }}</div>
</div>
@endsection
