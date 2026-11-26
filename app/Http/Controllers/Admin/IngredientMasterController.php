<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IngredientCategory;
use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Supplier;
use App\Services\DataMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IngredientMasterController extends Controller
{
    public function __construct(
        private readonly DataMasterService $masterService,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');
        $category = (string) $request->query('category', 'all');

        $query = Ingredient::query()->with('supplier')->orderBy('name');

        if ($q !== '') {
            $query->where('name', 'like', "%{$q}%");
        }

        if ($category !== 'all' && $category !== '') {
            $query->where('category', $category);
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        return view('admin.data-master.bahan', [
            'ingredients' => $query->paginate(10)->withQueryString(),
            'categories' => IngredientCategory::cases(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters' => ['q' => $q, 'status' => $status, 'category' => $category],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::enum(IngredientCategory::class)],
            'unit' => ['required', 'string', 'max:30'],
            'current_stock' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
        ]);

        $ingredient = Ingredient::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'unit' => $validated['unit'],
            'current_stock' => $validated['current_stock'] ?? 0,
            'unit_cost' => $validated['unit_cost'] ?? 0,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.data-master.bahan.index')
            ->with('success', "Bahan {$ingredient->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::enum(IngredientCategory::class)],
            'unit' => ['required', 'string', 'max:30'],
            'current_stock' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $old = $ingredient->only(['name', 'category', 'unit', 'current_stock', 'unit_cost', 'supplier_id']);
        $ingredient->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'unit' => $validated['unit'],
            'current_stock' => $validated['current_stock'] ?? 0,
            'unit_cost' => $validated['unit_cost'] ?? 0,
            'supplier_id' => $validated['supplier_id'] ?? null,
        ]);

        $this->masterService->recordRevision(
            $ingredient,
            $old,
            $ingredient->only(['name', 'category', 'unit', 'current_stock', 'unit_cost', 'supplier_id']),
            $validated['reason'] ?? null,
            $request->user(),
        );

        return redirect()
            ->route('admin.data-master.bahan.index')
            ->with('success', "Bahan {$ingredient->name} berhasil diperbarui.");
    }

    public function deactivate(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $this->masterService->setActive($ingredient, false, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.bahan.index')
            ->with('success', "Bahan {$ingredient->name} dinonaktifkan.");
    }

    public function activate(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $this->masterService->setActive($ingredient, true, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.bahan.index')
            ->with('success', "Bahan {$ingredient->name} diaktifkan kembali.");
    }
}
