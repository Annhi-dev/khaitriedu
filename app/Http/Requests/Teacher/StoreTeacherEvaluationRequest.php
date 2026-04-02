<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'comments' => trim((string) $this->input('comments')),
        ]);
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:nguoi_dung,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comments' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
