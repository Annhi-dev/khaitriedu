<?php

namespace App\Http\Requests\Admin;

use App\Models\PhongBan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department') instanceof PhongBan
            ? $this->route('department')->id
            : $this->route('department');

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('phong_ban', 'code')->ignore($departmentId)],
            'name' => ['required', 'string', 'max:150', Rule::unique('phong_ban', 'name')->ignore($departmentId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(array_keys(PhongBan::statusOptions()))],
        ];
    }
}
