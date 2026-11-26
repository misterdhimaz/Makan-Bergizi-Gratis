<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationCategoryQuota;
use App\Models\RecipientCategory;
use App\Services\DataMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LocationMasterController extends Controller
{
    public function __construct(
        private readonly DataMasterService $masterService,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $query = Location::query()
            ->with(['categoryQuotas.recipientCategory', 'activeGuruPic'])
            ->orderBy('code');

        if ($q !== '') {
            $query->where(function ($builder) use ($q): void {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('type', 'like', "%{$q}%");
            });
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        $locations = $query->paginate(10)->withQueryString();
        $categories = RecipientCategory::query()->where('is_active', true)->orderBy('code')->get();

        return view('admin.data-master.lokasi', [
            'locations' => $locations,
            'categories' => $categories,
            'filters' => ['q' => $q, 'status' => $status],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateLocation($request);

        DB::transaction(function () use ($validated): void {
            $location = Location::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'type' => $validated['type'],
                'address' => $validated['address'] ?? null,
                'target_portions' => $validated['target_portions'],
                'is_active' => true,
            ]);

            $this->syncQuotas($location, $validated['quotas'] ?? []);
        });

        return redirect()
            ->route('admin.data-master.lokasi.index')
            ->with('success', "Lokasi {$validated['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $validated = $this->validateLocation($request, $location);
        $old = $location->only(['code', 'name', 'type', 'address', 'target_portions']);

        DB::transaction(function () use ($location, $validated, $old, $request): void {
            $location->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'type' => $validated['type'],
                'address' => $validated['address'] ?? null,
                'target_portions' => $validated['target_portions'],
            ]);

            $this->syncQuotas($location, $validated['quotas'] ?? []);

            $this->masterService->recordRevision(
                $location,
                $old,
                $location->only(['code', 'name', 'type', 'address', 'target_portions']),
                $validated['reason'] ?? null,
                $request->user(),
            );
        });

        return redirect()
            ->route('admin.data-master.lokasi.index')
            ->with('success', "Lokasi {$location->name} berhasil diperbarui.");
    }

    public function deactivate(Request $request, Location $location): RedirectResponse
    {
        $this->masterService->setActive($location, false, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.lokasi.index')
            ->with('success', "Lokasi {$location->name} dinonaktifkan.");
    }

    public function activate(Request $request, Location $location): RedirectResponse
    {
        $this->masterService->setActive($location, true, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.lokasi.index')
            ->with('success', "Lokasi {$location->name} diaktifkan kembali.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLocation(Request $request, ?Location $location = null): array
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('locations', 'code')->ignore($location?->id)],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:500'],
            'target_portions' => ['required', 'integer', 'min:1'],
            'quotas' => ['nullable', 'array'],
            'quotas.*' => ['nullable', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $quotas = collect($validated['quotas'] ?? [])
            ->map(fn ($v): int => (int) ($v ?? 0))
            ->filter(fn (int $v): bool => $v >= 0);

        if ($quotas->isNotEmpty() && $quotas->sum() !== (int) $validated['target_portions']) {
            throw ValidationException::withMessages([
                'quotas' => 'Jumlah rincian porsi kategori harus sama dengan target porsi lokasi.',
            ]);
        }

        return $validated;
    }

    /**
     * @param  array<int|string, int|string|null>  $quotas
     */
    private function syncQuotas(Location $location, array $quotas): void
    {
        foreach ($quotas as $categoryId => $portionCount) {
            $count = (int) ($portionCount ?? 0);
            if ($count <= 0) {
                LocationCategoryQuota::where('location_id', $location->id)
                    ->where('recipient_category_id', (int) $categoryId)
                    ->delete();

                continue;
            }

            LocationCategoryQuota::updateOrCreate(
                [
                    'location_id' => $location->id,
                    'recipient_category_id' => (int) $categoryId,
                ],
                ['portion_count' => $count],
            );
        }
    }
}
