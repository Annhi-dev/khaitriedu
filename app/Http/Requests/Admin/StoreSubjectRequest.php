<?php

namespace App\Http\Requests\Admin;

use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'description' => $this->filled('description') ? trim((string) $this->input('description')) : null,
            'duration' => $this->filled('duration') ? (int) $this->input('duration') : null,
            'status' => $this->input('status', Subject::STATUS_OPEN),
            'category_id' => $this->filled('category_id') ? (int) $this->input('category_id') : null,
            'return_to_category_id' => $this->filled('return_to_category_id') ? (int) $this->input('return_to_category_id') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'status' => ['required', Rule::in([
                Subject::STATUS_DRAFT,
                Subject::STATUS_OPEN,
                Subject::STATUS_CLOSED,
                Subject::STATUS_ARCHIVED,
            ])],
            'category_id' => ['nullable', 'exists:danh_muc,id'],
            'return_to_category_id' => ['nullable', 'exists:danh_muc,id'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}