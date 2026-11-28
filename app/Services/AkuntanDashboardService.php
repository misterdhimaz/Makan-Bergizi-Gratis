<?php

namespace App\Services;

use App\Enums\MenuStatus;
use App\Enums\PaguCheckResult;
use App\Models\AuditLog;
use App\Models\DailyMenu;
use App\Models\LocationCategoryQuota;
use App\Models\PaguParameter;
use App\Models\RecipientCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AkuntanDashboardService
{
    /**
     * @return array{
     *   window_start: Carbon,
     *   window_end: Carbon,
     *   menunggu: int,
     *   lolos: int,
     *   perlu_revisi: int,
     *   parameter_count: int,
     *   parameter_updated_at: ?Carbon,
     *   oldest_waiting: ?DailyMenu,
     *   pagu_rows: Collection,
     *   menus: Collection,
     *   activities: Collection
     * }
     */
    public function build(User $user, ?Carbon $windowStart = null, ?Carbon $windowEnd = null): array
    {
        $windowStart ??= Carbon::parse('2026-11-26', 'Asia/Jakarta')->startOfDay();
        $windowEnd ??= Carbon::parse('2026-12-01', 'Asia/Jakarta')->endOfDay();

        $menusInWindow = DailyMenu::query()
            ->with([
                'categoryPortions.recipientCategory',
                'latestPaguCheck',
                'submitter',
                'nutritionist',
            ])
            ->whereBetween('operational_date', [$windowStart->toDateString(), $windowEnd->toDateString()])
            ->orderBy('operational_date')
            ->get();

        $menunggu = DailyMenu::query()
            ->where('status', MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN->value)
            ->count();

        $lolos = $menusInWindow->filter(function (DailyMenu $menu) {
            $check = $menu->latestPaguCheck;

            return $check && $check->hasil === PaguCheckResult::LOLOS;
        })->count();

        $perluRevisi = $menusInWindow->filter(function (DailyMenu $menu) {
            $check = $menu->latestPaguCheck;

            return $check && $check->hasil === PaguCheckResult::PERLU_REVISI;
        })->count();

        $oldestWaiting = DailyMenu::query()
            ->with(['categoryPortions.recipientCategory', 'submitter', 'nutritionist', 'components.ingredient'])
            ->where('status', MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN->value)
            ->orderBy('submitted_at')
            ->orderBy('id')
            ->first();

        $parameters = PaguParameter::query()
            ->with(['recipientCategory', 'updatedBy'])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $portionTotals = LocationCategoryQuota::query()
            ->selectRaw('recipient_category_id, SUM(portion_count) as total_portions, COUNT(DISTINCT location_id) as lokasi_count')
            ->groupBy('recipient_category_id')
            ->get()
            ->keyBy('recipient_category_id');

        $paguRows = $parameters->map(function (PaguParameter $pagu) use ($portionTotals) {
            $stats = $portionTotals->get($pagu->recipient_category_id);

            return [
                'category' => $pagu->recipientCategory?->name ?? '—',
                'nominal' => (int) $pagu->max_budget_per_portion,
                'portions' => (int) ($stats->total_portions ?? 0),
                'lokasi_count' => (int) ($stats->lokasi_count ?? 0),
                'effective_date' => $pagu->effective_date,
            ];
        });

        $activities = AuditLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('occurred_at')
            ->limit(4)
            ->get();

        return [
            'window_start' => $windowStart,
            'window_end' => $windowEnd,
            'menunggu' => $menunggu,
            'lolos' => $lolos,
            'perlu_revisi' => $perluRevisi,
            'parameter_count' => $parameters->count(),
            'parameter_updated_at' => $parameters->max('effective_date'),
            'oldest_waiting' => $oldestWaiting,
            'pagu_rows' => $paguRows,
            'menus' => $menusInWindow,
            'activities' => $activities,
            'diajukan_count' => $menusInWindow->filter(
                fn (DailyMenu $m) => $m->menuStatus() !== MenuStatus::DRAF
            )->count(),
        ];
    }

    /**
     * @return Collection<int, array{id:int,name:string,nominal:int}>
     */
    public function currentPaguByCategory(): Collection
    {
        return PaguParameter::query()
            ->with('recipientCategory')
            ->where('is_active', true)
            ->get()
            ->map(fn (PaguParameter $p) => [
                'id' => $p->recipient_category_id,
                'name' => $p->recipientCategory?->name ?? '—',
                'nominal' => (int) $p->max_budget_per_portion,
            ])
            ->keyBy('id');
    }

    public function categoryName(int $id): string
    {
        return RecipientCategory::query()->whereKey($id)->value('name') ?? '—';
    }
}
