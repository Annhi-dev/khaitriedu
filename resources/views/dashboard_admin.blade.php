@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('content')
<div class="space-y-6">
    <section class="rounded-[28px] bg-slate-950 p-6 text-white shadow-xl shadow-slate-900/10">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-cyan-300/80">Phase 1</p>
                <h3 class="mt-2 text-3xl font-semibold">Nền tảng quản trị Khải Triều Edu</h3>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-300">
                    Admin là trung tâm phê duyệt của toàn hệ thống. Từ đây có thể theo dõi dữ liệu chờ xử lý, kiểm soát luồng nghiệp vụ, và làm nền cho các phase quản lý tiếp theo.
                </p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 px-5 py-4 text-sm text-slate-200">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Đang đăng nhập</p>
                <p class="mt-2 text-lg font-semibold">{{ $user->name }}</p>
                <p class="text-slate-400">Admin</p>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Tổng học viên</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $studentCount }}</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700">
                    <i class="fas fa-user-graduate text-xl"></i>
                </div>
            </div>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Tổng giảng viên</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $teacherCount }}</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                    <i class="fas fa-chalkboard-user text-xl"></i>
                </div>
            </div>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Đơn ứng tuyển chờ duyệt</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $pendingTeacherApplications }}</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                    <i class="fas fa-file-signature text-xl"></i>
                </div>
            </div>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Đăng ký học chờ duyệt</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $pendingEnrollments }}</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-700">
                    <i class="fas fa-clipboard-check text-xl"></i>
                </div>
            </div>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Lớp học đang hoạt động</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $activeClassCount }}</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                    <i class="fas fa-people-group text-xl"></i>
                </div>
            </div>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Yêu cầu đổi lịch chờ</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $pendingScheduleChangeRequests }}</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
                    <i class="fas fa-calendar-rotate text-xl"></i>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h4 class="text-lg font-semibold text-slate-900">Lối tắt quản trị</h4>
                    <p class="mt-1 text-sm text-slate-500">Các màn hình nền đang sẵn sàng cho admin ở phase hiện tại.</p>
                </div>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <a href="{{ route('admin.teacher-applications') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-cyan-300 hover:bg-cyan-50">
                    <p class="text-sm font-semibold text-slate-900">Ứng tuyển giảng viên</p>
                    <p class="mt-1 text-sm text-slate-500">Xem và xử lý hồ sơ chờ admin duyệt.</p>
                </a>
                <a href="{{ route('admin.enrollments') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-cyan-300 hover:bg-cyan-50">
                    <p class="text-sm font-semibold text-slate-900">Đăng ký học</p>
                    <p class="mt-1 text-sm text-slate-500">Kiểm tra yêu cầu học viên đang chờ xác nhận.</p>
                </a>
                <a href="{{ route('admin.categories') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-cyan-300 hover:bg-cyan-50">
                    <p class="text-sm font-semibold text-slate-900">Nhóm học</p>
                    <p class="mt-1 text-sm text-slate-500">Quản lý cấu trúc nhóm ngành và chương trình.</p>
                </a>
                <a href="{{ route('admin.subjects') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-cyan-300 hover:bg-cyan-50">
                    <p class="text-sm font-semibold text-slate-900">Khóa học</p>
                    <p class="mt-1 text-sm text-slate-500">Quản lý khóa học public mà học viên nhìn thấy.</p>
                </a>
                <a href="{{ route('admin.courses') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-cyan-300 hover:bg-cyan-50">
                    <p class="text-sm font-semibold text-slate-900">Lớp học và module</p>
                    <p class="mt-1 text-sm text-slate-500">Theo dõi lớp nội bộ, giảng viên và nội dung học.</p>
                </a>
                <a href="{{ route('admin.report') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-cyan-300 hover:bg-cyan-50">
                    <p class="text-sm font-semibold text-slate-900">Báo cáo</p>
                    <p class="mt-1 text-sm text-slate-500">Xem bức tranh tổng quan của hệ thống quản trị.</p>
                </a>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h4 class="text-lg font-semibold text-slate-900">Ghi chú nền tảng</h4>
            <ul class="mt-4 space-y-3 text-sm leading-7 text-slate-600">
                <li class="rounded-2xl bg-slate-50 px-4 py-3">Toàn bộ route dưới <span class="font-semibold text-slate-900">/admin/*</span> đã được bảo vệ bằng middleware admin.</li>
                <li class="rounded-2xl bg-slate-50 px-4 py-3">Layout admin đã có sidebar, topbar, breadcrumb và flash message để tái sử dụng cho các phase sau.</li>
                <li class="rounded-2xl bg-slate-50 px-4 py-3">Đã bổ sung nền dữ liệu tối thiểu cho <span class="font-semibold text-slate-900">trạng thái tài khoản</span> và <span class="font-semibold text-slate-900">yêu cầu đổi lịch</span>.</li>
                <li class="rounded-2xl bg-slate-50 px-4 py-3">Các mục như quản lý học viên, giảng viên, lịch học và đổi lịch đã được đặt sẵn vị trí trong menu để phát triển ở phase tiếp theo.</li>
            </ul>
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                Tổng khóa học public hiện có: <span class="font-semibold text-slate-900">{{ $subjectCount }}</span>
            </div>
        </div>
    </section>
</div>
@endsection