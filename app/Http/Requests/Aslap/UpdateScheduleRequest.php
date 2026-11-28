<?php

namespace App\Http\Requests\Aslap;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kelompok_rute' => ['nullable', 'string', 'max:50'],
            'courier_name' => ['required', 'string', 'max:120'],
            'vehicle_plate' => ['required', 'string', 'max:40'],
            'planned_departure_time' => ['required', 'date_format:H:i'],
            'scheduled_arrival_time' => ['required', 'date_format:H:i', 'after:planned_departure_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_arrival_time.after' => 'Estimasi tiba harus setelah jam berangkat.',
        ];
    }
}
