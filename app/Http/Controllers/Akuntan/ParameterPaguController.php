<?php

namespace App\Http\Controllers\Akuntan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Akuntan\UpdatePaguParameterRequest;
use App\Models\PaguParameter;
use App\Models\PaguParameterHistory;
use App\Models\RecipientCategory;
use App\Services\PaguParameterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ParameterPaguController extends Controller
{
    public function index(): View
    {
        $categories = RecipientCategory::query()
            ->where('is_active', true)
            ->with([
                'paguParameters' => fn ($q) => $q
                    ->where('is_active', true)
                    ->with('updatedBy')
                    ->orderByDesc('effective_date')
                    ->orderByDesc('id'),
            ])
            ->orderBy('id')
            ->get();

        $histories = PaguParameterHistory::query()
            ->with(['recipientCategory', 'user'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        return view('akuntan.parameter-pagu', [
            'categories' => $categories,
            'histories' => $histories,
        ]);
    }

    public function update(
        UpdatePaguParameterRequest $request,
        RecipientCategory $kategori,
        PaguParameterService $service,
    ): RedirectResponse {
        $current = PaguParameter::query()
            ->where('recipient_category_id', $kategori->id)
            ->where('is_active', true)
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->first();

        if ($current !== null) {
            Gate::authorize('update', $current);
        }

        $service->updateNominal(
            category: $kategori,
            nominal: (int) $request->validated('nominal_per_porsi'),
            actor: $request->user(),
            catatan: $request->validated('catatan'),
        );

        return redirect()
            ->route('akuntan.parameter-pagu.index')
            ->with('success', 'Parameter PAGU untuk '.$kategori->name.' berhasil diperbarui.');
    }
}
