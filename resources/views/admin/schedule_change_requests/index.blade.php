@extends('layouts.admin')
@section('title', 'Yêu cầu đổi lịch')
@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Yêu cầu đổi lịch" subtitle="Các đề xuất đổi lịch từ giảng viên đang chờ admin xử lý" />

    <x-admin.filter-bar route="{{ route('admin.schedule-change-requests.index') }}" searchPlaceholder="Giảng viên, lớp học, lý do..." :statuses="['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối']" />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($requests as $requestItem)
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex justify-between items-start gap-4">
                    <div>
                        <h3 class="font-semibold text-slate-800">{{ $requestItem->targetTitle() }}</h3>
                        <p class="text-sm text-slate-500">{{ $requestItem->subjectName() }}</p>
                        <p class="text-sm text-slate-500">Giảng viên: {{ $requestItem->teacher?->displayName() }}</p>
                    </div>
                    <x-admin.badge :type="match($requestItem->status) {'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'default'}" :text="$requestItem->statusLabel()" />
                </div>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div><span class="text-slate-500">Lịch hiện tại:</span> {{ $requestItem->currentScheduleLabel() }}</div>
                    <div><span class="text-slate-500">Lịch đề xuất:</span> {{ $requestItem->requestedScheduleLabel() }}</div>
                </div>
                @if ($requestItem->isClassScheduleRequest())
                    <div class="mt-2 text-sm">
                        <span class="text-slate-500">Phòng đề xuất:</span> {{ $requestItem->requestedRoomLabel() }}
                    </div>
                @endif
                <div class="mt-4 p-3 bg-slate-50 rounded-xl text-sm">
                    <span class="font-medium">Lý do:</span> {{ \Illuminate\Support\Str::limit($requestItem->reason, 100) }}
                </div>
                <div class="mt-4 flex justify-end">
                    <a href="{{ route('admin.schedule-change-requests.show', $requestItem) }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-cyan-600 text-white text-sm font-semibold hover:bg-cyan-700 transition">Xử lý</a>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300">
                <p class="text-slate-500">Không có yêu cầu đổi lịch nào.</p>
            </div>
        @endforelse
    </div>

    @if($requests->hasPages())
        <div>{{ $requests->links() }}</div>
    @endif
</div>
@endsection
