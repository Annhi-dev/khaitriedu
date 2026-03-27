<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
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
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE])],
        ];
    }
}
