@extends('bo_cuc.ung_dung')
@section('title', $subject->name)
@section('content')
@php
    $days = [
        'Monday' => 'Thu 2',
        'Tuesday' => 'Thu 3',
        'Wednesday' => 'Thu 4',
        'Thursday' => 'Thu 5',
        'Friday' => 'Thu 6',
        'Saturday' => 'Thu 7',
        'Sunday' => 'Chu nhat',
    ];
    $storedPreferredDays = $userEnrollment?->preferred_days;
    $selectedDays = old('preferred_days', is_array($storedPreferredDays)
        ? $storedPreferredDays
        : ((is_string($storedPreferredDays) && $storedPreferredDays !== '') ? (json_decode($storedPreferredDays, true) ?: []) : []));
    $normalizedStatus = $userEnrollment?->normalizedStatus();
    $hasCourseAccess = $userEnrollment?->hasCourseAccess();
    $waitingOpenCourse = $userEnrollment?->course?->isPendingOpen() ?? false;
    $openClasses = $subject->courses->filter(fn ($course) => $course->isPendingOpen());
@endphp
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-primary">{{ $subject->category?->name ?? 'Chua phan nhom' }}</p>
            <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ $subject->name }}</h1>
            <p class="mt-2 text-gray-600">{{ $subject->description ?? 'Chọn lớp đang mở hoặc gửi yêu cầu lịch học phù hợp.' }}</p>
        </div>
        <a href="{{ route('courses.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2 font-medium text-gray-700 transition hover:border-primary hover:text-primary">
            <i class="fas fa-arrow-left text-sm"></i>
            Quay lai danh sach khoa hoc
        </a>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-gray-500">Nhom hoc</div>
            <div class="mt-2 text-xl font-bold text-gray-900">{{ $subject->category?->name ?? 'Chua phan nhom' }}</div>
            <p class="mt-2 text-sm text-gray-500">Cac lop duoc ghep theo mon hoc va lich phu hop.</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-gray-500">Hoc phi tham khao</div>
            <div class="mt-2 text-xl font-bold text-primary">{{ number_format($subject->price ?? 0, 0, ',', '.') }}d</div>
            <p class="mt-2 text-sm text-gray-500">Ap dung theo lop dang mo hoac lop duoc xep sau.</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-gray-500">Lop hien co</div>
            <div class="mt-2 text-xl font-bold text-gray-900">{{ $subject->courses->count() }}</div>
            <p class="mt-2 text-sm text-gray-500">Chọn lớp phù hợp hoặc gửi yêu cầu lịch riêng.</p>
        </div>
    </div>

    @if($userEnrollment)
        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <div class="text-sm font-semibold uppercase tracking-wide text-blue-700">Trang thai dang ky</div>
                    <div class="mt-2">
                        @if($waitingOpenCourse)
                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">{{ $userEnrollment->course->statusLabel() }}</span>
                        @elseif($normalizedStatus === \App\Models\GhiDanh::STATUS_PENDING)
                            <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-sm font-semibold text-yellow-800">Đang chờ duyệt</span>
                        @elseif($normalizedStatus === \App\Models\GhiDanh::STATUS_APPROVED)
                            <span class="inline-flex rounded-full bg-cyan-100 px-3 py-1 text-sm font-semibold text-cyan-800">Da duyet, cho xep lop</span>
                        @elseif(in_array($normalizedStatus, [\App\Models\GhiDanh::STATUS_SCHEDULED, \App\Models\GhiDanh::STATUS_ACTIVE, \App\Models\GhiDanh::STATUS_COMPLETED], true))
                            <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">{{ $userEnrollment->statusLabel() }}</span>
                        @else
                            <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-800">Đăng ký bị từ chối</span>
                        @endif
                    </div>

                    @if($userEnrollment->course)
                        <div class="mt-3 space-y-1 text-sm text-gray-700">
                            <p><strong>Lớp:</strong> {{ $userEnrollment->course->title }}</p>
                            <p><strong>Giảng viên:</strong> {{ $userEnrollment->assignedTeacher?->displayName() ?? 'Chưa phân công' }}</p>
                            <p><strong>Lịch học:</strong> {{ $userEnrollment->schedule ?? 'Chưa có lịch' }}</p>
                        </div>
                    @endif
                </div>

                <div class="text-sm text-gray-600">
                    @if($userEnrollment->submitted_at)
                        <p><strong>Gửi lúc:</strong> {{ $userEnrollment->submitted_at->format('d/m/Y H:i') }}</p>
                    @endif
                    @if($userEnrollment->start_time && $userEnrollment->end_time)
                        <p class="mt-1"><strong>Khung giờ mong muốn:</strong> {{ $userEnrollment->start_time }} - {{ $userEnrollment->end_time }}</p>
                    @endif
                    @if($selectedDays)
                        <p class="mt-1"><strong>Các ngày:</strong> {{ implode(', ', array_map(fn ($day) => $days[$day] ?? $day, $selectedDays)) }}</p>
                    @endif
                </div>
            </div>

            @if($waitingOpenCourse)
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Bạn đang chờ lớp mở.
                </div>
            @endif

            @if($userEnrollment->note)
                <div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                    <strong>{{ $normalizedStatus === \App\Models\GhiDanh::STATUS_REJECTED ? 'Lý do từ chối' : 'Ghi chú từ admin' }}:</strong> {{ $userEnrollment->note }}
                </div>
            @endif

            @if($hasCourseAccess && $userEnrollment->course_id && ! $waitingOpenCourse)
                <div class="mt-4">
                    <a href="{{ route('courses.show', $userEnrollment->course_id) }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 font-semibold text-white transition hover:bg-primary-dark">
                        <i class="fas fa-graduation-cap"></i>
                        Vào khóa học triển khai
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900 shadow-sm">
            <h2 class="text-lg font-semibold">Chua co dang ky</h2>
            <p class="mt-2 text-sm leading-6">Bạn có thể chọn lớp đang mở hoặc gửi yêu cầu lịch học.</p>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-3">
            <h2 class="text-2xl font-bold text-gray-900">Đăng ký khóa học</h2>
            <p class="mt-2 text-sm text-gray-600">Chon ngay va khung gio ban co the hoc.</p>

            @if(!$user || !$user->isStudent())
                <div class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-5 text-sm text-blue-800">
                    Bạn cần đăng nhập bằng tài khoản học viên trước khi gửi yêu cầu đăng ký.
                    <div class="mt-4">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 font-semibold text-white transition hover:bg-primary-dark">
                            <i class="fas fa-right-to-bracket"></i>
                            Dang nhap de dang ky
                        </a>
                    </div>
                </div>
            @elseif($waitingOpenCourse)
                <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
                    Ban dang duoc ghep vao lop cho mo.
                </div>
            @elseif($hasCourseAccess)
                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-800">
                    Ban da duoc xep vao lop hoc chinh thuc.
                </div>
            @else
                <form method="post" action="{{ route('khoa-hoc.enroll', $subject->id) }}" class="mt-6 space-y-5">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Gio bat dau</label>
                            <input type="time" name="start_time" required class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 focus:border-primary focus:outline-none" value="{{ old('start_time', $userEnrollment->start_time ?? '') }}" />
                            @error('start_time')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Gio ket thuc</label>
                            <input type="time" name="end_time" required class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2.5 focus:border-primary focus:outline-none" value="{{ old('end_time', $userEnrollment->end_time ?? '') }}" />
                            @error('end_time')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nhung ngay co the hoc</label>
                        <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-4">
                            @foreach($days as $day => $label)
                                <label class="flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 transition hover:border-primary">
                                    <input type="checkbox" name="preferred_days[]" value="{{ $day }}" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" {{ in_array($day, $selectedDays) ? 'checked' : '' }} />
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('preferred_days')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 font-semibold text-white transition hover:bg-primary-dark">
                        <i class="fas fa-paper-plane text-sm"></i>
                        @if($userEnrollment)
                            @if($normalizedStatus === \App\Models\GhiDanh::STATUS_REJECTED)
                                Gửi lại yêu cầu đăng ký
                            @elseif($normalizedStatus === \App\Models\GhiDanh::STATUS_APPROVED)
                                Cập nhật thời gian mong muốn
                            @else
                                Cập nhật yêu cầu đăng ký
                            @endif
                        @else
                            Gửi yêu cầu đăng ký khóa học
                        @endif
                    </button>
                </form>
            @endif
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Đăng ký nhanh</h3>
                <div class="mt-4 flex flex-col gap-3">
                    @if($openClasses->isNotEmpty())
                        <a href="{{ route('student.enroll.select', $subject) }}" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 hover:bg-cyan-100 transition">
                            Chọn lớp đang mở
                        </a>
                    @endif
                    <a href="{{ route('student.enroll.request-form', $subject) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Gửi yêu cầu lịch học
                    </a>
                </div>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Các lớp hiện có của khóa này</h3>
                @if($subject->courses->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">Hiện chưa có lớp đang mở cho khóa này.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach($subject->courses as $course)
                            <div class="rounded-2xl border border-gray-200 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-semibold text-gray-900">{{ $course->title }}</div>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $course->isPendingOpen() ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $course->statusLabel() }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-600">{{ $course->description ?? 'Thông tin lớp sẽ được cập nhật thêm.' }}</p>
                                <div class="mt-3 space-y-1 text-xs text-gray-500">
                                    <p><strong>Lịch:</strong> {{ $course->formattedSchedule() }}</p>
                                    <p><strong>Giảng viên:</strong> {{ $course->teacher?->displayName() ?? 'Chưa phân công' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
