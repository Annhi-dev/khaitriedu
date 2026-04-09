<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $schedules = $this->input('schedules');

        if (! is_array($schedules)) {
            $schedules = [];
        }

        $normalizedSchedules = [];

        foreach ($schedules as $row) {
            if (! is_array($row)) {
                continue;
            }

            $normalizedSchedules[] = [
                'day' => isset($row['day']) ? trim((string) $row['day']) : null,
                'start' => isset($row['start']) ? trim((string) $row['start']) : null,
                'end' => isset($row['end']) ? trim((string) $row['end']) : null,
            ];
        }

        $this->merge([
            'subject_id' => $this->filled('subject_id') ? (int) $this->input('subject_id') : null,
            'course_id' => $this->filled('course_id') ? (int) $this->input('course_id') : null,
            'teacher_id' => $this->filled('teacher_id') ? (int) $this->input('teacher_id') : null,
            'room_id' => $this->filled('room_id') ? (int) $this->input('room_id') : null,
            'start_date' => $this->input('start_date') ?: null,
            'note' => $this->filled('note') ? trim((string) $this->input('note')) : null,
            'schedules' => $normalizedSchedules,
        ]);
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:mon_hoc,id'],
            'course_id' => [
                'required',
                Rule::exists('khoa_hoc', 'id')->where(function ($query) {
                    $subjectId = $this->input('subject_id');

                    if ($subjectId) {
                        $query->where('subject_id', $subjectId);
                    }
                }),
            ],
            'teacher_id' => ['required', 'exists:nguoi_dung,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'start_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*.day' => ['required', Rule::in([
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
            ])],
            'schedules.*.start' => ['required', 'date_format:H:i'],
            'schedules.*.end' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $schedules = $this->input('schedules', []);

            foreach ($schedules as $index => $row) {
                $start = $row['start'] ?? null;
                $end = $row['end'] ?? null;

                if (! $start || ! $end) {
                    continue;
                }

                if ($end <= $start) {
                    $validator->errors()->add('schedules.' . $index . '.end', 'Giờ kết thúc phải lớn hơn giờ bắt đầu.');
                }
            }
        });
    }
}

