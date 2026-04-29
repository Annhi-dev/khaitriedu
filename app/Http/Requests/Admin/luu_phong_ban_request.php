<?php

namespace App\Http\Requests\Admin;

use App\Models\PhongBan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('phong_ban', 'code')],
            'name' => ['required', 'string', 'max:150', Rule::unique('phong_ban', 'name')],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(array_keys(PhongBan::statusOptions()))],
        ];
    }
}
