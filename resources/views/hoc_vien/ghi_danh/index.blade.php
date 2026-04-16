@extends('bo_cuc.hoc_vien')
@section('title', 'Đăng ký khóa học')
@section('eyebrow', 'Đăng ký khóa học')

@section('content')
@php
    $subjectList = collect($subjects->items());
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Khóa học công khai</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Đăng ký khóa học</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Chọn khóa học phù hợp, xem lớp đang mở và gửi yêu cầu học theo lịch mong muốn nếu chưa tìm được lớp phù hợp.
                </p>
            </div>

            <a href="{{ route('student.enroll.my-classes') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700">
                <i class="fas fa-list"></i>
                Lớp của tôi
            </a>
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($subjects as $subject)
            @php
                $openClasses = $subject->classRooms->filter(fn ($cl) => $cl->status === 'open' && ! $cl->isFull());
                $existingEnrollment = $subject->enrollments->sortByDesc('id')->first();
                $hasLockedEnrollment = $existingEnrollment?->hasCourseAccess();
            @endphp

            <article class="flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md">
                @if($subject->image_url)
                    <img src="{{ $subject->image_url }}" alt="{{ $subject->name }}" class="h-40 w-full object-cover">
                @else
                    <div class="flex h-40 w-full items-center justify-center bg-slate-50">
                        <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-cyan-50 text-cyan-700">
                            <i class="fas fa-book-open text-3xl"></i>
                        </div>
                    </div>
                @endif

                <div class="flex flex-1 flex-col p-5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="rounded-full bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-700">
                            {{ $subject->category?->name ?? 'Khóa học' }}
                        </span>
                        @if($existingEnrollment)
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $hasLockedEnrollment ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $existingEnrollment->displayStatusLabel() }}
                            </span>
                        @endif
                    </div>

                    <h3 class="mt-4 text-lg font-semibold leading-7 text-slate-900">{{ $subject->name }}</h3>
                    @if($subject->description)
                        <p class="mt-2 text-sm leading-6 text-slate-600 line-clamp-3">{{ $subject->description }}</p>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-600">
                        @if($subject->price)
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5">
                                <i class="fas fa-tag text-slate-400"></i>
                                {{ number_format($subject->price) }} VNĐ
                            </span>
                        @else
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700">
                                <i class="fas fa-gift"></i>
                                Miễn phí
                            </span>
                        @endif
                        @if($subject->duration)
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5">
                                <i class="fas fa-clock text-slate-400"></i>
                                {{ $subject->durationLabel() }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 rounded-2xl border {{ $openClasses->count() > 0 ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' }} px-4 py-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-medium {{ $openClasses->count() > 0 ? 'text-emerald-800' : 'text-rose-700' }}">
                                {{ $openClasses->count() > 0 ? $openClasses->count() . ' lớp cố định đang mở' : 'Hiện chưa có lớp cố định phù hợp' }}
                            </p>
                            <i class="fas fa-door-open {{ $openClasses->count() > 0 ? 'text-emerald-500' : 'text-rose-500' }}"></i>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-5">
                        @if($openClasses->count() > 0 && ! $hasLockedEnrollment)
                            <a href="{{ route('student.enroll.select', $subject) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                                <i class="fas fa-door-open"></i>
                                Chọn lớp cố định
                            </a>
                        @elseif($openClasses->count() > 0)
                            <a href="{{ route('student.enroll.my-classes') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                <i class="fas fa-users"></i>
                                Xem lớp đã ghi danh
                            </a>
                        @else
                            <span class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-3 text-sm font-medium text-slate-400">
                                Chưa có lớp cố định
                            </span>
                        @endif

                        <a href="{{ route('student.enroll.request-form', $subject) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
                            <i class="fas fa-paper-plane"></i>
                            {{ $hasLockedEnrollment ? 'Xem yêu cầu hiện tại' : ($existingEnrollment ? 'Cập nhật yêu cầu lịch học' : 'Gửi yêu cầu lịch học') }}
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center text-slate-500">
                <i class="fas fa-book text-4xl mb-3 block text-slate-300"></i>
                Chưa có khóa học nào.
            </div>
        @endforelse
    </section>

    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
        {{ $subjects->links() }}
    </div>
</div>
@endsection
