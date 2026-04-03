<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScheduleEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $selectedDays = $this->input('day_of_week');

        if (is_string($selectedDays)) {
            $selectedDays = $selectedDays !== '' ? [$selectedDays] : [];
        } elseif (! is_array($selectedDays)) {
            $selectedDays = [];
        }

        $this->merge([
            'course_id' => $this->filled('course_id') ? (int) $this->input('course_id') : null,
            'teacher_id' => $this->filled('teacher_id') ? (int) $this->input('teacher_id') : null,
            'new_course_title' => $this->filled('new_course_title') ? trim((string) $this->input('new_course_title')) : null,
            'new_course_description' => $this->filled('new_course_description') ? trim((string) $this->input('new_course_description')) : null,
            'day_of_week' => array_values(array_unique(array_filter(array_map(
                fn ($day) => is_string($day) ? trim($day) : null,
                $selectedDays
            )))),
            'start_date' => $this->input('start_date') ?: null,
            'end_date' => $this->input('end_date') ?: null,
            'start_time' => $this->input('start_time') ?: null,
            'end_time' => $this->input('end_time') ?: null,
            'capacity' => $this->filled('capacity') ? (int) $this->input('capacity') : null,
            'note' => $this->filled('note') ? trim((string) $this->input('note')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'exists:khoa_hoc,id'],
            'teacher_id' => ['nullable', 'exists:nguoi_dung,id'],
            'new_course_title' => ['nullable', 'string', 'max:255'],
            'new_course_description' => ['nullable', 'string'],
            'day_of_week' => ['nullable', 'array'],
            'day_of_week.*' => ['required', Rule::in([
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
            ])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
