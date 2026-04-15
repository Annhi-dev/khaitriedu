<?php

namespace App\Http\Requests\Admin;

use App\Models\Enrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $action = $this->input('action');

        if (! $action && $this->filled('status')) {
            $action = match ((string) $this->input('status')) {
                Enrollment::STATUS_APPROVED => 'approve',
                Enrollment::STATUS_REJECTED => 'reject',
                Enrollment::STATUS_SCHEDULED,
                Enrollment::LEGACY_STATUS_CONFIRMED => 'schedule',
                Enrollment::STATUS_ACTIVE => 'activate',
                Enrollment::STATUS_COMPLETED => 'complete',
                default => 'request_update',
            };
        }

        $this->merge([
            'action' => $action,
            'course_id' => $this->filled('course_id') ? (int) $this->input('course_id') : null,
            'class_room_id' => $this->filled('class_room_id') ? (int) $this->input('class_room_id') : null,
            'assigned_teacher_id' => $this->filled('assigned_teacher_id') ? (int) $this->input('assigned_teacher_id') : null,
            'schedule' => $this->filled('schedule') ? trim((string) $this->input('schedule')) : null,
            'note' => $this->filled('note') ? trim((string) $this->input('note')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in([
                'approve',
                'reject',
                'request_update',
                'schedule',
                'activate',
                'complete',
            ])],
            'course_id' => ['nullable', 'exists:khoa_hoc,id'],
            'class_room_id' => ['nullable', 'exists:lop_hoc,id'],
            'assigned_teacher_id' => ['nullable', 'exists:nguoi_dung,id'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (in_array($this->input('action'), ['reject', 'request_update'], true) && ! $this->filled('note')) {
                $validator->errors()->add('note', 'Vui lòng nhập ghi chú hoặc lý do cho thao tác này.');
            }
        });
    }
}
