<?php

namespace App\Http\Requests\Student;

use App\Helpers\ScheduleHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public static function sanitize(array $input): array
    {
        return [
            'start_time' => filled($input['start_time'] ?? null) ? ScheduleHelper::normalizeTimeValue((string) $input['start_time']) : null,
            'end_time' => filled($input['end_time'] ?? null)
                ? ScheduleHelper::normalizeTimeValue((string) $input['end_time'])
                : (filled($input['start_time'] ?? null) ? ScheduleHelper::normalizeEndTime((string) $input['start_time']) : null),
            'preferred_days' => $input['preferred_days'] ?? [],
            'preferred_schedule' => filled($input['preferred_schedule'] ?? null) ? trim((string) $input['preferred_schedule']) : null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(self::sanitize($this->all()));
    }

    public static function rulesList(): array
    {
        return [
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'preferred_days' => ['required', 'array', 'min:1'],
            'preferred_days.*' => ['required', Rule::in([
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
            ])],
            'preferred_schedule' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function rules(): array
    {
        return self::rulesList();
    }
}
