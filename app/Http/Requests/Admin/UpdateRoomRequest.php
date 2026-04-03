<?php

namespace App\Http\Requests\Admin;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        /** @var \App\Models\Room|null $room */
        $room = $this->route('room');

        $this->merge([
            'code' => $this->filled('code') ? Str::upper(trim((string) $this->input('code'))) : $room?->code,
            'name' => trim((string) $this->input('name', '')),
            'type' => $this->input('type', 'theory'),
            'location' => $this->filled('location') ? trim((string) $this->input('location')) : null,
            'capacity' => $this->filled('capacity') ? (int) $this->input('capacity') : null,
            'status' => $this->input('status', $room?->status ?? Room::STATUS_ACTIVE),
            'note' => $this->filled('note') ? trim((string) $this->input('note')) : null,
        ]);
    }

    public function rules(): array
    {
        /** @var \App\Models\Room|null $room */
        $room = $this->route('room');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('rooms', 'code')->ignore($room?->id)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:theory,practice'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:9999'],
            'status' => ['required', Rule::in(array_keys(Room::statusOptions()))],
            'note' => ['nullable', 'string'],
        ];
    }
}
