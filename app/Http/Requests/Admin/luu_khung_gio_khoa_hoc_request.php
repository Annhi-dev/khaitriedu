<?php

namespace App\Http\Requests\Admin;

use App\Helpers\ScheduleHelper;
use App\Models\KhungGioKhoaHoc;
use App\Models\PhongHoc;
use App\Models\NguoiDung;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseTimeSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'teacher_id' => $this->filled('teacher_id') ? (int) $this->input('teacher_id') : null,
            'room_id' => $this->filled('room_id') ? (int) $this->input('room_id') : null,
            'day_of_week' => $this->filled('day_of_week') ? (string) $this->input('day_of_week') : null,
            'slot_date' => $this->filled('slot_date') ? (string) $this->input('slot_date') : null,
            'start_time' => $this->filled('start_time') ? trim((string) $this->input('start_time')) : null,
            'end_time' => $this->filled('end_time') ? trim((string) $this->input('end_time')) : null,
            'registration_open_at' => $this->filled('registration_open_at') ? (string) $this->input('registration_open_at') : null,
            'registration_close_at' => $this->filled('registration_close_at') ? (string) $this->input('registration_close_at') : null,
            'min_students' => $this->filled('min_students') ? (int) $this->input('min_students') : 1,
            'max_students' => $this->filled('max_students') ? (int) $this->input('max_students') : 20,
            'status' => $this->input('status', KhungGioKhoaHoc::STATUS_PENDING_OPEN),
            'note' => $this->filled('note') ? trim((string) $this->input('note')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:mon_hoc,id'],
            'teacher_id' => ['nullable', 'exists:nguoi_dung,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'day_of_week' => ['nullable', Rule::in($this->dayOptions())],
            'slot_date' => ['nullable', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'registration_open_at' => ['nullable', 'date'],
            'registration_close_at' => ['nullable', 'date', 'after:registration_open_at'],
            'min_students' => ['required', 'integer', 'min:1', 'max:9999'],
            'max_students' => ['required', 'integer', 'min:1', 'max:9999', 'gte:min_students'],
            'status' => ['required', Rule::in(array_keys(KhungGioKhoaHoc::statusOptions()))],
            'note' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('day_of_week') && ! $this->filled('slot_date')) {
                $validator->errors()->add('day_of_week', 'Bạn cần chọn thứ học hoặc ngày học cụ thể.');
            }

            if ($this->filled('teacher_id')) {
                $teacher = NguoiDung::find((int) $this->input('teacher_id'));
                if (! $teacher || ! $teacher->isTeacher()) {
                    $validator->errors()->add('teacher_id', 'Giảng viên được chọn không hợp lệ.');
                }
            }

            if ($this->filled('room_id')) {
                $room = PhongHoc::find((int) $this->input('room_id'));
                if ($room && $this->filled('max_students') && (int) $this->input('max_students') > (int) $room->capacity) {
                    $validator->errors()->add('max_students', 'Sĩ số tối đa không được vượt quá sức chứa phòng học.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'start_time.required' => 'Giờ bắt đầu là bắt buộc.',
            'start_time.date_format' => 'Giờ bắt đầu phải có định dạng H:i.',
            'end_time.required' => 'Giờ kết thúc là bắt buộc.',
            'end_time.date_format' => 'Giờ kết thúc phải có định dạng H:i.',
            'end_time.after' => 'Giờ kết thúc phải sau giờ bắt đầu.',
            'registration_close_at.after' => 'Thời gian đóng đăng ký phải sau thời gian mở đăng ký.',
        ];
    }

    private function dayOptions(): array
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }
}
