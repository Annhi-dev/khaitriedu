@extends('layouts.admin')
@section('title', 'Chi tiết hồ sơ ứng tuyển')
@section('content')
@php
    $statusClasses = match ($application->status) {
        \App\Models\TeacherApplication::STATUS_APPROVED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        \App\Models\TeacherApplication::STATUS_REJECTED => 'border-rose-200 bg-rose-50 text-rose-700',
        \App\Models\TeacherApplication::STATUS_NEEDS_REVISION => 'border-amber-200 bg-amber-50 text-amber-700',
        default => 'border-slate-200 bg-slate-100 text-slate-700',
    };
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Teacher application</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900">{{ $application->name }}</h1>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                <span>{{ $application->email }}</span>
                <span class="text-slate-300">|</span>
                <span>{{ $application->phone ?: 'Chưa có số điện thoại' }}</span>
                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $application->statusLabel() }}</span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.teacher-applications') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Quay lại danh sách</a>
            @if ($relatedUser)
                <a href="{{ route('admin.teachers.show', $relatedUser) }}" class="inline-flex items-center justify-center rounded-2xl border border-cyan-200 px-4 py-2.5 text-sm font-medium text-cyan-700 hover:bg-cyan-50">Xem tài khoản giảng viên</a>
            @endif
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Thông tin ứng viên</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Họ và tên</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->name }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Email</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->email }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Số điện thoại</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->phone ?: 'Chưa cập nhật' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Ngày nộp</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ optional($application->created_at)->format('d/m/Y H:i') ?: 'Không có dữ liệu' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Kinh nghiệm giảng dạy</p>
                    <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700">{{ $application->experience ?: 'Ứng viên chưa cung cấp phần kinh nghiệm.' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Lý do ứng tuyển / chuyên môn</p>
                    <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700">{{ $application->message ?: 'Ứng viên chưa cung cấp mô tả thêm.' }}</p>
                </div>
            </div>
        </section>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Kết quả xử lý</h2>
            <div class="mt-4 grid gap-3">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Trạng thái hiện tại</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $application->statusLabel() }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Người duyệt</p>
                    <p class="mt-2 text-sm font-medium text-slate-900">{{ $application->reviewer?->name ?: 'Chưa có' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Thời gian duyệt</p>
                    <p class="mt-2 text-sm font-medium text-slate-900">{{ optional($application->reviewed_at)->format('d/m/Y H:i') ?: 'Chưa có' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Ghi chú admin</p>
                    <p class="mt-2 text-sm leading-6 text-slate-700">{{ $application->admin_note ?: 'Chưa có ghi chú.' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Lý do từ chối</p>
                    <p class="mt-2 text-sm leading-6 text-slate-700">{{ $application->rejection_reason ?: 'Không có.' }}</p>
                </div>
            </div>
        </aside>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-3xl border border-emerald-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Duyệt hồ sơ</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Dùng khi hồ sơ đạt yêu cầu và cần tạo hoặc kích hoạt tài khoản giảng viên.</p>
            <form method="post" action="{{ route('admin.teacher-applications.review', $application) }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="action" value="{{ \App\Models\TeacherApplication::STATUS_APPROVED }}" />
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Ghi chú nội bộ</label>
                    <textarea name="admin_note" rows="4" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" placeholder="Ghi chú thêm nếu cần">{{ old('action') === \App\Models\TeacherApplication::STATUS_APPROVED ? old('admin_note') : '' }}</textarea>
                </div>
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Duyệt và kích hoạt giảng viên</button>
            </form>
        </section>

        <section class="rounded-3xl border border-amber-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Yêu cầu bổ sung hồ sơ</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Dùng khi hồ sơ chưa đủ nhưng vẫn muốn ứng viên hoàn thiện để admin duyệt lại.</p>
            <form method="post" action="{{ route('admin.teacher-applications.review', $application) }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="action" value="{{ \App\Models\TeacherApplication::STATUS_NEEDS_REVISION }}" />
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Nội dung cần bổ sung</label>
                    <textarea name="admin_note" rows="4" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100" placeholder="Ví dụ: bổ sung kinh nghiệm, minh chứng, thông tin liên hệ">{{ old('action') === \App\Models\TeacherApplication::STATUS_NEEDS_REVISION ? old('admin_note') : '' }}</textarea>
                    @error('admin_note')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-amber-500 px-4 py-3 text-sm font-semibold text-white hover:bg-amber-600">Yêu cầu bổ sung</button>
            </form>
        </section>

        <section class="rounded-3xl border border-rose-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Từ chối hồ sơ</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Dùng khi hồ sơ không phù hợp. Lý do từ chối sẽ được lưu lại để admin theo dõi.</p>
            <form method="post" action="{{ route('admin.teacher-applications.review', $application) }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="action" value="{{ \App\Models\TeacherApplication::STATUS_REJECTED }}" />
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Lý do từ chối</label>
                    <textarea name="rejection_reason" rows="4" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100" placeholder="Nêu rõ lý do để hồ sơ được xử lý minh bạch">{{ old('action') === \App\Models\TeacherApplication::STATUS_REJECTED ? old('rejection_reason') : '' }}</textarea>
                    @error('rejection_reason')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white hover:bg-rose-700">Từ chối hồ sơ</button>
            </form>
        </section>
    </div>
</div>
@endsection