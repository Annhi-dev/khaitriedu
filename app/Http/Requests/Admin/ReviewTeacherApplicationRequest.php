<?php

namespace App\Http\Requests\Admin;

use App\Models\TeacherApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewTeacherApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in([
                TeacherApplication::STATUS_APPROVED,
                TeacherApplication::STATUS_REJECTED,
                TeacherApplication::STATUS_NEEDS_REVISION,
            ])],
            'admin_note' => ['nullable', 'string', 'max:2000', 'required_if:action,' . TeacherApplication::STATUS_NEEDS_REVISION],
            'rejection_reason' => ['nullable', 'string', 'max:2000', 'required_if:action,' . TeacherApplication::STATUS_REJECTED],
        ];
    }
}