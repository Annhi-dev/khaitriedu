<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'class_room_id' => $this->filled('class_room_id') ? (int) $this->input('class_room_id') : null,
            'attendance_date' => $this->filled('attendance_date') ? $this->input('attendance_date') : null,
            'reason' => $this->filled('reason') ? trim((string) $this->input('reason')) : null,
            'note' => $this->filled('note') ? trim((string) $this->input('note')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'class_room_id' => ['required', 'integer', 'exists:lop_hoc,id'],
            'attendance_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1500'],
        ];
    }

    public function messages(): array
    {
        return [
            'class_room_id.required' => 'Vui lòng chọn lớp học cần xin phép.',
            'attendance_date.required' => 'Vui lòng chọn ngày xin phép nghỉ.',
            'reason.required' => 'Vui lòng nhập lý do xin phép nghỉ.',
        ];
    }
}
