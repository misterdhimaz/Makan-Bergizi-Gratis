<?php

namespace App\Http\Requests\KepalaDapur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['setujui', 'minta_revisi'])],
            'catatan' => [
                Rule::requiredIf(fn () => $this->input('decision') === 'minta_revisi'),
                'nullable',
                'string',
                'max:1000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->input('decision') === 'minta_revisi' && mb_strlen(trim((string) $value)) < 5) {
                        $fail('Catatan wajib diisi minimal 5 karakter bila meminta revisi.');
                    }
                },
            ],
        ];
    }
}
