<?php

namespace App\Services;

use App\Enums\MenuStatus;
use App\Exports\OperationalReportExport;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\DataRevision;
use App\Models\DeliveryRoute;
use App\Models\FoodSafetyCheck;
use App\Models\FoodSafetyFinding;
use App\Models\InventoryTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportBuilder
{
    public function __construct(
        private readonly DeliveryMetrics $deliveryMetrics,
    ) {}

    /**
     * Ringkasan harian atau berkala untuk PDF, Excel, dan CSV.
     *
     * @return array<string, mixed>
     */
    public function build(Carbon $startDate, Carbon $endDate, ?string $periodType = 'harian'): array
    {
        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();

        $routes = DeliveryRoute::query()
            ->with(['location', 'confirmer'])
            ->whereDate('operational_date', '>=', $startStr)
            ->whereDate('operational_date', '<=', $endStr)
            ->orderBy('operational_date')
            ->orderBy('waybill_number')
            ->get();

        $menus = DailyMenu::query()
            ->with(['submittedBy', 'approvedBy', 'latestPaguCheck.checker'])
            ->whereDate('operational_date', '>=', $startStr)
            ->whereDate('operational_date', '<=', $endStr)
            ->orderBy('operational_date')
            ->get();

        $complaints = ComplaintTicket::query()
            ->with(['location', 'reporter', 'resolver'])
            ->whereDate('reported_at', '>=', $startStr)
            ->whereDate('reported_at', '<=', $endStr)
            ->orderByDesc('reported_at')
            ->get();

        $safetyChecks = FoodSafetyCheck::query()
            ->with('inspector')
            ->whereDate('check_date', '>=', $startStr)
            ->whereDate('check_date', '<=', $endStr)
            ->orderByDesc('check_date')
            ->get();

        $safetyFindings = FoodSafetyFinding::query()
            ->with('handler')
            ->whereDate('finding_date', '>=', $startStr)
            ->whereDate('finding_date', '<=', $endStr)
            ->orderByDesc('finding_date')
            ->get();

        $revisions = DataRevision::query()
            ->with(['requester', 'approver'])
            ->whereDate('created_at', '>=', $startStr)
            ->whereDate('created_at', '<=', $endStr)
            ->orderByDesc('created_at')
            ->get();

        $procurements = InventoryTransaction::query()
            ->with(['ingredient.supplier', 'dailyMenu'])
            ->where('type', 'inbound_purchase')
            ->whereHas('dailyMenu', function ($q) use ($startStr, $endStr) {
                $q->whereDate('operational_date', '>=', $startStr)
                    ->whereDate('operational_date', '<=', $endStr);
            })
            ->get();

        $todayMetrics = $this->deliveryMetrics->forDate($endDate);

        $totalDispatched = (int) $routes->filter(fn ($r) => $r->status->value !== 'Disiapkan')->sum('dispatched_portions');
        $confirmedRoutes = $routes->filter(fn ($r) => $r->confirmed_portions !== null);
        $totalConfirmed = (int) $confirmedRoutes->sum('confirmed_portions');
        $totalVariance = (int) $confirmedRoutes->sum(fn ($r) => (int) $r->confirmed_portions - (int) $r->dispatched_portions);

        return [
            'period_type' => $periodType,
            'start_date' => $startStr,
            'end_date' => $endStr,
            'period_label' => $startStr === $endStr
                ? $startDate->copy()->timezone('Asia/Jakarta')->translatedFormat('l, j M Y')
                : $startDate->copy()->timezone('Asia/Jakarta')->translatedFormat('j M Y').' – '.$endDate->copy()->timezone('Asia/Jakarta')->translatedFormat('j M Y'),
            'today_metrics' => $todayMetrics,
            'summary' => [
                'total_routes' => $routes->count(),
                'total_dispatched' => $totalDispatched,
                'total_confirmed' => $totalConfirmed,
                'total_variance' => $totalVariance,
                'confirmed_count' => $confirmedRoutes->count(),
                'menus_approved' => $menus->where('status', MenuStatus::DISETUJUI->value)->count(),
                'menus_total' => $menus->count(),
                'complaints_total' => $complaints->count(),
                'complaints_resolved' => $complaints->whereIn('status', ['resolved', 'selesai'])->count(),
                'safety_findings_open' => $safetyFindings->where('status', 'terbuka')->count(),
                'revisions_total' => $revisions->count(),
            ],
            'routes' => $routes,
            'menus' => $menus,
            'complaints' => $complaints,
            'safety_checks' => $safetyChecks,
            'safety_findings' => $safetyFindings,
            'revisions' => $revisions,
            'procurements' => $procurements,
        ];
    }

    /**
     * Parse filter periode (harian, 7hari, 30hari) dan tanggal akhir dari request.
     *
     * @return array{0:string,1:Carbon,2:Carbon}
     */
    public function parseDateFilters(Request $request): array
    {
        $periodType = $request->string('jenis')->toString();
        if (! in_array($periodType, ['harian', '7hari', '30hari'], true)) {
            $periodType = 'harian';
        }

        $dateStr = $request->string('tanggal')->toString();
        $endDate = $dateStr !== ''
            ? Carbon::parse($dateStr, 'Asia/Jakarta')
            : now('Asia/Jakarta');

        $startDate = match ($periodType) {
            '7hari' => $endDate->copy()->subDays(6),
            '30hari' => $endDate->copy()->subDays(29),
            default => $endDate->copy(),
        };

        return [$periodType, $startDate, $endDate];
    }

    /**
     * Bangun objek ekspor Excel/CSV dari ringkasan laporan.
     *
     * @param  array<string, mixed>  $report
     */
    public function makeDistributionExport(array $report): OperationalReportExport
    {
        $headings = [
            'Tanggal',
            'Nomor Pengiriman',
            'Lokasi Penerima',
            'Status',
            'Dikirim (Porsi)',
            'Diterima (Porsi)',
            'Selisih',
            'Pengantar',
            'Armada',
        ];

        $rows = $report['routes']->map(function (DeliveryRoute $route): array {
            $variance = $route->confirmed_portions !== null
                ? ($route->confirmed_portions - $route->dispatched_portions)
                : '-';

            return [
                $route->operational_date?->format('Y-m-d') ?? '-',
                $route->waybill_number,
                $route->location?->name ?? '-',
                $route->status->label(),
                $route->dispatched_portions,
                $route->confirmed_portions ?? '-',
                $variance,
                $route->courier_name ?? $route->courier?->name ?? '-',
                $route->vehicle_plate ?? '-',
            ];
        });

        return new OperationalReportExport($headings, $rows);
    }

    /**
     * Baris ringkas untuk ekspor CSV / Excel.
     *
     * @return Collection<int, array<int, string|int>>
     */
    public function distributionRows(Carbon $startDate, Carbon $endDate): Collection
    {
        $data = $this->build($startDate, $endDate);

        return $data['routes']->map(function (DeliveryRoute $route): array {
            $variance = $route->confirmed_portions !== null
                ? ($route->confirmed_portions - $route->dispatched_portions)
                : '-';

            return [
                $route->operational_date?->format('Y-m-d') ?? '-',
                $route->waybill_number,
                $route->location?->name ?? '-',
                $route->status->label(),
                $route->dispatched_portions,
                $route->confirmed_portions ?? '-',
                $variance,
                $route->courier_name ?? $route->courier?->name ?? '-',
                $route->vehicle_plate ?? '-',
                $route->departed_at?->timezone('Asia/Jakarta')->format('H:i') ?? '-',
                $route->arrived_at?->timezone('Asia/Jakarta')->format('H:i') ?? '-',
            ];
        });
    }
}
