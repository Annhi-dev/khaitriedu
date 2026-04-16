@extends('bo_cuc.hoc_vien')
@section('title', 'Lớp học của tôi')
@section('eyebrow', 'Lớp học của tôi')

@section('content')
@php
    $enrollmentList = method_exists($enrollments, 'getCollection') ? $enrollments->getCollection() : collect($enrollments);
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Lộ trình học tập</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Lớp học của tôi</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Tất cả lớp đã ghi danh, lớp chờ mở và yêu cầu lịch học đều được hiển thị ở một nơi để bạn dễ theo dõi.
                </p>
            </div>

            <a href="{{ route('student.enroll.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                <i class="fas fa-plus"></i>
                Đăng ký thêm
            </a>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Tổng lớp</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $enrollmentList->count() }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Đã ghi danh</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $enrollmentList->filter(fn ($item) => $item->hasCourseAccess())->count() }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Đang chờ</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $enrollmentList->filter(fn ($item) => ! $item->hasCourseAccess())->count() }}</p>
        </div>
    </section>

    <section class="grid gap-4">
        @forelse($enrollments as $enrollment)
            @php
                $class = $enrollment->classRoom;
                $course = $enrollment->course;
                $preferredDays = is_array($enrollment->preferred_days)
                    ? $enrollment->preferred_days
                    : ((is_string($enrollment->preferred_days) && $enrollment->preferred_days !== '') ? (json_decode($enrollment->preferred_days, true) ?: []) : []);
                $waitingOpen = $course?->isPendingOpen();
                $displayStatus = $enrollment->displayStatus();
                $badge = $waitingOpen
                    ? 'bg-amber-100 text-amber-700'
                    : match($displayStatus) {
                        'pending'   => 'bg-amber-100 text-amber-700',
                        'approved'  => 'bg-blue-100 text-blue-700',
                        'enrolled'  => 'bg-emerald-100 text-emerald-700',
                        'scheduled' => 'bg-emerald-100 text-emerald-700',
                        'active'    => 'bg-green-100 text-green-700',
                        'completed' => 'bg-slate-100 text-slate-600',
                        'rejected'  => 'bg-rose-100 text-rose-600',
                        default     => 'bg-slate-100 text-slate-600',
                    };
            @endphp

            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-semibold text-slate-900">{{ $enrollment->subject->name ?? '—' }}</h3>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                                {{ $waitingOpen ? $course->statusLabel() : $enrollment->displayStatusLabel() }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                            @if($class)
                                <div><span class="font-medium text-slate-500">Lớp:</span> {{ $class->displayName() }}</div>
                                <div><span class="font-medium text-slate-500">Giảng viên:</span> {{ $class->teacher?->displayName() ?? 'Chưa phân công' }}</div>
                                <div><span class="font-medium text-slate-500">Phòng:</span> {{ $class->room ? $class->room->name . ' (' . $class->room->code . ')' : 'Chưa chọn' }}</div>
                                <div><span class="font-medium text-slate-500">Ngày bắt đầu:</span> {{ $class->start_date?->format('d/m/Y') ?? 'Chưa xác định' }}</div>
                            @elseif($course && $waitingOpen)
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 md:col-span-2">
                                    <p class="font-medium text-amber-800">Bạn đã được ghép vào lớp chờ mở</p>
                                    <div class="mt-3 grid gap-2 text-sm text-amber-900 sm:grid-cols-2">
                                        <div><span class="font-medium text-amber-700">Lớp:</span> {{ $course->title }}</div>
                                        <div><span class="font-medium text-amber-700">Giảng viên:</span> {{ $course->teacher?->displayName() ?? 'Chưa phân công' }}</div>
                                        <div><span class="font-medium text-amber-700">Khung giờ:</span> {{ $course->start_time }} - {{ $course->end_time }}</div>
                                        <div><span class="font-medium text-amber-700">Ngày học:</span> {{ $course->meetingDaysLabel() }}</div>
                                    </div>
                                    <p class="mt-3 text-sm text-amber-800">Đang chờ lớp đủ sĩ số để mở chính thức.</p>
                                </div>
                            @else
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
                                    <p class="font-medium text-slate-700">Đăng ký theo yêu cầu lịch học riêng</p>
                                    <p class="mt-1 text-sm text-slate-500">Chưa được xếp vào lớp cố định.</p>
                                    <div class="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                                        <div>
                                            <span class="font-medium text-slate-500">Khung giờ mong muốn:</span>
                                            {{ $enrollment->start_time && $enrollment->end_time ? $enrollment->start_time . ' - ' . $enrollment->end_time : 'Chưa cung cấp' }}
                                        </div>
                                        <div>
                                            <span class="font-medium text-slate-500">Ngày có thể học:</span>
                                            {{ !empty($preferredDays) ? collect($preferredDays)->map(fn ($day) => \App\Models\ClassSchedule::$dayOptions[$day] ?? $day)->implode(', ') : 'Chưa cung cấp' }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($course && ! $waitingOpen && $course->formattedSchedule())
                            <div class="mt-4 text-sm text-slate-600">
                                <span class="font-medium text-slate-500">Lịch học:</span> {{ $course->formattedSchedule() }}
                            </div>
                        @endif

                        @if($enrollment->note)
                            <div class="mt-4 rounded-2xl border {{ $enrollment->status === 'rejected' ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-cyan-200 bg-cyan-50 text-cyan-700' }} px-4 py-3 text-sm">
                                <strong>{{ $enrollment->status === 'rejected' ? 'Lý do từ chối' : 'Ghi chú từ admin' }}:</strong>
                                {{ $enrollment->note }}
                            </div>
                        @endif
                    </div>

                    <div class="shrink-0 text-right text-xs text-slate-400">
                        Gửi yêu cầu<br>{{ ($enrollment->submitted_at ?? $enrollment->created_at)?->format('d/m/Y') }}
                    </div>
                </div>

                @if(!$class && ! $course && ! $enrollment->hasCourseAccess())
                    <div class="mt-5 flex flex-wrap gap-3 border-t border-slate-100 pt-4">
                        <a href="{{ route('student.enroll.request-form', $enrollment->subject) }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
                            <i class="fas fa-pen"></i>
                            Cập nhật yêu cầu lịch học
                        </a>

                        @if($enrollment->subject)
                            <a href="{{ route('student.enroll.select', $enrollment->subject) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                <i class="fas fa-door-open"></i>
                                Xem lớp cố định đang mở
                            </a>
                        @endif
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center shadow-sm">
                <i class="fas fa-inbox mb-3 block text-4xl text-slate-300"></i>
                <p class="text-lg font-semibold text-slate-700">Bạn chưa đăng ký lớp học nào.</p>
                <p class="mt-2 text-slate-500">Hãy bắt đầu bằng cách chọn một khóa học phù hợp.</p>
                <a href="{{ route('student.enroll.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    <i class="fas fa-book-open"></i>
                    Đăng ký ngay
                </a>
            </div>
        @endforelse
    </section>

    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
        {{ $enrollments->links() }}
    </div>
</div>
@endsection
