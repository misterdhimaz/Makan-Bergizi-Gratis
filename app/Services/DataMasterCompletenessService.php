<?php

namespace App\Services;

use App\Enums\SopChecklistType;
use App\Models\Ingredient;
use App\Models\Location;
use App\Models\PaguParameter;
use App\Models\PortionStandard;
use App\Models\RecipientCategory;
use App\Models\SopChecklist;
use App\Models\Supplier;

class DataMasterCompletenessService
{
    /**
     * Menghitung status kelengkapan 7 item Data Master secara eksplisit.
     *
     * @return array{
     *     siap_count: int,
     *     total: int,
     *     items: array<string, array{key: string, label: string, complete: bool, detail: string}>
     * }
     */
    public function evaluate(): array
    {
        $activeLocations = Location::with('categoryQuotas')->where('is_active', true)->get();
        $locationsComplete = $activeLocations->isNotEmpty() && $activeLocations->every(function (Location $loc): bool {
            return $loc->categoryQuotas->sum('portion_count') === (int) $loc->target_portions;
        });

        $activeCategories = RecipientCategory::where('is_active', true)->get();
        $categoriesComplete = $activeCategories->isNotEmpty();

        $portionComplete = $categoriesComplete && $activeCategories->every(function (RecipientCategory $cat): bool {
            return PortionStandard::where('recipient_category_id', $cat->id)
                ->where('is_active', true)
                ->exists();
        });

        $ingredientsCount = Ingredient::where('is_active', true)->count();
        $ingredientsComplete = $ingredientsCount > 0;

        $activeSuppliers = Supplier::where('is_active', true)->get();
        $suppliersWithoutContact = $activeSuppliers->filter(
            fn (Supplier $s): bool => empty(trim((string) $s->contact_person)) || empty(trim((string) $s->phone))
        )->count();
        $suppliersComplete = $activeSuppliers->isNotEmpty() && $suppliersWithoutContact === 0;

        $paguComplete = $categoriesComplete && $activeCategories->every(function (RecipientCategory $cat): bool {
            return PaguParameter::where('recipient_category_id', $cat->id)
                ->where('is_active', true)
                ->exists();
        });

        $suhuCount = SopChecklist::where('type', SopChecklistType::SUHU->value)->where('is_active', true)->count();
        $kebersihanCount = SopChecklist::where('type', SopChecklistType::KEBERSIHAN->value)->where('is_active', true)->count();
        $apdCount = SopChecklist::where('type', SopChecklistType::APD->value)->where('is_active', true)->count();
        $sopComplete = $suhuCount > 0 && $kebersihanCount > 0 && $apdCount > 0;

        $items = [
            'lokasi' => [
                'key' => 'lokasi',
                'label' => 'Lokasi Penerima',
                'complete' => $locationsComplete,
                'detail' => $locationsComplete
                    ? $activeLocations->count().' titik aktif (rincian kategori sesuai target porsi)'
                    : 'Rincian kategori belum sama dengan target porsi',
            ],
            'kategori' => [
                'key' => 'kategori',
                'label' => 'Kategori Penerima',
                'complete' => $categoriesComplete,
                'detail' => $activeCategories->count().' kategori aktif',
            ],
            'standar_porsi' => [
                'key' => 'standar_porsi',
                'label' => 'Standar Porsi',
                'complete' => $portionComplete,
                'detail' => $portionComplete ? 'Lengkap untuk semua kategori aktif' : 'Ada kategori tanpa standar porsi',
            ],
            'bahan' => [
                'key' => 'bahan',
                'label' => 'Daftar Bahan',
                'complete' => $ingredientsComplete,
                'detail' => $ingredientsCount.' bahan aktif',
            ],
            'pemasok' => [
                'key' => 'pemasok',
                'label' => 'Pemasok',
                'complete' => $suppliersComplete,
                'detail' => $suppliersComplete
                    ? $activeSuppliers->count().' pemasok dengan kontak lengkap'
                    : $suppliersWithoutContact.' pemasok belum memiliki kontak lengkap',
            ],
            'pagu' => [
                'key' => 'pagu',
                'label' => 'Parameter PAGU',
                'complete' => $paguComplete,
                'detail' => $paguComplete ? 'Lengkap untuk semua kategori aktif' : 'Ada kategori tanpa batas PAGU',
            ],
            'checklist_sop' => [
                'key' => 'checklist_sop',
                'label' => 'Checklist SOP BGN',
                'complete' => $sopComplete,
                'detail' => $sopComplete
                    ? "Suhu ({$suhuCount}), Kebersihan ({$kebersihanCount}), APD ({$apdCount})"
                    : "Perlu dilengkapi: Suhu ({$suhuCount}), Kebersihan ({$kebersihanCount}), APD ({$apdCount})",
            ],
        ];

        $readyCount = collect($items)->where('complete', true)->count();

        return [
            'siap_count' => $readyCount,
            'total' => count($items),
            'items' => $items,
        ];
    }
}
