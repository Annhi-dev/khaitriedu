<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherClassScheduleChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reason' => trim((string) $this->input('reason')),
        ]);
    }

    public function rules(): array
    {
        return [
            'requested_start_at' => ['required', 'date'],
            'requested_end_at' => ['required', 'date', 'after:requested_start_at'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
