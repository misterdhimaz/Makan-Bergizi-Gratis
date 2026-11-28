<?php

namespace App\Http\Requests\KepalaDapur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HandleComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['in_progress', 'resolved'])],
            'resolution_notes' => [
                'required',
                'string',
                'max:1000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $min = $this->input('status') === 'resolved' ? 10 : 5;
                    if (mb_strlen(trim((string) $value)) < $min) {
                        $fail("Tindakan penyelesaian wajib diisi minimal {$min} karakter.");
                    }
                },
            ],
        ];
    }
}
