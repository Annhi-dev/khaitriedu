<?php

namespace App\Http\Requests\Admin;

use App\Models\MonHoc;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
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
            'test_count' => $this->filled('test_count') ? (int) $this->input('test_count') : MonHoc::DEFAULT_TEST_COUNT,
            'category_id' => $this->filled('category_id') ? (int) $this->input('category_id') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'duration' => ['required', 'integer', 'min:1', 'max:120'],
            'test_count' => ['required', 'integer', 'min:1', 'max:12'],
            'status' => ['required', Rule::in([
                MonHoc::STATUS_DRAFT,
                MonHoc::STATUS_OPEN,
                MonHoc::STATUS_CLOSED,
                MonHoc::STATUS_ARCHIVED,
            ])],
            'category_id' => ['nullable', 'exists:danh_muc,id'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
