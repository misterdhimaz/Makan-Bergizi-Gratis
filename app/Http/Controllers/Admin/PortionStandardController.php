<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IngredientCategory;
use App\Http\Controllers\Controller;
use App\Models\PortionStandard;
use App\Models\RecipientCategory;
use App\Services\DataMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortionStandardController extends Controller
{
    public function __construct(
        private readonly DataMasterService $masterService,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');
        $categoryId = $request->query('category_id');

        $query = PortionStandard::query()->with('recipientCategory')->orderBy('recipient_category_id')->orderBy('meal_component');

        if ($q !== '') {
            $query->where(function ($builder) use ($q): void {
                $builder->where('meal_component', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%");
            });
        }

        if ($categoryId !== null && $categoryId !== '' && $categoryId !== 'all') {
            $query->where('recipient_category_id', (int) $categoryId);
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        return view('admin.data-master.standar-porsi', [
            'standards' => $query->paginate(10)->withQueryString(),
            'categories' => RecipientCategory::query()->where('is_active', true)->orderBy('code')->get(),
            'components' => IngredientCategory::cases(),
            'filters' => ['q' => $q, 'status' => $status, 'category_id' => $categoryId ?? 'all'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_category_id' => ['required', 'exists:recipient_categories,id'],
            'meal_component' => ['required', Rule::enum(IngredientCategory::class)],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', Rule::in(['g', 'ml'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        PortionStandard::create([
            ...$validated,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.data-master.standar-porsi.index')
            ->with('success', 'Standar porsi berhasil ditambahkan.');
    }

    public function update(Request $request, PortionStandard $standard): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_category_id' => ['required', 'exists:recipient_categories,id'],
            'meal_component' => ['required', Rule::enum(IngredientCategory::class)],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', Rule::in(['g', 'ml'])],
            'notes' => ['nullable', 'string', 'max:500'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $old = $standard->only(['recipient_category_id', 'meal_component', 'quantity', 'unit', 'notes']);
        $standard->update([
            'recipient_category_id' => $validated['recipient_category_id'],
            'meal_component' => $validated['meal_component'],
            'quantity' => $validated['quantity'],
            'unit' => $validated['unit'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->masterService->recordRevision(
            $standard,
            $old,
            $standard->only(['recipient_category_id', 'meal_component', 'quantity', 'unit', 'notes']),
            $validated['reason'] ?? null,
            $request->user(),
        );

        return redirect()
            ->route('admin.data-master.standar-porsi.index')
            ->with('success', 'Standar porsi berhasil diperbarui.');
    }

    public function deactivate(Request $request, PortionStandard $standard): RedirectResponse
    {
        $this->masterService->setActive($standard, false, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.standar-porsi.index')
            ->with('success', 'Standar porsi dinonaktifkan.');
    }

    public function activate(Request $request, PortionStandard $standard): RedirectResponse
    {
        $this->masterService->setActive($standard, true, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.standar-porsi.index')
            ->with('success', 'Standar porsi diaktifkan kembali.');
    }
}
