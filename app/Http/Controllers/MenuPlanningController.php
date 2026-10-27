<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DailyMenu;
use App\Models\DailyMenuItem;
use App\Models\Ingredient;
use App\Models\ProductionRun;
use App\Models\SopConfiguration;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuPlanningController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', 'all');
        $paguStatus = $request->query('pagu_status', 'all');
        $recipientCategory = $request->query('recipient_category', 'all');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $sort = $request->query('sort', 'date_desc');

        $query = DailyMenu::with(['nutritionist', 'headOfKitchen', 'accountant', 'items.ingredient', 'productionRun']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('menu_name', 'like', "%{$search}%")
                  ->orWhere('menu_code', 'like', "%{$search}%")
                  ->orWhere('approval_notes', 'like', "%{$search}%")
                  ->orWhere('pagu_notes', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($paguStatus !== 'all') {
            $query->where('pagu_status', $paguStatus);
        }

        if ($recipientCategory !== 'all') {
            $query->where('recipient_category', $recipientCategory);
        }

        if ($dateFrom) {
            $query->whereDate('operational_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('operational_date', '<=', $dateTo);
        }

        match ($sort) {
            'date_asc' => $query->orderBy('operational_date'),
            'kcal_desc' => $query->orderByDesc('calories_kcal'),
            'portions_desc' => $query->orderByDesc('target_portions'),
            'cost_desc' => $query->orderByDesc('estimated_cost_per_portion'),
            default => $query->orderByDesc('operational_date'),
        };

        $menus = $query->get();
        $ingredients = Ingredient::orderBy('category')->orderBy('name')->get();
        $paguLimit = (float) SopConfiguration::getValue('pagu_anggaran_per_porsi', 15000);

        // Data Grafik Perencanaan Menu: Tren Kalori, Protein & Biaya per Porsi
        $chartMenus = $menus->take(8)->reverse()->values();
        $chartLabels = $chartMenus->map(fn ($m) => $m->operational_date->translatedFormat('d M') . ' (' . $m->menu_code . ')');
        $chartCalories = $chartMenus->pluck('calories_kcal');
        $chartProteins = $chartMenus->pluck('protein_gram');
        $chartCosts = $chartMenus->pluck('estimated_cost_per_portion');

        // Distribusi Status Menu
        $statusCounts = [
            'draft' => DailyMenu::where('status', 'draft')->count(),
            'pending_budget_review' => DailyMenu::where('status', 'pending_budget_review')->count(),
            'pending_approval' => DailyMenu::where('status', 'pending_approval')->count(),
            'approved' => DailyMenu::where('status', 'approved')->count(),
            'in_production' => DailyMenu::where('status', 'in_production')->count(),
            'completed' => DailyMenu::where('status', 'completed')->count(),
            'revision_needed' => DailyMenu::where('status', 'revision_needed')->count(),
        ];

        // Default tanggal operasional baru & kode menu otomatis
        $existingToday = DailyMenu::whereDate('operational_date', today())->exists();
        $defaultOperationalDate = $existingToday ? today()->addDay()->format('Y-m-d') : today()->format('Y-m-d');
        $nextMenuCode = self::generateNextMenuCode($defaultOperationalDate);

        return view('menu-planning.index', compact(
            'menus',
            'ingredients',
            'search',
            'status',
            'paguStatus',
            'recipientCategory',
            'dateFrom',
            'dateTo',
            'sort',
            'chartLabels',
            'chartCalories',
            'chartProteins',
            'chartCosts',
            'statusCounts',
            'paguLimit',
            'defaultOperationalDate',
            'nextMenuCode'
        ));
    }

    /**
     * Endpoint API untuk Generate Kode Menu Otomatis secara Real-Time
     */
    public function generateCode(Request $request): JsonResponse
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        $code = self::generateNextMenuCode($date);

        return response()->json([
            'success' => true,
            'date' => $date,
            'menu_code' => $code,
        ]);
    }

    /**
     * Helper Static untuk Menghasilkan Kode Menu Unik & Otomatis: MNU-SS1-YYYYMMDD-XX
     */
    public static function generateNextMenuCode(string|Carbon $date): string
    {
        try {
            $parsed = $date instanceof Carbon ? $date : Carbon::parse($date);
        } catch (\Throwable) {
            $parsed = now();
        }

        $dateStr = $parsed->format('Ymd');
        $count = DailyMenu::whereDate('operational_date', $parsed->toDateString())->count() + 1;
        $candidate = sprintf('MNU-SS1-%s-%02d', $dateStr, $count);

        while (DailyMenu::where('menu_code', $candidate)->exists()) {
            $count++;
            $candidate = sprintf('MNU-SS1-%s-%02d', $dateStr, $count);
        }

        return $candidate;
    }

    /**
     * Input & Pengajuan Draf Rencana Menu oleh Ahli Gizi (Fitur 4)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'operational_date' => ['required', 'date'],
            'menu_code' => ['nullable', 'string', 'max:40'],
            'menu_name' => ['required', 'string', 'max:255'],
            'target_portions' => ['required', 'integer', 'min:10'],
            'recipient_category' => ['required', 'in:siswa,balita_bumil,balita_busui,gabungan'],
            'calories_kcal' => ['required', 'numeric', 'min:100'],
            'protein_gram' => ['required', 'numeric', 'min:1'],
            'carbs_gram' => ['required', 'numeric', 'min:1'],
            'fat_gram' => ['required', 'numeric', 'min:1'],
            'fiber_gram' => ['nullable', 'numeric', 'min:0'],
            'estimated_cost_per_portion' => ['required', 'numeric', 'min:1000'],
            'items' => ['nullable', 'array'],
            'items.*.ingredient_id' => ['nullable', 'exists:ingredients,id'],
            'items.*.qty_per_portion' => ['nullable', 'numeric', 'min:0.001'],
            'items.*.preparation_notes' => ['nullable', 'string', 'max:255'],
        ], [
            'operational_date.required' => 'Tanggal operasional wajib ditentukan.',
            'operational_date.date' => 'Format tanggal operasional tidak valid.',
            'menu_name.required' => 'Nama paket menu wajib diisi.',
            'target_portions.required' => 'Target porsi wajib diisi minimal 10 porsi.',
            'recipient_category.required' => 'Kategori penerima manfaat wajib dipilih.',
            'calories_kcal.required' => 'Nilai energi kalori wajib diisi.',
            'protein_gram.required' => 'Nilai protein wajib diisi.',
            'carbs_gram.required' => 'Nilai karbohidrat wajib diisi.',
            'fat_gram.required' => 'Nilai lemak wajib diisi.',
            'estimated_cost_per_portion.required' => 'Estimasi biaya bahan per porsi wajib diisi.',
        ]);

        // Kode Menu dibuat otomatis dan dijamin unik bebas duplikasi
        $code = !empty($validated['menu_code']) ? strtoupper(trim($validated['menu_code'])) : '';
        if (empty($code) || DailyMenu::where('menu_code', $code)->exists()) {
            $code = self::generateNextMenuCode($validated['operational_date']);
        }
        $validated['menu_code'] = $code;

        DB::transaction(function () use ($validated) {
            $userRole = Auth::user()->role->value;
            $isAdmin = $userRole === 'admin';

            $menu = DailyMenu::create([
                'operational_date' => $validated['operational_date'],
                'menu_code' => $validated['menu_code'],
                'menu_name' => $validated['menu_name'],
                'target_portions' => $validated['target_portions'],
                'recipient_category' => $validated['recipient_category'],
                'calories_kcal' => $validated['calories_kcal'],
                'protein_gram' => $validated['protein_gram'],
                'carbs_gram' => $validated['carbs_gram'],
                'fat_gram' => $validated['fat_gram'],
                'fiber_gram' => $validated['fiber_gram'] ?? 7.5,
                'estimated_cost_per_portion' => $validated['estimated_cost_per_portion'],
                'pagu_status' => $isAdmin ? 'sesuai_pagu' : 'pending',
                'pagu_notes' => $isAdmin ? 'Disetujui otomatis oleh Administrator Pusat.' : 'Menunggu pemeriksaan PAGU anggaran oleh Akuntan.',
                'status' => $isAdmin ? 'approved' : 'pending_budget_review',
                'created_by' => Auth::id(),
                'approved_by' => $isAdmin ? Auth::id() : null,
                'approved_at' => $isAdmin ? now() : null,
                'approval_notes' => $isAdmin ? 'Ditetapkan & disetujui langsung oleh Administrator Pusat.' : null,
            ]);

            if (! empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    if (empty($item['ingredient_id']) || empty($item['qty_per_portion'])) {
                        continue;
                    }
                    DailyMenuItem::create([
                        'daily_menu_id' => $menu->id,
                        'ingredient_id' => $item['ingredient_id'],
                        'qty_per_portion' => $item['qty_per_portion'],
                        'total_planned_qty' => round($item['qty_per_portion'] * $validated['target_portions'], 2),
                        'preparation_notes' => $item['preparation_notes'] ?? null,
                    ]);
                }
            }

            if ($isAdmin) {
                ProductionRun::create([
                    'daily_menu_id' => $menu->id,
                    'planned_portions' => $menu->target_portions,
                    'actual_good_portions' => $menu->target_portions,
                    'rejected_damaged_portions' => 0,
                    'status' => 'scheduled',
                    'supervised_by' => Auth::id(),
                    'volunteers_count' => 35,
                    'variance_reason' => 'Sesi produksi dijadwalkan otomatis oleh Administrator Pusat.',
                ]);
            }

            ActivityLog::record(
                'create_menu',
                'menu_planning',
                "Ahli Gizi mengajukan rencana menu {$menu->menu_code} ({$menu->menu_name}) target {$menu->target_portions} porsi untuk tanggal {$menu->operational_date->format('d/m/Y')}."
            );
        });

        return redirect()->route('menu-planning.index')
            ->with('success', 'Rencana Menu Harian baru berhasil diajukan dan diteruskan ke Akuntan untuk verifikasi PAGU.');
    }

    /**
     * Verifikasi Kesesuaian Anggaran Rencana Menu terhadap PAGU oleh Akuntan (Fitur 4)
     */
    public function verifyPagu(Request $request, DailyMenu $dailyMenu): RedirectResponse
    {
        $validated = $request->validate([
            'pagu_status' => ['required', 'in:sesuai_pagu,melebihi_pagu'],
            'pagu_notes' => ['required', 'string', 'max:500'],
        ]);

        $nextStatus = $validated['pagu_status'] === 'sesuai_pagu' ? 'pending_approval' : 'revision_needed';

        $dailyMenu->update([
            'pagu_status' => $validated['pagu_status'],
            'pagu_notes' => $validated['pagu_notes'],
            'verified_by_accountant_id' => Auth::id(),
            'pagu_verified_at' => now(),
            'status' => $nextStatus,
        ]);

        ActivityLog::record(
            'verify_pagu',
            'budget_pagu',
            "Akuntan memverifikasi PAGU menu {$dailyMenu->menu_code}: Status {$validated['pagu_status']} (Rp " . number_format($dailyMenu->estimated_cost_per_portion, 0, ',', '.') . "/porsi). Catatan: {$validated['pagu_notes']}."
        );

        $msg = $validated['pagu_status'] === 'sesuai_pagu'
            ? "Verifikasi PAGU berhasil! Menu {$dailyMenu->menu_code} telah diteruskan ke Kepala Dapur untuk persetujuan (approval)."
            : "Menu {$dailyMenu->menu_code} ditandai melebihi PAGU anggaran dan dikembalikan ke Ahli Gizi untuk revisi.";

        return redirect()->route('menu-planning.index')->with('success', $msg);
    }

    /**
     * Persetujuan (Approval) atau Permintaan Revisi oleh Kepala Dapur (Fitur 4)
     */
    public function approve(Request $request, DailyMenu $dailyMenu): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,revision_needed,in_production,completed'],
            'approval_notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($dailyMenu, $validated) {
            $dailyMenu->update([
                'status' => $validated['decision'],
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_notes' => $validated['approval_notes'] ?? 'Disetujui sesuai standar operasional dapur SPPG Sungai Selayur 1.',
            ]);

            if (in_array($validated['decision'], ['approved', 'in_production']) && ! $dailyMenu->productionRun) {
                ProductionRun::create([
                    'daily_menu_id' => $dailyMenu->id,
                    'planned_portions' => $dailyMenu->target_portions,
                    'actual_good_portions' => $dailyMenu->target_portions,
                    'rejected_damaged_portions' => 0,
                    'status' => 'scheduled',
                    'supervised_by' => Auth::id(),
                    'volunteers_count' => 35,
                    'variance_reason' => 'Dijadwalkan otomatis pasca validasi persetujuan Kepala Dapur.',
                ]);
            }

            ActivityLog::record(
                'approve_menu',
                'menu_approval',
                "Kepala Dapur memberikan putusan {$validated['decision']} untuk menu {$dailyMenu->menu_code}."
            );
        });

        return redirect()->route('menu-planning.index')
            ->with('success', 'Status approval menu ' . $dailyMenu->menu_code . ' berhasil diperbarui.');
    }

    public function destroy(DailyMenu $dailyMenu): RedirectResponse
    {
        $code = $dailyMenu->menu_code;
        $dailyMenu->delete();

        ActivityLog::record(
            'delete_menu',
            'menu_planning',
            "Jadwal menu {$code} beserta sesi produksinya telah dihapus dari sistem."
        );

        return redirect()->route('menu-planning.index')
            ->with('success', "Jadwal menu {$code} beserta sesi produksinya telah dihapus dari database.");
    }
}
