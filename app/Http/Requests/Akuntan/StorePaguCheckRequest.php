<?php

namespace App\Http\Requests\Akuntan;

use App\Enums\PaguCheckResult;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaguCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::AKUNTAN;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'hasil' => ['required', Rule::in([
                PaguCheckResult::LOLOS->value,
                PaguCheckResult::PERLU_REVISI->value,
            ])],
            'catatan' => [
                Rule::requiredIf(fn () => $this->input('hasil') === PaguCheckResult::PERLU_REVISI->value),
                'nullable',
                'string',
                'min:5',
                'max:2000',
            ],
            'estimates' => ['nullable', 'array'],
            'estimates.*.recipient_category_id' => ['required_with:estimates', 'integer', 'exists:recipient_categories,id'],
            'estimates.*.estimasi_per_porsi' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hasil.required' => 'Hasil pemeriksaan wajib dipilih.',
            'catatan.required' => 'Catatan wajib diisi minimal 5 karakter untuk hasil Perlu Revisi.',
            'catatan.min' => 'Catatan wajib diisi minimal 5 karakter untuk hasil Perlu Revisi.',
            'estimates.*.estimasi_per_porsi.min' => 'Estimasi biaya per porsi tidak boleh negatif.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('hasil') === PaguCheckResult::LOLOS->value && ! filled($this->input('catatan'))) {
            $this->merge(['catatan' => 'Sesuai batas PAGU.']);
        }
    }
}
