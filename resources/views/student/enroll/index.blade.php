@extends('layouts.student')
@section('title', 'Đăng ký khóa học')
@section('eyebrow', 'Course Registration')
@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-dark">Đăng ký khóa học</h1>
        </div>
        <a href="{{ route('student.enroll.my-classes') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-list mr-1"></i> Lớp của tôi
        </a>
    </div>

    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        @forelse($subjects as $subject)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm flex flex-col">
                @if($subject->image_url)
                    <img src="{{ $subject->image_url }}" alt="{{ $subject->name }}" class="mb-4 h-36 w-full rounded-xl object-cover">
                @else
                    <div class="mb-4 h-36 w-full rounded-xl bg-gradient-to-br from-cyan-50 to-blue-100 flex items-center justify-center">
                        <i class="fas fa-book-open text-3xl text-cyan-400"></i>
                    </div>
                @endif
                <div class="flex-1">
                    <div class="mb-1 text-xs font-medium text-cyan-600 uppercase tracking-wide">{{ $subject->category?->name ?? 'Khóa học' }}</div>
                    <h3 class="font-semibold text-gray-900 text-base leading-snug">{{ $subject->name }}</h3>
                    @if($subject->description)
                        <p class="mt-1.5 text-sm text-gray-500 line-clamp-2">{{ $subject->description }}</p>
                    @endif
                    <div class="mt-3 flex flex-wrap gap-3 text-sm text-gray-600">
                        @if($subject->price)
                            <span><i class="fas fa-tag mr-1 text-gray-400"></i>{{ number_format($subject->price) }} VNĐ</span>
                        @else
                            <span class="text-green-600 font-medium"><i class="fas fa-gift mr-1"></i>Miễn phí</span>
                        @endif
                        @if($subject->duration)
                            <span><i class="fas fa-clock mr-1 text-gray-400"></i>{{ $subject->durationLabel() }}</span>
                        @endif
                    </div>

                    
                    @php
                        $openClasses = $subject->classRooms->filter(fn ($cl) => $cl->status === 'open' && ! $cl->isFull());
                        $existingEnrollment = $subject->enrollments->sortByDesc('id')->first();
                        $hasLockedEnrollment = $existingEnrollment?->hasCourseAccess();
                    @endphp
                    <div class="mt-2 text-xs {{ $openClasses->count() > 0 ? 'text-green-600' : 'text-red-500' }}">
                        <i class="fas fa-door-open mr-1"></i>
                        {{ $openClasses->count() > 0 ? $openClasses->count() . ' lớp cố định đang mở' : 'Hiện chưa có lớp cố định phù hợp' }}
                    </div>

                    @if($existingEnrollment)
                        <div class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $hasLockedEnrollment ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $existingEnrollment->statusLabel() }}
                        </div>
                    @endif
                </div>
                <div class="mt-4 space-y-2 pt-4 border-t border-gray-100">
                    @if($openClasses->count() > 0 && ! $hasLockedEnrollment)
                        <a href="{{ route('student.enroll.select', $subject) }}"
                            class="block w-full rounded-xl bg-cyan-600 py-2.5 text-center text-sm font-semibold text-white hover:bg-cyan-700 transition">
                            Chọn lớp cố định
                        </a>
                    @elseif($openClasses->count() > 0)
                        <a href="{{ route('student.enroll.my-classes') }}"
                            class="block w-full rounded-xl bg-emerald-600 py-2.5 text-center text-sm font-semibold text-white hover:bg-emerald-700 transition">
                            Xem lớp đã ghi danh
                        </a>
                    @else
                        <span class="block w-full rounded-xl bg-gray-100 py-2.5 text-center text-sm font-medium text-gray-400 cursor-not-allowed">
                            Chưa có lớp cố định
                        </span>
                    @endif

                    <a href="{{ route('student.enroll.request-form', $subject) }}"
                        class="block w-full rounded-xl border border-cyan-200 bg-cyan-50 py-2.5 text-center text-sm font-semibold text-cyan-700 hover:bg-cyan-100 transition">
                        {{ $hasLockedEnrollment ? 'Xem yêu cầu hiện tại' : ($existingEnrollment ? 'Cập nhật yêu cầu lịch học' : 'Gửi yêu cầu lịch học') }}
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-16 text-center text-gray-400">
                <i class="fas fa-book text-4xl mb-3 block"></i>
                Chưa có khóa học nào.
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $subjects->links() }}</div>
</div>
@endsection
