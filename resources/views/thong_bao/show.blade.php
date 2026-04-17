@extends($layout)

@section('title', $pageTitle)
@section('eyebrow', $pageEyebrow)

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">Chi tiết thông báo</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ $notification->title }}</h2>
                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Thông báo này đã được đánh dấu là đã đọc. Nếu có liên kết đích, bạn có thể mở từ nút bên dưới.
                </p>
            </div>

            <a href="{{ $backRoute }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                <i class="fas fa-arrow-left"></i>
                <span>{{ $backLabel }}</span>
            </a>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">
                {{ $notification->type ?? 'info' }}
            </span>
            @if ($notification->is_read)
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Đã đọc</span>
            @else
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Chưa đọc</span>
            @endif
            <span class="text-xs text-slate-400">{{ optional($notification->created_at)->format('d/m/Y H:i') }}</span>
        </div>

        <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-5">
            <p class="text-sm leading-7 text-slate-700 whitespace-pre-line">{{ $notification->message }}</p>
        </div>

        @if (! empty($openUrl))
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ $openUrl }}" class="inline-flex items-center gap-2 rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    <i class="fas fa-arrow-up-right-from-square"></i>
                    <span>Xử lý ngay</span>
                </a>
                <a href="{{ $backRoute }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                    <i class="fas fa-list"></i>
                    <span>Quay lại hộp thông báo</span>
                </a>
            </div>
        @endif

    </section>
</div>
@endsection
