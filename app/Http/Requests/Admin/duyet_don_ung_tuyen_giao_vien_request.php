<?php

namespace App\Http\Requests\Admin;

use App\Models\DonUngTuyenGiaoVien;
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
                DonUngTuyenGiaoVien::STATUS_APPROVED,
                DonUngTuyenGiaoVien::STATUS_REJECTED,
                DonUngTuyenGiaoVien::STATUS_NEEDS_REVISION,
            ])],
            'admin_note' => ['nullable', 'string', 'max:2000', 'required_if:action,' . DonUngTuyenGiaoVien::STATUS_NEEDS_REVISION],
            'rejection_reason' => ['nullable', 'string', 'max:2000', 'required_if:action,' . DonUngTuyenGiaoVien::STATUS_REJECTED],
        ];
    }
}