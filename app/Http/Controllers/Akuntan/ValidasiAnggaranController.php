<?php

namespace App\Http\Controllers\Akuntan;

use App\Enums\MenuStatus;
use App\Enums\PaguCheckResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\Akuntan\RecordPaguCheckRequest;
use App\Models\DailyMenu;
use App\Models\PaguCheck;
use App\Models\PaguParameter;
use App\Models\RecipientCategory;
use App\Services\MenuWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ValidasiAnggaranController extends Controller
{
    public function index(Request $request): View
    {
        $allMenus = DailyMenu::query()
            ->with([
                'nutritionist',
                'submitter',
                'accountant',
                'headOfKitchen',
                'categoryPortions.recipientCategory',
                'components.ingredient',
                'histories.user',
                'paguChecks' => fn ($q) => $q->with(['accountant', 'estimates.recipientCategory'])->orderByDesc('siklus_pengajuan'),
            ])
            ->orderBy('operational_date')
            ->get();

        $waitingMenus = $allMenus->filter(
            fn (DailyMenu $m) => $m->menuStatus() === MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN
        )->values();

        $checkedMenus = $allMenus->filter(
            fn (DailyMenu $m) => $m->paguChecks->isNotEmpty() || in_array($m->menuStatus(), [
                MenuStatus::LOLOS_PAGU,
                MenuStatus::PERLU_REVISI_ANGGARAN,
                MenuStatus::DISETUJUI,
                MenuStatus::MINTA_REVISI,
            ], true)
        )->values();

        $tab = (string) $request->query('tab', 'menunggu');
        if (! in_array($tab, ['menunggu', 'sudah', 'semua'], true)) {
            $tab = 'menunggu';
        }

        $filteredMenus = match ($tab) {
            'sudah' => $checkedMenus,
            'semua' => $allMenus,
            default => $waitingMenus,
        };

        $selectedId = (int) $request->query('menu', 0);
        $selectedMenu = $filteredMenus->firstWhere('id', $selectedId)
            ?? $allMenus->firstWhere('id', $selectedId)
            ?? $filteredMenus->first()
            ?? $allMenus->first();

        $categories = RecipientCategory::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $activePagu = PaguParameter::query()
            ->where('is_active', true)
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->get()
            ->unique('recipient_category_id')
            ->keyBy('recipient_category_id');

        return view('akuntan.validasi', [
            'tab' => $tab,
            'allMenus' => $allMenus,
            'waitingMenus' => $waitingMenus,
            'checkedMenus' => $checkedMenus,
            'filteredMenus' => $filteredMenus,
            'selectedMenu' => $selectedMenu,
            'categories' => $categories,
            'activePagu' => $activePagu,
        ]);
    }

    public function store(
        RecordPaguCheckRequest $request,
        DailyMenu $menu,
        MenuWorkflowService $workflow,
    ): RedirectResponse {
        Gate::authorize('create', PaguCheck::class);

        $hasil = PaguCheckResult::from((string) $request->validated('hasil'));
        $catatan = trim((string) ($request->validated('catatan') ?? ''));
        if ($hasil === PaguCheckResult::LOLOS && $catatan === '') {
            $catatan = 'Sesuai batas PAGU.';
        }

        $workflow->recordPaguCheck(
            menu: $menu,
            actor: $request->user(),
            hasil: $hasil,
            catatan: $catatan,
            estimates: $request->validated('estimates', []),
        );

        return redirect()
            ->route('akuntan.validasi.index', ['tab' => 'sudah', 'menu' => $menu->id])
            ->with('success', 'Hasil pemeriksaan PAGU untuk menu '.$menu->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M Y').' berhasil dicatat.');
    }
}
