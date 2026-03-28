<?php

namespace App\Http\Requests\Admin;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code', ''))),
            'name' => trim((string) $this->input('name', '')),
            'location' => $this->filled('location') ? trim((string) $this->input('location')) : null,
            'capacity' => $this->filled('capacity') ? (int) $this->input('capacity') : null,
            'status' => $this->input('status', Room::STATUS_ACTIVE),
            'note' => $this->filled('note') ? trim((string) $this->input('note')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('rooms', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:9999'],
            'status' => ['required', Rule::in(array_keys(Room::statusOptions()))],
            'note' => ['nullable', 'string'],
        ];
    }
}
