<?php

namespace App\Http\Requests\Akuntan;

use App\Enums\PaguCheckResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RecordPaguCheckRequest extends FormRequest
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
            'hasil' => ['required', Rule::enum(PaguCheckResult::class)],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'estimates' => ['nullable', 'array'],
            'estimates.*.recipient_category_id' => ['required', 'integer', 'exists:recipient_categories,id'],
            'estimates.*.estimasi_per_porsi' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hasil.required' => 'Pilih hasil pemeriksaan PAGU terlebih dahulu.',
            'estimates.*.estimasi_per_porsi.integer' => 'Estimasi biaya per porsi harus berupa angka bulat.',
            'estimates.*.estimasi_per_porsi.min' => 'Estimasi biaya per porsi tidak boleh negatif.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hasil = (string) $this->input('hasil', '');
                $catatan = trim((string) $this->input('catatan', ''));

                if ($hasil === PaguCheckResult::PERLU_REVISI->value && mb_strlen($catatan) < 5) {
                    $validator->errors()->add('catatan', 'Catatan wajib diisi minimal 5 karakter jika hasil Perlu Revisi Anggaran.');
                }
            },
        ];
    }
}
