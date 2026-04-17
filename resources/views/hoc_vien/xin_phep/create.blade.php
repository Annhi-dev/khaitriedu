@extends('bo_cuc.hoc_vien')

@section('title', 'Gửi xin phép nghỉ')
@section('eyebrow', 'Hỗ trợ học tập')

@section('content')
<div class="grid gap-6 xl:grid-cols-[1fr_0.7fr]">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Tạo yêu cầu</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Xin phép nghỉ với giảng viên</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Hãy chọn lớp học, ngày nghỉ và mô tả ngắn gọn lý do để giảng viên có đủ thông tin xử lý.
                </p>
            </div>
            <a href="{{ route('student.leave-requests.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                <i class="fas fa-arrow-left"></i>
                Danh sách
            </a>
        </div>

        <form method="POST" action="{{ route('student.leave-requests.store') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Lớp học</label>
                <select name="class_room_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
                    <option value="">Chọn lớp học</option>
                    @foreach($availableEnrollments as $enrollment)
                        <option value="{{ $enrollment->classRoom->id }}" @selected(old('class_room_id') == $enrollment->classRoom->id)>
                            {{ $enrollment->classRoom->displayName() }} - {{ $enrollment->course?->formattedSchedule() ?? 'Chưa có lịch' }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-slate-500">Chỉ hiển thị các lớp bạn đang học và có giảng viên phụ trách.</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Ngày xin phép nghỉ</label>
                <input type="date" name="attendance_date" value="{{ old('attendance_date', now()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Lý do xin phép</label>
                <textarea name="reason" rows="4" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100" placeholder="Ví dụ: Em bị sốt, xin phép nghỉ buổi học ngày ...">{{ old('reason') }}</textarea>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Ghi chú thêm</label>
                <textarea name="note" rows="3" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100" placeholder="Thông tin thêm nếu cần">{{ old('note') }}</textarea>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    <i class="fas fa-paper-plane"></i>
                    Gửi yêu cầu
                </button>
                <a href="{{ route('student.leave-requests.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </section>

    <aside class="space-y-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Lưu ý nhanh</h3>
            <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                <li>• Chọn đúng lớp học bạn đang theo học.</li>
                <li>• Ngày xin phép nên khớp với buổi học cụ thể.</li>
                <li>• Giảng viên sẽ thấy yêu cầu ngay trong khu vực của họ.</li>
            </ul>
        </div>

        <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-6">
            <p class="text-sm font-semibold text-slate-900">Bạn muốn xem lại yêu cầu cũ?</p>
            <p class="mt-2 text-sm leading-6 text-slate-600">Toàn bộ trạng thái xin phép nghỉ được tổng hợp ở trang danh sách.</p>
            <a href="{{ route('student.leave-requests.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-700 ring-1 ring-cyan-200 transition hover:bg-cyan-50">
                Mở danh sách
            </a>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Yêu cầu gần đây</h3>
            <div class="mt-4 space-y-3">
                @forelse($recentRequests as $recentRequest)
                    <a href="{{ route('student.leave-requests.show', $recentRequest) }}" class="block rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-cyan-200 hover:bg-cyan-50/50">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-slate-900">{{ $recentRequest->targetLabel() }}</p>
                                <p class="mt-2 text-sm text-slate-500">{{ $recentRequest->attendance_date?->format('d/m/Y') ?? 'Chưa rõ' }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $recentRequest->isResolved() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $recentRequest->statusLabel() }}
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="text-sm leading-6 text-slate-500">Chưa có yêu cầu xin phép nào gần đây.</p>
                @endforelse
            </div>
        </div>
    </aside>
</div>
@endsection
