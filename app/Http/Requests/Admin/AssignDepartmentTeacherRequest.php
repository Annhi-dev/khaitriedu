<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class AssignDepartmentTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'integer', 'exists:nguoi_dung,id'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $teacherId = (int) $this->input('teacher_id');
                $user = User::with('role')->find($teacherId);

                if (! $user || ! $user->isTeacher()) {
                    $validator->errors()->add('teacher_id', 'Tài khoản được chọn không phải là giảng viên.');
                }
            },
        ];
    }
}
