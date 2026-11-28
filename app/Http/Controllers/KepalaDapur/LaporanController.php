<?php

namespace App\Http\Controllers\KepalaDapur;

use App\Exports\OperationalReportExport;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\AuditLogger;
use App\Services\KepalaDapurService;
use App\Services\ReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function index(Request $request, ReportBuilder $reportBuilder, KepalaDapurService $service): View
    {
        [$periodType, $startDate, $endDate, $locationId] = $this->parseFilters($request);
        $report = $this->filteredReport($reportBuilder, $periodType, $startDate, $endDate, $locationId);
        $locations = Location::query()->where('is_active', true)->orderBy('name')->get();

        return view('kepala-dapur.laporan', [
            'periodType' => $periodType,
            'selectedDate' => $endDate->toDateString(),
            'selectedLocationId' => $locationId,
            'locations' => $locations,
            'report' => $report,
            'badges' => $service->sidebarBadges(),
        ]);
    }

    public function exportPdf(Request $request, ReportBuilder $reportBuilder): Response
    {
        [$periodType, $startDate, $endDate, $locationId] = $this->parseFilters($request);
        $report = $this->filteredReport($reportBuilder, $periodType, $startDate, $endDate, $locationId);

        AuditLogger::record(
            action: 'Mengekspor laporan operasional PDF',
            module: 'Laporan Kepala Dapur',
            target: $report['period_label'],
            actor: $request->user(),
        );

        $pdf = Pdf::loadView('kepala-dapur.laporan-pdf', [
            'report' => $report,
            'generatedBy' => $request->user()->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-operasional-'.$endDate->format('Ymd').'.pdf');
    }

    public function exportExcel(Request $request, ReportBuilder $reportBuilder): BinaryFileResponse
    {
        [$periodType, $startDate, $endDate, $locationId] = $this->parseFilters($request);
        $report = $this->filteredReport($reportBuilder, $periodType, $startDate, $endDate, $locationId);

        AuditLogger::record(
            action: 'Mengekspor laporan operasional Excel',
            module: 'Laporan Kepala Dapur',
            target: $report['period_label'],
            actor: $request->user(),
        );

        return Excel::download(
            $this->makeExport($report),
            'laporan-operasional-'.$endDate->format('Ymd').'.xlsx',
            ExcelFormat::XLSX
        );
    }

    public function exportCsv(Request $request, ReportBuilder $reportBuilder): BinaryFileResponse
    {
        [$periodType, $startDate, $endDate, $locationId] = $this->parseFilters($request);
        $report = $this->filteredReport($reportBuilder, $periodType, $startDate, $endDate, $locationId);

        AuditLogger::record(
            action: 'Mengekspor laporan operasional CSV',
            module: 'Laporan Kepala Dapur',
            target: $report['period_label'],
            actor: $request->user(),
        );

        return Excel::download(
            $this->makeExport($report),
            'laporan-operasional-'.$endDate->format('Ymd').'.csv',
            ExcelFormat::CSV
        );
    }

    /**
     * @return array{0:string,1:Carbon,2:Carbon,3:?int}
     */
    private function parseFilters(Request $request): array
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

        $locationId = $request->filled('lokasi_id') ? (int) $request->input('lokasi_id') : null;

        return [$periodType, $startDate, $endDate, $locationId];
    }

    /**
     * @return array<string, mixed>
     */
    private function filteredReport(
        ReportBuilder $reportBuilder,
        string $periodType,
        Carbon $startDate,
        Carbon $endDate,
        ?int $locationId,
    ): array {
        $report = $reportBuilder->build($startDate, $endDate, $periodType);

        if ($locationId) {
            $report['routes'] = $report['routes']->where('location_id', $locationId)->values();
            $report['complaints'] = $report['complaints']->where('location_id', $locationId)->values();
            $report['summary']['total_routes'] = $report['routes']->count();
            $report['summary']['total_dispatched'] = (int) $report['routes']->filter(fn ($r) => $r->status->value !== 'Disiapkan')->sum('dispatched_portions');
            $confirmed = $report['routes']->filter(fn ($r) => $r->confirmed_portions !== null);
            $report['summary']['total_confirmed'] = (int) $confirmed->sum('confirmed_portions');
            $report['summary']['confirmed_count'] = $confirmed->count();
            $report['summary']['total_variance'] = (int) $confirmed->sum(fn ($r) => (int) $r->confirmed_portions - (int) $r->dispatched_portions);
        }

        return $report;
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function makeExport(array $report): OperationalReportExport
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

        $rows = $report['routes']->map(function ($route): array {
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
}
