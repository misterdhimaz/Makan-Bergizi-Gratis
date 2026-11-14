@extends('layouts.app')

@section('title', 'Produksi & bahan baku')
@section('page_title', 'Produksi & bahan baku')
@section('page_subtitle', 'Persediaan bahan baku dan porsi produksi real-time')

@section('content')
@php
    $criticalIngredientCount = $ingredients->filter(fn ($ingredient) => $ingredient->current_stock <= $ingredient->min_stock_threshold)->count();
    $goodPortions = (int) $productionRuns->sum('actual_good_portions');
    $damagedPortions = (int) $productionRuns->sum('rejected_damaged_portions');
    $netVar = (int) ($goodPortions - $productionRuns->sum('planned_portions'));
    $txTotal = array_sum($txSummary);
    $txNet = $txSummary['inbound'] - $txSummary['outbound'] - $txSummary['spoilage'];
    $txMax = max(1, ...array_values($txSummary));
    $chartIngredients = $ingredients->sortBy(static function ($ingredient) {
        $stock = (float) $ingredient->current_stock;
        $threshold = (float) $ingredient->min_stock_threshold;
        return $threshold > 0 ? $stock / $threshold : ($stock > 0 ? PHP_FLOAT_MAX : 0);
    })->take(8)->values();
    $chartIngNames = $chartIngredients->pluck('name')->values();
    $chartIngUnits = $chartIngredients->pluck('unit')->values();
    $chartCurrentStocks = $chartIngredients->pluck('current_stock')->map(static fn ($value) => (float) $value)->values();
    $chartMinThresholds = $chartIngredients->pluck('min_stock_threshold')->map(static fn ($value) => (float) $value)->values();
    $chartStockPercentages = $chartIngredients->map(static function ($ingredient) {
        $stock = (float) $ingredient->current_stock;
        $threshold = (float) $ingredient->min_stock_threshold;
        return $threshold > 0 ? ($stock / $threshold) * 100 : ($stock > 0 ? 150 : 0);
    })->values();
    $chartStockColors = $chartIngredients->map(static function ($ingredient) {
        $stock = (float) $ingredient->current_stock;
        $threshold = (float) $ingredient->min_stock_threshold;
        $ratio = $threshold > 0 ? $stock / $threshold : ($stock > 0 ? 1.5 : 0);
        return $ratio < 1 ? '#B91C1C' : ($ratio <= 1.2 ? '#B45309' : '#15803D');
    })->values();
@endphp
<div class="production-inventory-page" x-data="{
    openIngModal: false,
    openEditIngModal: false,
    openTxModal: false,
    openRunModal: false,
    editIng: { id: '', name: '', category: 'karbohidrat', unit: 'kg', current_stock: 0, min_stock_threshold: 10, unit_cost: 12000 },
    openEditIngredient(ing) {
        this.editIng = {
            id: ing.id,
            name: ing.name,
            category: ing.category,
            unit: ing.unit,
            current_stock: ing.current_stock,
            min_stock_threshold: ing.min_stock_threshold,
            unit_cost: ing.unit_cost
        };
        this.openEditIngModal = true;
    }
}">
    <style>
        .production-inventory-page .production-summary-card {
            min-width: 0;
            border-radius: 1rem;
        }

        .production-inventory-page .production-summary-label {
            color: #475569;
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.25rem;
        }

        .production-inventory-page .production-summary-value {
            margin-top: 0.375rem;
            color: #061F47;
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 2.5rem;
            letter-spacing: -0.04em;
            font-variant-numeric: tabular-nums;
        }

        .production-inventory-page .production-summary-caption {
            margin-top: 0.375rem;
            overflow: hidden;
            color: #475569;
            font-size: 0.8125rem;
            line-height: 1.25rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .production-inventory-page .production-action-button {
            box-sizing: border-box;
            min-height: 40px;
            white-space: nowrap;
        }

        .production-inventory-page input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        .production-inventory-page select,
        .production-inventory-page textarea {
            min-height: 44px;
            font-size: 16px;
        }

        .production-inventory-page button {
            min-height: 44px;
        }

        .production-inventory-modal input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        .production-inventory-modal select,
        .production-inventory-modal textarea {
            min-height: 44px;
            font-size: 16px;
        }

        .production-inventory-modal button {
            min-height: 44px;
        }
    </style>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <article class="surface-card production-summary-card p-5">
            <div class="flex items-center justify-between gap-2">
                <h2 class="production-summary-label">Item bahan baku</h2>
                <span class="status-badge {{ $criticalIngredientCount > 0 ? 'status-badge--danger' : 'status-badge--success' }}" title="{{ $criticalIngredientCount > 0 ? $criticalIngredientCount . ' bahan perlu perhatian' : 'Tidak ada bahan kritis' }}">
                    {{ $criticalIngredientCount > 0 ? number_format($criticalIngredientCount, 0, ',', '.') . ' kritis' : 'Aman' }}
                </span>
            </div>
            <p class="production-summary-value">{{ number_format($ingredients->count(), 0, ',', '.') }}</p>
            <p class="production-summary-caption" title="Terdaftar di gudang SPPG">Terdaftar di gudang SPPG</p>
        </article>

        <article class="surface-card production-summary-card p-5">
            <div class="flex items-center justify-between gap-2">
                <h2 class="production-summary-label">Sesi produksi dapur</h2>
                <span class="status-badge {{ $goodPortions > 0 ? 'status-badge--success' : 'status-badge--neutral' }}" title="{{ number_format($goodPortions, 0, ',', '.') }} porsi layak">
                    {{ $goodPortions > 0 ? 'Tersedia' : 'Belum ada' }}
                </span>
            </div>
            <p class="production-summary-value">{{ number_format($productionRuns->count(), 0, ',', '.') }}</p>
            <p class="production-summary-caption" title="{{ number_format($goodPortions, 0, ',', '.') }} porsi layak">Porsi layak: {{ number_format($goodPortions, 0, ',', '.') }}</p>
        </article>

        <article class="surface-card production-summary-card p-5">
            <div class="flex items-center justify-between gap-2">
                <h2 class="production-summary-label">Akumulasi selisih porsi</h2>
                <span class="status-badge {{ $netVar === 0 ? 'status-badge--success' : 'status-badge--warning' }}">
                    {{ $netVar === 0 ? 'Sesuai target' : 'Perlu perhatian' }}
                </span>
            </div>
            <p class="production-summary-value {{ $netVar !== 0 ? '!text-amber-700' : '!text-green-700' }}">{{ $netVar > 0 ? '+' : '' }}{{ number_format($netVar, 0, ',', '.') }}</p>
            <p class="production-summary-caption" title="Realisasi dibandingkan target porsi">Realisasi dibandingkan target</p>
        </article>

        <article class="surface-card production-summary-card p-5">
            <div class="flex items-center justify-between gap-2">
                <h2 class="production-summary-label">Porsi rusak</h2>
                <span class="status-badge {{ $damagedPortions > 0 ? 'status-badge--warning' : 'status-badge--success' }}" title="{{ $damagedPortions > 0 ? number_format($damagedPortions, 0, ',', '.') . ' porsi rusak' : 'Tidak ada porsi rusak' }}">
                    {{ $damagedPortions > 0 ? 'Perlu perhatian' : 'Tidak ada' }}
                </span>
            </div>
            <p class="production-summary-value {{ $damagedPortions > 0 ? '!text-amber-700' : '!text-green-700' }}">{{ number_format($damagedPortions, 0, ',', '.') }} <span class="text-sm font-semibold text-slate-500">porsi</span></p>
            <p class="production-summary-caption" title="Total porsi rusak atau cacat">Total porsi rusak atau cacat</p>
        </article>
    </div>

    <section class="surface-card rounded-2xl p-5 sm:p-6 mb-6" aria-labelledby="production-quick-actions-title">
        <div class="mb-4">
            <h2 id="production-quick-actions-title" class="text-base font-bold text-slate-900">Aksi cepat</h2>
            <p class="mt-0.5 text-sm text-slate-600">Gudang & dapur</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <button type="button" @click="openRunModal = true" class="production-action-button inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-navy px-4 text-sm font-bold text-white transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                Buka sesi produksi baru
            </button>
            <button type="button" @click="openIngModal = true" class="production-action-button inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-brand-navy transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                Bahan baku
            </button>
            <button type="button" @click="openTxModal = true" class="production-action-button inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-brand-navy transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 3l4 4-4 4M3 7h18M7 21l-4-4 4-4m14 4H3"/></svg>
                Mutasi stok
            </button>
        </div>
    </section>

    <!-- 2 Grafik Gudang & Finansial -->
    <div class="grid grid-cols-1 xl:grid-cols-12 items-stretch gap-4 mb-6">
        <section class="surface-card rounded-2xl p-5 sm:p-6 xl:col-span-7" aria-labelledby="stock-chart-title">
            <div class="mb-4">
                <h2 id="stock-chart-title" class="text-base font-bold text-slate-900">Stok aktual vs ambang minimum</h2>
                <p class="mt-0.5 text-sm text-slate-600">Safety stock per bahan baku · stok dibandingkan dalam persentase ambang</p>
            </div>
            <div class="mb-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-700" aria-label="Legenda grafik stok">
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-green-700"></span>Aman</span>
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-amber-700"></span>Mendekati ambang</span>
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-red-700"></span>Di bawah ambang</span>
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full border-2 border-slate-500"></span>Ambang minimum (100%)</span>
            </div>
            @if($chartIngredients->isNotEmpty())
                <div style="height: {{ max(180, $chartIngredients->count() * 40) }}px">
                    <canvas id="stockComparisonChart" role="img" aria-label="Grafik stok aktual dibandingkan ambang minimum bahan baku"></canvas>
                </div>
                @if($ingredients->count() > 8)
                    <a href="#ingredient-inventory" class="mt-3 inline-flex text-sm font-semibold text-blue-800 underline underline-offset-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Lihat semua bahan baku</a>
                @endif
            @else
                <p class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-600">Belum ada data bahan baku untuk ditampilkan.</p>
            @endif
        </section>

        <section class="surface-card rounded-2xl p-5 sm:p-6 xl:col-span-5" aria-labelledby="transaction-valuation-title">
            <div class="mb-5">
                <h2 id="transaction-valuation-title" class="text-base font-bold text-slate-900">Belanja masuk vs pemakaian</h2>
                <p class="mt-0.5 text-sm text-slate-600">Valuasi mutasi (Rp)</p>
            </div>
            <div class="mb-5 rounded-xl bg-slate-50 p-4">
                <span class="block text-xs font-medium text-slate-600">Nilai bersih</span>
                <span class="mt-1 block text-2xl font-extrabold tabular-nums text-brand-navy">Rp&nbsp;{{ number_format($txNet, 0, ',', '.') }}</span>
                <span class="mt-1 block text-xs text-slate-500" title="Total valuasi mutasi: Rp {{ number_format($txTotal, 0, ',', '.') }}">Total valuasi: Rp&nbsp;{{ number_format($txTotal, 0, ',', '.') }}</span>
            </div>
            <div class="space-y-5">
                @foreach([
                    ['label' => 'Penerimaan masuk', 'value' => $txSummary['inbound'], 'color' => '#15803D'],
                    ['label' => 'Pemakaian masak', 'value' => $txSummary['outbound'], 'color' => '#061F47'],
                    ['label' => 'Spoilage/susut', 'value' => $txSummary['spoilage'], 'color' => '#B91C1C'],
                ] as $valuation)
                    <div class="{{ $valuation['value'] == 0 ? 'opacity-60' : '' }}">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="flex min-w-0 items-center gap-2 text-sm font-medium text-slate-700">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $valuation['color'] }}"></span>
                                <span class="truncate">{{ $valuation['label'] }}</span>
                            </span>
                            <span class="shrink-0 text-sm font-bold tabular-nums text-slate-900">Rp&nbsp;{{ number_format($valuation['value'], 0, ',', '.') }}</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100" role="img" aria-label="{{ $valuation['label'] }} {{ number_format($valuation['value'] / $txMax * 100, 1, ',', '.') }} persen dari nilai mutasi terbesar">
                            <div class="h-full rounded-full" style="width: {{ min(100, $valuation['value'] / $txMax * 100) }}%; background-color: {{ $valuation['color'] }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <!-- Filter & Search Bar Lengkap -->
    <div class="surface-card rounded-3xl p-5 mb-6">
        <form method="GET" action="{{ route('production-inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-4">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Bahan Baku / SKU / No. Referensi</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari bahan, SKU, pemasok"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold focus:bg-white focus:border-emerald-500 focus:outline-none">
            </div>

            <div class="lg:col-span-3">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori Bahan</label>
                <select name="category" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700">
                    <option value="all" {{ $category === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                    <option value="karbohidrat" {{ $category === 'karbohidrat' ? 'selected' : '' }}>Karbohidrat</option>
                    <option value="protein_hewani" {{ $category === 'protein_hewani' ? 'selected' : '' }}>Protein Hewani</option>
                    <option value="protein_nabati" {{ $category === 'protein_nabati' ? 'selected' : '' }}>Protein Nabati</option>
                    <option value="sayuran" {{ $category === 'sayuran' ? 'selected' : '' }}>Sayuran Segar</option>
                    <option value="buah" {{ $category === 'buah' ? 'selected' : '' }}>Buah-Buahan</option>
                    <option value="susu" {{ $category === 'susu' ? 'selected' : '' }}>Susu & Olahan</option>
                    <option value="bumbu_minyak" {{ $category === 'bumbu_minyak' ? 'selected' : '' }}>Bumbu & Minyak</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Stok</label>
                <select name="stock_status" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700">
                    <option value="all" {{ $stockStatus === 'all' ? 'selected' : '' }}>Semua Stok</option>
                    <option value="low" {{ $stockStatus === 'low' ? 'selected' : '' }}>Stok Kritis / Menipis</option>
                    <option value="safe" {{ $stockStatus === 'safe' ? 'selected' : '' }}>Stok Aman</option>
                </select>
            </div>

            <div class="lg:col-span-3 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm flex items-center justify-center gap-1.5 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Terapkan Filter
                </button>
                @if($search !== '' || $category !== 'all' || $stockStatus !== 'all' || $txType !== 'all')
                    <a href="{{ route('production-inventory.index') }}" class="py-2.5 px-3 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 font-bold text-xs">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- PANEL 1: REALISASI PRODUKSI & SELISIH PORSI REAL-TIME -->
    <div class="surface-card rounded-3xl p-6 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 mb-5 border-b border-slate-100">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600">Dapur Produksi SPPG Sungai Selayur 1</span>
                <h3 class="font-display font-extrabold text-lg text-slate-900">Monitoring Realisasi Porsi & Selisih Produksi Real-Time</h3>
            </div>
            <button @click="openRunModal = true" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition cursor-pointer">
                + Buka Sesi Produksi Baru
            </button>
        </div>

        @if($productionRuns->isEmpty())
            <div class="text-center py-8 px-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50/50">
                <p class="font-bold text-sm text-slate-700">Belum Ada Sesi Produksi Dapur</p>
                <p class="text-xs text-slate-500 mt-1">Buka sesi produksi dari jadwal menu yang telah disetujui atau klik tombol "+ Buka Sesi Produksi Baru".</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($productionRuns as $run)
                    <form method="POST" action="{{ route('production-inventory.update', $run) }}"
                          class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200/80 space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-center">
                            <div class="lg:col-span-3">
                                <span class="font-mono font-extrabold text-[11px] px-2.5 py-1 rounded-xl bg-teal-50 text-teal-900 border border-teal-300">{{ $run->dailyMenu?->menu_code }}</span>
                                <p class="font-bold text-slate-900 text-sm mt-1">{{ $run->dailyMenu?->menu_name }}</p>
                                <p class="text-[11px] text-slate-500">Rencana: <strong>{{ number_format($run->planned_portions) }} Porsi</strong> &bull; <strong class="text-emerald-700">{{ $run->volunteers_count ?? 35 }} Relawan Dapur</strong></p>
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Porsi Layak Saji</label>
                                <input type="number" name="actual_good_portions" value="{{ $run->actual_good_portions }}" min="0"
                                       class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-emerald-700">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Porsi Rusak/Cacat</label>
                                <input type="number" name="rejected_damaged_portions" value="{{ $run->rejected_damaged_portions }}" min="0"
                                       class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-rose-600">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Relawan Hadir</label>
                                <input type="number" name="volunteers_count" value="{{ $run->volunteers_count ?? 35 }}" min="1" max="100"
                                       class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-800">
                            </div>

                            <div class="lg:col-span-3 flex items-center justify-between lg:justify-end gap-3">
                                <div class="text-right">
                                    <span class="text-[10px] font-bold uppercase text-slate-400 block">Selisih Porsi</span>
                                    <span class="font-display font-extrabold text-sm {{ $run->portion_variance < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $run->portion_variance >= 0 ? '+' . $run->portion_variance : $run->portion_variance }} Porsi
                                    </span>
                                </div>
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs cursor-pointer">
                                    Simpan Realisasi
                                </button>
                            </div>
                        </div>

                        <!-- Kendala Operasional & Tindak Lanjut Lapangan -->
                        <div class="pt-2 border-t border-slate-200/60 grid grid-cols-1 md:grid-cols-3 gap-3 items-center text-xs">
                            <div class="md:col-span-1">
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Status Tahap</label>
                                <select name="status" class="w-full px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-bold">
                                    <option value="scheduled" {{ $run->status === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                                    <option value="cooking" {{ $run->status === 'cooking' ? 'selected' : '' }}>Memasak ({{ $standardVolunteers }} Relawan)</option>
                                    <option value="qc_inspection" {{ $run->status === 'qc_inspection' ? 'selected' : '' }}>Inspeksi QC Suhu</option>
                                    <option value="packaging" {{ $run->status === 'packaging' ? 'selected' : '' }}>Pemorsian Ompreng</option>
                                    <option value="ready_for_dispatch" {{ $run->status === 'ready_for_dispatch' ? 'selected' : '' }}>Siap Kirim Armada</option>
                                </select>
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Catatan Kendala Operasional</label>
                                <input type="text" name="operational_obstacle" value="{{ $run->operational_obstacle }}" placeholder="Contoh: Tekanan api kompor normal, nihil kendala..."
                                       class="w-full px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs">
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Tindakan Penyelesaian</label>
                                <input type="text" name="action_taken" value="{{ $run->action_taken }}" placeholder="Contoh: Ompreng diperiksa segel sebelum muat armada..."
                                       class="w-full px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs">
                            </div>
                        </div>
                    </form>
                @endforeach
            </div>
        @endif
    </div>

    <!-- PANEL 2: MASTER BAHAN BAKU & LOG MUTASI -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Master Bahan Baku -->
        <div id="ingredient-inventory" class="lg:col-span-7 surface-card rounded-3xl p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-display font-extrabold text-base text-slate-900">Master Persediaan Bahan Baku Gudang</h3>
                    <p class="text-xs text-slate-500">Status ketersediaan stok bahan baku real-time</p>
                </div>
                <button @click="openIngModal = true" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs cursor-pointer">
                    + Bahan Baru
                </button>
            </div>

            @if($ingredients->isEmpty())
                <div class="text-center py-10 text-xs text-slate-500">
                    Belum ada bahan baku di database. Klik <strong>+ Bahan Baru</strong> untuk menambahkan.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-[10px] font-extrabold uppercase text-slate-400">
                                <th class="py-3 px-3">SKU & Nama Bahan</th>
                                <th class="py-3 px-3">Stok Aktual</th>
                                <th class="py-3 px-3">Min. Stok</th>
                                <th class="py-3 px-3">Harga/Unit</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($ingredients as $ing)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="py-3 px-3">
                                        <span class="font-mono text-[10px] font-bold text-slate-500">{{ $ing->sku }}</span>
                                        <p class="font-bold text-slate-900">{{ $ing->name }}</p>
                                        <span class="text-[10px] text-slate-400 uppercase">{{ str_replace('_', ' ', $ing->category) }}</span>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="font-display font-extrabold text-sm {{ $ing->current_stock <= $ing->min_stock_threshold ? 'text-rose-600' : 'text-emerald-700' }}">
                                            {{ $ing->current_stock }} {{ $ing->unit }}
                                        </span>
                                        <span class="mt-1 inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $ing->current_stock <= $ing->min_stock_threshold ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                                            <span aria-hidden="true">{{ $ing->current_stock <= $ing->min_stock_threshold ? '!' : '✓' }}</span>
                                            {{ $ing->current_stock <= $ing->min_stock_threshold ? 'Stok kritis' : 'Stok aman' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-slate-500">{{ $ing->min_stock_threshold }} {{ $ing->unit }}</td>
                                    <td class="py-3 px-3 font-semibold">Rp {{ number_format($ing->unit_cost, 0, ',', '.') }}</td>
                                    <td class="py-3 px-3 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button" @click="openEditIngredient({{ Js::from($ing) }})"
                                                    class="min-w-touch inline-flex items-center justify-center px-2 py-1 rounded-lg bg-slate-100 hover:bg-emerald-50 text-slate-700 font-bold text-[11px] cursor-pointer">
                                                Edit
                                            </button>
                                            @if(auth()->user()->role->value === 'admin')
                                                <form method="POST" action="{{ route('production-inventory.destroy-ingredient', $ing) }}" onsubmit="return confirm('Hapus bahan {{ $ing->name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="min-w-touch inline-flex items-center justify-center px-2 py-1 rounded-lg bg-rose-50 text-rose-600 font-bold text-[11px] cursor-pointer">✕</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Log Transaksi Gudang -->
        <div class="lg:col-span-5 surface-card rounded-3xl p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-display font-extrabold text-base text-slate-900">Log Mutasi Bahan Baku</h3>
                    <p class="text-xs text-slate-500">Riwayat penerimaan & pemakaian dapur</p>
                </div>
                <button @click="openTxModal = true" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs cursor-pointer">
                    + Catat Mutasi
                </button>
            </div>

            @if($transactions->isEmpty())
                <div class="text-center py-10 text-xs text-slate-500">
                    Belum ada log transaksi mutasi bahan baku.
                </div>
            @else
                <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
                    @foreach($transactions as $tx)
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-[10px] font-bold text-slate-500">{{ $tx->reference_no }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tx->type === 'inbound_purchase' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $tx->type === 'inbound_purchase' ? 'MASUK' : ($tx->type === 'outbound_production' ? 'PAKAI' : 'SPOILAGE') }}
                                    </span>
                                </div>
                                <p class="font-bold text-xs text-slate-900 mt-1">{{ $tx->ingredient?->name }} ({{ $tx->quantity }} {{ $tx->ingredient?->unit }})</p>
                                <p class="text-[11px] text-slate-500">{{ $tx->supplier_name }} • Rp {{ number_format($tx->total_cost, 0, ',', '.') }}</p>
                            </div>
                            <span class="text-[10px] font-semibold text-slate-400">{{ $tx->created_at->format('d M H:i') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL TAMBAH MASTER BAHAN BAKU -->
    <template x-teleport="body">
        <div x-show="openIngModal" x-cloak class="production-inventory-modal fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 overflow-y-auto">
            <div @click.away="openIngModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl my-auto max-h-[90vh] overflow-y-auto">
                <h3 class="font-display font-extrabold text-lg text-slate-900 mb-4">Tambah Master Bahan Baku Gudang</h3>
                <form method="POST" action="{{ route('production-inventory.store-ingredient') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode SKU</label>
                            <input type="text" name="sku" aria-label="Kode SKU" required value="ING-SS1-{{ rand(100,999) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori</label>
                            <select name="category" aria-label="Kategori" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                                <option value="karbohidrat">Karbohidrat</option>
                                <option value="protein_hewani">Protein Hewani</option>
                                <option value="protein_nabati">Protein Nabati</option>
                                <option value="sayuran">Sayuran Segar</option>
                                <option value="buah">Buah-Buahan</option>
                                <option value="susu">Susu & Olahan</option>
                                <option value="bumbu_minyak">Bumbu & Minyak</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Bahan Baku</label>
                        <input type="text" name="name" aria-label="Nama Bahan Baku" required placeholder="Cth: Beras Premium Slyp Super / Dada Ayam Fillet" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Satuan</label>
                            <input type="text" name="unit" aria-label="Satuan" required value="kg" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Stok Awal</label>
                            <input type="number" step="0.1" name="current_stock" aria-label="Stok Awal" required value="120" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Min. Stok</label>
                            <input type="number" step="0.1" name="min_stock_threshold" aria-label="Min. Stok" required value="25" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Harga/Unit</label>
                            <input type="number" name="unit_cost" aria-label="Harga/Unit" required value="14500" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="openIngModal = false" class="min-h-touch px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold">Batal</button>
                        <button type="submit" class="min-h-touch px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold cursor-pointer">Simpan Bahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL EDIT MASTER BAHAN BAKU -->
    <template x-teleport="body">
        <div x-show="openEditIngModal" x-cloak class="production-inventory-modal fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 overflow-y-auto">
            <div @click.away="openEditIngModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl my-auto max-h-[90vh] overflow-y-auto">
                <h3 class="font-display font-extrabold text-lg text-slate-900 mb-4" x-text="'Edit Bahan: ' + editIng.name"></h3>
                <form method="POST" :action="'{{ url('/production-inventory/ingredient') }}/' + editIng.id" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Bahan Baku</label>
                        <input type="text" name="name" aria-label="Nama Bahan Baku" x-model="editIng.name" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori</label>
                            <select name="category" aria-label="Kategori" x-model="editIng.category" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                                <option value="karbohidrat">Karbohidrat</option>
                                <option value="protein_hewani">Protein Hewani</option>
                                <option value="protein_nabati">Protein Nabati</option>
                                <option value="sayuran">Sayuran Segar</option>
                                <option value="buah">Buah-Buahan</option>
                                <option value="susu">Susu & Olahan</option>
                                <option value="bumbu_minyak">Bumbu & Minyak</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Satuan</label>
                            <input type="text" name="unit" aria-label="Satuan" x-model="editIng.unit" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Stok Aktual</label>
                            <input type="number" step="0.1" name="current_stock" aria-label="Stok Aktual" x-model="editIng.current_stock" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Min. Stok</label>
                            <input type="number" step="0.1" name="min_stock_threshold" aria-label="Min. Stok" x-model="editIng.min_stock_threshold" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Harga/Unit</label>
                            <input type="number" name="unit_cost" aria-label="Harga/Unit" x-model="editIng.unit_cost" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="openEditIngModal = false" class="min-h-touch px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold">Batal</button>
                        <button type="submit" class="min-h-touch px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL CATAT MUTASI STOK -->
    <template x-teleport="body">
        <div x-show="openTxModal" x-cloak class="production-inventory-modal fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 overflow-y-auto">
            <div @click.away="openTxModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl my-auto max-h-[90vh] overflow-y-auto">
                <h3 class="font-display font-extrabold text-lg text-slate-900 mb-4">Catat Penerimaan / Pemakaian Bahan Baku</h3>
                <form method="POST" action="{{ route('production-inventory.transaction') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Bahan Baku</label>
                        <select name="ingredient_id" aria-label="Pilih Bahan Baku" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                            @foreach($allIngredients as $ing)
                                <option value="{{ $ing->id }}">{{ $ing->name }} (Stok: {{ $ing->current_stock }} {{ $ing->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Mutasi</label>
                            <select name="type" aria-label="Jenis Mutasi" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                                <option value="inbound_purchase">Penerimaan Masuk (+)</option>
                                <option value="outbound_production">Pemakaian Masak (-)</option>
                                <option value="spoilage_adjustment">Spoilage / Rusak (-)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kuantitas</label>
                            <input type="number" step="0.1" name="quantity" aria-label="Kuantitas" required value="25" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Kesegaran</label>
                            <select name="freshness_condition" aria-label="Kondisi Kesegaran" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                                <option value="sangat_segar">Sangat Segar (Grade A)</option>
                                <option value="layak">Layak Produksi</option>
                                <option value="ditolak">Ditolak / Reject</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pemasok / Koperasi</label>
                            <input type="text" name="supplier_name" aria-label="Nama Pemasok / Koperasi" placeholder="Koperasi Pangan Sumsel" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="openTxModal = false" class="min-h-touch px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold">Batal</button>
                        <button type="submit" class="min-h-touch px-5 py-2 rounded-xl bg-amber-500 text-slate-950 text-xs font-bold cursor-pointer">Simpan Mutasi</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL BUKA SESI PRODUKSI BARU -->
    <template x-teleport="body">
        <div x-show="openRunModal" x-cloak class="production-inventory-modal fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 overflow-y-auto">
            <div @click.away="openRunModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl my-auto max-h-[90vh] overflow-y-auto">
                <h3 class="font-display font-extrabold text-lg text-slate-900 mb-4">Buka Sesi Produksi Dapur Baru</h3>
                <form method="POST" action="{{ route('production-inventory.store-run') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Jadwal Menu Harian</label>
                        <select name="daily_menu_id" aria-label="Pilih Jadwal Menu Harian" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                            @foreach($activeMenus as $m)
                                <option value="{{ $m->id }}">{{ $m->menu_code }} — {{ $m->menu_name }} (Target: {{ $m->target_portions }} Porsi)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Porsi Layak Saji Aktual</label>
                            <input type="number" name="actual_good_portions" aria-label="Porsi Layak Saji Aktual" required value="{{ old('actual_good_portions') }}" placeholder="Masukkan hasil aktual" min="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Porsi Rusak / Cacat</label>
                            <input type="number" name="rejected_damaged_portions" aria-label="Porsi Rusak / Cacat" required value="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Relawan Dapur Shift</label>
                            <input type="number" name="volunteers_count" required value="{{ old('volunteers_count', $standardVolunteers) }}" aria-label="Relawan Dapur Shift" min="1" max="100" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Status Tahapan Produksi</label>
                            <select name="status" aria-label="Status Tahapan Produksi" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                                <option value="cooking">Sedang Memasak ({{ $standardVolunteers }} Relawan)</option>
                                <option value="qc_inspection">Inspeksi QC Suhu</option>
                                <option value="packaging">Pemorsian Ompreng</option>
                                <option value="ready_for_dispatch">Siap Kirim Armada</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Kendala Operasional (Jika Ada)</label>
                        <input type="text" name="operational_obstacle" aria-label="Catatan Kendala Operasional" placeholder="Nihil kendala, air dan gas bertekanan stabil..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tindakan Penyelesaian</label>
                        <input type="text" name="action_taken" aria-label="Tindakan Penyelesaian" placeholder="Semua ompreng dipastikan terkunci rapi..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium">
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="openRunModal = false" class="min-h-touch px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold">Batal</button>
                        <button type="submit" class="min-h-touch px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold cursor-pointer">Simpan Sesi Produksi</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ingNames = @json($chartIngNames);
    const ingUnits = @json($chartIngUnits);
    const currentStocks = @json($chartCurrentStocks);
    const minThresholds = @json($chartMinThresholds);
    const stockPercentages = @json($chartStockPercentages);
    const stockColors = @json($chartStockColors);

    const ctxStock = document.getElementById('stockComparisonChart');
    if (ctxStock) {
        new Chart(ctxStock, {
            type: 'bar',
            data: {
                labels: ingNames,
                datasets: [
                    {
                        label: 'Stok aktual',
                        data: stockPercentages,
                        backgroundColor: stockColors,
                        borderRadius: 5,
                        barPercentage: 0.72,
                        categoryPercentage: 0.8,
                        maxBarThickness: 22,
                        order: 2
                    },
                    {
                        type: 'scatter',
                        label: 'Ambang minimum',
                        data: ingNames.map(name => ({ x: 100, y: name })),
                        pointStyle: 'line',
                        pointRotation: 90,
                        pointRadius: 9,
                        pointHoverRadius: 10,
                        pointBorderWidth: 2,
                        pointBorderColor: '#475569',
                        pointBackgroundColor: '#475569',
                        showLine: false,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                interaction: { intersect: false, mode: 'nearest' },
                scales: {
                    x: {
                        type: 'linear',
                        beginAtZero: true,
                        max: Math.max(150, ...stockPercentages, 100) * 1.05,
                        grid: { color: 'rgba(100, 116, 139, 0.12)' },
                        ticks: {
                            color: '#475569',
                            font: { family: 'Plus Jakarta Sans', size: 12 },
                            callback: value => `${value}%`
                        },
                        title: { display: true, text: 'Stok dibanding ambang minimum', color: '#475569', font: { size: 12, weight: '600' } }
                    },
                    y: {
                        type: 'category',
                        grid: { display: false },
                        ticks: {
                            color: '#334155',
                            font: { family: 'Plus Jakarta Sans', size: 12 },
                            callback: (value, index) => {
                                const words = (ingNames[index] ?? '').split(/\s+/);
                                const lines = [];
                                let line = '';
                                words.forEach(word => {
                                    if (line && `${line} ${word}`.length > 18) {
                                        lines.push(line);
                                        line = word;
                                    } else {
                                        line = line ? `${line} ${word}` : word;
                                    }
                                });
                                if (line) lines.push(line);
                                return lines;
                            }
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#061F47',
                        borderColor: '#334155',
                        borderWidth: 1,
                        titleColor: '#FFFFFF',
                        bodyColor: '#FFFFFF',
                        padding: 10,
                        filter: item => item.datasetIndex === 0,
                        callbacks: {
                            title: items => ingNames[items[0]?.dataIndex] ?? '',
                            label: item => {
                                const index = item.dataIndex;
                                const format = value => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value);
                                return [
                                    `Stok aktual: ${format(currentStocks[index])} ${ingUnits[index]} (${format(stockPercentages[index])}% ambang)`,
                                    `Ambang minimum: ${format(minThresholds[index])} ${ingUnits[index]}`
                                ];
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
