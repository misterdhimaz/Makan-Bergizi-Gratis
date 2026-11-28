<?php

namespace App\Http\Controllers\AhliGizi;

use App\Http\Controllers\Controller;
use App\Models\DailyMenu;
use App\Models\DailyMenuItem;
use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Models\ProductionObstacle;
use App\Models\ProductionRun;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryPortionController extends Controller
{
    public function index(Request $request): View
    {
        $date = (string) $request->query('tanggal', now('Asia/Jakarta')->toDateString());
        $activeTab = (string) $request->query('tab', 'kebutuhan');

        $menu = DailyMenu::with(['items.ingredient', 'productionRun'])
            ->whereDate('operational_date', $date)
            ->first()
            ?? DailyMenu::with(['items.ingredient', 'productionRun'])->orderBy('operational_date')->first();

        $items = $menu?->items ?? collect();

        $receipts = InventoryTransaction::with(['ingredient', 'recorder', 'verifier'])
            ->when($menu, fn ($q) => $q->where('daily_menu_id', $menu->id))
            ->where('type', 'inbound_purchase')
            ->orderBy('id')
            ->get();

        $obstacles = ProductionObstacle::with(['reporter', 'handler'])
            ->whereDate('operational_date', $date)
            ->orderByDesc('id')
            ->get();

        $targetPortions = (int) ($menu?->target_portions ?? 1414);
        $productionRun = $menu?->productionRun;
        $omprengFilled = (int) ($productionRun?->actual_good_portions ?? 0);
        $omprengPct = $targetPortions > 0 ? min(100, (int) round(($omprengFilled / $targetPortions) * 100)) : 0;

        $ingredients = Ingredient::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'category', 'unit']);

        return view('ahli-gizi.persediaan-pemorsian', [
            'selectedDate' => $date,
            'activeTab' => $activeTab,
            'menu' => $menu,
            'items' => $items,
            'receipts' => $receipts,
            'obstacles' => $obstacles,
            'targetPortions' => $targetPortions,
            'omprengFilled' => $omprengFilled,
            'omprengPct' => $omprengPct,
            'ingredients' => $ingredients,
        ]);
    }

    public function storeNeeds(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'daily_menu_id' => ['required', 'exists:daily_menus,id'],
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'total_planned_qty' => ['required', 'numeric', 'min:0.1'],
        ]);

        $menu = DailyMenu::findOrFail($validated['daily_menu_id']);
        $perPortion = $menu->target_portions > 0 ? round($validated['total_planned_qty'] / $menu->target_portions, 4) : 0;

        DailyMenuItem::updateOrCreate(
            ['daily_menu_id' => $menu->id, 'ingredient_id' => $validated['ingredient_id']],
            [
                'qty_per_portion' => $perPortion,
                'total_planned_qty' => $validated['total_planned_qty'],
            ]
        );

        return redirect()
            ->route('ahli-gizi.inventory.index', ['tanggal' => $menu->operational_date?->toDateString(), 'tab' => 'kebutuhan'])
            ->with('success', 'Kebutuhan bahan berhasil diperbarui.');
    }

    public function verifyReceipt(Request $request, InventoryTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'verification_status' => ['required', 'in:sesuai,tidak_sesuai'],
            'verification_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $transaction->update([
            'verification_status' => $validated['verification_status'],
            'verification_notes' => $validated['verification_notes'] ?? null,
            'verified_by' => $request->user()?->id,
            'verified_at' => now(),
        ]);

        AuditLogService::record(
            action: 'verifikasi',
            module: 'Ahli Gizi · Penerimaan Bahan',
            target: $transaction->ingredient?->name ?? $transaction->reference_no,
            newValues: ['verification_status' => $validated['verification_status']],
            reason: $validated['verification_notes'] ?? null,
            actor: $request->user(),
        );

        return back()->with('success', 'Verifikasi penerimaan bahan berhasil disimpan.');
    }

    public function storeRealization(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:daily_menu_items,id'],
            'items.*.dispatched_qty' => ['required', 'numeric', 'min:0'],
            'items.*.actual_used_qty' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            foreach ($validated['items'] as $row) {
                DailyMenuItem::where('id', $row['id'])->update([
                    'dispatched_qty' => $row['dispatched_qty'],
                    'actual_used_qty' => $row['actual_used_qty'],
                ]);
            }

            AuditLogService::record(
                action: 'ubah',
                module: 'Ahli Gizi · Realisasi Pemakaian Bahan',
                target: 'Realisasi bahan harian',
                actor: $request->user(),
            );
        });

        return back()->with('success', 'Realisasi penggunaan bahan berhasil disimpan.');
    }

    public function storeOmpreng(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'daily_menu_id' => ['required', 'exists:daily_menus,id'],
            'actual_good_portions' => ['required', 'integer', 'min:0'],
        ]);

        $menu = DailyMenu::findOrFail($validated['daily_menu_id']);
        $max = (int) $menu->target_portions;

        if ((int) $validated['actual_good_portions'] > $max) {
            return back()
                ->withErrors(['actual_good_portions' => "Jumlah ompreng terisi tidak boleh melebihi target porsi ({$max})."])
                ->withInput();
        }

        ProductionRun::updateOrCreate(
            ['daily_menu_id' => $menu->id],
            [
                'planned_portions' => $max,
                'actual_good_portions' => (int) $validated['actual_good_portions'],
                'supervised_by' => $request->user()?->id,
            ]
        );

        return back()->with('success', 'Jumlah ompreng terisi berhasil dicatat.');
    }

    public function storeObstacle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'operational_date' => ['required', 'date'],
            'daily_menu_id' => ['nullable', 'exists:daily_menus,id'],
            'description' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'description.required' => 'Uraian kendala wajib diisi.',
        ]);

        ProductionObstacle::create([
            'operational_date' => $validated['operational_date'],
            'daily_menu_id' => $validated['daily_menu_id'] ?? null,
            'description' => $validated['description'],
            'status' => 'terbuka',
            'reported_by' => $request->user()?->id,
        ]);

        return back()->with('success', 'Catatan kendala berhasil ditambahkan.');
    }
}
