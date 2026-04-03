<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentClassEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'lop_hoc_id' => $this->filled('lop_hoc_id') ? (int) $this->input('lop_hoc_id') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'lop_hoc_id' => ['required', 'exists:lop_hoc,id'],
        ];
    }
}
