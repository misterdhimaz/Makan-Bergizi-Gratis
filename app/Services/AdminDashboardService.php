<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\DeliveryRoute;
use App\Models\Location;
use App\Models\SystemBackup;
use App\Models\User;
use Carbon\Carbon;

class AdminDashboardService
{
    public function __construct(
        private readonly DataMasterCompletenessService $completenessService,
    ) {}

    /**
     * Menghitung seluruh metrik Dashboard Admin per operational_date.
     *
     * @return array<string, mixed>
     */
    public function metrics(?Carbon $operationalDate = null): array
    {
        $date = ($operationalDate ?? now())->toDateString();

        $routes = DeliveryRoute::with('location')
            ->whereDate('operational_date', $date)
            ->get();

        $dispatchedStatuses = [
            DeliveryStatus::BERANGKAT->value,
            DeliveryStatus::TIBA->value,
            DeliveryStatus::DITERIMA->value,
            DeliveryStatus::BERMASALAH->value,
        ];

        $unconfirmedStatuses = [
            DeliveryStatus::BERANGKAT->value,
            DeliveryStatus::TIBA->value,
            DeliveryStatus::BERMASALAH->value,
        ];

        $dikirim = (int) $routes
            ->filter(fn (DeliveryRoute $r): bool => in_array($r->status->value, $dispatchedStatuses, true))
            ->sum('dispatched_portions');

        $receivedRoutes = $routes->filter(
            fn (DeliveryRoute $r): bool => $r->status === DeliveryStatus::DITERIMA
        );

        $diterima = (int) $receivedRoutes->sum('confirmed_portions');
        $dispatchedOnReceived = (int) $receivedRoutes->sum('dispatched_portions');

        $belumDikonfirmasi = $dikirim - $dispatchedOnReceived;

        $selisihTerkonfirmasi = (int) $receivedRoutes->sum(
            fn (DeliveryRoute $r): int => (int) ($r->confirmed_portions ?? 0) - (int) $r->dispatched_portions
        );

        $jumlahTitikBelumDikonfirmasi = $routes
            ->filter(fn (DeliveryRoute $r): bool => in_array($r->status->value, $unconfirmedStatuses, true))
            ->count();

        $masihDisiapkan = (int) $routes
            ->filter(fn (DeliveryRoute $r): bool => $r->status === DeliveryStatus::DISIAPKAN)
            ->sum('dispatched_portions');

        $master = $this->completenessService->evaluate();

        $users = User::all();
        $akunAktif = $users->where('is_active', true)->count();
        $akunNonaktif = $users->where('is_active', false)->count();
        $akunTotal = $users->count();
        $peranTerpakai = $users->where('is_active', true)->pluck('role')->unique()->count();

        $akunPerPeran = [];
        foreach (UserRole::cases() as $role) {
            $akunPerPeran[$role->value] = [
                'label' => $role->label(),
                'aktif' => $users->where('role', $role)->where('is_active', true)->count(),
                'total' => $users->where('role', $role)->count(),
            ];
        }

        $loginGagal24Jam = AuditLog::where('action', 'login_gagal')
            ->where('occurred_at', '>=', now()->subHours(24))
            ->count();

        $recentLogs = AuditLog::query()
            ->orderByDesc('occurred_at')
            ->limit(8)
            ->get();

        $lastBackup = SystemBackup::query()
            ->where('status', 'success')
            ->orderByDesc('created_at')
            ->first();

        $targetPorsi = (int) Location::query()->where('is_active', true)->sum('target_portions');
        $jumlahLokasi = (int) Location::query()->where('is_active', true)->count();

        return [
            'operational_date' => $date,
            'dikirim' => $dikirim,
            'diterima' => $diterima,
            'belum_dikonfirmasi' => $belumDikonfirmasi,
            'selisih_terkonfirmasi' => $selisihTerkonfirmasi,
            'jumlah_titik_belum_dikonfirmasi' => $jumlahTitikBelumDikonfirmasi,
            'masih_disiapkan' => $masihDisiapkan,
            'titik_terkonfirmasi' => $receivedRoutes->count(),
            'target_porsi' => $targetPorsi,
            'jumlah_lokasi' => $jumlahLokasi,
            'data_master_siap' => $master['siap_count'],
            'data_master_total' => $master['total'],
            'data_master_items' => $master['items'],
            'akun_aktif' => $akunAktif,
            'akun_nonaktif' => $akunNonaktif,
            'akun_total' => $akunTotal,
            'peran_terpakai' => $peranTerpakai,
            'peran_total' => count(UserRole::cases()),
            'akun_per_peran' => $akunPerPeran,
            'login_gagal_24_jam' => $loginGagal24Jam,
            'recent_logs' => $recentLogs,
            'last_backup' => $lastBackup,
            'routes' => $routes,
        ];
    }
}
