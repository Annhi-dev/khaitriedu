@extends('layouts.admin')
@section('title', 'Chi tiết học viên')
@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Chi tiết học viên" subtitle="Thông tin cá nhân và hoạt động học tập">
        <div class="flex gap-2">
            <a href="{{ route('admin.students.index') }}" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition">Quay lại</a>
            <a href="{{ route('admin.students.edit', $student) }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Sửa thông tin</a>
            @if($student->isLocked())
                <form method="post" action="{{ route('admin.students.unlock', $student) }}">
                    @csrf
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Mở khóa</button>
                </form>
            @else
                <form method="post" action="{{ route('admin.students.lock', $student) }}" onsubmit="return confirm('Khóa tài khoản này?')">
                    @csrf
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Khóa tài khoản</button>
                </form>
            @endif
        </div>
    </x-admin.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="text-lg font-semibold mb-4">Thông tin cá nhân</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><p class="text-sm text-slate-500">Họ và tên</p><p class="font-medium">{{ $student->name }}</p></div>
                <div><p class="text-sm text-slate-500">Tên đăng nhập</p><p class="font-medium">{{ $student->username }}</p></div>
                <div><p class="text-sm text-slate-500">Email</p><p class="font-medium">{{ $student->email }}</p></div>
                <div><p class="text-sm text-slate-500">Số điện thoại</p><p class="font-medium">{{ $student->phone ?: 'Chưa cập nhật' }}</p></div>
                <div><p class="text-sm text-slate-500">Trạng thái</p><x-admin.badge :type="match($student->status) {'active'=>'success','inactive'=>'warning','locked'=>'danger', default=>'default'}" :text="$student->statusLabel()" /></div>
                <div><p class="text-sm text-slate-500">Ngày tạo</p><p class="font-medium">{{ optional($student->created_at)->format('d/m/Y H:i') }}</p></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="text-lg font-semibold mb-4">Thống kê</h2>
            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2"><span class="text-slate-500">Đăng ký học</span><span class="font-semibold">{{ $student->enrollments_count }}</span></div>
                <div class="flex justify-between border-b pb-2"><span class="text-slate-500">Đã hoàn thành</span><span class="font-semibold">{{ $enrollments->where('status', \App\Models\Enrollment::STATUS_COMPLETED)->count() }}</span></div>
                <div class="flex justify-between border-b pb-2"><span class="text-slate-500">Lớp đang theo học</span><span class="font-semibold">{{ $currentSchedules->count() }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Đánh giá gần đây</span><span class="font-semibold">{{ $reviews->count() }}</span></div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-semibold mb-4">Đăng ký học gần đây</h2>
        @if($enrollments->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium">Khóa học</th>
                            <th class="px-4 py-3 text-left text-xs font-medium">Lớp</th>
                            <th class="px-4 py-3 text-left text-xs font-medium">Trạng thái</th>
                            <th class="px-4 py-3 text-left text-xs font-medium">Lịch học</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($enrollments as $enrollment)
                            <tr>
                                <td class="px-4 py-3">{{ $enrollment->subject?->name ?? 'Chưa xác định' }}</td>
                                <td class="px-4 py-3">{{ $enrollment->course?->title ?? 'Chưa xếp lớp' }}</td>
                                <td class="px-4 py-3"><x-admin.badge :type="match($enrollment->status) {'approved'=>'info','scheduled'=>'success','active'=>'success','completed'=>'default','rejected'=>'danger', default=>'warning'}" :text="$enrollment->statusLabel()" /></td>
                                <td class="px-4 py-3">{{ $enrollment->schedule ?: 'Chưa có' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-slate-500 text-center py-8">Chưa có đăng ký học nào.</p>
        @endif
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-semibold">Kết quả học tập</h2>
                    <p class="text-sm text-slate-500">Điểm số và nhận xét gần nhất của học viên.</p>
                </div>
                <span class="text-sm font-medium text-slate-500">{{ $grades->count() }} bản ghi</span>
            </div>

            @if($grades->isNotEmpty())
                <div class="space-y-3">
                    @foreach($grades as $grade)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $grade->enrollment?->course?->title ?? 'Khóa học chưa xác định' }}</p>
                                    <p class="text-sm text-slate-500">
                                        {{ $grade->module?->title ?? 'Đánh giá tổng kết' }}
                                        @if($grade->feedback)
                                            • {{ $grade->feedback }}
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-semibold text-cyan-700">{{ rtrim(rtrim(number_format((float) $grade->score, 1), '0'), '.') }}</p>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">{{ $grade->grade ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-slate-500 text-center py-8">Chưa có dữ liệu điểm số.</p>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-semibold">Phản hồi khóa học</h2>
                    <p class="text-sm text-slate-500">Tóm tắt những đánh giá và cảm nhận gần đây.</p>
                </div>
                <span class="text-sm font-medium text-slate-500">{{ $reviews->count() }} đánh giá</span>
            </div>

            @if($reviews->isNotEmpty())
                <div class="space-y-3">
                    @foreach($reviews as $review)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $review->course?->title ?? 'Khóa học chưa xác định' }}</p>
                                    <p class="text-sm text-slate-500 mt-1">{{ $review->comment ?: 'Học viên chưa để lại nhận xét chi tiết.' }}</p>
                                </div>
                                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">{{ number_format((float) $review->rating, 1) }}/5</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-slate-500 text-center py-8">Chưa có phản hồi nào từ học viên.</p>
            @endif
        </div>
    </div>
</div>
@endsection