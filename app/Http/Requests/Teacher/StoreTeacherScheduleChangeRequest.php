<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherScheduleChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requested_day_of_week' => $this->input('requested_day_of_week') ?: null,
            'requested_date' => $this->input('requested_date') ?: null,
            'requested_end_date' => $this->input('requested_end_date') ?: null,
            'requested_start_time' => $this->input('requested_start_time') ?: null,
            'requested_end_time' => $this->input('requested_end_time') ?: null,
            'reason' => $this->filled('reason') ? trim((string) $this->input('reason')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'requested_day_of_week' => ['required', Rule::in([
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
            ])],
            'requested_date' => ['required', 'date'],
            'requested_end_date' => ['nullable', 'date', 'after_or_equal:requested_date'],
            'requested_start_time' => ['required', 'date_format:H:i'],
            'requested_end_time' => ['required', 'date_format:H:i', 'after:requested_start_time'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}