@extends('layouts.app')
@section('title', 'Lớp học của tôi')
@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-dark">Lớp học của tôi</h1>
            <p class="text-gray-600 text-sm">Danh sách các lớp bạn đã đăng ký.</p>
        </div>
        <a href="{{ route('student.enroll.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
            <i class="fas fa-plus mr-1"></i> Đăng ký thêm
        </a>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('status') }}</div>
    @endif

    @forelse($enrollments as $enrollment)
        @php $class = $enrollment->classRoom; @endphp
        <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-semibold text-gray-900">{{ $enrollment->subject->name ?? '—' }}</h3>
                        @php
                            $badge = match($enrollment->status) {
                                'pending'   => 'bg-amber-100 text-amber-700',
                                'approved'  => 'bg-blue-100 text-blue-700',
                                'active'    => 'bg-green-100 text-green-700',
                                'completed' => 'bg-slate-100 text-slate-600',
                                'rejected'  => 'bg-red-100 text-red-600',
                                default     => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">{{ $enrollment->statusLabel() }}</span>
                    </div>

                    @if($class)
                        <div class="mt-3 grid gap-2 text-sm text-gray-600 sm:grid-cols-2">
                            <div>
                                <span class="font-medium text-gray-500">Lớp:</span>
                                {{ $class->course->title ?? '—' }}
                            </div>
                            <div>
                                <span class="font-medium text-gray-500">Giảng viên:</span>
                                {{ $class->teacher?->name ?? 'Chưa phân công' }}
                            </div>
                            <div>
                                <span class="font-medium text-gray-500">Phòng:</span>
                                {{ $class->room ? $class->room->name . ' (' . $class->room->code . ')' : 'Chưa chọn' }}
                            </div>
                            <div>
                                <span class="font-medium text-gray-500">Ngày bắt đầu:</span>
                                {{ $class->start_date?->format('d/m/Y') ?? 'Chưa xác định' }}
                            </div>
                        </div>

                        @if($class->schedules->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($class->schedules as $s)
                                    <span class="rounded-lg bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ \App\Models\ClassSchedule::$dayOptions[$s->day_of_week] ?? $s->day_of_week }}
                                        {{ $s->start_time }}–{{ $s->end_time }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <p class="mt-2 text-sm text-gray-400">Chờ admin xếp lớp.</p>
                    @endif
                </div>

                <div class="text-right shrink-0 text-xs text-gray-400">
                    Đăng ký<br>{{ $enrollment->created_at?->format('d/m/Y') }}
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center text-gray-400">
            <i class="fas fa-inbox text-4xl mb-3 block"></i>
            Bạn chưa đăng ký lớp học nào.
            <div class="mt-4">
                <a href="{{ route('student.enroll.index') }}" class="rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700 transition">
                    Đăng ký ngay
                </a>
            </div>
        </div>
    @endforelse

    <div class="mt-4">{{ $enrollments->links() }}</div>
</div>
@endsection
