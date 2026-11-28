<?php

namespace App\Http\Controllers\AhliGizi;

use App\Enums\DeliveryStatus;
use App\Enums\MenuStatus;
use App\Http\Controllers\Controller;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\DataRevision;
use App\Models\DeliveryRoute;
use App\Models\FoodSafetyCheck;
use App\Models\FoodSafetyFinding;
use App\Models\ProductionRun;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = now('Asia/Jakarta');
        $today = $now->toDateString();

        $todayMenu = DailyMenu::with(['items.ingredient', 'productionRun'])
            ->whereDate('operational_date', $today)
            ->whereHas('items')
            ->first()
            ?? DailyMenu::with(['items.ingredient', 'productionRun'])
                ->whereDate('operational_date', $today)
                ->first()
            ?? DailyMenu::with(['items.ingredient', 'productionRun'])->orderBy('operational_date')->first();

        $actionMenus = DailyMenu::query()
            ->whereIn('status', MenuStatus::editableByAhliGizi())
            ->orderBy('operational_date')
            ->limit(5)
            ->get();

        $targetPortions = (int) ($todayMenu?->target_portions ?? 1414);
        $productionRun = $todayMenu?->productionRun ?? ProductionRun::query()->latest('id')->first();
        $omprengFilled = (int) ($productionRun?->actual_good_portions ?? 0);
        $omprengPct = $targetPortions > 0 ? min(100, (int) round(($omprengFilled / $targetPortions) * 100)) : 0;

        $varianceItems = collect($todayMenu?->items ?? [])
            ->filter(fn ($item) => $item->actual_used_qty !== null && (float) $item->actual_used_qty !== (float) $item->total_planned_qty)
            ->map(function ($item): array {
                $diff = (float) $item->actual_used_qty - (float) $item->total_planned_qty;
                $unit = $item->ingredient?->unit ?? 'kg';
                $shortName = explode(' ', (string) ($item->ingredient?->name ?? 'Bahan'))[0];
                $formatted = ($diff > 0 ? '+' : '').(fmod($diff, 1.0) === 0.0 ? (string) (int) $diff : number_format($diff, 1, ',', '.')).' '.$unit;

                return [
                    'name' => $item->ingredient?->name ?? 'Bahan',
                    'short' => $shortName.' '.$formatted,
                ];
            })
            ->values();

        $morningCheck = FoodSafetyCheck::query()
            ->whereDate('check_date', $today)
            ->where('shift', 'Pagi')
            ->first();

        $openFindings = FoodSafetyFinding::query()
            ->where('status', 'terbuka')
            ->orderByDesc('finding_date')
            ->get();

        $activeComplaints = ComplaintTicket::with(['location', 'institution'])
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByDesc('reported_at')
            ->get();

        $routes = DeliveryRoute::with('location')
            ->whereDate('operational_date', $today)
            ->orderBy('waybill_number')
            ->get();

        $dispatchedStatuses = [
            DeliveryStatus::BERANGKAT->value,
            DeliveryStatus::TIBA->value,
            DeliveryStatus::DITERIMA->value,
            DeliveryStatus::BERMASALAH->value,
        ];
        $dikirim = (int) $routes->filter(fn ($r) => in_array($r->status->value, $dispatchedStatuses, true))->sum('dispatched_portions');
        $receivedRoutes = $routes->filter(fn ($r) => $r->status === DeliveryStatus::DITERIMA);
        $diterima = (int) $receivedRoutes->sum('confirmed_portions');
        $belumDikonfirmasi = $dikirim - (int) $receivedRoutes->sum('dispatched_portions');
        $selisih = (int) $receivedRoutes->sum(fn ($r) => (int) ($r->confirmed_portions ?? 0) - (int) $r->dispatched_portions);

        return view('ahli-gizi.dashboard', [
            'todayMenu' => $todayMenu,
            'actionMenus' => $actionMenus,
            'targetPortions' => $targetPortions,
            'omprengFilled' => $omprengFilled,
            'omprengPct' => $omprengPct,
            'varianceItems' => $varianceItems,
            'morningCheck' => $morningCheck,
            'openFindings' => $openFindings,
            'activeComplaints' => $activeComplaints,
            'routes' => $routes,
            'deliveryStats' => [
                'dikirim' => $dikirim,
                'diterima' => $diterima,
                'belum_dikonfirmasi' => $belumDikonfirmasi,
                'selisih' => $selisih,
            ],
        ]);
    }

    public function requestRevision(Request $request, DailyMenu $dailyMenu): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'proposed_change' => ['nullable', 'string', 'max:500'],
        ], [
            'reason.required' => 'Alasan revisi wajib diisi sebelum mengirim pengajuan.',
            'reason.min' => 'Alasan revisi minimal 5 karakter.',
        ]);

        $actor = $request->user();
        $oldStatus = $dailyMenu->status;

        $history = is_array($dailyMenu->status_history_json) ? $dailyMenu->status_history_json : [];
        array_unshift($history, [
            'title' => 'Pengajuan Revisi · Ahli Gizi ('.($actor?->name ?? 'Nadia Putri').')',
            'note' => $validated['reason'],
            'at' => now('Asia/Jakarta')->format('d M Y · H:i').' WIB',
            'tone' => 'alert',
        ]);

        $dailyMenu->update([
            'revision_reason' => $validated['reason'],
            'status_history_json' => $history,
        ]);

        DataRevision::create([
            'revisable_type' => DailyMenu::class,
            'revisable_id' => $dailyMenu->id,
            'old_values' => ['status' => $oldStatus, 'menu_name' => $dailyMenu->menu_name],
            'new_values' => ['proposed_change' => ($validated['proposed_change'] ?? null) ?: $validated['reason']],
            'reason' => $validated['reason'],
            'requested_by' => $actor?->id,
        ]);

        AuditLogService::record(
            action: 'revisi_data',
            module: 'Ahli Gizi · Perencanaan Menu',
            target: $dailyMenu->menu_code.' ('.$dailyMenu->operational_date?->format('d M Y').')',
            oldValues: ['status' => $oldStatus],
            newValues: ['revision_reason' => $validated['reason']],
            reason: $validated['reason'],
            actor: $actor,
        );

        return back()->with('success', 'Pengajuan revisi menu berhasil dikirim ke Kepala Dapur.');
    }
}
