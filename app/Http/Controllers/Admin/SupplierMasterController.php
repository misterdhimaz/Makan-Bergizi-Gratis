<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\DataMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierMasterController extends Controller
{
    public function __construct(
        private readonly DataMasterService $masterService,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $query = Supplier::query()->orderBy('code');

        if ($q !== '') {
            $query->where(function ($builder) use ($q): void {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('contact_person', 'like', "%{$q}%")
                    ->orWhere('category_focus', 'like', "%{$q}%");
            });
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        return view('admin.data-master.pemasok', [
            'suppliers' => $query->paginate(10)->withQueryString(),
            'filters' => ['q' => $q, 'status' => $status],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:suppliers,code'],
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'category_focus' => ['nullable', 'string', 'max:100'],
        ]);

        $supplier = Supplier::create([
            ...$validated,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.data-master.pemasok.index')
            ->with('success', "Pemasok {$supplier->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('suppliers', 'code')->ignore($supplier->id)],
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'category_focus' => ['nullable', 'string', 'max:100'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $old = $supplier->only(['code', 'name', 'contact_person', 'phone', 'address', 'category_focus']);
        $supplier->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'category_focus' => $validated['category_focus'] ?? null,
        ]);

        $this->masterService->recordRevision(
            $supplier,
            $old,
            $supplier->only(['code', 'name', 'contact_person', 'phone', 'address', 'category_focus']),
            $validated['reason'] ?? null,
            $request->user(),
        );

        return redirect()
            ->route('admin.data-master.pemasok.index')
            ->with('success', "Pemasok {$supplier->name} berhasil diperbarui.");
    }

    public function deactivate(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->masterService->setActive($supplier, false, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.pemasok.index')
            ->with('success', "Pemasok {$supplier->name} dinonaktifkan.");
    }

    public function activate(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->masterService->setActive($supplier, true, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.pemasok.index')
            ->with('success', "Pemasok {$supplier->name} diaktifkan kembali.");
    }
}
