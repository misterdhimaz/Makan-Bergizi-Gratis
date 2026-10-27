@extends('layouts.app')

@section('title', 'Monitoring Produksi & Bahan Baku Gudang')
@section('page_title', 'Monitoring Produksi & Gudang Bahan Baku')
@section('page_subtitle', 'Pencatatan persediaan bahan baku & porsi produksi real-time')

@section('content')
<div x-data="{
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

    <!-- Top Action & KPI Cards -->
    <div class="gsap-reveal grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="surface-card rounded-3xl p-5 border-l-4 border-l-emerald-500">
            <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Item Bahan Baku</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="font-display font-extrabold text-3xl text-slate-900">{{ $ingredients->count() }}</h3>
                <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full">
                    {{ $ingredients->filter(fn($i) => $i->current_stock <= $i->min_stock_threshold)->count() }} Kritis
                </span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Terdaftar di gudang SPPG</p>
        </div>

        <div class="surface-card rounded-3xl p-5 border-l-4 border-l-teal-500">
            <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Sesi Produksi Dapur</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="font-display font-extrabold text-3xl text-slate-900">{{ $productionRuns->count() }}</h3>
                <span class="text-xs font-bold text-teal-700 bg-teal-50 px-2.5 py-1 rounded-full">
                    {{ number_format($productionRuns->sum('actual_good_portions')) }} Porsi Layak
                </span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Total porsi layak saji</p>
        </div>

        <div class="surface-card rounded-3xl p-5 border-l-4 border-l-amber-500">
            @php
                $netVar = $productionRuns->sum('actual_good_portions') - $productionRuns->sum('planned_portions');
            @endphp
            <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Akumulasi Selisih Porsi</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="font-display font-extrabold text-3xl {{ $netVar < 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                    {{ $netVar >= 0 ? '+' . $netVar : $netVar }}
                </h3>
                <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">
                    {{ $productionRuns->sum('rejected_damaged_portions') }} Rusak
                </span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Realisasi vs Target Porsi</p>
        </div>

        <div class="surface-card rounded-3xl p-5 border-l-4 border-l-indigo-500 flex flex-col justify-between gap-2">
            <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Aksi Cepat Gudang & Dapur</p>
            <div class="grid grid-cols-2 gap-2">
                <button @click="openIngModal = true" class="py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition cursor-pointer">
                    + Bahan Baku
                </button>
                <button @click="openTxModal = true" class="py-2 px-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-[11px] transition cursor-pointer">
                    + Mutasi Stok
                </button>
            </div>
            <button @click="openRunModal = true" class="w-full py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] transition cursor-pointer shadow-sm">
                + Buka Sesi Produksi Baru
            </button>
        </div>
    </div>

    <!-- 2 Grafik Gudang & Finansial -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        <div class="gsap-reveal lg:col-span-8 surface-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">Grafik Inventori Gudang</span>
                    <h3 class="font-display font-extrabold text-base text-slate-900 mt-1.5">Perbandingan Stok Aktual vs Ambang Minimum (Safety Stock)</h3>
                </div>
            </div>
            <div class="h-64">
                <canvas id="stockComparisonChart"></canvas>
            </div>
        </div>

        <div class="gsap-reveal lg:col-span-4 surface-card rounded-3xl p-6">
            <div class="mb-4">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">Valuasi Mutasi</span>
                <h3 class="font-display font-extrabold text-base text-slate-900 mt-1.5">Belanja Masuk vs Pemakaian Dapur (Rp)</h3>
            </div>
            <div class="h-64">
                <canvas id="txFinancialChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar Lengkap -->
    <div class="gsap-reveal surface-card rounded-3xl p-5 mb-6">
        <form method="GET" action="{{ route('production-inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-4">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Bahan Baku / SKU / No. Referensi</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama bahan baku, SKU, atau pemasok..."
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
    <div class="gsap-reveal surface-card rounded-3xl p-6 mb-6">
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
                                <span class="font-mono font-extrabold text-[11px] px-2.5 py-1 rounded-xl bg-teal-50 text-teal-900 border border-teal-300 dark:bg-teal-950/40 dark:text-teal-200 dark:border-teal-700/50">{{ $run->dailyMenu?->menu_code }}</span>
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
                                    <option value="cooking" {{ $run->status === 'cooking' ? 'selected' : '' }}>Memasak (35 Relawan)</option>
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
        <div class="lg:col-span-7 surface-card rounded-3xl p-6">
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
                                    </td>
                                    <td class="py-3 px-3 text-slate-500">{{ $ing->min_stock_threshold }} {{ $ing->unit }}</td>
                                    <td class="py-3 px-3 font-semibold">Rp {{ number_format($ing->unit_cost, 0, ',', '.') }}</td>
                                    <td class="py-3 px-3 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button" @click="openEditIngredient({{ Js::from($ing) }})"
                                                    class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-emerald-50 text-slate-700 font-bold text-[11px] cursor-pointer">
                                                Edit
                                            </button>
                                            @if(auth()->user()->role->value === 'admin')
                                                <form method="POST" action="{{ route('production-inventory.destroy-ingredient', $ing) }}" onsubmit="return confirm('Hapus bahan {{ $ing->name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2 py-1 rounded-lg bg-rose-50 text-rose-600 font-bold text-[11px] cursor-pointer">✕</button>
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
        <div x-show="openIngModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openIngModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl my-auto max-h-[90vh] overflow-y-auto">
                <h3 class="font-display font-extrabold text-lg text-slate-900 mb-4">Tambah Master Bahan Baku Gudang</h3>
                <form method="POST" action="{{ route('production-inventory.store-ingredient') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode SKU</label>
                            <input type="text" name="sku" required value="ING-SS1-{{ rand(100,999) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori</label>
                            <select name="category" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
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
                        <input type="text" name="name" required placeholder="Cth: Beras Premium Slyp Super / Dada Ayam Fillet" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Satuan</label>
                            <input type="text" name="unit" required value="kg" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Stok Awal</label>
                            <input type="number" step="0.1" name="current_stock" required value="120" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Min. Stok</label>
                            <input type="number" step="0.1" name="min_stock_threshold" required value="25" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Harga/Unit</label>
                            <input type="number" name="unit_cost" required value="14500" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="openIngModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold cursor-pointer">Simpan Bahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL EDIT MASTER BAHAN BAKU -->
    <template x-teleport="body">
        <div x-show="openEditIngModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openEditIngModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl my-auto max-h-[90vh] overflow-y-auto">
                <h3 class="font-display font-extrabold text-lg text-slate-900 mb-4" x-text="'Edit Bahan: ' + editIng.name"></h3>
                <form method="POST" :action="'{{ url('/production-inventory/ingredient') }}/' + editIng.id" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Bahan Baku</label>
                        <input type="text" name="name" x-model="editIng.name" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori</label>
                            <select name="category" x-model="editIng.category" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
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
                            <input type="text" name="unit" x-model="editIng.unit" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Stok Aktual</label>
                            <input type="number" step="0.1" name="current_stock" x-model="editIng.current_stock" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Min. Stok</label>
                            <input type="number" step="0.1" name="min_stock_threshold" x-model="editIng.min_stock_threshold" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Harga/Unit</label>
                            <input type="number" name="unit_cost" x-model="editIng.unit_cost" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="openEditIngModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL CATAT MUTASI STOK -->
    <template x-teleport="body">
        <div x-show="openTxModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openTxModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl my-auto max-h-[90vh] overflow-y-auto">
                <h3 class="font-display font-extrabold text-lg text-slate-900 mb-4">Catat Penerimaan / Pemakaian Bahan Baku</h3>
                <form method="POST" action="{{ route('production-inventory.transaction') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Bahan Baku</label>
                        <select name="ingredient_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                            @foreach($allIngredients as $ing)
                                <option value="{{ $ing->id }}">{{ $ing->name }} (Stok: {{ $ing->current_stock }} {{ $ing->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Mutasi</label>
                            <select name="type" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                                <option value="inbound_purchase">Penerimaan Masuk (+)</option>
                                <option value="outbound_production">Pemakaian Masak (-)</option>
                                <option value="spoilage_adjustment">Spoilage / Rusak (-)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kuantitas</label>
                            <input type="number" step="0.1" name="quantity" required value="25" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Kesegaran</label>
                            <select name="freshness_condition" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                                <option value="sangat_segar">Sangat Segar (Grade A)</option>
                                <option value="layak">Layak Produksi</option>
                                <option value="ditolak">Ditolak / Reject</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pemasok / Koperasi</label>
                            <input type="text" name="supplier_name" placeholder="Koperasi Pangan Sumsel" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="openTxModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 text-slate-950 text-xs font-bold cursor-pointer">Simpan Mutasi</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL BUKA SESI PRODUKSI BARU -->
    <template x-teleport="body">
        <div x-show="openRunModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openRunModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl my-auto max-h-[90vh] overflow-y-auto">
                <h3 class="font-display font-extrabold text-lg text-slate-900 mb-4">Buka Sesi Produksi Dapur Baru</h3>
                <form method="POST" action="{{ route('production-inventory.store-run') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Jadwal Menu Harian</label>
                        <select name="daily_menu_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                            @foreach($activeMenus as $m)
                                <option value="{{ $m->id }}">{{ $m->menu_code }} — {{ $m->menu_name }} (Target: {{ $m->target_portions }} Porsi)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Porsi Layak Saji Aktual</label>
                            <input type="number" name="actual_good_portions" required value="1414" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Porsi Rusak / Cacat</label>
                            <input type="number" name="rejected_damaged_portions" required value="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Relawan Dapur Shift</label>
                            <input type="number" name="volunteers_count" required value="35" min="1" max="100" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Status Tahapan Produksi</label>
                            <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                                <option value="cooking">Sedang Memasak (35 Relawan)</option>
                                <option value="qc_inspection">Inspeksi QC Suhu</option>
                                <option value="packaging">Pemorsian Ompreng</option>
                                <option value="ready_for_dispatch">Siap Kirim Armada</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Kendala Operasional (Jika Ada)</label>
                        <input type="text" name="operational_obstacle" placeholder="Nihil kendala, air dan gas bertekanan stabil..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tindakan Penyelesaian</label>
                        <input type="text" name="action_taken" placeholder="Semua ompreng dipastikan terkunci rapi..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium">
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="openRunModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold cursor-pointer">Simpan Sesi Produksi</button>
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
    const currentStocks = @json($chartCurrentStocks);
    const minThresholds = @json($chartMinThresholds);

    const ctxStock = document.getElementById('stockComparisonChart');
    if (ctxStock) {
        new Chart(ctxStock, {
            type: 'bar',
            data: {
                labels: ingNames.length ? ingNames : ['Belum Ada Bahan Baku'],
                datasets: [
                    {
                        label: 'Stok Aktual',
                        data: currentStocks.length ? currentStocks : [0],
                        backgroundColor: 'rgba(146, 208, 93, 0.85)',
                        borderRadius: 8
                    },
                    {
                        label: 'Ambang Minimum',
                        data: minThresholds.length ? minThresholds : [0],
                        backgroundColor: 'rgba(244, 63, 94, 0.75)',
                        borderRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    const txSummary = @json(array_values($txSummary));
    const totalTx = txSummary.reduce((a, b) => a + b, 0);
    const ctxFin = document.getElementById('txFinancialChart');
    if (ctxFin) {
        new Chart(ctxFin, {
            type: 'doughnut',
            data: {
                labels: totalTx > 0 ? ['Penerimaan Masuk', 'Pemakaian Masak', 'Spoilage/Susut'] : ['Belum Ada Mutasi'],
                datasets: [{
                    data: totalTx > 0 ? txSummary : [1],
                    backgroundColor: totalTx > 0 ? ['#92D05D', '#D1B06C', '#f43f5e'] : ['#1E3266'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '66%',
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
</script>
@endpush
