<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DailyMenu;
use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Models\ProductionRun;
use App\Models\SopConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductionInventoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $category = $request->query('category', 'all');
        $stockStatus = $request->query('stock_status', 'all');
        $txType = $request->query('tx_type', 'all');

        // 1. Sesi Produksi Dapur & Pemorsian Ompreng
        $productionRuns = ProductionRun::with(['dailyMenu.items.ingredient', 'supervisor', 'qcInspection'])
            ->latest('id')
            ->get();

        // 2. Filter Master Bahan Baku Gudang
        $ingQuery = Ingredient::query();
        if ($search !== '') {
            $ingQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        if ($category !== 'all') {
            $ingQuery->where('category', $category);
        }
        if ($stockStatus === 'low') {
            $ingQuery->whereColumn('current_stock', '<=', 'min_stock_threshold');
        } elseif ($stockStatus === 'safe') {
            $ingQuery->whereColumn('current_stock', '>', 'min_stock_threshold');
        }
        $ingredients = $ingQuery->orderBy('category')->orderBy('name')->get();
        $allIngredients = Ingredient::orderBy('name')->get();

        // 3. Filter Log Transaksi Gudang
        $txQuery = InventoryTransaction::with(['ingredient', 'dailyMenu', 'recorder', 'accountant']);
        if ($txType !== 'all') {
            $txQuery->where('type', $txType);
        }
        if ($search !== '') {
            $txQuery->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhereHas('ingredient', fn ($iq) => $iq->where('name', 'like', "%{$search}%"));
            });
        }
        $transactions = $txQuery->latest()->take(30)->get();

        $activeMenus = DailyMenu::orderByDesc('operational_date')->get();
        $standardVolunteers = (int) SopConfiguration::getValue('jumlah_relawan_dapur_shift', 35);

        // 4. Data Grafik
        $chartIngNames = $ingredients->take(8)->pluck('name');
        $chartCurrentStocks = $ingredients->take(8)->pluck('current_stock');
        $chartMinThresholds = $ingredients->take(8)->pluck('min_stock_threshold');

        $txSummary = [
            'inbound' => (float) InventoryTransaction::where('type', 'inbound_purchase')->sum('total_cost'),
            'outbound' => (float) InventoryTransaction::where('type', 'outbound_production')->sum('total_cost'),
            'spoilage' => (float) InventoryTransaction::where('type', 'spoilage_adjustment')->sum('total_cost'),
        ];

        return view('production-inventory.index', compact(
            'productionRuns',
            'ingredients',
            'allIngredients',
            'transactions',
            'activeMenus',
            'search',
            'category',
            'stockStatus',
            'txType',
            'chartIngNames',
            'chartCurrentStocks',
            'chartMinThresholds',
            'txSummary',
            'standardVolunteers'
        ));
    }

    public function storeIngredient(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:30', 'unique:ingredients,sku'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:karbohidrat,protein_hewani,protein_nabati,sayuran,buah,susu,bumbu_minyak'],
            'unit' => ['required', 'string', 'max:20'],
            'current_stock' => ['required', 'numeric', 'min:0'],
            'min_stock_threshold' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $ingredient = Ingredient::create(array_merge($validated, [
            'sku' => strtoupper($validated['sku']),
        ]));

        ActivityLog::record(
            'create_ingredient',
            'inventory',
            "Menambahkan bahan baku baru {$ingredient->sku} - {$ingredient->name} (Stok: {$ingredient->current_stock} {$ingredient->unit})."
        );

        return redirect()->route('production-inventory.index')
            ->with('success', 'Master bahan baku baru berhasil ditambahkan ke database gudang SPPG.');
    }

    public function updateIngredient(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:karbohidrat,protein_hewani,protein_nabati,sayuran,buah,susu,bumbu_minyak'],
            'unit' => ['required', 'string', 'max:20'],
            'current_stock' => ['required', 'numeric', 'min:0'],
            'min_stock_threshold' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $ingredient->update($validated);

        ActivityLog::record(
            'update_ingredient',
            'inventory',
            "Memperbarui data bahan baku {$ingredient->sku} - {$ingredient->name} (Stok: {$ingredient->current_stock} {$ingredient->unit})."
        );

        return redirect()->route('production-inventory.index')
            ->with('success', "Data bahan baku {$ingredient->name} berhasil diperbarui.");
    }

    public function destroyIngredient(Ingredient $ingredient): RedirectResponse
    {
        $name = $ingredient->name;
        $sku = $ingredient->sku;
        $ingredient->delete();

        ActivityLog::record(
            'delete_ingredient',
            'inventory',
            "Menghapus bahan baku {$sku} - {$name} dari master gudang."
        );

        return redirect()->route('production-inventory.index')
            ->with('success', "Bahan baku {$name} telah dihapus dari master gudang.");
    }

    public function storeProductionRun(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'daily_menu_id' => ['required', 'exists:daily_menus,id', 'unique:production_runs,daily_menu_id'],
            'actual_good_portions' => ['required', 'integer', 'min:0'],
            'rejected_damaged_portions' => ['required', 'integer', 'min:0'],
            'volunteers_count' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'in:scheduled,cooking,qc_inspection,packaging,ready_for_dispatch'],
            'variance_reason' => ['nullable', 'string', 'max:500'],
            'operational_obstacle' => ['nullable', 'string', 'max:500'],
            'action_taken' => ['nullable', 'string', 'max:500'],
        ]);

        $menu = DailyMenu::findOrFail($validated['daily_menu_id']);

        $run = ProductionRun::create([
            'daily_menu_id' => $menu->id,
            'planned_portions' => $menu->target_portions,
            'actual_good_portions' => $validated['actual_good_portions'],
            'rejected_damaged_portions' => $validated['rejected_damaged_portions'],
            'volunteers_count' => $validated['volunteers_count'],
            'cooking_started_at' => now()->subHours(2),
            'cooking_finished_at' => now(),
            'status' => $validated['status'],
            'supervised_by' => Auth::id(),
            'variance_reason' => $validated['variance_reason'],
            'operational_obstacle' => $validated['operational_obstacle'],
            'action_taken' => $validated['action_taken'],
        ]);

        ActivityLog::record(
            'create_production',
            'production',
            "Membuka sesi pemorsian ompreng menu {$menu->menu_code}: Rencana {$menu->target_portions} porsi, Hasil baik {$run->actual_good_portions} porsi, Selisih " . ($run->portion_variance >= 0 ? '+' : '') . "{$run->portion_variance} porsi."
        );

        return redirect()->route('production-inventory.index')
            ->with('success', 'Sesi produksi dapur & pemorsian ompreng berhasil dibuka dan dihitung selisih porsinya.');
    }

    public function storeTransaction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'daily_menu_id' => ['nullable', 'exists:daily_menus,id'],
            'type' => ['required', 'in:inbound_purchase,outbound_production,spoilage_adjustment'],
            'quantity' => ['required', 'numeric', 'min:0.1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'freshness_condition' => ['required', 'in:sangat_segar,layak,ditolak'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated) {
            $ingredient = Ingredient::lockForUpdate()->findOrFail($validated['ingredient_id']);
            $unitPrice = $validated['unit_price'] ?? $ingredient->unit_cost;
            $prefix = match ($validated['type']) {
                'inbound_purchase' => 'INB',
                'outbound_production' => 'OUT',
                default => 'ADJ',
            };

            $refNo = $prefix . '/SS1/' . now()->format('Ymd/His');

            InventoryTransaction::create([
                'reference_no' => $refNo,
                'ingredient_id' => $ingredient->id,
                'daily_menu_id' => $validated['daily_menu_id'] ?? null,
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'unit_price' => $unitPrice,
                'total_cost' => round($validated['quantity'] * $unitPrice, 2),
                'supplier_name' => $validated['supplier_name'] ?? 'Pemasok Terdaftar SPPG Sungai Selayur 1',
                'freshness_condition' => $validated['freshness_condition'],
                'recorded_by' => Auth::id(),
                'verified_by_accountant' => in_array(Auth::user()->role->value, ['akuntan', 'admin']) ? Auth::id() : null,
                'notes' => $validated['notes'],
            ]);

            if ($validated['type'] === 'inbound_purchase' && $validated['freshness_condition'] !== 'ditolak') {
                $ingredient->increment('current_stock', $validated['quantity']);
            } elseif (in_array($validated['type'], ['outbound_production', 'spoilage_adjustment'])) {
                $newStock = max(0, $ingredient->current_stock - $validated['quantity']);
                $ingredient->update(['current_stock' => $newStock]);
            }

            ActivityLog::record(
                'inventory_transaction',
                'inventory',
                "Mencatat transaksi {$validated['type']} {$refNo} untuk {$ingredient->name} ({$validated['quantity']} {$ingredient->unit}) dari {$validated['supplier_name']}."
            );
        });

        return redirect()->route('production-inventory.index')
            ->with('success', 'Log mutasi bahan baku berhasil dicatat dan stok gudang telah diperbarui secara real-time.');
    }

    public function updateProduction(Request $request, ProductionRun $productionRun): RedirectResponse
    {
        $validated = $request->validate([
            'actual_good_portions' => ['required', 'integer', 'min:0'],
            'rejected_damaged_portions' => ['required', 'integer', 'min:0'],
            'volunteers_count' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'in:scheduled,cooking,qc_inspection,packaging,ready_for_dispatch'],
            'variance_reason' => ['nullable', 'string', 'max:500'],
            'operational_obstacle' => ['nullable', 'string', 'max:500'],
            'action_taken' => ['nullable', 'string', 'max:500'],
        ]);

        $productionRun->update([
            'actual_good_portions' => $validated['actual_good_portions'],
            'rejected_damaged_portions' => $validated['rejected_damaged_portions'],
            'volunteers_count' => $validated['volunteers_count'],
            'status' => $validated['status'],
            'variance_reason' => $validated['variance_reason'],
            'operational_obstacle' => $validated['operational_obstacle'],
            'action_taken' => $validated['action_taken'],
            'supervised_by' => Auth::id(),
        ]);

        ActivityLog::record(
            'update_production',
            'production',
            "Memperbarui pemorsian ompreng: {$productionRun->actual_good_portions} baik, {$productionRun->rejected_damaged_portions} cacat, selisih {$productionRun->portion_variance} porsi."
        );

        return redirect()->route('production-inventory.index')
            ->with('success', 'Data realisasi pemorsian ompreng diperbarui! Selisih porsi saat ini: ' . ($productionRun->portion_variance >= 0 ? '+' : '') . $productionRun->portion_variance . ' Porsi.');
    }
}
