<?php

namespace App\Http\Requests\Admin;

use App\Helpers\ScheduleHelper;
use App\Models\ClassSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckScheduleConflictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $days = $this->input('day_of_week');

        if (is_string($days) && $days !== '') {
            $days = [$days];
        }

        if (! is_array($days)) {
            $days = [];
        }

        $this->merge([
            'teacher_id' => $this->filled('teacher_id') ? (int) $this->input('teacher_id') : null,
            'room_id' => $this->filled('room_id') ? (int) $this->input('room_id') : null,
            'course_id' => $this->filled('course_id') ? (int) $this->input('course_id') : null,
            'class_room_id' => $this->filled('class_room_id') ? (int) $this->input('class_room_id') : null,
            'exclude_course_id' => $this->filled('exclude_course_id') ? (int) $this->input('exclude_course_id') : null,
            'exclude_class_room_id' => $this->filled('exclude_class_room_id') ? (int) $this->input('exclude_class_room_id') : null,
            'day_of_week' => array_values(array_filter(array_map(function ($day) {
                return is_string($day) ? trim($day) : null;
            }, $days))),
            'start_date' => $this->input('start_date') ?: null,
            'end_date' => $this->input('end_date') ?: null,
            'start_time' => $this->input('start_time') ?: null,
            'end_time' => $this->filled('start_time')
                ? ScheduleHelper::normalizeEndTime((string) $this->input('start_time'))
                : ($this->input('end_time') ?: null),
        ]);
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['nullable', 'integer', 'exists:nguoi_dung,id'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'course_id' => ['nullable', 'integer', 'exists:khoa_hoc,id'],
            'class_room_id' => ['nullable', 'integer', 'exists:lop_hoc,id'],
            'exclude_course_id' => ['nullable', 'integer', 'exists:khoa_hoc,id'],
            'exclude_class_room_id' => ['nullable', 'integer', 'exists:lop_hoc,id'],
            'day_of_week' => ['nullable', 'array'],
            'day_of_week.*' => ['string', Rule::in(array_keys(ClassSchedule::$dayOptions))],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
        ];
    }
}
