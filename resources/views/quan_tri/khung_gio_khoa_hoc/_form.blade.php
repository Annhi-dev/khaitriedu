@php
    $startTime = old('start_time', $courseTimeSlot->start_time ? substr((string) $courseTimeSlot->start_time, 0, 5) : '');
    $endTime = old('end_time', $courseTimeSlot->end_time ? substr((string) $courseTimeSlot->end_time, 0, 5) : '');
    $slotDate = old('slot_date', optional($courseTimeSlot->slot_date)->format('Y-m-d'));
    $registrationOpenAt = old('registration_open_at', optional($courseTimeSlot->registration_open_at)->format('Y-m-d\TH:i'));
    $registrationCloseAt = old('registration_close_at', optional($courseTimeSlot->registration_close_at)->format('Y-m-d\TH:i'));
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        <div class="font-semibold">Dữ liệu khung giờ chưa hợp lệ.</div>
        <ul class="mt-2 list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Khóa học công khai</label>
        <select name="subject_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
            <option value="">Chọn khóa học</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" @selected((string) old('subject_id', $courseTimeSlot->subject_id) === (string) $subject->id)>
                    {{ $subject->name }}{{ $subject->category ? ' - ' . $subject->category->name : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Giảng viên</label>
        <select name="teacher_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            <option value="">Chưa phân công</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $courseTimeSlot->teacher_id) === (string) $teacher->id)>{{ $teacher->displayName() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Phòng học</label>
        <select name="room_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            <option value="">Chưa gán phòng</option>
            @foreach ($rooms as $room)
                <option value="{{ $room->id }}" @selected((string) old('room_id', $courseTimeSlot->room_id) === (string) $room->id)>
                    {{ $room->code }} - {{ $room->name }} ({{ $room->capacity }} chỗ)
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Thứ học</label>
        <select name="day_of_week" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            <option value="">Chọn theo ngày cụ thể</option>
            @foreach ($dayOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('day_of_week', $courseTimeSlot->day_of_week) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Ngày học cụ thể</label>
        <input type="date" name="slot_date" value="{{ $slotDate }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái</label>
        <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $courseTimeSlot->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Bắt đầu</label>
        <input type="time" name="start_time" value="{{ $startTime }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Kết thúc</label>
        <input type="time" name="end_time" value="{{ $endTime }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500 bg-slate-50" readonly required>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Mở đăng ký từ</label>
        <input type="datetime-local" name="registration_open_at" value="{{ $registrationOpenAt }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Đóng đăng ký lúc</label>
        <input type="datetime-local" name="registration_close_at" value="{{ $registrationCloseAt }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Số học viên tối thiểu</label>
        <input type="number" min="1" name="min_students" value="{{ old('min_students', $courseTimeSlot->min_students) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Số học viên tối đa</label>
        <input type="number" min="1" name="max_students" value="{{ old('max_students', $courseTimeSlot->max_students) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500" required>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
        <textarea name="note" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 focus:ring-cyan-500 focus:border-cyan-500">{{ old('note', $courseTimeSlot->note) }}</textarea>
    </div>
</div>

<div class="mt-6 flex flex-wrap items-center justify-end gap-3">
    <a href="{{ route('admin.course-time-slots.index') }}" class="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition">Quay lại</a>
    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
        {{ $submitLabel }}
    </button>
</div>
