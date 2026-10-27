<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ProductionRun;
use App\Models\QcInspection;
use App\Models\SopConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QualityControlController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $verdict = $request->query('verdict', 'all');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = QcInspection::with(['productionRun.dailyMenu', 'inspector', 'resolver']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('food_sample_code', 'like', "%{$search}%")
                  ->orWhere('corrective_action', 'like', "%{$search}%")
                  ->orWhereHas('productionRun.dailyMenu', fn ($mq) => $mq->where('menu_name', 'like', "%{$search}%")->orWhere('menu_code', 'like', "%{$search}%"));
            });
        }

        if ($verdict !== 'all') {
            $query->where('qc_verdict', $verdict);
        }

        if ($dateFrom) {
            $query->whereDate('inspected_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('inspected_at', '<=', $dateTo);
        }

        $inspections = $query->latest('inspected_at')->get();

        $productionRuns = ProductionRun::with('dailyMenu')
            ->latest('id')
            ->get();

        $minCookingTemp = (float) SopConfiguration::getValue('suhu_masak_inti_minimal', 75);
        $minHoldingTemp = (float) SopConfiguration::getValue('suhu_holding_minimal', 60);
        $minDispatchTemp = (float) SopConfiguration::getValue('suhu_pra_distribusi_minimal', 60);

        // 4 KPI Suhu & HACCP
        $avgCookingTemp = $inspections->count() ? number_format($inspections->avg('cooking_core_temp_c'), 1) : '0';
        $avgHoldingTemp = $inspections->count() ? number_format($inspections->avg('holding_temp_c'), 1) : '0';
        $avgDispatchTemp = $inspections->count() ? number_format($inspections->avg('pre_dispatch_temp_c'), 1) : '0';
        $foodBankCount = $inspections->where('food_sample_retained', true)->count();

        // Data Grafik Termal Suhu QC
        $chartItems = $inspections->take(8)->reverse()->values();
        $chartLabels = $chartItems->map(fn ($i) => $i->food_sample_code ?? $i->inspected_at->format('d M'));
        $chartCoreTemps = $chartItems->pluck('cooking_core_temp_c');
        $chartHoldTemps = $chartItems->pluck('holding_temp_c');
        $chartDispatchTemps = $chartItems->pluck('pre_dispatch_temp_c');

        // Data Grafik Putusan Kelayakan Suhu
        $verdictCounts = [
            'passed' => $inspections->where('qc_verdict', 'passed')->count(),
            'conditional_pass' => $inspections->where('qc_verdict', 'conditional_pass')->count(),
            'failed_hold' => $inspections->where('qc_verdict', 'failed_hold')->count(),
        ];

        return view('quality-control.index', compact(
            'inspections',
            'productionRuns',
            'search',
            'verdict',
            'dateFrom',
            'dateTo',
            'chartLabels',
            'chartCoreTemps',
            'chartHoldTemps',
            'chartDispatchTemps',
            'verdictCounts',
            'minCookingTemp',
            'minHoldingTemp',
            'minDispatchTemp',
            'avgCookingTemp',
            'avgHoldingTemp',
            'avgDispatchTemp',
            'foodBankCount'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'production_run_id' => ['required', 'exists:production_runs,id'],
            'cooking_core_temp_c' => ['required', 'numeric', 'min:30', 'max:120'],
            'holding_temp_c' => ['required', 'numeric', 'min:20', 'max:100'],
            'pre_dispatch_temp_c' => ['required', 'numeric', 'min:20', 'max:100'],
            'food_sample_code' => ['required', 'string', 'max:40'],
            'qc_verdict' => ['required', 'in:passed,conditional_pass,failed_hold'],
            'corrective_action' => ['nullable', 'string', 'max:500'],
        ]);

        $qc = QcInspection::updateOrCreate(
            ['production_run_id' => $validated['production_run_id']],
            [
                'inspector_id' => Auth::id(),
                'cooking_core_temp_c' => $validated['cooking_core_temp_c'],
                'holding_temp_c' => $validated['holding_temp_c'],
                'pre_dispatch_temp_c' => $validated['pre_dispatch_temp_c'],
                'total_kitchen_staff_present' => 35,
                'apd_mask_compliant' => true,
                'apd_hairnet_compliant' => true,
                'apd_gloves_compliant' => true,
                'apd_apron_compliant' => true,
                'apd_safety_shoes_compliant' => true,
                'kitchen_sanitation_passed' => $request->boolean('kitchen_sanitation_passed'),
                'food_sample_retained' => $request->boolean('food_sample_retained'),
                'food_sample_code' => strtoupper($validated['food_sample_code']),
                'qc_verdict' => $validated['qc_verdict'],
                'corrective_action' => $validated['corrective_action'],
                'corrective_action_status' => $validated['qc_verdict'] === 'passed' ? 'resolved' : 'open',
                'inspected_at' => now(),
            ]
        );

        ActivityLog::record(
            'qc_inspection',
            'quality_control',
            "Mencatat pemeriksaan Suhu & Keamanan Pangan HACCP: Suhu Masak {$qc->cooking_core_temp_c}°C, Holding {$qc->holding_temp_c}°C, Pra-Distribusi {$qc->pre_dispatch_temp_c}°C, Kode Sampel {$qc->food_sample_code}, Putusan " . strtoupper($qc->qc_verdict) . "."
        );

        return redirect()->route('quality-control.index')
            ->with('success', 'Hasil audit suhu kritis masak, holding, dan bank sampel makanan berhasil disimpan.');
    }

    /**
     * Tindak Lanjut Temuan QC oleh Kepala Dapur (Fitur 6)
     */
    public function resolveAction(Request $request, QcInspection $qcInspection): RedirectResponse
    {
        $validated = $request->validate([
            'corrective_action' => ['required', 'string', 'max:500'],
            'corrective_action_status' => ['required', 'in:open,resolved'],
        ]);

        $qcInspection->update([
            'corrective_action' => $validated['corrective_action'],
            'corrective_action_status' => $validated['corrective_action_status'],
            'resolved_by' => Auth::id(),
        ]);

        ActivityLog::record(
            'resolve_qc_action',
            'quality_control',
            "Kepala Dapur " . Auth::user()->name . " menyelesaikan tindakan korektif pada sampel {$qcInspection->food_sample_code}: {$validated['corrective_action']}."
        );

        return redirect()->route('quality-control.index')
            ->with('success', 'Tindakan korektif audit suhu berhasil diperbarui.');
    }

    public function destroy(QcInspection $qcInspection): RedirectResponse
    {
        $code = $qcInspection->food_sample_code;
        $qcInspection->delete();

        ActivityLog::record(
            'delete_qc_inspection',
            'quality_control',
            "Menghapus data audit QC sampel {$code}."
        );

        return redirect()->route('quality-control.index')
            ->with('success', "Data audit QC sampel {$code} telah dihapus.");
    }
}
