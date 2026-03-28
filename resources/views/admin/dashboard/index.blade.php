@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-cyan-600 to-cyan-500 rounded-3xl p-6 text-white shadow-lg">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-wider opacity-80">Dashboard Admin</p>
                <h1 class="text-3xl font-bold mt-1">Trung tâm điều phối đào tạo</h1>
                <p class="mt-2 text-cyan-100 max-w-2xl">
                    Quản lý học viên, giảng viên, khóa học và các yêu cầu vận hành.
                </p>
            </div>
            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-4 text-center">
                <p class="text-xs uppercase">Xin chào</p>
                <p class="text-xl font-bold">{{ $user->name }}</p>
                <p class="text-xs mt-1">Admin đang đăng nhập</p>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <x-admin.stat-card label="Học viên" value="{{ $studentCount }}" icon="fas fa-user-graduate" color="cyan" trend="tổng số" />
        <x-admin.stat-card label="Giảng viên" value="{{ $teacherCount }}" icon="fas fa-chalkboard-user" color="emerald" />
        <x-admin.stat-card label="Đơn ứng tuyển chờ" value="{{ $pendingTeacherApplications }}" icon="fas fa-file-signature" color="amber" />
        <x-admin.stat-card label="Đăng ký chờ xử lý" value="{{ $pendingEnrollments }}" icon="fas fa-clipboard-check" color="rose" />
        <x-admin.stat-card label="Lớp đang hoạt động" value="{{ $activeClassCount }}" icon="fas fa-people-group" color="violet" />
        <x-admin.stat-card label="Yêu cầu đổi lịch" value="{{ $pendingScheduleChangeRequests }}" icon="fas fa-calendar-rotate" color="slate" />
    </div>

    <!-- Quick actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('admin.enrollments') }}" class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center">
                <i class="fas fa-clipboard-list text-xl"></i>
            </div>
            <div>
                <p class="font-semibold">Xử lý đăng ký học</p>
                <p class="text-sm text-slate-500">{{ $pendingEnrollments }} đăng ký chờ</p>
            </div>
        </a>
        <a href="{{ route('admin.teacher-applications') }}" class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i class="fas fa-file-signature text-xl"></i>
            </div>
            <div>
                <p class="font-semibold">Duyệt giảng viên</p>
                <p class="text-sm text-slate-500">{{ $pendingTeacherApplications }} hồ sơ chờ</p>
            </div>
        </a>
        <a href="{{ route('admin.schedules.queue') }}" class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                <i class="fas fa-calendar-week text-xl"></i>
            </div>
            <div>
                <p class="font-semibold">Hàng chờ xếp lịch</p>
                <p class="text-sm text-slate-500">Chờ xếp lớp</p>
            </div>
        </a>
    </div>

    <!-- Recent Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent enrollments -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h2 class="font-semibold text-slate-800">Đăng ký học gần đây</h2>
                <a href="{{ route('admin.enrollments') }}" class="text-sm text-cyan-600 hover:underline">Xem tất cả</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Học viên</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Khóa học</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trạng thái</th>
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
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">{{ $enrollment->statusLabel() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-slate-500">Chưa có đăng ký mới</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending teacher applications -->
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
                    <div class="p-8 text-center text-slate-500">Không có hồ sơ chờ duyệt</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent courses -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <h2 class="font-semibold text-slate-800">Lớp học mới cập nhật</h2>
            <a href="{{ route('admin.courses') }}" class="text-sm text-cyan-600 hover:underline">Quản lý lớp học</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-5">
            @forelse($recentCourses as $course)
                <div class="border border-slate-200 rounded-xl p-4 hover:shadow-md transition">
                    <p class="text-xs text-slate-500 uppercase">{{ $course->subject?->category?->name ?? 'Chưa phân nhóm' }}</p>
                    <p class="font-semibold text-slate-800 mt-1">{{ $course->title }}</p>
                    <div class="mt-3 text-sm text-slate-600">
                        <p>Khóa học: {{ $course->subject?->name ?? 'N/A' }}</p>
                        <p>Giảng viên: {{ $course->teacher?->name ?? 'Chưa phân công' }}</p>
                        <p>Lịch: {{ $course->formattedSchedule() }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-8 text-slate-500">Chưa có lớp học nào</div>
            @endforelse
        </div>
    </div>
</div>
@endsection