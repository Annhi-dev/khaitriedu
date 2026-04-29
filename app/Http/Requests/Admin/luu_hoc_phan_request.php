<?php

namespace App\Http\Requests\Admin;

use App\Models\HocPhan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseModuleRequest extends FormRequest
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
            'session_count' => $this->filled('session_count') ? (int) $this->input('session_count') : null,
            'duration' => $this->filled('duration') ? (int) $this->input('duration') : null,
            'position' => $this->filled('position') ? (int) $this->input('position') : null,
            'status' => $this->input('status', HocPhan::STATUS_PUBLISHED),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'session_count' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'position' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'status' => ['required', Rule::in([HocPhan::STATUS_PUBLISHED, HocPhan::STATUS_UNPUBLISHED])],
        ];
    }
}
