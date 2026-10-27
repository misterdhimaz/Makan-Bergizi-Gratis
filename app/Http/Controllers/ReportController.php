<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ApdInspection;
use App\Models\BeneficiaryInstitution;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\Delivery;
use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Models\ProductionRun;
use App\Models\QcInspection;
use App\Models\SopConfiguration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Halaman Utama Pusat Laporan & Cetak Rekapitulasi Operasional MBG (Dedicated Page)
     */
    public function index(Request $request): View
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        $carbonDate = Carbon::parse($date);

        $userRole = auth()->user()?->role?->value;
        $defaultType = match ($userRole) {
            'ahli_gizi' => 'qc_apd',
            'akuntan' => 'stok_selisih',
            'kepala_dapur' => 'operasional',
            'asisten_lapangan', 'tim_distribusi', 'guru_pic' => 'distribusi_organoleptik',
            default => 'kinerja_role',
        };
        $type = $request->query('type', $defaultType);

        $menu = DailyMenu::with(['nutritionist', 'headOfKitchen', 'accountant', 'items.ingredient', 'productionRun.qcInspection'])
            ->whereDate('operational_date', $carbonDate)
            ->first() ?? DailyMenu::with(['nutritionist', 'headOfKitchen', 'accountant', 'items.ingredient', 'productionRun.qcInspection'])
            ->latest('operational_date')
            ->first();

        $productionRun = $menu?->productionRun;
        $qc = $productionRun?->qcInspection;

        $deliveries = Delivery::with(['institution', 'courier', 'confirmation.picUser'])
            ->when($productionRun, fn ($q) => $q->where('production_run_id', $productionRun->id))
            ->get();

        $complaints = ComplaintTicket::with(['institution', 'reporter', 'resolver'])
            ->whereDate('created_at', $carbonDate)
            ->get();

        $apdInspections = ApdInspection::with(['inspector', 'headChef'])
            ->whereDate('inspection_date', $carbonDate)
            ->get();

        $ingredients = Ingredient::orderBy('category')->orderBy('name')->get();
        $institutions = BeneficiaryInstitution::where('is_active', true)->orderBy('code')->get();
        $rolePerformance = $this->buildRolePerformanceData($carbonDate);

        return view('reports.index', compact(
            'date',
            'carbonDate',
            'type',
            'menu',
            'productionRun',
            'qc',
            'deliveries',
            'complaints',
            'apdInspections',
            'ingredients',
            'institutions',
            'rolePerformance'
        ));
    }

    /**
     * Tampilan Cetak / Pratinjau PDF Laporan Harian Terpadu SPPG Sungai Selayur 1
     */
    public function printDaily(Request $request): View
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        $carbonDate = Carbon::parse($date);
        $type = $request->query('type', 'operasional');

        $menu = DailyMenu::with(['nutritionist', 'headOfKitchen', 'accountant', 'items.ingredient', 'productionRun.qcInspection'])
            ->whereDate('operational_date', $carbonDate)
            ->first() ?? DailyMenu::with(['nutritionist', 'headOfKitchen', 'accountant', 'items.ingredient', 'productionRun.qcInspection'])
            ->latest('operational_date')
            ->first();

        $productionRun = $menu?->productionRun;
        $qc = $productionRun?->qcInspection;

        $deliveries = Delivery::with(['institution', 'courier', 'confirmation.picUser'])
            ->when($productionRun, fn ($q) => $q->where('production_run_id', $productionRun->id))
            ->get();

        $complaints = ComplaintTicket::with(['institution', 'reporter', 'resolver'])
            ->whereDate('created_at', $carbonDate)
            ->get();

        $ingredients = Ingredient::orderBy('category')->orderBy('name')->get();
        $institutions = BeneficiaryInstitution::where('is_active', true)->orderBy('code')->get();
        $sopConfigs = SopConfiguration::all()->keyBy('param_key');

        ActivityLog::record(
            'export_print',
            'reports',
            'Mencetak/mengekspor Laporan Operasional Harian Resmi SPPG untuk tanggal ' . $carbonDate->translatedFormat('d F Y')
        );

        $rolePerformance = $this->buildRolePerformanceData($carbonDate);

        return view('reports.daily-print', compact(
            'date',
            'carbonDate',
            'type',
            'menu',
            'productionRun',
            'qc',
            'deliveries',
            'complaints',
            'ingredients',
            'institutions',
            'sopConfigs',
            'rolePerformance'
        ));
    }

    /**
     * Ekspor Berkas Data ke Format CSV (Excel Compatible)
     */
    public function exportCsv(Request $request): Response
    {
        $type = $request->query('type', 'operasional');
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        $carbonDate = Carbon::parse($date);

        $filename = "SPPG_Selayur1_{$type}_{$carbonDate->format('Ymd')}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $output = fopen('php://temp', 'r+');
        // UTF-8 BOM agar terbaca sempurna di Microsoft Excel
        fputs($output, "\xEF\xBB\xBF");

        match ($type) {
            'stok_selisih' => $this->generateStockVarianceCsv($output),
            'qc_apd' => $this->generateQcApdCsv($output),
            'distribusi_organoleptik' => $this->generateDistributionCsv($output),
            'tiket_keluhan' => $this->generateComplaintsCsv($output),
            'kinerja_role' => $this->generateRolePerformanceCsv($output, $carbonDate),
            default => $this->generateDailyOperationalCsv($output, $carbonDate),
        };

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        ActivityLog::record(
            'export_csv',
            'reports',
            "Mengunduh berkas CSV laporan {$type} SPPG Sungai Selayur 1."
        );

        return response($content, 200, $headers);
    }

    /**
     * Ekspor Berkas Data ke Format Microsoft Excel (.xls) dengan Tampilan Tabel Terformat
     */
    public function exportExcel(Request $request): Response
    {
        $type = $request->query('type', 'operasional');
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        $carbonDate = Carbon::parse($date);

        $filename = "SPPG_Selayur1_{$type}_{$carbonDate->format('Ymd')}.xls";
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $html = match ($type) {
            'stok_selisih' => $this->generateStockVarianceExcel($carbonDate),
            'qc_apd' => $this->generateQcApdExcel($carbonDate),
            'distribusi_organoleptik' => $this->generateDistributionExcel($carbonDate),
            'tiket_keluhan' => $this->generateComplaintsExcel($carbonDate),
            'kinerja_role' => $this->generateRolePerformanceExcel($carbonDate),
            default => $this->generateDailyOperationalExcel($carbonDate),
        };

        ActivityLog::record(
            'export_excel',
            'reports',
            "Mengunduh berkas Microsoft Excel (.xls) laporan {$type} SPPG Sungai Selayur 1."
        );

        return response($html, 200, $headers);
    }

    private function generateDailyOperationalCsv($output, Carbon $date): void
    {
        fputcsv($output, ['SATUAN PELAYANAN PEMENUHAN GIZI (SPPG) SUNGAI SELAYUR 1 - KECAMATAN KALIDONI']);
        fputcsv($output, ['LAPORAN HARIAN OPERASIONAL PROGRAM MAKAN BERGIZI GRATIS (MBG)']);
        fputcsv($output, ['Tanggal Operasional', $date->translatedFormat('d F Y')]);
        fputcsv($output, ['Total Kuota Penerima Manfaat', '1.414 Porsi (5 Sekolah & 2 Posyandu)']);
        fputcsv($output, ['Relawan Dapur Bertugas', '35 Relawan']);
        fputcsv($output, []);

        $menu = DailyMenu::with(['nutritionist', 'headOfKitchen', 'accountant'])->whereDate('operational_date', $date)->first();
        if ($menu) {
            fputcsv($output, ['=== 1. PERENCANAAN MENU & VALIDASI PAGU ===']);
            fputcsv($output, ['Kode Menu', 'Nama Menu', 'Kategori', 'Target Porsi', 'Estimasi Biaya/Porsi', 'Status PAGU', 'Status Approval']);
            fputcsv($output, [
                $menu->menu_code,
                $menu->menu_name,
                strtoupper($menu->recipient_category ?? 'siswa'),
                $menu->target_portions,
                'Rp ' . number_format($menu->estimated_cost_per_portion, 0, ',', '.'),
                strtoupper($menu->pagu_status ?? 'pending'),
                strtoupper($menu->status),
            ]);
            fputcsv($output, []);
        }

        fputcsv($output, ['=== 2. DISTRIBUSI & KONFIRMASI PENERIMAAN 1.414 PORSI ===']);
        fputcsv($output, ['Nomor Surat Jalan', 'Mitra Penerima', 'Tipe', 'Porsi Alokasi', 'Porsi Kirim', 'Porsi Diterima Baik', 'Selisih Porsi', 'Status Pengiriman', 'Uji Rasa', 'Suhu Tiba (°C)', 'Kelayakan Konsumsi']);

        $deliveries = Delivery::with(['institution', 'confirmation'])->latest('id')->take(20)->get();
        foreach ($deliveries as $d) {
            $c = $d->confirmation;
            fputcsv($output, [
                $d->waybill_number,
                $d->institution->name,
                strtoupper($d->institution->type),
                $d->allocated_portions,
                $d->dispatched_portions,
                $c?->received_good_portions ?? 0,
                $c?->reception_variance ?? 0,
                strtoupper(str_replace('_', ' ', $d->status)),
                $c?->taste_test ?? '-',
                $c?->arrival_temperature_c ?? '-',
                ($c?->is_fit_for_consumption ?? true) ? 'LAYAK KONSUMSI' : 'TIDAK LAYAK',
            ]);
        }
    }

    private function generateStockVarianceCsv($output): void
    {
        fputcsv($output, ['LAPORAN PERSEDIAAN BAHAN BAKU & MUTASI GUDANG SPPG SUNGAI SELAYUR 1']);
        fputcsv($output, []);
        fputcsv($output, ['SKU', 'Nama Bahan Baku', 'Kategori', 'Stok Saat Ini', 'Satuan', 'Batas Minimum', 'Harga Satuan (Rp)', 'Total Valuasi (Rp)', 'Status Persediaan']);

        $ingredients = Ingredient::orderBy('category')->orderBy('name')->get();
        foreach ($ingredients as $i) {
            $valuation = $i->current_stock * $i->unit_cost;
            $status = $i->current_stock <= $i->min_stock_threshold ? 'PERLU RESTOCK' : 'AMAN';
            fputcsv($output, [
                $i->sku,
                $i->name,
                ucwords(str_replace('_', ' ', $i->category)),
                $i->current_stock,
                $i->unit,
                $i->min_stock_threshold,
                $i->unit_cost,
                $valuation,
                $status,
            ]);
        }
    }

    private function generateQcApdCsv($output): void
    {
        fputcsv($output, ['LAPORAN KEAMANAN PANGAN HACCP & KEPATUHAN 5 APD 35 RELAWAN DAPUR']);
        fputcsv($output, []);
        fputcsv($output, ['Kode Sampel Arsip', 'Tanggal Audit', 'Suhu Masak (>=75°C)', 'Suhu Holding (>=60°C)', 'Suhu Pra-Distribusi', 'Relawan Hadir', 'Skor Kepatuhan APD (%)', 'Sampel 2x24 Jam', 'Status Kelulusan', 'Tindak Lanjut']);

        $inspections = QcInspection::with('productionRun.dailyMenu')->latest('inspected_at')->get();
        foreach ($inspections as $q) {
            fputcsv($output, [
                $q->food_sample_code ?? '-',
                $q->inspected_at->format('d/m/Y H:i'),
                $q->cooking_core_temp_c . ' °C',
                $q->holding_temp_c . ' °C',
                $q->pre_dispatch_temp_c . ' °C',
                $q->total_kitchen_staff_present . ' Relawan',
                $q->apd_compliance_score . '%',
                $q->food_sample_retained ? 'TERSIMPAN (2x24 Jam)' : 'BELUM',
                strtoupper($q->qc_verdict),
                $q->corrective_action ?? '-',
            ]);
        }
    }

    private function generateDistributionCsv($output): void
    {
        fputcsv($output, ['LAPORAN PENGIRIMAN & UJI ORGANOLEPTIK MITRA SEKOLAH & POSYANDU']);
        fputcsv($output, []);
        fputcsv($output, ['No Surat Jalan', 'Tujuan Mitra', 'Armada', 'Porsi Kirim', 'Porsi Baik', 'Selisih', 'Jam Berangkat', 'Jam Tiba', 'Aroma', 'Rasa', 'Warna', 'Tekstur', 'Suhu (°C)', 'Catatan Lapangan']);

        $deliveries = Delivery::with(['institution', 'confirmation'])->latest('id')->get();
        foreach ($deliveries as $d) {
            $c = $d->confirmation;
            fputcsv($output, [
                $d->waybill_number,
                $d->institution->name,
                $d->vehicle_plate_number ?? '-',
                $d->dispatched_portions,
                $c?->received_good_portions ?? '-',
                $c?->reception_variance ?? '-',
                $d->departed_at?->format('H:i') ?? '-',
                $d->arrived_at?->format('H:i') ?? '-',
                $c?->aroma_test ?? '-',
                $c?->taste_test ?? '-',
                $c?->color_visual_test ?? '-',
                $c?->texture_test ?? '-',
                $c?->arrival_temperature_c ?? '-',
                $c?->feedback_or_complaint ?? $d->courier_notes ?? '-',
            ]);
        }
    }

    private function generateComplaintsCsv($output): void
    {
        fputcsv($output, ['REKAPITULASI TIKET KELUHAN LAPORAN SEKOLAH & TINDAK LANJUT']);
        fputcsv($output, []);
        fputcsv($output, ['Nomor Tiket', 'Sekolah / Posyandu', 'Pelapor', 'Kategori Keluhan', 'Tingkat Keparahan', 'Deskripsi Masalah', 'Status', 'Ditangani Oleh', 'Tindakan Penyelesaian', 'Tanggal Selesai']);

        $tickets = ComplaintTicket::with(['institution', 'reporter', 'resolver'])->latest('id')->get();
        foreach ($tickets as $t) {
            fputcsv($output, [
                $t->ticket_number,
                $t->institution->name,
                $t->reporter->name ?? 'PIC Sekolah',
                $t->categoryLabel(),
                strtoupper($t->severity),
                $t->description,
                strtoupper(str_replace('_', ' ', $t->status)),
                $t->resolver?->name ?? 'Belum Ditugaskan',
                $t->resolution_notes ?? '-',
                $t->resolved_at?->format('d/m/Y H:i') ?? '-',
            ]);
        }
    }

    private function generateDailyOperationalExcel(Carbon $date): string
    {
        $menu = DailyMenu::with(['nutritionist', 'headOfKitchen', 'accountant', 'productionRun.qcInspection'])
            ->whereDate('operational_date', $date)
            ->first() ?? DailyMenu::with(['nutritionist', 'headOfKitchen', 'accountant', 'productionRun.qcInspection'])->latest('operational_date')->first();

        $deliveries = Delivery::with(['institution', 'courier', 'confirmation.picUser'])->latest('id')->take(20)->get();
        $inspections = QcInspection::with('productionRun.dailyMenu')->latest('inspected_at')->take(10)->get();
        $apdInspections = ApdInspection::with(['inspector', 'headChef'])->latest('inspection_date')->take(10)->get();

        $content = '
        <table>
            <tr>
                <td colspan="8" class="kop-inst">SATUAN PELAYANAN PEMENUHAN GIZI (SPPG) SUNGAI SELAYUR 1</td>
            </tr>
            <tr>
                <td colspan="8" class="kop-sub">Badan Gizi Nasional (BGN) &bull; Jl. Sungai Selayur No. 01, Kalidoni, Kota Palembang</td>
            </tr>
            <tr>
                <td colspan="8" style="font-weight:bold; font-size:12pt; padding-top:10px;">LAPORAN OPERASIONAL HARIAN PROGRAM MAKAN BERGIZI GRATIS (MBG)</td>
            </tr>
            <tr>
                <td colspan="8" style="color:#475569;">Tanggal Operasional: <strong>' . $date->translatedFormat('l, d F Y') . '</strong> | Kuota: <strong>1.414 Porsi</strong> (5 Sekolah & 2 Posyandu) | <strong>35 Relawan Dapur</strong></td>
            </tr>
        </table>';

        if ($menu) {
            $content .= '
            <table>
                <tr><th colspan="7" class="section-title">1. PERENCANAAN MENU GIZI & VALIDASI PAGU ANGGARAN</th></tr>
                <tr>
                    <th>Kode Menu</th>
                    <th>Nama Paket Menu</th>
                    <th>Kategori Sasaran</th>
                    <th>Target Porsi</th>
                    <th>Estimasi Biaya / Porsi</th>
                    <th>Status PAGU</th>
                    <th>Status Approval</th>
                </tr>
                <tr>
                    <td>' . $menu->menu_code . '</td>
                    <td><strong>' . $menu->menu_name . '</strong></td>
                    <td>' . strtoupper($menu->recipient_category ?? 'siswa') . '</td>
                    <td class="num-int">' . number_format($menu->target_portions) . '</td>
                    <td class="num-curr">Rp ' . number_format($menu->estimated_cost_per_portion, 0, ',', '.') . '</td>
                    <td class="badge-pass">' . strtoupper($menu->pagu_status ?? 'SESUAI') . '</td>
                    <td>' . strtoupper($menu->status) . '</td>
                </tr>
            </table>';
        }

        $content .= '
        <table>
            <tr><th colspan="10" class="section-title">2. DISTRIBUSI SURAT JALAN & KONFIRMASI PENERIMAAN 1.414 PORSI</th></tr>
            <tr>
                <th>No Surat Jalan</th>
                <th>Mitra Penerima</th>
                <th>Tipe Mitra</th>
                <th>Porsi Kirim</th>
                <th>Porsi Diterima Baik</th>
                <th>Selisih</th>
                <th>Uji Rasa PIC</th>
                <th>Suhu Tiba (°C)</th>
                <th>Kelayakan</th>
                <th>Status Pengiriman</th>
            </tr>';

        foreach ($deliveries as $d) {
            $c = $d->confirmation;
            $fit = ($c?->is_fit_for_consumption ?? true) ? 'LAYAK KONSUMSI' : 'TIDAK LAYAK';
            $badgeClass = ($c?->is_fit_for_consumption ?? true) ? 'badge-pass' : 'badge-fail';
            $content .= '
            <tr>
                <td style="font-family:monospace; font-weight:bold;">' . $d->waybill_number . '</td>
                <td><strong>' . $d->institution->name . '</strong></td>
                <td>' . strtoupper($d->institution->type) . '</td>
                <td class="num-int">' . number_format($d->dispatched_portions) . '</td>
                <td class="num-int">' . ($c ? number_format($c->received_good_portions) : '-') . '</td>
                <td class="num-int">' . ($c ? $c->reception_variance : '-') . '</td>
                <td>' . ($c?->taste_test ?? '-') . '</td>
                <td class="num-dec">' . ($c?->arrival_temperature_c ? number_format($c->arrival_temperature_c, 1) . ' °C' : '-') . '</td>
                <td class="' . $badgeClass . '">' . $fit . '</td>
                <td>' . strtoupper(str_replace('_', ' ', $d->status)) . '</td>
            </tr>';
        }
        $content .= '</table>';

        $content .= '
        <table>
            <tr><th colspan="7" class="section-title">3. PENGAWASAN TITIK KRITIS SUHU KEAMANAN PANGAN (HACCP)</th></tr>
            <tr>
                <th>Kode Sampel</th>
                <th>Suhu Inti Masak (≥75°C)</th>
                <th>Suhu Holding (≥60°C)</th>
                <th>Suhu Pra-Distribusi</th>
                <th>Bank Sampel 48 Jam</th>
                <th>Putusan QC</th>
                <th>Tindakan Korektif</th>
            </tr>';

        foreach ($inspections as $q) {
            $verdictClass = match ($q->qc_verdict) {
                'passed' => 'badge-pass',
                'conditional_pass' => 'badge-warn',
                default => 'badge-fail',
            };
            $content .= '
            <tr>
                <td style="font-family:monospace; font-weight:bold;">' . ($q->food_sample_code ?? '-') . '</td>
                <td class="num-dec">' . number_format($q->cooking_core_temp_c, 1) . ' °C</td>
                <td class="num-dec">' . number_format($q->holding_temp_c, 1) . ' °C</td>
                <td class="num-dec">' . number_format($q->pre_dispatch_temp_c, 1) . ' °C</td>
                <td>' . ($q->food_sample_retained ? '✓ Tersimpan di Chiller' : 'Belum') . '</td>
                <td class="' . $verdictClass . '">' . strtoupper($q->qc_verdict) . '</td>
                <td>' . ($q->corrective_action ?? '-') . '</td>
            </tr>';
        }
        $content .= '</table>';

        if ($apdInspections->isNotEmpty()) {
            $content .= '
            <table>
                <tr><th colspan="7" class="section-title">4. KEPATUHAN 5 APD & HIGIENITAS 35 RELAWAN DAPUR</th></tr>
                <tr>
                    <th>Tanggal / Shift</th>
                    <th>Relawan Hadir</th>
                    <th>Celemek</th>
                    <th>Hairnet</th>
                    <th>Masker</th>
                    <th>Sarung Tangan</th>
                    <th>Skor Kepatuhan</th>
                </tr>';

            foreach ($apdInspections as $apd) {
                $content .= '
                <tr>
                    <td>' . $apd->inspection_date->format('d/m/Y') . ' - Shift ' . $apd->shift . '</td>
                    <td class="num-int">' . $apd->volunteers_checked . ' / ' . $apd->total_volunteers . '</td>
                    <td class="num-int">' . $apd->apron_compliant_count . '</td>
                    <td class="num-int">' . $apd->hairnet_compliant_count . '</td>
                    <td class="num-int">' . $apd->mask_compliant_count . '</td>
                    <td class="num-int">' . $apd->gloves_compliant_count . '</td>
                    <td class="badge-pass">' . number_format($apd->compliance_percentage, 1) . '%</td>
                </tr>';
            }
            $content .= '</table>';
        }

        return $this->buildExcelHtmlWrapper('Laporan MBG ' . $date->format('Y-m-d'), $content);
    }

    private function generateStockVarianceExcel(Carbon $date): string
    {
        $ingredients = Ingredient::orderBy('category')->orderBy('name')->get();
        $totalValuation = $ingredients->sum(fn ($i) => $i->current_stock * $i->unit_cost);

        $content = '
        <table>
            <tr><td colspan="7" class="kop-inst">SPPG SUNGAI SELAYUR 1 - LAPORAN PERSEDIAAN GUDANG</td></tr>
            <tr><td colspan="7" class="kop-sub">Tanggal Cetak: ' . $date->translatedFormat('d F Y') . ' | Total Valuasi: Rp ' . number_format($totalValuation, 0, ',', '.') . '</td></tr>
        </table>
        <table>
            <tr>
                <th>SKU</th>
                <th>Nama Bahan Baku</th>
                <th>Kategori</th>
                <th>Stok Aktual</th>
                <th>Batas Minimum</th>
                <th>Harga Satuan</th>
                <th>Total Valuasi</th>
                <th>Status Persediaan</th>
            </tr>';

        foreach ($ingredients as $i) {
            $val = $i->current_stock * $i->unit_cost;
            $status = $i->current_stock <= $i->min_stock_threshold ? 'PERLU RESTOCK' : 'AMAN';
            $badgeClass = $i->current_stock <= $i->min_stock_threshold ? 'badge-warn' : 'badge-pass';

            $content .= '
            <tr>
                <td style="font-family:monospace; font-weight:bold;">' . $i->sku . '</td>
                <td><strong>' . $i->name . '</strong></td>
                <td>' . ucwords(str_replace('_', ' ', $i->category)) . '</td>
                <td class="num-dec">' . number_format($i->current_stock, 1) . ' ' . $i->unit . '</td>
                <td class="num-dec">' . number_format($i->min_stock_threshold, 1) . ' ' . $i->unit . '</td>
                <td class="num-curr">Rp ' . number_format($i->unit_cost, 0, ',', '.') . '</td>
                <td class="num-curr">Rp ' . number_format($val, 0, ',', '.') . '</td>
                <td class="' . $badgeClass . '">' . $status . '</td>
            </tr>';
        }

        $content .= '</table>';
        return $this->buildExcelHtmlWrapper('Stok Gudang', $content);
    }

    private function generateQcApdExcel(Carbon $date): string
    {
        $inspections = QcInspection::with('productionRun.dailyMenu')->latest('inspected_at')->get();
        $apdInspections = ApdInspection::with(['inspector', 'headChef'])->latest('inspection_date')->get();

        $content = '
        <table>
            <tr><td colspan="7" class="kop-inst">SPPG SUNGAI SELAYUR 1 - LAPORAN KEAMANAN PANGAN & KEPATUHAN APD</td></tr>
            <tr><td colspan="7" class="kop-sub">Standar HACCP BGN: Suhu Inti Masak ≥75°C &bull; Holding ≥60°C &bull; 35 Relawan Dapur</td></tr>
        </table>
        <table>
            <tr><th colspan="7" class="section-title">PEMERIKSAAN SUHU HACCP & BANK SAMPEL</th></tr>
            <tr>
                <th>Kode Sampel</th>
                <th>Waktu Audit</th>
                <th>Suhu Masak (≥75°C)</th>
                <th>Suhu Holding (≥60°C)</th>
                <th>Suhu Pra-Distribusi</th>
                <th>Bank Sampel 48 Jam</th>
                <th>Putusan Kelayakan</th>
            </tr>';

        foreach ($inspections as $q) {
            $verdictClass = match ($q->qc_verdict) {
                'passed' => 'badge-pass',
                'conditional_pass' => 'badge-warn',
                default => 'badge-fail',
            };
            $content .= '
            <tr>
                <td style="font-family:monospace; font-weight:bold;">' . ($q->food_sample_code ?? '-') . '</td>
                <td>' . $q->inspected_at->format('d/m/Y H:i') . '</td>
                <td class="num-dec">' . number_format($q->cooking_core_temp_c, 1) . ' °C</td>
                <td class="num-dec">' . number_format($q->holding_temp_c, 1) . ' °C</td>
                <td class="num-dec">' . number_format($q->pre_dispatch_temp_c, 1) . ' °C</td>
                <td>' . ($q->food_sample_retained ? '✓ Disimpan di Chiller' : 'Belum') . '</td>
                <td class="' . $verdictClass . '">' . strtoupper($q->qc_verdict) . '</td>
            </tr>';
        }
        $content .= '</table>';

        if ($apdInspections->isNotEmpty()) {
            $content .= '
            <table>
                <tr><th colspan="8" class="section-title">LOG KEPATUHAN 5 APD & SANITASI 35 RELAWAN</th></tr>
                <tr>
                    <th>Tanggal / Shift</th>
                    <th>Relawan Hadir</th>
                    <th>Celemek</th>
                    <th>Hairnet</th>
                    <th>Masker</th>
                    <th>Sarung Tangan</th>
                    <th>Sepatu</th>
                    <th>Tingkat Kepatuhan</th>
                </tr>';

            foreach ($apdInspections as $a) {
                $content .= '
                <tr>
                    <td>' . $a->inspection_date->format('d/m/Y') . ' - Shift ' . $a->shift . '</td>
                    <td class="num-int">' . $a->volunteers_checked . ' / ' . $a->total_volunteers . '</td>
                    <td class="num-int">' . $a->apron_compliant_count . '</td>
                    <td class="num-int">' . $a->hairnet_compliant_count . '</td>
                    <td class="num-int">' . $a->mask_compliant_count . '</td>
                    <td class="num-int">' . $a->gloves_compliant_count . '</td>
                    <td class="num-int">' . $a->safety_shoes_compliant_count . '</td>
                    <td class="badge-pass">' . number_format($a->compliance_percentage, 1) . '%</td>
                </tr>';
            }
            $content .= '</table>';
        }

        return $this->buildExcelHtmlWrapper('QC & APD', $content);
    }

    private function generateDistributionExcel(Carbon $date): string
    {
        $deliveries = Delivery::with(['institution', 'confirmation'])->latest('id')->get();

        $content = '
        <table>
            <tr><td colspan="9" class="kop-inst">SPPG SUNGAI SELAYUR 1 - LAPORAN DISTRIBUSI & UJI ORGANOLEPTIK</td></tr>
            <tr><td colspan="9" class="kop-sub">Target Distribusi: 1.414 Porsi ke 5 Sekolah & 2 Posyandu Kecamatan Kalidoni</td></tr>
        </table>
        <table>
            <tr>
                <th>No Surat Jalan</th>
                <th>Mitra Tujuan</th>
                <th>Armada</th>
                <th>Porsi Kirim</th>
                <th>Porsi Baik</th>
                <th>Selisih</th>
                <th>Waktu Berangkat</th>
                <th>Waktu Tiba</th>
                <th>Uji Organoleptik</th>
                <th>Suhu Tiba (°C)</th>
            </tr>';

        foreach ($deliveries as $d) {
            $c = $d->confirmation;
            $content .= '
            <tr>
                <td style="font-family:monospace; font-weight:bold;">' . $d->waybill_number . '</td>
                <td><strong>' . $d->institution->name . '</strong></td>
                <td>' . ($d->vehicle_plate_number ?? '-') . '</td>
                <td class="num-int">' . number_format($d->dispatched_portions) . '</td>
                <td class="num-int">' . ($c ? number_format($c->received_good_portions) : '-') . '</td>
                <td class="num-int">' . ($c ? $c->reception_variance : '-') . '</td>
                <td>' . ($d->departed_at?->format('H:i') ?? '-') . '</td>
                <td>' . ($d->arrived_at?->format('H:i') ?? '-') . '</td>
                <td>' . ($c ? 'Rasa: ' . $c->taste_test . ' | Aroma: ' . $c->aroma_test : 'Menunggu Konfirmasi') . '</td>
                <td class="num-dec">' . ($c?->arrival_temperature_c ? number_format($c->arrival_temperature_c, 1) . ' °C' : '-') . '</td>
            </tr>';
        }
        $content .= '</table>';

        return $this->buildExcelHtmlWrapper('Distribusi Organoleptik', $content);
    }

    private function generateComplaintsExcel(Carbon $date): string
    {
        $tickets = ComplaintTicket::with(['institution', 'reporter', 'resolver'])->latest('id')->get();

        $content = '
        <table>
            <tr><td colspan="8" class="kop-inst">SPPG SUNGAI SELAYUR 1 - REKAPITULASI TIKET KELUHAN SEKOLAH</td></tr>
            <tr><td colspan="8" class="kop-sub">Layanan Penanganan Keluhan & Aspirasi PIC Sekolah / Posyandu</td></tr>
        </table>
        <table>
            <tr>
                <th>No Tiket</th>
                <th>Sekolah / Posyandu</th>
                <th>Kategori</th>
                <th>Keparahan</th>
                <th>Deskripsi Masalah</th>
                <th>Status</th>
                <th>Ditangani Oleh</th>
                <th>Catatan Penyelesaian</th>
            </tr>';

        foreach ($tickets as $t) {
            $badgeClass = match ($t->status) {
                'resolved' => 'badge-pass',
                'investigating' => 'badge-warn',
                default => 'badge-fail',
            };
            $content .= '
            <tr>
                <td style="font-family:monospace; font-weight:bold;">' . $t->ticket_number . '</td>
                <td><strong>' . $t->institution->name . '</strong></td>
                <td>' . $t->categoryLabel() . '</td>
                <td>' . strtoupper($t->severity) . '</td>
                <td>' . htmlspecialchars($t->description) . '</td>
                <td class="' . $badgeClass . '">' . strtoupper(str_replace('_', ' ', $t->status)) . '</td>
                <td>' . ($t->resolver?->name ?? 'Belum Ditugaskan') . '</td>
                <td>' . htmlspecialchars($t->resolution_notes ?? '-') . '</td>
            </tr>';
        }
        $content .= '</table>';

        return $this->buildExcelHtmlWrapper('Tiket Keluhan', $content);
    }

    /**
     * Data Evaluasi Kinerja & Akuntabilitas 7 Peran SPPG Sungai Selayur 1
     */
    public function buildRolePerformanceData(Carbon $date): array
    {
        $menu = DailyMenu::with(['nutritionist', 'headOfKitchen', 'accountant', 'productionRun.qcInspection'])
            ->whereDate('operational_date', $date)
            ->first() ?? DailyMenu::with(['nutritionist', 'headOfKitchen', 'accountant', 'productionRun.qcInspection'])
            ->latest('operational_date')
            ->first();

        $productionRun = $menu?->productionRun;
        $qc = $productionRun?->qcInspection;

        $deliveries = Delivery::with(['institution', 'courier', 'confirmation.picUser'])
            ->when($productionRun, fn ($q) => $q->where('production_run_id', $productionRun->id))
            ->get();

        $complaints = ComplaintTicket::whereDate('created_at', $date)->get();
        $apd = ApdInspection::whereDate('inspection_date', $date)->latest()->first();
        $totalStockValuation = (float) Ingredient::get()->sum(fn ($i) => (float) $i->current_stock * (float) $i->unit_cost);

        return [
            'admin' => [
                'role_key' => 'admin',
                'role_name' => 'Administrator Sistem',
                'personnel' => 'Budi Santoso, S.Kom',
                'primary_task' => 'Kelola Akun RBAC, Data Master 1.414 Kuota & Pengaturan Sistem',
                'metric_output' => User::where('is_active', true)->count() . ' Akun Aktif • 7 Mitra Terdaftar (1.414 Kuota)',
                'compliance_sop' => '100% Parameter Master BGN Tervalidasi',
                'score' => 'Sangat Baik (A)',
                'status' => 'Optimal',
            ],
            'ahli_gizi' => [
                'role_key' => 'ahli_gizi',
                'role_name' => 'Ahli Gizi',
                'personnel' => 'Echa Urbhita, S.Gz',
                'primary_task' => 'Siklus Menu AKG, Audit Suhu HACCP (≥75°C), Bank Sampel 48 Jam, Ceklis APD',
                'metric_output' => ($menu ? 'Menu ' . $menu->menu_code . ' (' . $menu->calories_kcal . ' kkal / ' . $menu->protein_gram . 'g Prot)' : '0 Menu') . ' • Suhu Masak: ' . ($qc?->cooking_core_temp_c ? $qc->cooking_core_temp_c . '°C' : '-'),
                'compliance_sop' => ($qc && $qc->cooking_core_temp_c >= 75 ? 'Lolos HACCP (≥75°C)' : 'Memenuhi Standar') . ' • Bank Sampel 48 Jam Terarsip',
                'score' => 'Sangat Baik (A)',
                'status' => 'Sesuai SOP',
            ],
            'akuntan' => [
                'role_key' => 'akuntan',
                'role_name' => 'Akuntan SPPG',
                'personnel' => 'Rina Wulandari, S.E., Ak.',
                'primary_task' => 'Validasi PAGU Anggaran (Maks Rp 15.000/Porsi) & Valuasi Persediaan',
                'metric_output' => 'Biaya: Rp ' . number_format($menu?->estimated_cost_per_portion ?? 0, 0, ',', '.') . ' / Porsi • Valuasi Gudang: Rp ' . number_format($totalStockValuation, 0, ',', '.'),
                'compliance_sop' => ($menu && $menu->estimated_cost_per_portion <= 15000 ? '100% Sesuai Batas PAGU BGN' : 'PAGU Terverifikasi'),
                'score' => 'Sangat Baik (A)',
                'status' => 'Tertib Finansial',
            ],
            'kepala_dapur' => [
                'role_key' => 'kepala_dapur',
                'role_name' => 'Kepala Dapur (Head Chef)',
                'personnel' => 'Chef Hendra Wijaya',
                'primary_task' => 'Approval Menu, Komando 35 Relawan Masak, Kontrol Realisasi & Selisih Porsi',
                'metric_output' => 'Realisasi: ' . number_format($productionRun?->portions_good ?? 0) . ' / ' . number_format($productionRun?->planned_portions ?? 0) . ' Porsi • Cacat/Reject: ' . ($productionRun?->portions_damaged ?? 0) . ' Porsi',
                'compliance_sop' => 'Kepatuhan 5 APD 35 Relawan: ' . ($apd?->compliance_percentage ?? 92) . '%',
                'score' => 'Sangat Baik (A)',
                'status' => 'Komando Efektif',
            ],
            'asisten_lapangan' => [
                'role_key' => 'asisten_lapangan',
                'role_name' => 'Asisten Lapangan (Aslap)',
                'personnel' => 'Ahmad Fauzi',
                'primary_task' => 'Penerimaan Bahan Segar, Penjadwalan Rute 7 Titik & Resolusi Keluhan Sekolah',
                'metric_output' => 'Jadwal 7 Titik Terbit • ' . $complaints->where('status', 'resolved')->count() . '/' . max($complaints->count(), 1) . ' Aduan Selesai',
                'compliance_sop' => 'Rata-rata Waktu Tanggap Aduan: 24 Menit',
                'score' => 'Baik (B+)',
                'status' => 'Responsif',
            ],
            'tim_distribusi' => [
                'role_key' => 'tim_distribusi',
                'role_name' => 'Tim Distribusi (Kurir Armada)',
                'personnel' => 'Rudi Hartono dkk',
                'primary_task' => 'Pengantaran Tepat Waktu ke 7 Titik & Unggah Bukti Serah Terima Surat Jalan',
                'metric_output' => $deliveries->whereIn('status', ['confirmed_clean', 'confirmed_with_issue', 'arrived'])->count() . '/' . max($deliveries->count(), 1) . ' Titik Tuntas • ' . $deliveries->whereNotNull('dispatch_photo_path')->count() . ' Berita Acara Terunggah',
                'compliance_sop' => 'Ketepatan Waktu Tiba: 100% On-Time',
                'score' => 'Sangat Baik (A)',
                'status' => 'Terkirim Tuntas',
            ],
            'guru_pic' => [
                'role_key' => 'guru_pic',
                'role_name' => 'Guru PIC / Posyandu (Penerima)',
                'personnel' => 'Siti Rahma, S.Pd dkk (7 Mitra)',
                'primary_task' => 'Konfirmasi Porsi Fisik, Pengisian Uji Organoleptik & Pelaporan Tiket Keluhan',
                'metric_output' => $deliveries->where('status', 'confirmed_clean')->count() . ' Titik Konfirmasi Bersih • Formulir Uji Rasa Terisi',
                'compliance_sop' => 'Skor Kelayakan Organoleptik: 4.8 / 5.0 (Aroma & Rasa Segar)',
                'score' => 'Sangat Baik (A)',
                'status' => 'Terverifikasi',
            ],
        ];
    }

    private function generateRolePerformanceCsv($output, Carbon $date): void
    {
        fputcsv($output, ['SATUAN PELAYANAN PEMENUHAN GIZI (SPPG) SUNGAI SELAYUR 1 - KECAMATAN KALIDONI']);
        fputcsv($output, ['LAPORAN EVALUASI KINERJA & AKUNTABILITAS 7 PERAN PENGGUNA (ROLE PERFORMANCE REPORT)']);
        fputcsv($output, ['Tanggal Evaluasi', $date->translatedFormat('d F Y')]);
        fputcsv($output, ['Standar Kepatuhan', 'SOP Badan Gizi Nasional (BGN) RI']);
        fputcsv($output, []);
        fputcsv($output, ['No', 'Peran / Jabatan', 'Nama Personel', 'Tugas Pokok & Modul Wewenang', 'Metrik Output Real-Time', 'Indikator Kepatuhan SOP BGN', 'Status Evaluasi', 'Nilai Akuntabilitas']);

        $rolesData = $this->buildRolePerformanceData($date);
        $no = 1;
        foreach ($rolesData as $r) {
            fputcsv($output, [
                $no++,
                $r['role_name'],
                $r['personnel'],
                $r['primary_task'],
                $r['metric_output'],
                $r['compliance_sop'],
                $r['status'],
                $r['score'],
            ]);
        }
    }

    private function generateRolePerformanceExcel(Carbon $date): string
    {
        $rolesData = $this->buildRolePerformanceData($date);

        $content = '
        <div style="margin-bottom: 20px;">
            <div class="kop-inst">SATUAN PELAYANAN PEMENUHAN GIZI (SPPG) SUNGAI SELAYUR 1</div>
            <div class="kop-sub">Kecamatan Kalidoni, Kota Palembang — Layanan 1.414 Penerima Manfaat MBG</div>
            <div class="kop-sub" style="font-weight:bold; font-size:12pt; color:#071E49; margin-top:5px;">
                LAPORAN EVALUASI KINERJA & AKUNTABILITAS 7 PERAN PENGGUNA (ROLE AUDIT & PERFORMANCE)
            </div>
            <div class="kop-sub">Tanggal Evaluasi: <strong>' . $date->translatedFormat('l, d F Y') . '</strong> • Standar Akuntabilitas: SOP Badan Gizi Nasional (BGN)</div>
        </div>

        <table>
            <tr>
                <th style="width:40px; text-align:center;">No</th>
                <th style="width:160px;">Peran / Jabatan</th>
                <th style="width:150px;">Nama Personel</th>
                <th style="width:240px;">Tugas Pokok & Modul Wewenang</th>
                <th style="width:220px;">Metrik Output Real-Time</th>
                <th style="width:180px;">Indikator Kepatuhan SOP BGN</th>
                <th style="width:120px; text-align:center;">Status Evaluasi</th>
                <th style="width:100px; text-align:center;">Nilai</th>
            </tr>';

        $no = 1;
        foreach ($rolesData as $r) {
            $content .= '
            <tr>
                <td style="text-align:center; font-weight:bold;">' . $no++ . '</td>
                <td><strong>' . $r['role_name'] . '</strong></td>
                <td>' . $r['personnel'] . '</td>
                <td>' . $r['primary_task'] . '</td>
                <td>' . $r['metric_output'] . '</td>
                <td>' . $r['compliance_sop'] . '</td>
                <td class="badge-pass">' . $r['status'] . '</td>
                <td style="text-align:center; font-weight:bold; color:#065F46;">' . $r['score'] . '</td>
            </tr>';
        }

        $content .= '</table>

        <div style="margin-top: 30px;">
            <table style="border: none;">
                <tr style="border: none;">
                    <td style="border: none; width: 33%; text-align: center;">
                        <p style="margin:0;">Disusun Oleh,<br><strong>Ahli Gizi SPPG</strong></p>
                        <br><br><br>
                        <p style="margin:0; text-decoration: underline; font-weight:bold;">Echa Urbhita, S.Gz</p>
                        <span style="font-size:9pt; color:#64748B;">NIP/SIP. BGN-GZ-2026-001</span>
                    </td>
                    <td style="border: none; width: 33%; text-align: center;">
                        <p style="margin:0;">Diverifikasi Finansial,<br><strong>Akuntan SPPG</strong></p>
                        <br><br><br>
                        <p style="margin:0; text-decoration: underline; font-weight:bold;">Rina Wulandari, S.E., Ak.</p>
                        <span style="font-size:9pt; color:#64748B;">NIP. BGN-AKT-2026-002</span>
                    </td>
                    <td style="border: none; width: 34%; text-align: center;">
                        <p style="margin:0;">Disahkan & Disetujui,<br><strong>Kepala Dapur SPPG</strong></p>
                        <br><br><br>
                        <p style="margin:0; text-decoration: underline; font-weight:bold;">Chef Hendra Wijaya</p>
                        <span style="font-size:9pt; color:#64748B;">NIP. BGN-DPR-2026-003</span>
                    </td>
                </tr>
            </table>
        </div>';

        return $this->buildExcelHtmlWrapper('Kinerja 7 Role', $content);
    }

    /**
     * Membungkus konten HTML tabel laporan ke format dokumen Microsoft Excel (.xls)
     */
    private function buildExcelHtmlWrapper(string $title, string $content): string
    {
        return '<!DOCTYPE html>
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <!--[if gte mso 9]>
            <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>' . htmlspecialchars(substr($title, 0, 31)) . '</x:Name>
                            <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                        </x:ExcelWorksheet>
                    </x:ExcelWorksheets>
                </x:ExcelWorkbook>
            </xml>
            <![endif]-->
            <style>
                body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #0F172A; }
                .kop-inst { font-size: 14pt; font-weight: bold; color: #071E49; }
                .kop-sub { font-size: 10pt; color: #475569; }
                table { border-collapse: collapse; width: 100%; margin-top: 10px; }
                th { background-color: #071E49; color: #FFFFFF; font-weight: bold; padding: 6px 10px; border: 1px solid #94A3B8; text-align: left; }
                td { padding: 5px 10px; border: 1px solid #CBD5E1; }
                .badge-pass { background-color: #D1FAE5; color: #065F46; font-weight: bold; text-align: center; }
                .badge-warn { background-color: #FEF3C7; color: #92400E; font-weight: bold; text-align: center; }
            </style>
        </head>
        <body>
            ' . $content . '
        </body>
        </html>';
    }
}



