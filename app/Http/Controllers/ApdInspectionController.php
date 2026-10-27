<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ApdInspection;
use App\Models\ProductionRun;
use App\Models\SopConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ApdInspectionController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->query('date', today()->toDateString());
        $shift = $request->query('shift', 'all');
        $status = $request->query('status', 'all');

        $query = ApdInspection::with(['inspector', 'headChef', 'productionRun.dailyMenu']);

        if ($date) {
            $query->whereDate('inspection_date', $date);
        }

        if ($shift !== 'all') {
            $query->where('shift', $shift);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $inspections = $query->latest('id')->get();

        // Riwayat 14 hari terakhir untuk tabel riwayat & grafik tren
        $recentInspections = ApdInspection::with('inspector')
            ->latest('inspection_date')
            ->take(14)
            ->get();

        // Data Grafik Tren
        $chartData = $recentInspections->reverse()->values();
        $chartLabels = $chartData->map(fn ($i) => $i->inspection_date->translatedFormat('d M') . ' (' . (str_contains($i->shift, 'Pagi') ? 'Pagi' : 'Siang') . ')');
        $chartScores = $chartData->pluck('compliance_percentage');

        // Parameter SOP Relawan BGN
        $defaultVolunteers = (int) SopConfiguration::getValue('tenaga_relawan_dapur_per_shift', 35);

        // Produksi aktif hari ini
        $activeProduction = ProductionRun::with('dailyMenu')
            ->whereHas('dailyMenu', fn ($q) => $q->whereDate('operational_date', $date))
            ->first() ?? ProductionRun::with('dailyMenu')->latest('id')->first();

        // Statistik Ringkasan
        $allInspections = ApdInspection::all();
        $avgCompliance = $allInspections->count() > 0
            ? round($allInspections->avg('compliance_percentage'), 1)
            : 100;
        $totalChecked = $allInspections->sum('volunteers_checked');
        $criticalCount = $allInspections->where('status', 'pelanggaran_kritis')->count();

        // Cek apakah hari ini sudah diinspeksi
        $todayInspection = ApdInspection::whereDate('inspection_date', $date)->first();

        return view('apd-inspection.index', compact(
            'inspections',
            'recentInspections',
            'chartLabels',
            'chartScores',
            'date',
            'shift',
            'status',
            'defaultVolunteers',
            'activeProduction',
            'avgCompliance',
            'totalChecked',
            'criticalCount',
            'todayInspection'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'inspection_date' => ['required', 'date'],
            'shift' => ['required', 'string', 'max:50'],
            'production_run_id' => ['nullable', 'exists:production_runs,id'],
            'total_volunteers' => ['required', 'integer', 'min:1', 'max:100'],
            'volunteers_checked' => ['required', 'integer', 'min:1', 'max:100'],
            'apron_compliant_count' => ['required', 'integer', 'min:0'],
            'hairnet_compliant_count' => ['required', 'integer', 'min:0'],
            'mask_compliant_count' => ['required', 'integer', 'min:0'],
            'gloves_compliant_count' => ['required', 'integer', 'min:0'],
            'safety_shoes_compliant_count' => ['required', 'integer', 'min:0'],
            'clean_nails_compliant' => ['nullable', 'boolean'],
            'no_jewelry_compliant' => ['nullable', 'boolean'],
            'handwash_facility_ready' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'corrective_action' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        $volunteersChecked = (int) $validated['volunteers_checked'];
        $sumApdCompliant = (int) $validated['apron_compliant_count']
            + (int) $validated['hairnet_compliant_count']
            + (int) $validated['mask_compliant_count']
            + (int) $validated['gloves_compliant_count']
            + (int) $validated['safety_shoes_compliant_count'];

        $maxApdTotal = 5 * $volunteersChecked;
        $basePercentage = $maxApdTotal > 0 ? ($sumApdCompliant / $maxApdTotal) * 100 : 100;

        // Penalti higienitas sanitasi (masing-masing -3% jika tidak terpenuhi)
        $cleanNails = $request->boolean('clean_nails_compliant');
        $noJewelry = $request->boolean('no_jewelry_compliant');
        $handwashReady = $request->boolean('handwash_facility_ready');

        $penalty = 0;
        if (! $cleanNails) { $penalty += 3; }
        if (! $noJewelry) { $penalty += 3; }
        if (! $handwashReady) { $penalty += 4; }

        $compliancePercentage = max(0, min(100, round($basePercentage - $penalty, 2)));

        // Klasifikasi status kepatuhan SOP BGN
        if ($compliancePercentage >= 90.0) {
            $status = 'patuh_penuh';
        } elseif ($compliancePercentage >= 75.0) {
            $status = 'peringatan';
        } else {
            $status = 'pelanggaran_kritis';
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('branding/apd-photos', 'public');
        }

        $inspection = ApdInspection::create([
            'inspection_date' => $validated['inspection_date'],
            'shift' => $validated['shift'],
            'inspector_id' => Auth::id(),
            'production_run_id' => $validated['production_run_id'] ?? null,
            'total_volunteers' => (int) $validated['total_volunteers'],
            'volunteers_checked' => $volunteersChecked,
            'apron_compliant_count' => (int) $validated['apron_compliant_count'],
            'hairnet_compliant_count' => (int) $validated['hairnet_compliant_count'],
            'mask_compliant_count' => (int) $validated['mask_compliant_count'],
            'gloves_compliant_count' => (int) $validated['gloves_compliant_count'],
            'safety_shoes_compliant_count' => (int) $validated['safety_shoes_compliant_count'],
            'clean_nails_compliant' => $cleanNails,
            'no_jewelry_compliant' => $noJewelry,
            'handwash_facility_ready' => $handwashReady,
            'compliance_percentage' => $compliancePercentage,
            'status' => $status,
            'notes' => $validated['notes'] ?? null,
            'corrective_action' => $validated['corrective_action'] ?? null,
            'photo_path' => $photoPath,
            'verified_by_head_chef_id' => null,
        ]);

        ActivityLog::record(
            'submit_apd_inspection',
            'apd_inspection',
            "Ahli Gizi/Inspektor " . Auth::user()->name . " merekam Form Ceklis APD ({$inspection->shift}) dengan tingkat kepatuhan {$compliancePercentage}% ({$status}) untuk {$volunteersChecked} relawan dapur."
        );

        return redirect()->route('apd-inspection.index', ['date' => $validated['inspection_date']])
            ->with('success', "Form Ceklis Kepatuhan APD Dapur berhasil disimpan! Tingkat kepatuhan: {$compliancePercentage}%.");
    }

    public function verify(Request $request, ApdInspection $apdInspection): RedirectResponse
    {
        $validated = $request->validate([
            'corrective_action' => ['nullable', 'string', 'max:1000'],
        ]);

        $apdInspection->update([
            'verified_by_head_chef_id' => Auth::id(),
            'corrective_action' => $validated['corrective_action'] ?? $apdInspection->corrective_action,
        ]);

        ActivityLog::record(
            'verify_apd_inspection',
            'apd_inspection',
            "Kepala Dapur " . Auth::user()->name . " memverifikasi hasil inspeksi APD tanggal {$apdInspection->inspection_date->translatedFormat('d M Y')} ({$apdInspection->shift})."
        );

        return redirect()->route('apd-inspection.index', ['date' => $apdInspection->inspection_date->toDateString()])
            ->with('success', 'Hasil inspeksi kepatuhan APD berhasil diverifikasi oleh Kepala Dapur.');
    }
}

