@extends('layouts.admin')
@section('title', 'Chi tiết lớp học')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $class->course->title ?? ($class->subject->name ?? 'Lớp học') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $class->subject->name ?? 'Chưa có môn học' }} — ID lớp: {{ $class->id }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.classes.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Quay lại</a>
            @if(! $class->enrollments->count())
            <form method="POST" action="{{ route('admin.classes.delete', $class) }}" onsubmit="return confirm('Xóa lớp này?')">
                @csrf
                <button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition">Xóa lớp</button>
            </form>
            @endif
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-5 md:grid-cols-2">
        
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-slate-700 uppercase tracking-wide">Thông tin lớp</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Trạng thái</dt>
                    <dd>
                        @php $badge = match($class->status) { 'open' => 'bg-green-100 text-green-700', 'full' => 'bg-amber-100 text-amber-700', 'completed' => 'bg-blue-100 text-blue-700', default => 'bg-slate-100 text-slate-600' }; @endphp
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">{{ $class->statusLabel() }}</span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Khóa học</dt>
                    <dd class="font-medium">{{ $class->course->title ?? 'Chưa gắn khóa học' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Giảng viên</dt>
                    <dd class="font-medium">{{ $class->teacher?->displayName() ?? 'Chưa phân công' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Phòng học</dt>
                    <dd class="font-medium">{{ $class->room ? $class->room->name . ' (' . $class->room->code . ')' : 'Chưa chọn' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Sức chứa</dt>
                    <dd class="font-medium">{{ $class->enrollments->count() }} / {{ $class->room?->capacity ?? '∞' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Ngày bắt đầu</dt>
                    <dd class="font-medium">{{ $class->start_date?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Thời lượng</dt>
                    <dd class="font-medium">{{ $class->duration ? $class->duration . ' tháng' : '—' }}</dd>
                </div>
                @if($class->note)
                <div>
                    <dt class="text-slate-500 mb-1">Ghi chú</dt>
                    <dd>{{ $class->note }}</dd>
                </div>
                @endif
            </dl>
        </div>

        
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-slate-700 uppercase tracking-wide">Lịch học hàng tuần</h2>
            @forelse($class->schedules as $s)
                <div class="mb-2 flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-2.5 text-sm">
                    <i class="fas fa-clock text-slate-400"></i>
                    <span class="font-medium">{{ \App\Models\ClassSchedule::$dayOptions[$s->day_of_week] ?? $s->day_of_week }}</span>
                    <span class="text-slate-500">{{ $s->start_time }} – {{ $s->end_time }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">Chưa có lịch học.</p>
            @endforelse
        </div>
    </div>

    
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Học viên đã đăng ký ({{ $class->enrollments->count() }})</h2>
        </div>
        @if($class->enrollments->isEmpty())
            <p class="px-5 py-6 text-sm text-slate-400">Chưa có học viên nào.</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($class->enrollments as $enrollment)
                <li class="flex items-center gap-3 px-5 py-3 text-sm">
                    <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-semibold shrink-0">
                        {{ substr($enrollment->user->name ?? '?', 0, 1) }}
                    </div>
                    <div>
                        <div class="font-medium text-slate-800">{{ $enrollment->user->name ?? '—' }}</div>
                        <div class="text-xs text-slate-500">{{ $enrollment->user->email ?? '' }}</div>
                    </div>
                    <div class="ml-auto">
                        @php $st = match($enrollment->status) { 'pending' => 'bg-amber-100 text-amber-700', 'active' => 'bg-green-100 text-green-700', 'completed' => 'bg-blue-100 text-blue-700', default => 'bg-slate-100 text-slate-600' }; @endphp
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $st }}">{{ $enrollment->statusLabel() }}</span>
                    </div>
                </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
