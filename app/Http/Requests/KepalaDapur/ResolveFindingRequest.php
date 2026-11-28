<?php

namespace App\Http\Requests\KepalaDapur;

use Illuminate\Foundation\Http\FormRequest;

class ResolveFindingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'handler_note' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'handler_note.required' => 'Tindakan korektif wajib diisi.',
            'handler_note.min' => 'Tindakan korektif wajib diisi minimal 10 karakter.',
        ];
    }
}
