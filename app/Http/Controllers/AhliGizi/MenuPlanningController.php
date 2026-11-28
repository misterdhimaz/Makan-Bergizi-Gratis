<?php

namespace App\Http\Controllers\AhliGizi;

use App\Enums\MenuStatus;
use App\Http\Controllers\Controller;
use App\Models\DailyMenu;
use App\Models\Ingredient;
use App\Models\RecipientCategory;
use App\Services\AuditLogService;
use App\Services\MenuWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuPlanningController extends Controller
{
    public function __construct(
        private readonly MenuWorkflowService $workflow,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $statusFilter = (string) $request->query('status', '');

        $allMenus = DailyMenu::query()->get();
        $kpi = [
            'draft' => $allMenus->filter(fn (DailyMenu $m) => $m->menuStatus() === MenuStatus::DRAF)->count(),
            'pending' => $allMenus->filter(fn (DailyMenu $m) => in_array($m->menuStatus(), [
                MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN,
                MenuStatus::LOLOS_PAGU,
            ], true))->count(),
            'approved' => $allMenus->filter(fn (DailyMenu $m) => $m->menuStatus() === MenuStatus::DISETUJUI)->count(),
            'revision' => $allMenus->filter(fn (DailyMenu $m) => in_array($m->menuStatus(), [
                MenuStatus::MINTA_REVISI,
                MenuStatus::PERLU_REVISI_ANGGARAN,
            ], true))->count(),
        ];

        $query = DailyMenu::query()->orderBy('operational_date');

        if ($q !== '') {
            $query->where(function ($builder) use ($q): void {
                $builder->where('menu_name', 'like', "%{$q}%")
                    ->orWhere('menu_code', 'like', "%{$q}%")
                    ->orWhere('approval_notes', 'like', "%{$q}%");
            });
        }

        if ($statusFilter !== '') {
            match ($statusFilter) {
                'draf' => $query->where('status', MenuStatus::DRAF->value),
                'menunggu' => $query->whereIn('status', [
                    MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN->value,
                    MenuStatus::LOLOS_PAGU->value,
                ]),
                'revisi-anggaran' => $query->where('status', MenuStatus::PERLU_REVISI_ANGGARAN->value),
                'disetujui' => $query->where('status', MenuStatus::DISETUJUI->value),
                'minta-revisi' => $query->where('status', MenuStatus::MINTA_REVISI->value),
                default => null,
            };
        }

        $menus = $query->paginate(10)->withQueryString();
        $ingredients = Ingredient::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'category', 'unit']);
        $categories = RecipientCategory::query()->where('is_active', true)->orderBy('code')->get(['id', 'name']);

        return view('ahli-gizi.perencanaan-menu', [
            'menus' => $menus,
            'kpi' => $kpi,
            'ingredients' => $ingredients,
            'categories' => $categories,
            'filters' => ['q' => $q, 'status' => $statusFilter],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMenu($request);
        $submitAction = $request->input('submit_action', 'draft');
        $actor = $request->user();

        $menu = DailyMenu::create([
            'operational_date' => $validated['operational_date'],
            'menu_code' => 'MNU-SS1-'.now()->format('His'),
            'menu_name' => $validated['menu_name'],
            'target_portions' => $validated['target_portions'],
            'recipient_category' => 'siswa',
            'categories_json' => $validated['categories'] ?? ['Siswa'],
            'components_json' => $validated['components'] ?? [],
            'calories_kcal' => 0,
            'protein_gram' => 0,
            'carbs_gram' => 0,
            'fat_gram' => 0,
            'fiber_gram' => 0,
            'estimated_cost_per_portion' => 0,
            'pagu_status' => 'pending',
            'status' => MenuStatus::DRAF->value,
            'created_by' => $actor?->id,
            'last_note' => 'Ahli Gizi: Belum diajukan',
            'approval_notes' => 'Ahli Gizi: Belum diajukan',
            'notes' => $validated['notes'] ?? null,
            'status_history_json' => [[
                'title' => 'Draf Dibuat · Ahli Gizi ('.($actor?->name ?? 'Nadia Putri').')',
                'note' => $validated['notes'] ?? 'Belum diajukan ke Akuntan.',
                'at' => now('Asia/Jakarta')->format('d M Y · H:i').' WIB',
                'tone' => 'neutral',
            ]],
        ]);

        if ($submitAction === 'submit' && $actor) {
            $this->workflow->submitForBudgetReview($menu, $actor, $validated['notes'] ?? null);
        } else {
            AuditLogService::record(
                action: 'tambah',
                module: 'Ahli Gizi · Perencanaan Menu',
                target: $menu->menu_name,
                newValues: [
                    'operational_date' => $validated['operational_date'],
                    'target_portions' => $validated['target_portions'],
                    'status' => MenuStatus::DRAF->value,
                ],
                actor: $actor,
            );
        }

        return redirect()
            ->route('ahli-gizi.menu.index')
            ->with('success', $submitAction === 'submit'
                ? 'Menu berhasil diajukan ke Akuntan untuk pemeriksaan PAGU.'
                : 'Draf menu berhasil disimpan.');
    }

    public function update(Request $request, DailyMenu $dailyMenu): RedirectResponse
    {
        if ($dailyMenu->isLocked()) {
            return back()->with('error', 'Menu berstatus Disetujui sudah terkunci dan tidak dapat diubah langsung. Gunakan Ajukan revisi menu.');
        }

        $validated = $this->validateMenu($request);
        $submitAction = $request->input('submit_action', 'draft');
        $actor = $request->user();

        $history = is_array($dailyMenu->status_history_json) ? $dailyMenu->status_history_json : [];
        array_unshift($history, [
            'title' => ($submitAction === 'submit' ? 'Diajukan Ulang' : 'Draf Diperbarui').' · Ahli Gizi ('.($actor?->name ?? 'Nadia Putri').')',
            'note' => $validated['notes'] ?? null,
            'at' => now('Asia/Jakarta')->format('d M Y · H:i').' WIB',
            'tone' => 'neutral',
        ]);

        $payload = [
            'operational_date' => $validated['operational_date'],
            'menu_name' => $validated['menu_name'],
            'target_portions' => $validated['target_portions'],
            'categories_json' => $validated['categories'] ?? ['Siswa'],
            'components_json' => $validated['components'] ?? [],
            'notes' => $validated['notes'] ?? null,
            'status_history_json' => $history,
        ];

        if ($submitAction !== 'submit') {
            $payload['status'] = MenuStatus::DRAF->value;
            $payload['approval_notes'] = 'Ahli Gizi: Draf diperbarui';
            $payload['last_note'] = 'Ahli Gizi: Draf diperbarui';
        }

        $dailyMenu->update($payload);

        if ($submitAction === 'submit' && $actor) {
            $this->workflow->submitForBudgetReview($dailyMenu->fresh(), $actor, $validated['notes'] ?? null);
        } else {
            AuditLogService::record(
                action: 'ubah',
                module: 'Ahli Gizi · Perencanaan Menu',
                target: $dailyMenu->menu_name,
                newValues: [
                    'operational_date' => $validated['operational_date'],
                    'target_portions' => $validated['target_portions'],
                    'status' => MenuStatus::DRAF->value,
                ],
                actor: $actor,
            );
        }

        return redirect()
            ->route('ahli-gizi.menu.index')
            ->with('success', $submitAction === 'submit'
                ? 'Perubahan menu berhasil diajukan ke Akuntan.'
                : 'Draf menu berhasil diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMenu(Request $request): array
    {
        return $request->validate([
            'operational_date' => ['required', 'date'],
            'menu_name' => ['required', 'string', 'max:255'],
            'target_portions' => ['required', 'integer', 'min:1', 'max:100000'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['string', 'max:60'],
            'components' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'menu_name.required' => 'Nama menu wajib diisi.',
            'target_portions.required' => 'Target porsi wajib diisi.',
            'target_portions.min' => 'Target porsi minimal 1.',
        ]);
    }
}
