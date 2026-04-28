<?php

namespace App\Http\Requests\Teacher;

use App\Helpers\ScheduleHelper;
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
            'requested_start_time' => ScheduleHelper::normalizeTimeValue($this->input('requested_start_time')) ?: null,
            'requested_end_time' => $this->input('requested_start_time') ? ScheduleHelper::normalizeEndTime((string) $this->input('requested_start_time')) : (ScheduleHelper::normalizeTimeValue($this->input('requested_end_time')) ?: null),
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

    public function messages(): array
    {
        return [
            'requested_day_of_week.required' => 'Vui lòng chọn ngày dạy bù trong tuần.',
            'requested_date.required' => 'Vui lòng chọn ngày dạy bù bắt đầu.',
            'requested_start_time.required' => 'Vui lòng chọn giờ dạy bù bắt đầu.',
            'requested_end_time.required' => 'Vui lòng chọn giờ dạy bù kết thúc.',
            'requested_end_time.after' => 'Giờ kết thúc phải sau giờ bắt đầu.',
            'reason.required' => 'Vui lòng nhập lý do dời buổi.',
        ];
    }
}
