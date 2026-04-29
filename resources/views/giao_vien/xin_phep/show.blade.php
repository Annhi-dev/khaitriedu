@extends('bo_cuc.giao_vien')

@section('title', 'Chi tiết xin phép nghỉ')
@section('eyebrow', 'Hỗ trợ học tập')

@section('content')
@php
    $badgeClasses = match ($leaveRequest->status) {
        \App\Models\YeuCauXinPhep::STATUS_ACCEPTED => 'bg-emerald-100 text-emerald-700',
        \App\Models\YeuCauXinPhep::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
        \App\Models\YeuCauXinPhep::STATUS_ACKNOWLEDGED => 'bg-cyan-100 text-cyan-700',
        default => 'bg-amber-100 text-amber-700',
    };
    $statusOptions = \App\Models\YeuCauXinPhep::statusOptions();
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <a href="{{ route('teacher.leave-requests.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại danh sách xin phép nghỉ
                </a>
                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Leave request</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Chi tiết yêu cầu xin phép nghỉ</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Xem thông tin học viên, buổi học bị ảnh hưởng và xử lý yêu cầu ngay tại đây.
                </p>
            </div>

            <div class="flex flex-col items-start gap-3">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ $leaveRequest->statusLabel() }}</span>
                @if ($leaveRequest->isPending())
                    <span class="inline-flex items-center gap-2 rounded-xl bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700">
                        <i class="fas fa-hourglass-half"></i>
                        Chờ giảng viên xử lý
                    </span>
                @endif
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.8fr)]">
        <section class="space-y-6">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-xl font-semibold text-slate-900">Thông tin học viên</h3>
                <div class="mt-5 grid gap-4 md:grid-cols-2 text-sm text-slate-600">
                    <p><strong>Học viên:</strong> {{ $leaveRequest->student?->displayName() ?? 'Chưa xác định' }}</p>
                    <p><strong>Email:</strong> {{ $leaveRequest->student?->email ?? 'Chưa xác định' }}</p>
                    <p><strong>Lớp học:</strong> {{ $leaveRequest->targetLabel() }}</p>
                    <p><strong>Môn học:</strong> {{ $leaveRequest->course?->subject?->name ?? $leaveRequest->classRoom?->subject?->name ?? 'Chưa xác định' }}</p>
                    <p><strong>Ngày xin phép:</strong> {{ $leaveRequest->attendance_date?->format('d/m/Y') ?? 'Chưa rõ' }}</p>
                    <p><strong>Lịch buổi học:</strong> {{ $leaveRequest->scheduleLabel() }}</p>
                </div>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-xl font-semibold text-slate-900">Nội dung yêu cầu</h3>
                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-700">
                    <p class="font-semibold text-slate-900">Lý do</p>
                    <p class="mt-2">{{ $leaveRequest->reason }}</p>
                </div>

                @if ($leaveRequest->note)
                    <div class="mt-4 rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-4 text-sm leading-6 text-cyan-900">
                        <p class="font-semibold">Ghi chú của học viên</p>
                        <p class="mt-2">{{ $leaveRequest->note }}</p>
                    </div>
                @endif

                @if ($leaveRequest->teacher_note)
                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm leading-6 text-slate-700">
                        <p class="font-semibold text-slate-900">Ghi chú xử lý</p>
                        <p class="mt-2">{{ $leaveRequest->teacher_note }}</p>
                    </div>
                @endif
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-xl font-semibold text-slate-900">Dấu mốc xử lý</h3>
                <div class="mt-5 grid gap-4 md:grid-cols-2 text-sm text-slate-600">
                    <p><strong>Gửi lúc:</strong> {{ optional($leaveRequest->created_at)->format('d/m/Y H:i') }}</p>
                    <p><strong>Xử lý lúc:</strong> {{ optional($leaveRequest->reviewed_at)->format('d/m/Y H:i') ?? 'Chưa có' }}</p>
                    <p><strong>Người xử lý:</strong> {{ $leaveRequest->reviewer?->displayName() ?? $leaveRequest->teacher?->displayName() ?? 'Chưa xác định' }}</p>
                    <p><strong>Trạng thái hiện tại:</strong> {{ $leaveRequest->statusLabel() }}</p>
                </div>
            </article>
        </section>

        <aside class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Xử lý yêu cầu</h3>
                @if ($leaveRequest->isPending())
                    <p class="mt-2 text-sm leading-6 text-slate-500">Bạn có thể chấp nhận, từ chối hoặc ghi nhận yêu cầu ngay bên dưới.</p>
                @else
                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-700">
                        <p class="font-semibold text-slate-900">Yêu cầu đã được xử lý</p>
                        <p class="mt-2">Trạng thái hiện tại: {{ $leaveRequest->statusLabel() }}</p>
                        <p class="mt-1">Người duyệt: {{ $leaveRequest->reviewer?->displayName() ?? $leaveRequest->teacher?->displayName() ?? 'Chưa xác định' }}</p>
                        <p class="mt-1">Thời gian xử lý: {{ optional($leaveRequest->reviewed_at)->format('d/m/Y H:i') ?? 'Chưa có' }}</p>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-amber-700">Bạn vẫn có thể thay đổi quyết định xử lý ở form bên dưới. Khi lưu lại, hệ thống sẽ đồng bộ lại điểm danh theo trạng thái mới.</p>
                @endif

                <form method="POST" action="{{ route('teacher.leave-requests.review', $leaveRequest) }}" class="mt-5 space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Kết quả xử lý</label>
                        <select name="status" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">
                            @foreach (\App\Models\YeuCauXinPhep::teacherReviewStatuses() as $status)
                                <option value="{{ $status }}" @selected(old('status', $leaveRequest->status) === $status)>{{ $statusOptions[$status] ?? $status }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Ghi chú giảng viên</label>
                        <textarea name="teacher_note" rows="5" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Dùng khi muốn nhắn thêm cho học viên hoặc giải thích lý do">{{ old('teacher_note', $leaveRequest->teacher_note) }}</textarea>
                        @error('teacher_note')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs leading-5 text-slate-500">Nếu chọn từ chối, vui lòng nhập ghi chú để học viên hiểu lý do xử lý.</p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                            <i class="fas fa-check"></i>
                            {{ $leaveRequest->isPending() ? 'Lưu xử lý' : 'Cập nhật xử lý' }}
                        </button>
                        <a href="{{ route('teacher.leave-requests.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                            Hủy
                        </a>
                    </div>
                </form>
            </div>

            <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-6">
                <p class="text-sm font-semibold text-slate-900">Lưu ý đồng bộ điểm danh</p>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Khi bạn chấp nhận hoặc ghi nhận yêu cầu, hệ thống sẽ tự chuyển trạng thái điểm danh của buổi đó sang <strong>Có phép</strong> nếu đã có bản ghi.
                </p>
            </div>
        </aside>
    </div>
</div>
@endsection
