@extends('bo_cuc.ung_dung')
@section('title', 'Chứng chỉ của tôi')
@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Chứng chỉ của tôi</h1>
    @if($certificates->isEmpty())
        <div class="bg-blue-50 border border-blue-200 p-6 rounded-lg text-center">Bạn chưa có chứng chỉ nào.</div>
    @else
        <div class="grid md:grid-cols-2 gap-6">
            @foreach($certificates as $cert)
                <div class="bg-white border border-gray-200 p-5 rounded-lg">
                    <h2 class="text-xl font-semibold mb-2">{{ $cert->course->title ?? 'Khóa học' }}</h2>
                    <p class="text-sm text-gray-500">Mã chứng chỉ: {{ $cert->certificate_number }}</p>
                    <p class="text-sm text-gray-500">Điểm: {{ $cert->score }}%</p>
                    <p class="text-sm text-gray-500">Ngày cấp: {{ $cert->issued_at ? $cert->issued_at->format('d/m/Y') : '-' }}</p>
                    <p class="text-sm text-gray-500">Trạng thái: {{ ucfirst($cert->status) }}</p>
                    <a href="{{ route('certificates.show', $cert->id) }}" class="inline-block mt-3 text-primary hover:underline">Xem chi tiết</a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection