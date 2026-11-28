<?php

namespace App\Http\Controllers\GuruPic;

use App\Enums\DeliveryStatus;
use App\Enums\MenuStatus;
use App\Http\Controllers\Controller;
use App\Models\DailyMenu;
use App\Models\DeliveryRoute;
use App\Models\Location;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfirmationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $location = $user?->location_id
            ? Location::find($user->location_id)
            : Location::where('is_active', true)->orderBy('id')->first();

        $today = now('Asia/Jakarta')->toDateString();

        $route = $location
            ? (DeliveryRoute::with(['courier', 'location'])
                ->where('location_id', $location->id)
                ->whereDate('operational_date', $today)
                ->latest('id')
                ->first()
                ?? DeliveryRoute::with(['courier', 'location'])
                    ->where('location_id', $location->id)
                    ->orderByDesc('operational_date')
                    ->first())
            : null;

        $todayMenu = DailyMenu::query()
            ->whereDate('operational_date', $today)
            ->where('status', MenuStatus::DISETUJUI->value)
            ->first()
            ?? DailyMenu::query()->where('status', MenuStatus::DISETUJUI->value)->orderByDesc('operational_date')->first()
            ?? DailyMenu::query()->orderByDesc('operational_date')->first();

        return view('guru-pic.konfirmasi', [
            'location' => $location,
            'route' => $route,
            'todayMenu' => $todayMenu,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $location = $user?->location_id
            ? Location::findOrFail($user->location_id)
            : Location::where('is_active', true)->orderBy('id')->firstOrFail();

        $route = DeliveryRoute::query()
            ->where('location_id', $location->id)
            ->orderByDesc('operational_date')
            ->orderByDesc('id')
            ->firstOrFail();

        $maxPortions = max(1, (int) ($route->dispatched_portions ?: $location->target_portions ?: 250));

        $validated = $request->validate([
            'confirmed_portions' => ['required', 'integer', 'min:0', "max:{$maxPortions}"],
            'arrival_time' => ['required', 'date_format:H:i'],
            'packaging_condition' => ['required', 'in:baik,sebagian-tumpah,tidak-layak'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'organoleptic_taste' => ['required', 'in:normal,kurang,tidak-layak'],
            'organoleptic_aroma' => ['required', 'in:normal,kurang,tidak-layak'],
            'organoleptic_texture' => ['required', 'in:normal,kurang,tidak-layak'],
            'organoleptic_color' => ['required', 'in:normal,kurang,tidak-layak'],
        ], [
            'confirmed_portions.required' => 'Jumlah porsi diterima wajib diisi.',
            'confirmed_portions.max' => "Jumlah porsi diterima tidak boleh melebihi porsi dikirim ({$maxPortions}).",
            'arrival_time.required' => 'Jam tiba aktual wajib diisi.',
            'packaging_condition.required' => 'Kondisi kemasan & makanan wajib dipilih.',
            'organoleptic_taste.required' => 'Penilaian uji rasa wajib diisi.',
            'organoleptic_aroma.required' => 'Penilaian uji aroma wajib diisi.',
            'organoleptic_texture.required' => 'Penilaian uji tekstur wajib diisi.',
            'organoleptic_color.required' => 'Penilaian uji warna wajib diisi.',
        ]);

        $opDate = $route->operational_date?->copy() ?? now('Asia/Jakarta');
        $arrivedAt = $opDate->copy()->setTimeFromTimeString($validated['arrival_time'].':00');

        $tests = [
            $validated['organoleptic_taste'],
            $validated['organoleptic_aroma'],
            $validated['organoleptic_texture'],
            $validated['organoleptic_color'],
        ];

        $organolepticStatus = 'Layak';
        if (in_array('tidak-layak', $tests, true) || $validated['packaging_condition'] === 'tidak-layak') {
            $organolepticStatus = 'Tidak Layak';
        } elseif (in_array('kurang', $tests, true)) {
            $organolepticStatus = 'Perlu Perhatian';
        }

        $hasIssue = (int) $validated['confirmed_portions'] < $maxPortions
            || $validated['packaging_condition'] !== 'baik'
            || $organolepticStatus !== 'Layak';

        $oldValues = [
            'confirmed_portions' => $route->confirmed_portions,
            'status' => $route->status?->value,
            'organoleptic_status' => $route->organoleptic_status,
        ];

        $route->update([
            'confirmed_portions' => (int) $validated['confirmed_portions'],
            'arrived_at' => $arrivedAt,
            'confirmed_at' => now('Asia/Jakarta'),
            'confirmed_by' => $user?->id,
            'packaging_condition' => $validated['packaging_condition'],
            'notes' => $validated['notes'] ?? null,
            'organoleptic_taste' => $validated['organoleptic_taste'],
            'organoleptic_aroma' => $validated['organoleptic_aroma'],
            'organoleptic_texture' => $validated['organoleptic_texture'],
            'organoleptic_color' => $validated['organoleptic_color'],
            'organoleptic_status' => $organolepticStatus,
            'status' => $hasIssue ? DeliveryStatus::BERMASALAH : DeliveryStatus::DITERIMA,
        ]);

        AuditLogService::record(
            action: 'konfirmasi',
            module: 'Guru PIC · Konfirmasi & Organoleptik',
            target: $location->name.' · '.$route->waybill_number,
            oldValues: $oldValues,
            newValues: [
                'confirmed_portions' => (int) $validated['confirmed_portions'],
                'arrival_time' => $validated['arrival_time'],
                'packaging_condition' => $validated['packaging_condition'],
                'organoleptic_status' => $organolepticStatus,
            ],
            actor: $user,
        );

        return redirect()
            ->route('guru-pic.confirmation.index')
            ->with('success', 'Konfirmasi penerimaan & uji organoleptik berhasil disimpan.');
    }
}
