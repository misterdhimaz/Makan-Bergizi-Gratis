<?php

namespace App\Http\Requests\Akuntan;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaguParameterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nominal_per_porsi' => ['required', 'integer', 'min:1'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nominal_per_porsi.required' => 'Nominal harus lebih dari 0.',
            'nominal_per_porsi.integer' => 'Nominal harus berupa angka bulat.',
            'nominal_per_porsi.min' => 'Nominal harus lebih dari 0.',
        ];
    }
}
