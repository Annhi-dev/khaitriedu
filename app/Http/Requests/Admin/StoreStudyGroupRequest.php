<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreStudyGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));
        $slug = trim((string) $this->input('slug', ''));

        $this->merge([
            'name' => $name,
            'slug' => $slug !== '' ? Str::slug($slug) : Str::slug($name),
            'description' => $this->filled('description') ? trim((string) $this->input('description')) : null,
            'program' => $this->filled('program') ? trim((string) $this->input('program')) : null,
            'level' => $this->filled('level') ? trim((string) $this->input('level')) : null,
            'status' => $this->input('status', Category::STATUS_ACTIVE),
            'order' => $this->filled('order') ? (int) $this->input('order') : 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('danh_muc', 'name')],
            'slug' => ['required', 'string', 'max:255', Rule::unique('danh_muc', 'slug')],
            'description' => ['nullable', 'string'],
            'program' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in([Category::STATUS_ACTIVE, Category::STATUS_INACTIVE])],
            'order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}