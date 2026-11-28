<?php

namespace App\Http\Controllers\Akuntan;

use App\Enums\MenuStatus;
use App\Http\Controllers\Controller;
use App\Models\DailyMenu;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $periode = (string) $request->query('periode', 'minggu_ini');
        if (! in_array($periode, ['minggu_ini', 'bulan_ini', 'semua'], true)) {
            $periode = 'minggu_ini';
        }

        $statusFilter = (string) $request->query('status', 'semua');

        $now = now()->timezone('Asia/Jakarta');
        $query = DailyMenu::query()
            ->with([
                'nutritionist',
                'submitter',
                'accountant',
                'headOfKitchen',
                'categoryPortions.recipientCategory',
                'paguChecks' => fn ($q) => $q->with(['accountant', 'estimates.recipientCategory'])->orderByDesc('siklus_pengajuan'),
            ])
            ->where('status', '!=', MenuStatus::DRAF->value)
            ->orderBy('operational_date');

        if ($periode === 'minggu_ini') {
            $start = $now->copy()->startOfWeek()->startOfDay();
            $end = $now->copy()->endOfWeek()->endOfDay();
            $query->whereBetween('operational_date', [$start->toDateString(), $end->toDateString()]);
        } elseif ($periode === 'bulan_ini') {
            $start = $now->copy()->startOfMonth()->startOfDay();
            $end = $now->copy()->endOfMonth()->endOfDay();
            $query->whereBetween('operational_date', [$start->toDateString(), $end->toDateString()]);
        }

        $periodMenus = $query->get();

        $diajukanCount = $periodMenus->count();
        $lolosCount = $periodMenus->filter(
            fn (DailyMenu $m) => in_array($m->menuStatus(), [MenuStatus::LOLOS_PAGU, MenuStatus::DISETUJUI, MenuStatus::MINTA_REVISI], true)
        )->count();
        $revisiCount = $periodMenus->filter(
            fn (DailyMenu $m) => $m->menuStatus() === MenuStatus::PERLU_REVISI_ANGGARAN
        )->count();
        $menungguCount = $periodMenus->filter(
            fn (DailyMenu $m) => $m->menuStatus() === MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN
        )->count();

        $filteredMenus = $statusFilter === 'semua'
            ? $periodMenus
            : $periodMenus->filter(fn (DailyMenu $m) => $m->menuStatus()->value === $statusFilter)->values();

        return view('akuntan.laporan', [
            'periode' => $periode,
            'statusFilter' => $statusFilter,
            'diajukanCount' => $diajukanCount,
            'lolosCount' => $lolosCount,
            'revisiCount' => $revisiCount,
            'menungguCount' => $menungguCount,
            'menus' => $filteredMenus,
        ]);
    }
}
