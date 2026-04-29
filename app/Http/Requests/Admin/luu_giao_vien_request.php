<?php

namespace App\Http\Requests\Admin;

use App\Models\NguoiDung;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', Rule::unique('nguoi_dung', 'username')],
            'email' => ['required', 'email', 'max:255', Rule::unique('nguoi_dung', 'email')],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('nguoi_dung', 'phone')],
            'department_id' => ['required', 'exists:phong_ban,id'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'status' => ['required', Rule::in([NguoiDung::STATUS_ACTIVE, NguoiDung::STATUS_INACTIVE])],
        ];
    }
}
