@extends('layouts.student')
@section('title', 'Lop hoc cua toi')
@section('eyebrow', 'My Classes')
@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-dark">Lop hoc cua toi</h1>
            <p class="text-sm text-gray-600">Theo doi ca lop co dinh da ghi danh, lop dang cho mo va cac yeu cau lich hoc dang cho admin xu ly.</p>
        </div>
        <a href="{{ route('student.enroll.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
            <i class="fas fa-plus mr-1"></i> Dang ky them
        </a>
    </div>

    @forelse($enrollments as $enrollment)
        @php
            $class = $enrollment->classRoom;
            $course = $enrollment->course;
            $preferredDays = is_array($enrollment->preferred_days)
                ? $enrollment->preferred_days
                : ((is_string($enrollment->preferred_days) && $enrollment->preferred_days !== '') ? (json_decode($enrollment->preferred_days, true) ?: []) : []);
            $waitingOpen = $course?->isPendingOpen();
            $badge = $waitingOpen
                ? 'bg-amber-100 text-amber-700'
                : match($enrollment->status) {
                    'pending'   => 'bg-amber-100 text-amber-700',
                    'approved'  => 'bg-blue-100 text-blue-700',
                    'enrolled'  => 'bg-emerald-100 text-emerald-700',
                    'scheduled' => 'bg-emerald-100 text-emerald-700',
                    'active'    => 'bg-green-100 text-green-700',
                    'completed' => 'bg-slate-100 text-slate-600',
                    'rejected'  => 'bg-red-100 text-red-600',
                    default     => 'bg-gray-100 text-gray-600',
                };
        @endphp
        <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="font-semibold text-gray-900">{{ $enrollment->subject->name ?? '—' }}</h3>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">
                            {{ $waitingOpen ? $course->statusLabel() : $enrollment->statusLabel() }}
                        </span>
                    </div>

                    @if($class)
                        <div class="mt-3 grid gap-2 text-sm text-gray-600 sm:grid-cols-2">
                            <div><span class="font-medium text-gray-500">Lop:</span> {{ $class->displayName() }}</div>
                            <div><span class="font-medium text-gray-500">Giang vien:</span> {{ $class->teacher?->name ?? 'Chua phan cong' }}</div>
                            <div><span class="font-medium text-gray-500">Phong:</span> {{ $class->room ? $class->room->name . ' (' . $class->room->code . ')' : 'Chua chon' }}</div>
                            <div><span class="font-medium text-gray-500">Ngay bat dau:</span> {{ $class->start_date?->format('d/m/Y') ?? 'Chua xac dinh' }}</div>
                        </div>
                    @elseif($course && $waitingOpen)
                        <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                            <p class="text-sm font-medium text-amber-800">Ban da duoc ghep vao lop cho mo</p>
                            <div class="mt-3 grid gap-2 text-sm text-amber-900 sm:grid-cols-2">
                                <div><span class="font-medium text-amber-700">Lop:</span> {{ $course->title }}</div>
                                <div><span class="font-medium text-amber-700">Giang vien:</span> {{ $course->teacher?->name ?? 'Chua phan cong' }}</div>
                                <div><span class="font-medium text-amber-700">Khung gio:</span> {{ $course->start_time }} - {{ $course->end_time }}</div>
                                <div><span class="font-medium text-amber-700">Ngay hoc:</span> {{ $course->meetingDaysLabel() }}</div>
                            </div>
                            <p class="mt-3 text-sm text-amber-800">Admin se chot ngay bat dau va ngay ket thuc khi lop du toi thieu 5 hoc vien.</p>
                        </div>
                    @else
                        <div class="mt-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-medium text-slate-700">Dang ky theo yeu cau lich hoc rieng</p>
                            <p class="mt-1 text-sm text-slate-500">Admin chua xep ban vao lop co dinh. Ho so nay se duoc dung de xem lich ranh va ghep lop phu hop.</p>

                            <div class="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                                <div>
                                    <span class="font-medium text-slate-500">Khung gio mong muon:</span>
                                    {{ $enrollment->start_time && $enrollment->end_time ? $enrollment->start_time . ' - ' . $enrollment->end_time : 'Chua cung cap' }}
                                </div>
                                <div>
                                    <span class="font-medium text-slate-500">Ngay co the hoc:</span>
                                    {{ !empty($preferredDays) ? collect($preferredDays)->map(fn ($day) => \App\Models\ClassSchedule::$dayOptions[$day] ?? $day)->implode(', ') : 'Chua cung cap' }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($course && ! $waitingOpen && $course->formattedSchedule())
                        <div class="mt-3 text-sm text-slate-600">
                            <span class="font-medium text-slate-500">Lich hoc:</span> {{ $course->formattedSchedule() }}
                        </div>
                    @endif

                    @if($enrollment->note)
                        <div class="mt-3 rounded-2xl border {{ $enrollment->status === 'rejected' ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-blue-200 bg-blue-50 text-blue-700' }} px-4 py-3 text-sm">
                            <strong>{{ $enrollment->status === 'rejected' ? 'Ly do tu choi' : 'Ghi chu tu admin' }}:</strong>
                            {{ $enrollment->note }}
                        </div>
                    @endif
                </div>

                <div class="shrink-0 text-right text-xs text-gray-400">
                    Gui yeu cau<br>{{ ($enrollment->submitted_at ?? $enrollment->created_at)?->format('d/m/Y') }}
                </div>
            </div>

            @if(!$class && ! $course && ! $enrollment->hasCourseAccess())
                <div class="mt-4 flex flex-wrap gap-3 border-t border-slate-100 pt-4">
                    <a href="{{ route('student.enroll.request-form', $enrollment->subject) }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
                        <i class="fas fa-pen"></i>
                        Cap nhat yeu cau lich hoc
                    </a>

                    @if($enrollment->subject)
                        <a href="{{ route('student.enroll.select', $enrollment->subject) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <i class="fas fa-door-open"></i>
                            Xem lop co dinh dang mo
                        </a>
                    @endif
                </div>
            @endif
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center text-gray-400">
            <i class="fas fa-inbox mb-3 block text-4xl"></i>
            Ban chua dang ky lop hoc nao.
            <div class="mt-4">
                <a href="{{ route('student.enroll.index') }}" class="rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    Dang ky ngay
                </a>
            </div>
        </div>
    @endforelse

    <div class="mt-4">{{ $enrollments->links() }}</div>
</div>
@endsection
