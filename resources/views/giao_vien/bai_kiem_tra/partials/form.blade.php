@php
    $safeValue = static function ($value, $default = '') {
        return is_scalar($value) || $value === null ? (string) $value : (string) $default;
    };

    $quiz = $quiz ?? null;
    $questionRows = $questionRows ?? [];
    $formOptions = $formOptions ?? [];
    $selectedClassRoom = $selectedClassRoom ?? null;
    $statusOptions = $statusOptions ?? [
        \App\Models\Quiz::STATUS_DRAFT => 'Nháp',
        \App\Models\Quiz::STATUS_PUBLISHED => 'Công khai',
    ];

    $rows = old('questions');

    if (! is_array($rows) || $rows === []) {
        $rows = $questionRows;
    }

    $selectedCourseId = $safeValue(old('course_id', $quiz?->course_id));
    $selectedSubjectId = $safeValue(old('subject_id', $quiz?->subject_id));
    $selectedClassRoomId = $safeValue(old('lop_hoc_id', $quiz?->lop_hoc_id));
    if ($selectedClassRoom && ! old('lop_hoc_id')) {
        $selectedClassRoomId = $selectedClassRoom->id;
    }
@endphp

<form method="POST" action="{{ $formAction }}" class="space-y-6">
    @csrf
    @if (($formMethod ?? 'POST') !== 'POST')
        @method($formMethod)
    @endif

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Thông tin bài kiểm tra</h2>
                <p class="mt-1 text-sm text-slate-500">Tạo bài kiểm tra trắc nghiệm cho lớp, khóa hoặc môn học.</p>
            </div>
        </div>

        <div class="mt-6 grid gap-5 lg:grid-cols-2">
            <div class="lg:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Tên bài kiểm tra</label>
                <input type="text" name="title" value="{{ $safeValue(old('title', $quiz?->title ?? ($selectedClassRoom ? 'Bài kiểm tra ' . $selectedClassRoom->displayName() : '')) ) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Ví dụ: Kiểm tra giữa kỳ 1">
            </div>

            <div class="lg:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Mô tả ngắn</label>
                <textarea name="description" rows="4" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Nội dung, phạm vi hoặc ghi chú cho học viên">{{ $safeValue(old('description', $quiz?->description)) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700">Thời gian làm bài</label>
                <input type="number" min="1" max="480" name="duration_minutes" value="{{ $safeValue(old('duration_minutes', $quiz?->duration_minutes ?? 15), 15) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700">Điểm tối đa</label>
                <input type="number" min="1" step="0.01" name="total_score" value="{{ $safeValue(old('total_score', $quiz?->total_score ?? 10), 10) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700">Trạng thái</label>
                <select name="status" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">
                    @foreach($statusOptions as $value => $label)
                        @php $statusValue = $safeValue(old('status', $quiz?->status ?? \App\Models\Quiz::STATUS_DRAFT), \App\Models\Quiz::STATUS_DRAFT); @endphp
                        <option value="{{ $value }}" @selected($statusValue === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Gán cho lớp / khóa / môn</h2>
                <p class="mt-1 text-sm text-slate-500">Chỉ cần chọn một đích chính. Hệ thống sẽ tự gắn bài kiểm tra vào bài học phù hợp để học viên nhìn thấy.</p>
            </div>
        </div>

        @if($selectedClassRoom)
            <div class="mt-6 rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-4 text-sm text-cyan-900">
                <p class="font-semibold">Đang tạo bài kiểm tra cho lớp này</p>
                <p class="mt-1">{{ $selectedClassRoom->displayName() }}</p>
                <p class="mt-1 text-cyan-700">Khóa học và môn học sẽ được tự lấy từ lớp đã chọn.</p>
                <input type="hidden" name="lop_hoc_id" value="{{ $selectedClassRoom->id }}">
            </div>
        @else
            <div class="mt-6 grid gap-5 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Lớp học</label>
                    <select name="lop_hoc_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">
                        <option value="">Không chọn</option>
                        @foreach(($formOptions['classRooms'] ?? collect()) as $classRoom)
                            <option value="{{ $classRoom->id }}" @selected((string) $selectedClassRoomId === (string) $classRoom->id)>{{ $classRoom->displayName() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Khóa học</label>
                    <select name="course_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">
                        <option value="">Không chọn</option>
                        @foreach(($formOptions['courses'] ?? collect()) as $course)
                            <option value="{{ $course->id }}" @selected((string) $selectedCourseId === (string) $course->id)>{{ $course->title }} @if($course->subject) - {{ $course->subject->name }} @endif</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Môn học</label>
                    <select name="subject_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">
                        <option value="">Không chọn</option>
                        @foreach(($formOptions['subjects'] ?? collect()) as $subject)
                            <option value="{{ $subject->id }}" @selected((string) $selectedSubjectId === (string) $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Chỉ nên chọn <strong>một</strong> trong ba mục trên. Nếu chọn nhiều mục, hệ thống sẽ ưu tiên lớp học, rồi đến khóa học, rồi đến môn học.
            </div>
        @endif
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Danh sách câu hỏi</h2>
                <p class="mt-1 text-sm text-slate-500">Mỗi câu hỏi có 4 lựa chọn A, B, C, D và 1 đáp án đúng.</p>
            </div>
            <button type="button" onclick="window.addQuestion()" class="inline-flex items-center gap-2 rounded-2xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                <i class="fas fa-plus"></i>
                Thêm câu hỏi
            </button>
        </div>

        <div class="mt-6 space-y-5" id="question-list">
            @forelse($rows as $index => $row)
                @php
                    $options = $row['options'] ?? [];
                    $normalizedOptions = collect($options)->map(function ($option) {
                        return is_array($option) ? ($option['value'] ?? '') : $option;
                    })->all();
                    $optionIds = $row['option_ids'] ?? [];
                    $correctOption = $safeValue(old("questions.$index.correct_option", $row['correct_option'] ?? 'A'), 'A');
                @endphp
                <article class="question-card rounded-3xl border border-slate-200 bg-slate-50 p-5" data-question-card>
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">Câu hỏi {{ $loop->iteration }}</p>
                            <p class="mt-1 text-sm text-slate-500">Mẫu câu hỏi trắc nghiệm.</p>
                        </div>
                        <button type="button" class="rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50" onclick="window.removeQuestion(this)">
                            Xóa
                        </button>
                    </div>

                    <div class="mt-5 grid gap-4">
                        <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $safeValue(old("questions.$index.id", $row['id'] ?? '')) }}">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Nội dung câu hỏi</label>
                            <textarea name="questions[{{ $index }}][question]" rows="3" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Nhập câu hỏi">{{ $safeValue(old("questions.$index.question", $row['question'] ?? '')) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Ghi chú / mô tả</label>
                            <textarea name="questions[{{ $index }}][description]" rows="2" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Tuỳ chọn">{{ $safeValue(old("questions.$index.description", $row['description'] ?? '')) }}</textarea>
                        </div>

                        <div class="grid gap-4 md:grid-cols-[180px_minmax(0,1fr)]">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Điểm câu hỏi</label>
                                <input type="number" min="0.1" step="0.1" name="questions[{{ $index }}][points]" value="{{ $safeValue(old("questions.$index.points", $row['points'] ?? 1), 1) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Đáp án đúng</label>
                                <select name="questions[{{ $index }}][correct_option]" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">
                                    @foreach(['A', 'B', 'C', 'D'] as $letter)
                                        <option value="{{ $letter }}" @selected($correctOption === $letter)>{{ $letter }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach(['A', 'B', 'C', 'D'] as $letter)
                                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <label class="text-sm font-semibold text-slate-700">Đáp án {{ $letter }}</label>
                                        <input type="hidden" name="questions[{{ $index }}][option_ids][{{ $letter }}]" value="{{ $safeValue(old("questions.$index.option_ids.$letter", $optionIds[$letter] ?? '')) }}">
                                    </div>
                                    <input type="text" name="questions[{{ $index }}][options][{{ $letter }}]" value="{{ $safeValue(old("questions.$index.options.$letter", $normalizedOptions[$letter] ?? '')) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Nhập đáp án {{ $letter }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            @empty
                <article class="question-card rounded-3xl border border-slate-200 bg-slate-50 p-5" data-question-card>
                    <p class="text-sm text-slate-500">Chưa có câu hỏi nào.</p>
                </article>
            @endforelse
        </div>

        @error('questions')
            <p class="mt-4 text-sm text-rose-600">{{ $message }}</p>
        @enderror

        <template id="question-template">
            <article class="question-card rounded-3xl border border-slate-200 bg-slate-50 p-5" data-question-card>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">Câu hỏi mới</p>
                        <p class="mt-1 text-sm text-slate-500">Câu hỏi trắc nghiệm.</p>
                    </div>
                    <button type="button" class="rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50" onclick="window.removeQuestion(this)">
                        Xóa
                    </button>
                </div>

                <div class="mt-5 grid gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Nội dung câu hỏi</label>
                        <textarea name="questions[__INDEX__][question]" rows="3" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Nhập câu hỏi"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Ghi chú / mô tả</label>
                        <textarea name="questions[__INDEX__][description]" rows="2" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Tuỳ chọn"></textarea>
                    </div>

                    <div class="grid gap-4 md:grid-cols-[180px_minmax(0,1fr)]">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Điểm câu hỏi</label>
                            <input type="number" min="0.1" step="0.1" name="questions[__INDEX__][points]" value="1" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Đáp án đúng</label>
                            <select name="questions[__INDEX__][correct_option]" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach(['A', 'B', 'C', 'D'] as $letter)
                            <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                                <label class="block text-sm font-semibold text-slate-700">Đáp án {{ $letter }}</label>
                                <input type="hidden" name="questions[__INDEX__][option_ids][{{ $letter }}]" value="">
                                <input type="text" name="questions[__INDEX__][options][{{ $letter }}]" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none" placeholder="Nhập đáp án {{ $letter }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>
        </template>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                {{ $submitLabel ?? 'Lưu bài kiểm tra' }}
            </button>
        </div>
    </section>
</form>

<script>
    (function () {
        const list = document.getElementById('question-list');
        const template = document.getElementById('question-template');

        if (!list || !template) {
            return;
        }

        window.addQuestion = function () {
            const index = list.querySelectorAll('[data-question-card]').length;
            const html = template.innerHTML.replaceAll('__INDEX__', String(index));
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            list.appendChild(wrapper.firstElementChild);
        };

        window.removeQuestion = function (button) {
            const cards = list.querySelectorAll('[data-question-card]');

            if (cards.length <= 1) {
                return;
            }

            const card = button.closest('[data-question-card]');
            if (card) {
                card.remove();
            }
        };

    })();
</script>
