@extends('bo_cuc.giao_vien')

@section('title', 'Tạo bài kiểm tra')
@section('eyebrow', 'Teacher Tests')

@section('content')
<div class="space-y-6">
    <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <a href="{{ route('teacher.tests.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-700 hover:text-cyan-800">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại danh sách
                </a>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Tạo bài kiểm tra mới</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Thiết lập tiêu đề, đích áp dụng và các câu hỏi trắc nghiệm cho lớp mình phụ trách.</p>
            </div>
        </div>
    </section>

    @include('giao_vien.bai_kiem_tra.partials.form', [
        'quiz' => $quiz,
        'formOptions' => $formOptions,
        'questionRows' => $questionRows,
        'selectedClassRoom' => $selectedClassRoom ?? null,
        'statusOptions' => $statusOptions,
        'formAction' => route('teacher.tests.store'),
        'formMethod' => 'POST',
        'submitLabel' => 'Tạo bài kiểm tra',
    ])
</div>
@endsection
