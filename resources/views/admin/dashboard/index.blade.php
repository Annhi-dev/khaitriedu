@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <section class="bg-gradient-to-r from-cyan-600 via-sky-600 to-blue-700 rounded-3xl p-6 text-white shadow-lg">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.25em] text-cyan-100">Dashboard Admin</p>
                <h1 class="mt-2 text-3xl font-bold">Trung tâm điều phối đào tạo</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-cyan-50/95">
                    Theo dõi toàn bộ vận hành admin: người dùng, nhóm học, khóa học, lớp đang hoạt động,
                    hạ tầng phòng học, khung giờ mở đăng ký và các luồng chờ xử lý trước khi mở lớp chính thức.
                </p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:min-w-[360px]">
                <div class="rounded-2xl bg-white/15 p-4 backdrop-blur-sm">
                    <p class="text-xs uppercase tracking-wider text-cyan-100">Admin đang đăng nhập</p>
                    <p class="mt-2 text-xl font-semibold">{{ $user->name }}</p>
                    <p class="mt-1 text-xs text-cyan-100">KhaiTriEdu Control Center</p>
                </div>
                <div class="rounded-2xl bg-slate-950/20 p-4 backdrop-blur-sm">
                    <p class="text-xs uppercase tracking-wider text-cyan-100">Hạ tầng dashboard</p>
                    <p class="mt-2 text-2xl font-semibold">{{ collect($infrastructureChecks)->filter()->count() }}/{{ count($infrastructureChecks) }}</p>
                    <p class="mt-1 text-xs text-cyan-100">Thành phần nền đã sẵn sàng</p>
                </div>
            </div>
        </div>
    </section>

    @if ($infrastructureWarnings)
        <section class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
            <p class="font-semibold">A1 đang chờ đủ hạ tầng dashboard</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($infrastructureWarnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <x-admin.stat-card label="Học viên" value="{{ $studentCount }}" icon="fas fa-user-graduate" color="cyan" trend="Tổng tài khoản học viên" />
        <x-admin.stat-card label="Giảng viên" value="{{ $teacherCount }}" icon="fas fa-chalkboard-user" color="emerald" trend="Tổng tài khoản giảng viên" />
        <x-admin.stat-card label="Đơn ứng tuyển chờ" value="{{ $pendingTeacherApplications }}" icon="fas fa-file-signature" color="amber" trend="Teacher application pending" />
        <x-admin.stat-card label="Khóa học" value="{{ $subjectCount }}" icon="fas fa-book-open" color="violet" trend="Khóa học public hiện có" />
        <x-admin.stat-card label="Nhóm học" value="{{ $groupCount }}" icon="fas fa-layer-group" color="slate" trend="Danh mục đào tạo" />
        <x-admin.stat-card label="Phòng học" value="{{ $roomCount }}" icon="fas fa-door-open" color="cyan" trend="{{ $maintenanceRoomCount }} phòng bảo trì" />
        <x-admin.stat-card label="Khung giờ mở đăng ký" value="{{ $openTimeSlotCount }}" icon="fas fa-clock" color="emerald" trend="{{ $configuredTimeSlotCount }} slot đã cấu hình" />
        <x-admin.stat-card label="Nguyện vọng chờ xử lý" value="{{ $pendingSlotRegistrationCount }}" icon="fas fa-list-check" color="amber" trend="{{ $slotChoiceCount }} lựa chọn đã ghi nhận" />
        <x-admin.stat-card label="Slot đủ điều kiện mở lớp" value="{{ $readyToOpenClassSlotCount }}" icon="fas fa-chart-simple" color="rose" trend="Có thể chuyển sang mở lớp" />
        <x-admin.stat-card label="Yêu cầu đổi lịch chờ" value="{{ $pendingScheduleChangeRequests }}" icon="fas fa-calendar-rotate" color="slate" trend="{{ $activeClassCount }} lớp đang hoạt động" />
    </section>

    <section class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-4">
        <a href="{{ route('admin.enrollments') }}" class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center">
                <i class="fas fa-clipboard-list text-xl"></i>
            </div>
            <div>
                <p class="font-semibold">Xử lý đăng ký học</p>
                <p class="text-sm text-slate-500">Theo dõi pipeline enrollment hiện tại</p>
            </div>
        </a>
        <a href="{{ route('admin.teacher-applications') }}" class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i class="fas fa-file-signature text-xl"></i>
            </div>
            <div>
                <p class="font-semibold">Duyệt giảng viên</p>
                <p class="text-sm text-slate-500">{{ $pendingTeacherApplications }} hồ sơ đang chờ admin</p>
            </div>
        </a>
        <a href="{{ route('admin.schedules.queue') }}" class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                <i class="fas fa-calendar-week text-xl"></i>
            </div>
            <div>
                <p class="font-semibold">Hàng chờ xếp lịch</p>
                <p class="text-sm text-slate-500">Mở lớp và xếp lịch chính thức cho học viên</p>
            </div>
        </a>
        <a href="{{ route('admin.schedule-change-requests.index') }}" class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <i class="fas fa-calendar-rotate text-xl"></i>
            </div>
            <div>
                <p class="font-semibold">Duyệt đổi lịch</p>
                <p class="text-sm text-slate-500">{{ $pendingScheduleChangeRequests }} yêu cầu cần phản hồi</p>
            </div>
        </a>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-[1.1fr_0.9fr] gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-slate-800">Hạ tầng lịch học</h2>
                    <p class="mt-1 text-sm text-slate-500">Mức sẵn sàng của phòng học, khung giờ và luồng đăng ký theo slot.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">A1 Foundation</span>
            </div>
            <div class="grid gap-4 p-5 md:grid-cols-2">
                @php
                    $infraCards = [
                        [
                            'label' => 'Phòng học',
                            'ready' => $infrastructureChecks['rooms'] ?? false,
                            'meta' => $roomCount . ' phòng, ' . $maintenanceRoomCount . ' bảo trì',
                        ],
                        [
                            'label' => 'Khung giờ học',
                            'ready' => $infrastructureChecks['time_slots'] ?? false,
                            'meta' => $configuredTimeSlotCount . ' slot, ' . $openTimeSlotCount . ' đang mở',
                        ],
                        [
                            'label' => 'Nguyện vọng slot',
                            'ready' => $infrastructureChecks['slot_registrations'] ?? false,
                            'meta' => $pendingSlotRegistrationCount . ' chờ, ' . $recordedSlotRegistrationCount . ' đã ghi nhận',
                        ],
                        [
                            'label' => 'Lựa chọn khung giờ',
                            'ready' => $infrastructureChecks['slot_registration_choices'] ?? false,
                            'meta' => $slotChoiceCount . ' lựa chọn đã lưu',
                        ],
                    ];
                @endphp

                @foreach ($infraCards as $card)
                    <div class="rounded-2xl border {{ $card['ready'] ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold text-slate-900">{{ $card['label'] }}</p>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $card['ready'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $card['ready'] ? 'Sẵn sàng' : 'Thiếu hạ tầng' }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">{{ $card['meta'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-slate-800">Nhu cầu theo khung giờ</h2>
                    <p class="mt-1 text-sm text-slate-500">Những slot đang mở hoặc đã đủ điều kiện mở lớp gần đây nhất.</p>
                </div>
                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $slotDemandSummary->count() }} slot</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($slotDemandSummary as $slot)
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $slot->subject?->name ?? 'Khóa học chưa xác định' }}</p>
                                <p class="text-sm text-slate-500">{{ $slot->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $slot->status === \App\Models\CourseTimeSlot::STATUS_READY_TO_OPEN_CLASS ? 'bg-emerald-100 text-emerald-700' : 'bg-cyan-100 text-cyan-700' }}">
                                {{ $slot->statusLabel() }}
                            </span>
                        </div>
                        <div class="mt-3 grid gap-2 text-sm text-slate-600">
                            <p><span class="text-slate-500">Khung giờ:</span> {{ $slot->formattedWindow() }}</p>
                            <p><span class="text-slate-500">Giảng viên:</span> {{ $slot->teacher?->name ?? 'Chưa gán' }}</p>
                            <p><span class="text-slate-500">Phòng học:</span> {{ $slot->room?->name ?? 'Chưa gán' }}</p>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-sm">
                            <span class="text-slate-500">Nguyện vọng đã ghi nhận</span>
                            <span class="font-semibold text-slate-900">{{ (int) ($slot->registrations_count ?? 0) }}/{{ $slot->max_students }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500">Chưa có dữ liệu nhu cầu theo khung giờ để hiển thị.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h2 class="font-semibold text-slate-800">Nguyện vọng slot gần đây</h2>
                <span class="text-sm text-slate-500">{{ $pendingSlotRegistrationsList->count() }} hồ sơ</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($pendingSlotRegistrationsList as $registration)
                    <div class="p-5">
                        <p class="font-medium text-slate-800">{{ $registration->student?->name ?? 'Học viên' }}</p>
                        <p class="text-sm text-slate-500">{{ $registration->subject?->name ?? 'Khóa học chưa xác định' }}</p>
                        <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                            <span>{{ $registration->statusLabel() }}</span>
                            <span>{{ (int) ($registration->choices_count ?? 0) }} lựa chọn</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500">Chưa có nguyện vọng slot nào đang chờ xử lý.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h2 class="font-semibold text-slate-800">Ứng tuyển giảng viên chờ duyệt</h2>
                <a href="{{ route('admin.teacher-applications') }}" class="text-sm text-cyan-600 hover:underline">Xem tất cả</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($pendingTeacherApplicationsList as $application)
                    <a href="{{ route('admin.teacher-applications.show', $application) }}" class="block p-5 hover:bg-slate-50 transition">
                        <p class="font-medium text-slate-800">{{ $application->name }}</p>
                        <p class="text-sm text-slate-500">{{ $application->email }}</p>
                        <p class="text-xs text-amber-600 mt-1">Chờ duyệt</p>
                    </a>
                @empty
                    <div class="p-8 text-center text-slate-500">Không có hồ sơ chờ duyệt.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h2 class="font-semibold text-slate-800">Yêu cầu đổi lịch chờ xử lý</h2>
                <a href="{{ route('admin.schedule-change-requests.index') }}" class="text-sm text-cyan-600 hover:underline">Xem tất cả</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($pendingScheduleRequestsList as $request)
                    <a href="{{ route('admin.schedule-change-requests.show', $request) }}" class="block p-5 hover:bg-slate-50 transition">
                        <p class="font-medium text-slate-800">{{ $request->course?->title ?? 'Lớp học không xác định' }}</p>
                        <p class="text-sm text-slate-500">{{ $request->teacher?->name ?? 'Giảng viên không xác định' }}</p>
                        <p class="text-xs text-rose-600 mt-1">{{ $request->reason }}</p>
                    </a>
                @empty
                    <div class="p-8 text-center text-slate-500">Không có yêu cầu đổi lịch nào đang chờ.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-[1.1fr_0.9fr] gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h2 class="font-semibold text-slate-800">Đăng ký học gần đây</h2>
                <a href="{{ route('admin.enrollments') }}" class="text-sm text-cyan-600 hover:underline">Xem tất cả</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-slate-500">Học viên</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-slate-500">Khóa học</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-slate-500">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentEnrollments as $enrollment)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-slate-800">{{ $enrollment->user?->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-slate-500">{{ $enrollment->user?->email }}</p>
                                </td>
                                <td class="px-5 py-3">{{ $enrollment->subject?->name ?? 'Chưa xác định' }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">{{ $enrollment->statusLabel() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-slate-500">Chưa có đăng ký mới.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h2 class="font-semibold text-slate-800">Lớp học mới cập nhật</h2>
                <a href="{{ route('admin.courses') }}" class="text-sm text-cyan-600 hover:underline">Quản lý lớp học</a>
            </div>
            <div class="grid gap-4 p-5">
                @forelse($recentCourses as $course)
                    <div class="rounded-xl border border-slate-200 p-4 hover:shadow-sm transition">
                        <p class="text-xs uppercase text-slate-500">{{ $course->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ $course->title }}</p>
                        <div class="mt-3 space-y-1 text-sm text-slate-600">
                            <p>Khóa học: {{ $course->subject?->name ?? 'N/A' }}</p>
                            <p>Giảng viên: {{ $course->teacher?->name ?? 'Chưa phân công' }}</p>
                            <p>Lịch: {{ $course->formattedSchedule() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-500">Chưa có lớp học nào.</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection