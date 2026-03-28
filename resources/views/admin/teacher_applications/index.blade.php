@extends('layouts.admin')
@section('title', 'Quản lý ứng tuyển giảng viên')
@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Quản lý ứng tuyển giảng viên" subtitle="Duyệt hồ sơ ứng tuyển" />

    <x-admin.filter-bar route="{{ route('admin.teacher-applications') }}" searchPlaceholder="Tên, email, kinh nghiệm..." :statuses="['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', 'needs_revision' => 'Cần bổ sung']" />

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Ứng viên</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Liên hệ</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trạng thái</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Phản hồi admin</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Ngày nộp</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-500 uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($applications as $app)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-4">
                            <div class="font-medium">{{ $app->name }}</div>
                            <div class="text-xs text-slate-500">{{ Str::limit($app->experience, 50) }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div>{{ $app->email }}</div>
                            <div class="text-xs text-slate-500">{{ $app->phone ?: 'Chưa có số' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <x-admin.badge :type="match($app->status) {'pending'=>'warning','approved'=>'success','rejected'=>'danger','needs_revision'=>'info', default=>'default'}" :text="$app->statusLabel()" />
                        </td>
                        <td class="px-5 py-4">{{ Str::limit($app->admin_note ?: ($app->rejection_reason ?: 'Chưa có'), 60) }}</td>
                        <td class="px-5 py-4">{{ $app->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.teacher-applications.show', $app) }}" class="inline-flex items-center px-3 py-1 rounded-xl bg-cyan-50 text-cyan-700 text-xs font-medium hover:bg-cyan-100 transition">Chi tiết</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">Không có hồ sơ ứng tuyển nào</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-200">
            {{ $applications->links() }}
        </div>
    </div>
</div>
@endsection