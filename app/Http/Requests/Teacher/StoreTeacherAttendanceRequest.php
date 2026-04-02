<?php

namespace App\Http\Requests\Teacher;

use App\Models\AttendanceRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_schedule_id' => ['required', 'exists:lich_hoc,id'],
            'attendance_date' => ['required', 'date'],
            'attendance' => ['required', 'array', 'min:1'],
            'attendance.*.status' => ['required', Rule::in(array_keys(AttendanceRecord::statusOptions()))],
            'attendance.*.note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
