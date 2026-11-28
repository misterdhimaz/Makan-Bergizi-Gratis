<?php

namespace App\Http\Requests\Aslap;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HandleAslapComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['in_progress', 'resolved'])],
            'resolution_notes' => ['required', 'string', 'min:10', 'max:1000'],
            'handled_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'resolution_notes.required' => 'Tindakan penyelesaian wajib diisi.',
            'resolution_notes.min' => 'Tindakan penyelesaian wajib diisi minimal 10 karakter.',
        ];
    }
}
