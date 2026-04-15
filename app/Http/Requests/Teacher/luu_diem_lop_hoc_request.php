<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherClassGradesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'test_name' => trim((string) $this->input('test_name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'test_name' => ['required', 'string', 'max:100'],
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.feedback' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
