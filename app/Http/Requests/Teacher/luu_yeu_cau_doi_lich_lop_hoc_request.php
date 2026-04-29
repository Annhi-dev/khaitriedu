<?php

namespace App\Http\Requests\Teacher;

use App\Helpers\ScheduleHelper;
use App\Models\PhongHoc;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherClassScheduleChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $requestedDate = trim((string) $this->input('requested_date', ''));
        $requestedStartTime = trim((string) $this->input('requested_start_time', ''));
        $requestedEndTime = trim((string) $this->input('requested_end_time', ''));
        $requestedStartAt = trim((string) $this->input('requested_start_at', ''));
        $requestedEndAt = trim((string) $this->input('requested_end_at', ''));

        if ($requestedStartAt === '' && $requestedDate !== '' && $requestedStartTime !== '') {
            $requestedStartAt = $requestedDate . ' ' . $requestedStartTime;
        }

        if ($requestedEndAt === '' && $requestedDate !== '' && $requestedEndTime !== '') {
            $requestedEndAt = $requestedDate . ' ' . $requestedEndTime;
        }

        if ($requestedDate === '' && $requestedStartAt !== '') {
            try {
                $requestedDate = Carbon::parse($requestedStartAt)->format('Y-m-d');
            } catch (\Throwable $exception) {
            }
        }

        if ($requestedStartAt !== '') {
            try {
                $startAt = Carbon::parse($requestedStartAt);
                $requestedStartTime = $startAt->format('H:i');
                $requestedEndTime = $startAt->copy()->addMinutes(ScheduleHelper::sessionMinutes())->format('H:i');
                $requestedEndAt = $startAt->copy()->addMinutes(ScheduleHelper::sessionMinutes())->format('Y-m-d H:i:s');
            } catch (\Throwable $exception) {
            }
        } elseif ($requestedDate !== '' && $requestedStartTime !== '') {
            try {
                $startAt = Carbon::parse($requestedDate . ' ' . $requestedStartTime);
                $requestedEndTime = $startAt->copy()->addMinutes(ScheduleHelper::sessionMinutes())->format('H:i');
                $requestedEndAt = $startAt->copy()->addMinutes(ScheduleHelper::sessionMinutes())->format('Y-m-d H:i:s');
            } catch (\Throwable $exception) {
            }
        }

        $this->merge([
            'requested_date' => $requestedDate !== '' ? $requestedDate : null,
            'requested_start_time' => $requestedStartTime !== '' ? ScheduleHelper::normalizeTimeValue($requestedStartTime) : null,
            'requested_end_time' => $requestedEndTime !== '' ? ScheduleHelper::normalizeTimeValue($requestedEndTime) : null,
            'requested_start_at' => $requestedStartAt !== '' ? $requestedStartAt : null,
            'requested_end_at' => $requestedEndAt !== '' ? $requestedEndAt : null,
            'requested_room_id' => $this->filled('requested_room_id') ? (int) $this->input('requested_room_id') : null,
            'reason' => trim((string) $this->input('reason')),
        ]);
    }

    public function rules(): array
    {
        return [
            'requested_date' => ['nullable', 'required_without:requested_start_at', 'date'],
            'requested_start_time' => ['nullable', 'required_without:requested_start_at', 'date_format:H:i'],
            'requested_end_time' => ['nullable', 'required_without:requested_end_at', 'date_format:H:i', 'after:requested_start_time'],
            'requested_start_at' => ['nullable', 'required_without:requested_date', 'date'],
            'requested_end_at' => ['nullable', 'required_without:requested_start_time', 'date', 'after:requested_start_at'],
            'requested_room_id' => [
                'nullable',
                Rule::exists('rooms', 'id')->where(fn ($query) => $query->where('status', PhongHoc::STATUS_ACTIVE)),
            ],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'requested_date.required_without' => 'Vui lòng chọn ngày dạy bù.',
            'requested_start_time.required_without' => 'Vui lòng chọn giờ dạy bù bắt đầu.',
            'requested_end_time.required_without' => 'Vui lòng chọn giờ dạy bù kết thúc.',
            'requested_end_time.after' => 'Giờ kết thúc phải sau giờ bắt đầu.',
            'requested_end_at.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'requested_room_id.exists' => 'Phòng học đề xuất không hợp lệ hoặc đang tạm ngừng.',
            'reason.required' => 'Vui lòng nhập lý do dời buổi.',
        ];
    }
}
