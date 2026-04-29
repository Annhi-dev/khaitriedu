<?php

namespace App\Http\Requests\Admin;

use App\Models\NguoiDung;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student') instanceof NguoiDung
            ? $this->route('student')->id
            : $this->route('student');

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', Rule::unique('nguoi_dung', 'username')->ignore($studentId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('nguoi_dung', 'email')->ignore($studentId)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('nguoi_dung', 'phone')->ignore($studentId)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'status' => ['required', Rule::in([NguoiDung::STATUS_ACTIVE, NguoiDung::STATUS_INACTIVE])],
        ];
    }
}
