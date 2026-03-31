@extends('layouts.app')
@section('title', 'Chọn lớp học — ' . $subject->name)
@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-dark">Chọn lớp — {{ $subject->name }}</h1>
            <p class="text-gray-600 text-sm">{{ $subject->category?->name ?? '' }} • {{ $subject->durationLabel() }}</p>
        </div>
        <a href="{{ route('student.enroll.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
            ← Quay lại
        </a>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    {{-- Đã đăng ký rồi --}}
    @if($existingEnrollment)
        <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
            <p class="font-semibold text-amber-800">Bạn đã đăng ký khóa học này</p>
            <p class="mt-1 text-sm text-amber-700">
                Trạng thái: <strong>{{ $existingEnrollment->statusLabel() }}</strong>.
                @if($existingEnrollment->classRoom)
                    Lớp: <strong>{{ $existingEnrollment->classRoom->subject->name ?? '—' }}</strong>
                @endif
            </p>
            <a href="{{ route('student.enroll.my-classes') }}" class="mt-2 inline-block text-sm font-medium text-amber-700 underline">Xem lớp của tôi</a>
        </div>
    @endif

    {{-- Danh sách lớp --}}
    @if($classes->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center text-gray-400">
            <i class="fas fa-door-closed text-4xl mb-3 block"></i>
            Hiện không có lớp nào đang mở cho khóa này.
        </div>
    @else
        <div class="space-y-4">
            @foreach($classes as $class)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-semibold text-gray-900">{{ $class->subject->name ?? '—' }}</h3>
                            @if($class->availableSlots() <= 3)
                                <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-600">
                                    Còn {{ $class->availableSlots() }} chỗ
                                </span>
                            @else
                                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-600">
                                    Còn {{ $class->availableSlots() }} chỗ
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 grid gap-2 text-sm text-gray-600 sm:grid-cols-2">
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
                            <div>
                                <span class="font-medium text-gray-500">Thời lượng:</span>
                                {{ $class->duration ? $class->duration . ' tháng' : '—' }}
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
                    </div>

                    <div class="shrink-0">
                        @if($existingEnrollment)
                            <span class="block rounded-xl bg-gray-100 px-5 py-2.5 text-center text-sm font-medium text-gray-400 cursor-not-allowed">
                                Đã đăng ký
                            </span>
                        @else
                            <form method="POST" action="{{ route('student.enroll.store', $subject) }}">
                                @csrf
                                <input type="hidden" name="lop_hoc_id" value="{{ $class->id }}">
                                <button type="submit"
                                    onclick="return confirm('Xác nhận đăng ký lớp này?')"
                                    class="rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700 transition">
                                    Đăng ký lớp này
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
