<?php

namespace App\Http\Requests\Aslap;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProcurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'numeric', 'min:0'],
            'supplier_name' => ['nullable', 'string', 'max:150'],
            'received_at' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $dt = Carbon::parse((string) $value, 'Asia/Jakarta');
                    if ($dt->greaterThan(now('Asia/Jakarta')->addMinute())) {
                        $fail('Waktu penerimaan tidak boleh di masa depan.');
                    }
                },
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
