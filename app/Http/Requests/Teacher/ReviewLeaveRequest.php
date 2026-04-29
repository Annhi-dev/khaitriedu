<?php

namespace App\Http\Requests\Teacher;

use App\Models\YeuCauXinPhep;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status') ?: null,
            'teacher_note' => $this->filled('teacher_note') ? trim((string) $this->input('teacher_note')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(YeuCauXinPhep::teacherReviewStatuses())],
            'teacher_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('status') === YeuCauXinPhep::STATUS_REJECTED && ! $this->filled('teacher_note')) {
                $validator->errors()->add('teacher_note', 'Vui lòng nhập ghi chú khi từ chối yêu cầu xin phép.');
            }
        });
    }
}
