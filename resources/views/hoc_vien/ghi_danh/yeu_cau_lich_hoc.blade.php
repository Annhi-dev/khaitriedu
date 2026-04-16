@extends('bo_cuc.hoc_vien')
@section('title', 'Yêu cầu lịch học — ' . $subject->name)
@section('eyebrow', 'Lịch học cá nhân')

@section('content')
@php
    $storedPreferredDays = $existingEnrollment?->preferred_days;
    $selectedDays = old('preferred_days', is_array($storedPreferredDays)
        ? $storedPreferredDays
        : ((is_string($storedPreferredDays) && $storedPreferredDays !== '') ? (json_decode($storedPreferredDays, true) ?: []) : []));
    $canEditRequest = ! $existingEnrollment || ! $existingEnrollment->hasCourseAccess();
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Yêu cầu lịch học</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Gửi yêu cầu lịch học — {{ $subject->name }}</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Dùng khi bạn muốn chủ động gửi khung giờ học mong muốn để admin sắp xếp lớp phù hợp hơn.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if($openClasses->isNotEmpty())
                    <a href="{{ route('student.enroll.select', $subject) }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
                        <i class="fas fa-door-open"></i>
                        Xem lớp cố định đang mở
                    </a>
                @endif
                <a href="{{ route('student.enroll.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
            </div>
        </div>
    </section>

    @if($existingEnrollment)
        <section class="rounded-3xl border {{ $existingEnrollment->hasCourseAccess() ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} p-5 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] {{ $existingEnrollment->hasCourseAccess() ? 'text-emerald-700' : 'text-amber-700' }}">Trạng thái hiện tại</p>
                    <p class="mt-2 text-lg font-semibold {{ $existingEnrollment->hasCourseAccess() ? 'text-emerald-900' : 'text-amber-900' }}">
                        {{ $existingEnrollment->displayStatusLabel() }}
                    </p>
                    @if($existingEnrollment->classRoom)
                        <p class="mt-2 text-sm leading-6 text-slate-700">Bạn đã được ghi danh vào lớp <strong>{{ $existingEnrollment->classRoom->displayName() }}</strong>.</p>
                    @elseif($existingEnrollment->start_time && $existingEnrollment->end_time)
                        <p class="mt-2 text-sm leading-6 text-slate-700">Khung giờ đã gửi: <strong>{{ $existingEnrollment->start_time }} - {{ $existingEnrollment->end_time }}</strong></p>
                    @endif
                </div>

                <div class="text-sm leading-6 text-slate-600">
                    @if(!empty($selectedDays))
                        <p><strong>Ngày có thể học:</strong> {{ collect($selectedDays)->map(fn ($day) => $dayOptions[$day] ?? $day)->implode(', ') }}</p>
                    @endif
                    @if($existingEnrollment->preferred_schedule)
                        <p class="mt-1"><strong>Ghi chú thêm:</strong> {{ $existingEnrollment->preferred_schedule }}</p>
                    @endif
                </div>
            </div>

            @if($existingEnrollment->note)
                <div class="mt-4 rounded-2xl border border-white/80 bg-white/80 px-4 py-3 text-sm text-slate-700">
                    <strong>Ghi chú từ admin:</strong> {{ $existingEnrollment->note }}
                </div>
            @endif
        </section>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.6fr)_minmax(300px,1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-xl font-semibold text-slate-900">Biểu mẫu yêu cầu lịch học</h3>

            @if(!$canEditRequest)
                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm leading-6 text-emerald-800">
                    Bạn đã được ghi danh hoặc xếp lớp cho khóa học này. Nếu muốn thay đổi, vui lòng liên hệ admin.
                </div>
                <div class="mt-4">
                    <a href="{{ route('student.enroll.my-classes') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        <i class="fas fa-users"></i>
                        Xem lớp của tôi
                    </a>
                </div>
            @else
                <form method="POST" action="{{ route('student.enroll.request-store', $subject) }}" class="mt-6 space-y-5">
                    @csrf

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Giờ bắt đầu có thể học</label>
                            <input type="time" name="start_time" value="{{ old('start_time', $existingEnrollment?->start_time) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                            @error('start_time')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Giờ kết thúc</label>
                            <input type="time" name="end_time" value="{{ old('end_time', $existingEnrollment?->end_time) }}" class="mt-2 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" readonly>
                            @error('end_time')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Những ngày bạn có thể học</label>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($dayOptions as $day => $label)
                                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50">
                                    <input type="checkbox" name="preferred_days[]" value="{{ $day }}" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" @checked(in_array($day, $selectedDays))>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('preferred_days')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                        @error('preferred_days.*')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Ghi chú thêm</label>
                        <textarea name="preferred_schedule" rows="4" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" placeholder="Ghi chú">{{ old('preferred_schedule', $existingEnrollment?->preferred_schedule) }}</textarea>
                        @error('preferred_schedule')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                        <i class="fas fa-paper-plane"></i>
                        {{ $existingEnrollment ? 'Cập nhật yêu cầu lịch học' : 'Gửi yêu cầu lịch học' }}
                    </button>
                </form>
            @endif
        </section>

        <aside class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Lớp cố định đang mở</h3>
                @if($openClasses->isEmpty())
                    <p class="mt-3 text-sm leading-6 text-slate-500">Hiện chưa có lớp cố định phù hợp.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach($openClasses as $classRoom)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="font-semibold text-slate-900">{{ $classRoom->displayName() }}</div>
                                <div class="mt-2 space-y-1 text-sm text-slate-600">
                                    <p><span class="font-medium text-slate-500">Giảng viên:</span> {{ $classRoom->teacher?->displayName() ?? 'Chưa phân công' }}</p>
                                    <p><span class="font-medium text-slate-500">Lịch:</span> {{ $classRoom->scheduleSummary() }}</p>
                                    <p><span class="font-medium text-slate-500">Chỗ trống:</span> {{ $classRoom->availableSlots() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <a href="{{ route('student.enroll.select', $subject) }}" class="mt-4 inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
                        <i class="fas fa-door-open"></i>
                        Chọn lớp cố định
                    </a>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection
