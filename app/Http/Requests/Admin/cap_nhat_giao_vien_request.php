<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacherId = $this->route('teacher') instanceof User
            ? $this->route('teacher')->id
            : $this->route('teacher');

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', Rule::unique('nguoi_dung', 'username')->ignore($teacherId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('nguoi_dung', 'email')->ignore($teacherId)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('nguoi_dung', 'phone')->ignore($teacherId)],
            'department_id' => ['required', 'exists:phong_ban,id'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE])],
        ];
    }
}
