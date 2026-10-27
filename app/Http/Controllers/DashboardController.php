<?php

namespace App\Http\Controllers;

use App\Models\BeneficiaryInstitution;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\Delivery;
use App\Models\DeliveryConfirmation;
use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Models\ProductionRun;
use App\Models\QcInspection;
use App\Models\SopConfiguration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $institutionId = $request->query('institution_id', 'all');
        $institutionType = $request->query('institution_type', 'all');
        $recipientCategory = $request->query('recipient_category', 'all');
        $menuStatus = $request->query('menu_status', 'all');

        // 1. Menu Operasional Aktif dari Database
        $menuQuery = DailyMenu::with(['nutritionist', 'headOfKitchen', 'accountant', 'items.ingredient', 'productionRun.qcInspection']);
        if ($dateFrom) {
            $menuQuery->whereDate('operational_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $menuQuery->whereDate('operational_date', '<=', $dateTo);
        }
        if ($menuStatus !== 'all') {
            $menuQuery->where('status', $menuStatus);
        }
        if ($recipientCategory !== 'all') {
            $menuQuery->where('recipient_category', $recipientCategory);
        }
        if ($search !== '') {
            $menuQuery->where(function ($q) use ($search) {
                $q->where('menu_name', 'like', "%{$search}%")
                  ->orWhere('menu_code', 'like', "%{$search}%");
            });
        }

        $todayMenu = (clone $menuQuery)->whereDate('operational_date', Carbon::today())->first()
            ?? (clone $menuQuery)->latest('operational_date')->first();

        $todayProduction = $todayMenu?->productionRun
            ?? ProductionRun::with('dailyMenu', 'qcInspection')->latest('id')->first();

        $latestQc = $todayProduction?->qcInspection
            ?? QcInspection::with(['inspector', 'resolver'])->latest('inspected_at')->first();

        // 2. Persediaan Bahan Baku & Valuasi Gudang dari Database
        $ingredientsQuery = Ingredient::orderBy('category');
        if ($search !== '') {
            $ingredientsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        $ingredients = $ingredientsQuery->get();
        $lowStockIngredients = $ingredients->filter(fn (Ingredient $i) => $i->isLowStock());
        $totalInventoryValue = $ingredients->sum(fn (Ingredient $i) => (float) $i->current_stock * (float) $i->unit_cost);

        // 3. Pengiriman & Konfirmasi Uji Organoleptik dari Database
        $deliveriesQuery = Delivery::with(['institution', 'courier', 'confirmation.picUser']);

        if ($institutionId !== 'all') {
            $deliveriesQuery->where('beneficiary_institution_id', $institutionId);
        }
        if ($institutionType !== 'all') {
            $deliveriesQuery->whereHas('institution', fn ($q) => $q->where('type', $institutionType));
        }
        if ($recipientCategory !== 'all') {
            $deliveriesQuery->whereHas('institution', fn ($q) => $q->where('recipient_category', $recipientCategory));
        }
        if ($search !== '') {
            $deliveriesQuery->where(function ($q) use ($search) {
                $q->where('waybill_number', 'like', "%{$search}%")
                  ->orWhere('vehicle_plate_number', 'like', "%{$search}%")
                  ->orWhereHas('institution', fn ($iq) => $iq->where('name', 'like', "%{$search}%"));
            });
        }
        $deliveries = $deliveriesQuery->latest('id')->get();

        // 4. Metrik Master Mitra & Kuota 1.414 Porsi
        $institutionsQuery = BeneficiaryInstitution::where('is_active', true);
        if ($institutionType !== 'all') {
            $institutionsQuery->where('type', $institutionType);
        }
        if ($recipientCategory !== 'all') {
            $institutionsQuery->where('recipient_category', $recipientCategory);
        }
        $activeInstitutions = $institutionsQuery->orderBy('code')->get();
        $allInstitutions = BeneficiaryInstitution::where('is_active', true)->orderBy('code')->get();
        $institutionsCount = $activeInstitutions->count();
        $totalAllocatedQuota = (int) $activeInstitutions->sum('default_quota');
        $grandTotalQuota = (int) $allInstitutions->sum('default_quota');

        $pendingMenusCount = DailyMenu::whereIn('status', ['pending_budget_review', 'pending_approval'])->count();
        $totalUsersCount = User::where('is_active', true)->count();
        $volunteersCount = (int) SopConfiguration::getValue('jumlah_relawan_dapur_shift', 35);
        $paguLimit = (float) SopConfiguration::getValue('pagu_anggaran_per_porsi', 15000);

        // 5. Tiket Keluhan & Notifikasi Kritis (Fitur 7)
        $activeComplaints = ComplaintTicket::with(['institution', 'reporter', 'resolver'])
            ->where('status', '!=', 'resolved')
            ->latest('id')
            ->get();
        $criticalComplaintsCount = $activeComplaints->where('severity', 'kritis')->count();

        // 6. Data Grafik 1: Target vs Realisasi Porsi Baik & Porsi Cacat
        $recentRuns = ProductionRun::with('dailyMenu')->latest('id')->take(7)->get()->reverse()->values();
        $chartProductionLabels = $recentRuns->map(fn ($r) => $r->dailyMenu->operational_date->translatedFormat('d M'));
        $chartPlannedData = $recentRuns->pluck('planned_portions');
        $chartActualData = $recentRuns->pluck('actual_good_portions');
        $chartDamagedData = $recentRuns->pluck('rejected_damaged_portions');

        // 7. Data Grafik 2: Sebaran Kuota 7 Mitra SPPG Sungai Selayur 1
        $chartCategoryLabels = $allInstitutions->map(fn ($i) => $i->name);
        $chartCategoryQuotas = $allInstitutions->pluck('default_quota');

        // 8. Data Grafik 3: Komposisi Makronutrisi Menu Aktif
        $nutritionChartData = [
            (float) ($todayMenu?->protein_gram ?? 0),
            (float) ($todayMenu?->carbs_gram ?? 0),
            (float) ($todayMenu?->fat_gram ?? 0),
            (float) ($todayMenu?->fiber_gram ?? 0),
        ];

        // 9. Data Grafik 4: Tren Suhu HACCP & APD 35 Relawan
        $recentQcList = QcInspection::with('productionRun.dailyMenu')->latest('inspected_at')->take(7)->get()->reverse()->values();
        $chartQcLabels = $recentQcList->map(fn ($q) => $q->productionRun?->dailyMenu?->operational_date?->translatedFormat('d M') ?? $q->inspected_at->format('d M'));
        $chartQcCoreTemp = $recentQcList->pluck('cooking_core_temp_c');
        $chartQcHoldingTemp = $recentQcList->pluck('holding_temp_c');
        $chartQcApdScore = $recentQcList->map(fn ($q) => $q->apd_compliance_score);

        // 10. Data Grafik 5: Valuasi Stok Gudang per Kategori
        $stockByCategory = Ingredient::selectRaw('category, SUM(current_stock * unit_cost) as valuation, COUNT(*) as item_count')
            ->groupBy('category')
            ->get();
        $chartStockCatLabels = $stockByCategory->map(fn ($c) => ucwords(str_replace('_', ' ', $c->category)));
        $chartStockCatValues = $stockByCategory->pluck('valuation');

        $recentConfirmations = DeliveryConfirmation::with(['delivery.institution', 'picUser'])
            ->latest('confirmed_at')
            ->take(6)
            ->get();

        $recentTransactions = InventoryTransaction::with(['ingredient', 'recorder', 'accountant'])
            ->latest()
            ->take(6)
            ->get();

        $totalInboundSpend = (float) InventoryTransaction::where('type', 'inbound_purchase')->sum('total_cost');

        // Data Khusus Spesifik Per-Role untuk Workspace Dashboard Terarah
        $currentUser = auth()->user();
        $currentUserRole = $currentUser->role->value;

        // 1. Khusus Ahli Gizi
        $myDraftMenus = DailyMenu::whereIn('status', ['draft', 'revision_needed'])->latest()->get();
        $uninspectedRuns = ProductionRun::with('dailyMenu')->doesntHave('qcInspection')->latest()->get();
        $todayApd = \App\Models\ApdInspection::with(['inspector', 'headChef'])->whereDate('inspection_date', Carbon::today())->latest()->first();

        // 2. Khusus Akuntan SPPG
        $pendingPaguMenus = DailyMenu::whereIn('status', ['draft', 'pending_budget_review'])
            ->where(function ($q) {
                $q->where('pagu_status', 'menunggu_review')
                  ->orWhereNull('pagu_status');
            })->latest()->get();
        $paguCompliantCount = DailyMenu::where('pagu_status', 'sesuai_pagu')->count();

        // 3. Khusus Kepala Dapur
        $pendingApprovalMenus = DailyMenu::where('status', 'pending_approval')->latest()->get();
        $activeKitchenRuns = ProductionRun::with(['dailyMenu', 'qcInspection'])->whereIn('status', ['in_progress', 'completed'])->latest()->take(5)->get();

        // 4. Khusus Asisten Lapangan
        $pendingShipments = Delivery::with('institution')->where('status', 'preparing')->latest()->get();
        $unresolvedTickets = ComplaintTicket::with(['institution', 'reporter'])->where('status', '!=', 'resolved')->latest()->get();

        // 5. Khusus Tim Distribusi
        $activeCourierDeliveries = Delivery::with(['institution', 'confirmation'])
            ->whereIn('status', ['preparing', 'in_transit', 'arrived'])
            ->latest()
            ->get();

        // 6. Khusus Guru PIC / Posyandu
        $userInstitution = $currentUser->institution;
        $mySchoolDeliveries = $userInstitution
            ? Delivery::with(['courier', 'confirmation'])->where('beneficiary_institution_id', $userInstitution->id)->latest()->take(5)->get()
            : collect();
        $mySchoolComplaints = $userInstitution
            ? ComplaintTicket::where('beneficiary_institution_id', $userInstitution->id)->latest()->take(5)->get()
            : collect();
        $pendingOrganolepticCount = $userInstitution
            ? Delivery::where('beneficiary_institution_id', $userInstitution->id)
                ->where('status', 'arrived')
                ->doesntHave('confirmation')
                ->count()
            : 0;

        return view('dashboard.index', compact(
            'currentUser',
            'currentUserRole',
            'myDraftMenus',
            'uninspectedRuns',
            'todayApd',
            'pendingPaguMenus',
            'paguCompliantCount',
            'pendingApprovalMenus',
            'activeKitchenRuns',
            'pendingShipments',
            'unresolvedTickets',
            'activeCourierDeliveries',
            'userInstitution',
            'mySchoolDeliveries',
            'mySchoolComplaints',
            'pendingOrganolepticCount',
            'search',
            'dateFrom',
            'dateTo',
            'institutionId',
            'institutionType',
            'recipientCategory',
            'menuStatus',
            'todayMenu',
            'todayProduction',
            'latestQc',
            'ingredients',
            'lowStockIngredients',
            'totalInventoryValue',
            'deliveries',
            'activeInstitutions',
            'allInstitutions',
            'pendingMenusCount',
            'institutionsCount',
            'totalAllocatedQuota',
            'grandTotalQuota',
            'totalUsersCount',
            'volunteersCount',
            'paguLimit',
            'activeComplaints',
            'criticalComplaintsCount',
            'chartProductionLabels',
            'chartPlannedData',
            'chartActualData',
            'chartDamagedData',
            'chartCategoryLabels',
            'chartCategoryQuotas',
            'nutritionChartData',
            'chartQcLabels',
            'chartQcCoreTemp',
            'chartQcHoldingTemp',
            'chartQcApdScore',
            'chartStockCatLabels',
            'chartStockCatValues',
            'recentConfirmations',
            'recentTransactions',
            'totalInboundSpend'
        ));
    }

    /**
     * Pencarian Global Real-Time (JSON) lintas seluruh tabel database
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '' || strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // 1. Menu Harian
        $menus = DailyMenu::where('menu_name', 'like', "%{$q}%")
            ->orWhere('menu_code', 'like', "%{$q}%")
            ->take(4)
            ->get();
        foreach ($menus as $m) {
            $results[] = [
                'type' => 'Menu Gizi',
                'title' => $m->menu_name,
                'subtitle' => $m->menu_code . ' • ' . $m->target_portions . ' Porsi • ' . $m->operational_date->format('d/m/Y'),
                'url' => route('menu-planning.index', ['search' => $m->menu_code]),
            ];
        }

        // 2. Mitra Sekolah & Posyandu
        $institutions = BeneficiaryInstitution::where('name', 'like', "%{$q}%")
            ->orWhere('code', 'like', "%{$q}%")
            ->orWhere('pic_name', 'like', "%{$q}%")
            ->take(4)
            ->get();
        foreach ($institutions as $inst) {
            $results[] = [
                'type' => 'Mitra Penerima',
                'title' => $inst->name,
                'subtitle' => $inst->code . ' • ' . $inst->default_quota . ' Porsi • PIC: ' . ($inst->pic_name ?? '-'),
                'url' => route('distribution.index', ['institution_id' => $inst->id]),
            ];
        }

        // 3. Bahan Baku
        $ingredients = Ingredient::where('name', 'like', "%{$q}%")
            ->orWhere('sku', 'like', "%{$q}%")
            ->take(4)
            ->get();
        foreach ($ingredients as $ing) {
            $results[] = [
                'type' => 'Bahan Baku Gudang',
                'title' => $ing->name,
                'subtitle' => $ing->sku . ' • Stok: ' . $ing->current_stock . ' ' . $ing->unit,
                'url' => route('production-inventory.index', ['search' => $ing->sku]),
            ];
        }

        // 4. Surat Jalan Distribusi
        $deliveries = Delivery::with('institution')
            ->where('waybill_number', 'like', "%{$q}%")
            ->orWhere('vehicle_plate_number', 'like', "%{$q}%")
            ->take(4)
            ->get();
        foreach ($deliveries as $deliv) {
            $results[] = [
                'type' => 'Surat Jalan Distribusi',
                'title' => $deliv->waybill_number,
                'subtitle' => ($deliv->institution->name ?? '') . ' • ' . $deliv->dispatched_portions . ' Porsi • ' . strtoupper($deliv->status),
                'url' => route('distribution.index', ['search' => $deliv->waybill_number]),
            ];
        }

        // 5. Tiket Keluhan
        $tickets = ComplaintTicket::with('institution')
            ->where('ticket_number', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->take(4)
            ->get();
        foreach ($tickets as $tkt) {
            $results[] = [
                'type' => 'Tiket Keluhan',
                'title' => $tkt->ticket_number . ' - ' . $tkt->categoryLabel(),
                'subtitle' => $tkt->institution->name . ' • Keparahan: ' . strtoupper($tkt->severity),
                'url' => route('distribution.index', ['search' => $tkt->ticket_number]),
            ];
        }

        return response()->json(['results' => $results]);
    }
}
