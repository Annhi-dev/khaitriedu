@props(['session'])

@if($session->has('status'))
    <div class="mb-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-start gap-3 animate-fade-in-down">
        <i class="fas fa-check-circle mt-0.5"></i>
        <span>{{ $session->get('status') }}</span>
    </div>
@endif
@if($session->has('error'))
    <div class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 flex items-start gap-3 animate-fade-in-down">
        <i class="fas fa-exclamation-circle mt-0.5"></i>
        <span>{{ $session->get('error') }}</span>
    </div>
@endif
@if($session->has('warning'))
    <div class="mb-4 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 flex items-start gap-3 animate-fade-in-down">
        <i class="fas fa-triangle-exclamation mt-0.5"></i>
        <span>{{ $session->get('warning') }}</span>
    </div>
@endif
@if($errors->any())
    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-700 animate-fade-in-down shadow-sm">
        <div class="flex items-start gap-3">
            <i class="fas fa-circle-exclamation mt-0.5"></i>
            <div class="min-w-0">
                <p class="font-semibold">Dữ liệu chưa hợp lệ</p>
                <p class="mt-1 text-sm text-rose-600">Một hoặc nhiều trường chưa đúng định dạng. Các lỗi chi tiết nằm bên dưới.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
