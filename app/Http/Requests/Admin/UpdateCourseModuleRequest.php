<?php

namespace App\Http\Requests\Admin;

use App\Models\Module;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title', '')),
            'content' => $this->filled('content') ? trim((string) $this->input('content')) : null,
            'duration' => $this->filled('duration') ? (int) $this->input('duration') : null,
            'position' => $this->filled('position') ? (int) $this->input('position') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'position' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'status' => ['required', Rule::in([Module::STATUS_PUBLISHED, Module::STATUS_UNPUBLISHED])],
        ];
    }
}