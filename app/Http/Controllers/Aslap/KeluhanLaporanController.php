<?php

namespace App\Http\Controllers\Aslap;

use App\Exports\OperationalReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Aslap\HandleAslapComplaintRequest;
use App\Models\ComplaintTicket;
use App\Models\DeliveryRoute;
use App\Services\AslapService;
use App\Services\AuditLogger;
use App\Services\ReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KeluhanLaporanController extends Controller
{
    public function index(Request $request, AslapService $service, ReportBuilder $reportBuilder): View
    {
        $todayStr = now('Asia/Jakarta')->toDateString();
        $routesToday = DeliveryRoute::query()
            ->with(['location', 'confirmer'])
            ->whereDate('operational_date', $todayStr)
            ->orderBy('waybill_number')
            ->get();

        $complaints = ComplaintTicket::query()
            ->with(['location', 'reporter', 'resolver', 'histories.user'])
            ->orderByRaw("CASE WHEN status IN ('open','in_progress','baru','dalam_tindak_lanjut') THEN 0 ELSE 1 END")
            ->orderByDesc('reported_at')
            ->get();

        [$periodType, $startDate, $endDate] = $this->parseFilters($request);
        $report = $reportBuilder->build($startDate, $endDate, $periodType);

        return view('aslap.keluhan-laporan', [
            'routesToday' => $routesToday,
            'complaints' => $complaints,
            'periodType' => $periodType,
            'selectedDate' => $endDate->toDateString(),
            'report' => $report,
            'complaintBadge' => $service->activeComplaintsBadge(),
        ]);
    }

    public function handleComplaint(
        HandleAslapComplaintRequest $request,
        ComplaintTicket $ticket,
        AslapService $service,
    ): RedirectResponse {
        $handledAt = $request->filled('handled_at')
            ? Carbon::parse((string) $request->input('handled_at'), 'Asia/Jakarta')
            : now('Asia/Jakarta');

        $service->handleComplaint(
            ticket: $ticket,
            actor: $request->user(),
            status: $request->string('status')->toString(),
            resolutionNotes: $request->string('resolution_notes')->toString(),
            handledAt: $handledAt,
        );

        return redirect()
            ->route('aslap.keluhan.index')
            ->with('success', 'Tindakan penyelesaian keluhan berhasil disimpan.');
    }

    public function exportPdf(Request $request, ReportBuilder $reportBuilder): Response
    {
        [$periodType, $startDate, $endDate] = $this->parseFilters($request);
        $report = $reportBuilder->build($startDate, $endDate, $periodType);

        AuditLogger::record(
            action: 'Mengekspor laporan distribusi & keluhan PDF',
            module: 'Keluhan & Laporan Aslap',
            target: $report['period_label'],
            actor: $request->user(),
        );

        $pdf = Pdf::loadView('aslap.laporan-pdf', [
            'report' => $report,
            'generatedBy' => $request->user()->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-distribusi-keluhan-'.$endDate->format('Ymd').'.pdf');
    }

    public function exportExcel(Request $request, ReportBuilder $reportBuilder): BinaryFileResponse
    {
        [$periodType, $startDate, $endDate] = $this->parseFilters($request);
        $report = $reportBuilder->build($startDate, $endDate, $periodType);

        AuditLogger::record(
            action: 'Mengekspor laporan distribusi & keluhan Excel',
            module: 'Keluhan & Laporan Aslap',
            target: $report['period_label'],
            actor: $request->user(),
        );

        return Excel::download(
            $this->makeExport($report),
            'laporan-distribusi-keluhan-'.$endDate->format('Ymd').'.xlsx',
            ExcelFormat::XLSX
        );
    }

    public function exportCsv(Request $request, ReportBuilder $reportBuilder): BinaryFileResponse
    {
        [$periodType, $startDate, $endDate] = $this->parseFilters($request);
        $report = $reportBuilder->build($startDate, $endDate, $periodType);

        AuditLogger::record(
            action: 'Mengekspor laporan distribusi & keluhan CSV',
            module: 'Keluhan & Laporan Aslap',
            target: $report['period_label'],
            actor: $request->user(),
        );

        return Excel::download(
            $this->makeExport($report),
            'laporan-distribusi-keluhan-'.$endDate->format('Ymd').'.csv',
            ExcelFormat::CSV
        );
    }

    /**
     * @return array{0:string,1:Carbon,2:Carbon}
     */
    private function parseFilters(Request $request): array
    {
        $periodType = $request->string('jenis')->toString();
        if (! in_array($periodType, ['harian', '7hari', '30hari'], true)) {
            $periodType = 'harian';
        }

        $dateStr = $request->string('tanggal')->toString();
        $endDate = $dateStr !== '' ? Carbon::parse($dateStr, 'Asia/Jakarta') : now('Asia/Jakarta');

        $startDate = match ($periodType) {
            '7hari' => $endDate->copy()->subDays(6),
            '30hari' => $endDate->copy()->subDays(29),
            default => $endDate->copy(),
        };

        return [$periodType, $startDate, $endDate];
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function makeExport(array $report): OperationalReportExport
    {
        $headings = [
            'Nomor Pengiriman',
            'Tanggal',
            'Lokasi Penerima',
            'Status Pengiriman',
            'Dikirim',
            'Diterima',
            'Selisih',
            'Pengantar',
            'Armada',
        ];

        $rows = $report['routes']->map(function ($route): array {
            $variance = $route->confirmed_portions !== null
                ? ($route->confirmed_portions - $route->dispatched_portions)
                : '-';

            return [
                $route->waybill_number,
                $route->operational_date?->format('Y-m-d') ?? '-',
                $route->location?->name ?? '-',
                $route->status->label(),
                $route->dispatched_portions,
                $route->confirmed_portions ?? '-',
                $variance,
                $route->courier_name ?? '-',
                $route->vehicle_plate ?? '-',
            ];
        });

        return new OperationalReportExport($headings, $rows);
    }
}
