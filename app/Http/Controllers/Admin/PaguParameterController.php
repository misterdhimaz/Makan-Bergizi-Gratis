<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaguParameter;
use App\Models\RecipientCategory;
use App\Services\DataMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaguParameterController extends Controller
{
    public function __construct(
        private readonly DataMasterService $masterService,
    ) {}

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'all');
        $categoryId = $request->query('category_id');

        $query = PaguParameter::query()->with('recipientCategory')->orderByDesc('effective_date')->orderBy('id');

        if ($categoryId !== null && $categoryId !== '' && $categoryId !== 'all') {
            $query->where('recipient_category_id', (int) $categoryId);
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        return view('admin.data-master.pagu', [
            'pagus' => $query->paginate(10)->withQueryString(),
            'categories' => RecipientCategory::query()->where('is_active', true)->orderBy('code')->get(),
            'filters' => ['status' => $status, 'category_id' => $categoryId ?? 'all'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_category_id' => ['required', 'exists:recipient_categories,id'],
            'max_budget_per_portion' => ['required', 'integer', 'min:1000'],
            'effective_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        PaguParameter::create([
            ...$validated,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.data-master.pagu.index')
            ->with('success', 'Parameter PAGU berhasil ditambahkan.');
    }

    public function update(Request $request, PaguParameter $pagu): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_category_id' => ['required', 'exists:recipient_categories,id'],
            'max_budget_per_portion' => ['required', 'integer', 'min:1000'],
            'effective_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $old = $pagu->only(['recipient_category_id', 'max_budget_per_portion', 'effective_date', 'notes']);
        $pagu->update([
            'recipient_category_id' => $validated['recipient_category_id'],
            'max_budget_per_portion' => $validated['max_budget_per_portion'],
            'effective_date' => $validated['effective_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->masterService->recordRevision(
            $pagu,
            $old,
            $pagu->only(['recipient_category_id', 'max_budget_per_portion', 'effective_date', 'notes']),
            $validated['reason'] ?? null,
            $request->user(),
        );

        return redirect()
            ->route('admin.data-master.pagu.index')
            ->with('success', 'Parameter PAGU berhasil diperbarui.');
    }

    public function deactivate(Request $request, PaguParameter $pagu): RedirectResponse
    {
        $this->masterService->setActive($pagu, false, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.pagu.index')
            ->with('success', 'Parameter PAGU dinonaktifkan.');
    }

    public function activate(Request $request, PaguParameter $pagu): RedirectResponse
    {
        $this->masterService->setActive($pagu, true, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.pagu.index')
            ->with('success', 'Parameter PAGU diaktifkan kembali.');
    }
}
