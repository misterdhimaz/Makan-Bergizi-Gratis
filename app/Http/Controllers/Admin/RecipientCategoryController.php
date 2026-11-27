<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecipientCategory;
use App\Services\DataMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecipientCategoryController extends Controller
{
    public function __construct(
        private readonly DataMasterService $masterService,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $query = RecipientCategory::query()->orderBy('code');

        if ($q !== '') {
            $query->where(function ($builder) use ($q): void {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        return view('admin.data-master.kategori', [
            'categories' => $query->paginate(10)->withQueryString(),
            'filters' => ['q' => $q, 'status' => $status],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:recipient_categories,code'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category = RecipientCategory::create([
            ...$validated,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.data-master.kategori.index')
            ->with('success', "Kategori {$category->name} berhasil ditambahkan.");
    }

    public function update(Request $request, RecipientCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('recipient_categories', 'code')->ignore($category->id)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $old = $category->only(['code', 'name', 'description']);
        $category->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $this->masterService->recordRevision(
            $category,
            $old,
            $category->only(['code', 'name', 'description']),
            $validated['reason'] ?? null,
            $request->user(),
        );

        return redirect()
            ->route('admin.data-master.kategori.index')
            ->with('success', "Kategori {$category->name} berhasil diperbarui.");
    }

    public function deactivate(Request $request, RecipientCategory $category): RedirectResponse
    {
        $this->masterService->setActive($category, false, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.kategori.index')
            ->with('success', "Kategori {$category->name} dinonaktifkan.");
    }

    public function activate(Request $request, RecipientCategory $category): RedirectResponse
    {
        $this->masterService->setActive($category, true, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.kategori.index')
            ->with('success', "Kategori {$category->name} diaktifkan kembali.");
    }
}
