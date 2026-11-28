<?php

namespace App\Http\Requests\Distribusi;

use App\Enums\DeliveryStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeliveryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $route = $this->route('route');
        $maxPortions = (int) ($route?->location?->target_portions ?? $route?->dispatched_portions ?? 1000);

        return [
            'status' => ['required', Rule::in([DeliveryStatus::BERANGKAT->value, DeliveryStatus::TIBA->value])],
            'portions' => ['required', 'integer', 'min:0', 'max:'.$maxPortions],
            'nama_penerima_serah' => [
                Rule::requiredIf(fn () => $this->input('status') === DeliveryStatus::TIBA->value),
                'nullable',
                'string',
                'max:120',
            ],
            'event_time' => [
                'nullable',
                'date',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value && Carbon::parse((string) $value, 'Asia/Jakarta')->greaterThan(now('Asia/Jakarta')->addMinute())) {
                        $fail('Waktu pencatatan tidak boleh di masa depan.');
                    }
                },
            ],
            'catatan_lapangan' => ['nullable', 'string', 'max:1000'],
            'document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Transisi status hanya diperbolehkan ke Berangkat atau Tiba.',
            'nama_penerima_serah.required' => 'Nama penerima wajib diisi saat status Tiba.',
            'document.max' => 'Ukuran berkas bukti maksimal 2 MB.',
            'document.mimes' => 'Berkas bukti harus berformat JPG, PNG, atau PDF.',
        ];
    }
}
