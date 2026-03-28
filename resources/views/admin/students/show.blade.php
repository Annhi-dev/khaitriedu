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
                <div class="flex justify-between border-b pb-2"><span class="text-slate-500">Đã hoàn thành</span><span class="font-semibold">{{ $student->enrollments->where('status', 'completed')->count() }}</span></div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-semibold mb-4">Đăng ký học gần đây</h2>
        @if($enrollments->count())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left text-xs font-medium">Khóa học</th><th class="px-4 py-3 text-left text-xs font-medium">Lớp</th><th class="px-4 py-3 text-left text-xs font-medium">Trạng thái</th><th class="px-4 py-3 text-left text-xs font-medium">Lịch học</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($enrollments as $enrollment)
                    <tr><td class="px-4 py-3">{{ $enrollment->subject?->name ?? 'Chưa xác định' }}</td><td class="px-4 py-3">{{ $enrollment->course?->title ?? 'Chưa xếp lớp' }}</td><td class="px-4 py-3"><x-admin.badge :type="match($enrollment->status) {'approved'=>'info','scheduled'=>'success','active'=>'success','completed'=>'default','rejected'=>'danger', default=>'warning'}" :text="$enrollment->statusLabel()" /></td><td class="px-4 py-3">{{ $enrollment->schedule ?: 'Chưa có' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else <p class="text-slate-500 text-center py-8">Chưa có đăng ký học nào.</p> @endif
    </div>
</div>
@endsection