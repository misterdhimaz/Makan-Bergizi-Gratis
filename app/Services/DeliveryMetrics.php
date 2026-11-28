<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\MenuStatus;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\DataRevision;
use App\Models\DeliveryRoute;
use App\Models\FoodSafetyFinding;
use Carbon\Carbon;

class DeliveryMetrics
{
    /**
     * Hitung seluruh metrik pengiriman dan indikator operasional dari database.
     *
     * @return array<string, mixed>
     */
    public function forDate(?Carbon $operationalDate = null): array
    {
        $date = ($operationalDate ?? now('Asia/Jakarta'))->toDateString();

        $routes = DeliveryRoute::query()
            ->with('location')
            ->whereDate('operational_date', $date)
            ->orderBy('waybill_number')
            ->get();

        $dikirim = (int) $routes
            ->filter(fn (DeliveryRoute $r): bool => $r->status !== DeliveryStatus::DISIAPKAN)
            ->sum('dispatched_portions');

        $confirmedRoutes = $routes->filter(
            fn (DeliveryRoute $r): bool => $r->confirmed_portions !== null
        );

        $diterima = (int) $confirmedRoutes->sum('confirmed_portions');
        $dispatchedOnConfirmed = (int) $confirmedRoutes->sum('dispatched_portions');
        $belumDikonfirmasi = max(0, $dikirim - $dispatchedOnConfirmed);
        $selisihTerkonfirmasi = (int) $confirmedRoutes->sum(
            fn (DeliveryRoute $r): int => (int) $r->confirmed_portions - (int) $r->dispatched_portions
        );

        $titikTerkonfirmasi = $confirmedRoutes->count();
        $totalTitik = $routes->count();

        $statusCounts = [];
        foreach (DeliveryStatus::cases() as $status) {
            $statusCounts[$status->value] = $routes->where('status', $status)->count();
        }

        $keluhanAktif = ComplaintTicket::query()
            ->whereNotIn('status', ['resolved', 'selesai'])
            ->count();

        $keluhanSerius = ComplaintTicket::query()
            ->whereNotIn('status', ['resolved', 'selesai'])
            ->whereIn('severity', ['kritis', 'serius'])
            ->count();

        $temuanTerbuka = FoodSafetyFinding::query()
            ->where('status', 'terbuka')
            ->count();

        $koreksiMenunggu = DataRevision::query()
            ->where('status', 'menunggu')
            ->whereNull('approved_at')
            ->count();

        $menuMenungguKepalaDapur = DailyMenu::query()
            ->where('status', MenuStatus::LOLOS_PAGU->value)
            ->count();

        return [
            'operational_date' => $date,
            'dikirim' => $dikirim,
            'diterima' => $diterima,
            'titik_terkonfirmasi' => $titikTerkonfirmasi,
            'total_titik' => $totalTitik,
            'belum_dikonfirmasi' => $belumDikonfirmasi,
            'selisih_terkonfirmasi' => $selisihTerkonfirmasi,
            'ringkasan_titik' => "{$titikTerkonfirmasi} dari {$totalTitik} titik terkonfirmasi",
            'status_counts' => $statusCounts,
            'keluhan_aktif' => $keluhanAktif,
            'keluhan_serius' => $keluhanSerius,
            'temuan_keamanan_terbuka' => $temuanTerbuka,
            'koreksi_menunggu' => $koreksiMenunggu,
            'menu_menunggu_kepala_dapur' => $menuMenungguKepalaDapur,
        ];
    }
}
