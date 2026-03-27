<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewScheduleChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'action' => $this->input('action') ?: null,
            'admin_note' => $this->filled('admin_note') ? trim((string) $this->input('admin_note')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'admin_note' => ['nullable', 'string', 'max:1000', 'required_if:action,reject'],
        ];
    }
}