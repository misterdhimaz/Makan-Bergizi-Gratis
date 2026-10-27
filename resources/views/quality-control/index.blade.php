@extends('layouts.app')

@section('title', 'Pemeriksaan Suhu & Keamanan Pangan (HACCP)')
@section('page_title', 'Pemeriksaan Suhu & Keamanan Pangan (HACCP)')
@section('page_subtitle', 'Audit titik kritis suhu masak inti (≥75°C), holding (≥60°C), pra-distribusi, dan retensi bank sampel 48 jam')

@section('content')
<div x-data="{
    activeTab: 'all', // 'all', 'form', 'history', 'charts'
    coreTemp: 82.5,
    holdTemp: 66.0,
    dispatchTemp: 64.5,
    sanitation: true,
    sampleRetained: true,
    organolepticSample: true,
    openResolveModal: false,
    selectedQc: null,

    get coreTempStatus() {
        if (this.coreTemp >= 75) return { label: 'Lolos Standar HACCP BGN (≥75°C)', class: 'bg-emerald-50 text-emerald-700 border-emerald-200', safe: true };
        return { label: 'Bahaya: Suhu Inti <75°C (Patogen Berisiko)', class: 'bg-rose-50 text-rose-700 border-rose-200', safe: false };
    },

    get holdTempStatus() {
        if (this.holdTemp >= 60) return { label: 'Suhu Hangat Aman (≥60°C)', class: 'bg-emerald-50 text-emerald-700 border-emerald-200', safe: true };
        return { label: 'Peringatan: Zona Bahaya Bakteri (<60°C)', class: 'bg-amber-50 text-amber-700 border-amber-200', safe: false };
    },

    get dispatchTempStatus() {
        if (this.dispatchTemp >= 60) return { label: 'Suhu Siap Angkut Armada (≥60°C)', class: 'bg-emerald-50 text-emerald-700 border-emerald-200', safe: true };
        return { label: 'Perlu Dihangatkan Sebelum Masuk Box', class: 'bg-amber-50 text-amber-700 border-amber-200', safe: false };
    },

    openResolve(qc) {
        this.selectedQc = qc;
        this.openResolveModal = true;
    }
}">

    <!-- Hero Banner Audit Suhu & Keamanan Pangan -->
    <div class="gsap-reveal relative rounded-3xl overflow-hidden bg-gradient-to-r from-[#06204D] via-[#092B66] to-[#041432] text-white p-6 lg:p-8 mb-6 shadow-xl border border-teal-500/20">
        <div class="ambient-orb w-72 h-72 bg-teal-500/20 -top-20 -right-10"></div>
        <div class="ambient-orb w-56 h-56 bg-amber-400/15 bottom-0 left-1/3" style="animation-delay: -5s;"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/15 border border-teal-400/30 text-teal-300 text-[11px] font-bold uppercase tracking-widest mb-3">
                    <span class="w-2 h-2 rounded-full bg-teal-400 animate-ping"></span>
                    Standar HACCP BGN • Titik Kendali Kritis Suhu Termal & Bank Sampel
                </div>
                <h2 class="font-display font-extrabold text-2xl lg:text-3xl tracking-tight text-white">
                    Pemeriksaan Suhu Makanan & Keamanan Pangan (QC)
                </h2>
                <p class="text-xs sm:text-sm text-teal-100/75 mt-1.5 max-w-2xl leading-relaxed">
                    Pengawasan termal masak inti (<strong class="text-white">≥ 75°C</strong>), wadah penghangat holding (<strong class="text-white">≥ 60°C</strong>), suhu pra-distribusi siap muat, serta pengarsipan <strong class="text-white">Bank Sampel Makanan 2x24 Jam</strong> di SPPG Sungai Selayur 1.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('apd-inspection.index') }}"
                   class="px-4 py-3 rounded-2xl bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-400/40 text-emerald-200 font-bold text-xs flex items-center gap-2 transition shadow-sm">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Buka Modul Cek APD Relawan &rarr;
                </a>
            </div>
        </div>

        <!-- 4 KPI Strip Khusus Suhu -->
        <div class="relative z-10 grid grid-cols-2 lg:grid-cols-4 gap-3.5 mt-6 pt-6 border-t border-white/10">
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-teal-300/80">Total Audit Suhu</p>
                <p class="font-display font-extrabold text-2xl text-white mt-1">{{ $inspections->count() }} <span class="text-xs font-normal text-slate-300">Batch Produksi</span></p>
            </div>
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-300/80">Rata-Rata Suhu Masak</p>
                <p class="font-display font-extrabold text-2xl text-emerald-300 mt-1">{{ $avgCookingTemp }}°C <span class="text-xs font-normal text-slate-300">Standar ≥ 75°C</span></p>
            </div>
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-300/80">Rata-Rata Suhu Holding</p>
                <p class="font-display font-extrabold text-2xl text-white mt-1">{{ $avgHoldingTemp }}°C <span class="text-xs font-normal text-slate-300">Standar ≥ 60°C</span></p>
            </div>
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-300/80">Bank Sampel Chiller</p>
                <p class="font-display font-extrabold text-2xl text-white mt-1">{{ $foodBankCount }} <span class="text-xs font-normal text-slate-300">Sampel 48 Jam</span></p>
            </div>
        </div>
    </div>

    <!-- Section Switcher Bar (Navigasi Per Section - Tidak Ke-Split 2!) -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="inline-flex flex-wrap p-1.5 rounded-2xl bg-white border border-slate-200/80 shadow-xs gap-1">
            <button @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                Tampilkan Semua Panel
            </button>
            <button @click="activeTab = 'form'"
                    :class="activeTab === 'form' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                + Form Input Pemeriksaan Suhu
            </button>
            <button @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                Riwayat Audit Suhu & Bank Sampel ({{ $inspections->count() }})
            </button>
            <button @click="activeTab = 'charts'"
                    :class="activeTab === 'charts' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                Grafik Telemetri Suhu & Putusan
            </button>
        </div>

        @if($search !== '' || $verdict !== 'all' || $dateFrom || $dateTo)
            <a href="{{ route('quality-control.index') }}" class="px-3.5 py-2 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold hover:bg-rose-100 transition">
                Reset Filter Aktif
            </a>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- SECTION 1: FORM INPUT PEMERIKSAAN SUHU HACCP (FULL WIDTH - TIDAK SPLIT!) -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'all' || activeTab === 'form'" x-transition class="surface-card rounded-3xl p-6 sm:p-7 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                    <span class="text-[11px] font-extrabold uppercase tracking-widest text-teal-600">Formulir Audit Termal HACCP</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 mt-1">Input Pemeriksaan Suhu Kritis & Bank Sampel Makanan</h3>
                <p class="text-xs text-slate-500">Formulir resmi Ahli Gizi untuk mencatat suhu masak inti, suhu holding wadah saji, dan suhu pra-distribusi sebelum muat.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200 shrink-0">
                Standar BGN: Masak ≥ 75°C • Holding ≥ 60°C
            </span>
        </div>

        @if($productionRuns->isEmpty())
            <div class="text-center py-10 px-4 rounded-2xl bg-slate-50 border border-dashed border-slate-200 text-xs text-slate-500">
                Belum ada sesi produksi dapur yang aktif untuk diaudit suhunya.
            </div>
        @else
            <form method="POST" action="{{ route('quality-control.store') }}" class="space-y-6">
                @csrf

                <!-- Baris 1: Sesi Produksi & Kode Sampel -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-7">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Sesi Produksi Dapur (Batch Terjadwal)</label>
                        <select name="production_run_id" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs font-semibold focus:border-teal-500 focus:outline-none bg-white">
                            @foreach($productionRuns as $run)
                                <option value="{{ $run->id }}">{{ $run->dailyMenu?->menu_code }} — {{ $run->dailyMenu?->menu_name }} ({{ number_format($run->planned_portions) }} Porsi) [{{ $run->status }}]</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-5">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kode Label Bank Sampel Makanan</label>
                        <input type="text" name="food_sample_code" required value="SMP-SS1-{{ now()->format('Ymd') }}-A"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs font-mono font-bold text-slate-900 focus:border-teal-500 focus:outline-none bg-white">
                        <p class="text-[11px] text-slate-400 mt-1">Label arsip bertiket pada chiller khusus penyimpanan 2x24 jam</p>
                    </div>
                </div>

                <!-- Baris 2: 3 KARTU TERMOMETER SUHU BERJAJAR PENUH (LEGA & INFORMATIF) -->
                <div>
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-3">Pengukuran 3 Titik Kritis Suhu Termal (°C):</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        
                        <!-- 1. Suhu Masak Inti -->
                        <div class="p-5 rounded-2xl border-2 border-slate-200/80 bg-slate-50/50 hover:border-rose-400 hover:bg-white transition flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-bold shrink-0">🌡️</span>
                                        <div>
                                            <h5 class="text-xs font-bold text-slate-900">1. Suhu Masak Inti</h5>
                                            <p class="text-[10px] text-slate-400">Titik pusat daging/protein</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <input type="number" step="0.1" name="cooking_core_temp_c" x-model.number="coreTemp" required min="30" max="120"
                                               class="w-20 px-2.5 py-1.5 rounded-xl border border-slate-300 text-sm font-extrabold text-center text-rose-700 bg-white focus:border-rose-500">
                                        <span class="text-xs font-bold text-slate-500">°C</span>
                                    </div>
                                </div>
                                <input type="range" min="50" max="100" step="0.5" x-model.number="coreTemp" class="w-full accent-rose-600 cursor-pointer">
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-200/70 flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-500">Standar BGN: ≥ 75°C</span>
                                <span class="px-2.5 py-0.5 rounded-full font-bold border text-[10px]" :class="coreTempStatus.class" x-text="coreTempStatus.label"></span>
                            </div>
                        </div>

                        <!-- 2. Suhu Holding Panas -->
                        <div class="p-5 rounded-2xl border-2 border-slate-200/80 bg-slate-50/50 hover:border-amber-400 hover:bg-white transition flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold shrink-0">🔥</span>
                                        <div>
                                            <h5 class="text-xs font-bold text-slate-900">2. Suhu Holding Panas</h5>
                                            <p class="text-[10px] text-slate-400">Wadah penyimpanan saji</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <input type="number" step="0.1" name="holding_temp_c" x-model.number="holdTemp" required min="20" max="100"
                                               class="w-20 px-2.5 py-1.5 rounded-xl border border-slate-300 text-sm font-extrabold text-center text-amber-700 bg-white focus:border-amber-500">
                                        <span class="text-xs font-bold text-slate-500">°C</span>
                                    </div>
                                </div>
                                <input type="range" min="40" max="90" step="0.5" x-model.number="holdTemp" class="w-full accent-amber-500 cursor-pointer">
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-200/70 flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-500">Standar BGN: ≥ 60°C</span>
                                <span class="px-2.5 py-0.5 rounded-full font-bold border text-[10px]" :class="holdTempStatus.class" x-text="holdTempStatus.label"></span>
                            </div>
                        </div>

                        <!-- 3. Suhu Pra-Distribusi -->
                        <div class="p-5 rounded-2xl border-2 border-slate-200/80 bg-slate-50/50 hover:border-teal-400 hover:bg-white transition flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center text-sm font-bold shrink-0">🚚</span>
                                        <div>
                                            <h5 class="text-xs font-bold text-slate-900">3. Suhu Pra-Distribusi</h5>
                                            <p class="text-[10px] text-slate-400">Sebelum muat ke armada</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <input type="number" step="0.1" name="pre_dispatch_temp_c" x-model.number="dispatchTemp" required min="20" max="100"
                                               class="w-20 px-2.5 py-1.5 rounded-xl border border-slate-300 text-sm font-extrabold text-center text-teal-700 bg-white focus:border-teal-500">
                                        <span class="text-xs font-bold text-slate-500">°C</span>
                                    </div>
                                </div>
                                <input type="range" min="40" max="85" step="0.5" x-model.number="dispatchTemp" class="w-full accent-teal-600 cursor-pointer">
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-200/70 flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-500">Standar BGN: ≥ 60°C</span>
                                <span class="px-2.5 py-0.5 rounded-full font-bold border text-[10px]" :class="dispatchTempStatus.class" x-text="dispatchTempStatus.label"></span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Baris 3: 3 Checklist Keamanan Pangan & Uji Organoleptik -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-xs">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-3">Prosedur Keamanan Pangan & Retensi SOP BGN:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="food_sample_retained" value="1" x-model="sampleRetained" class="w-4 h-4 mt-0.5 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Bank Sampel Chiller (2x24 Jam)</span>
                                <span class="text-[11px] text-slate-500">Sampel 250g disimpan pada suhu 4°C berlabel bertiket</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="kitchen_sanitation_passed" value="1" x-model="sanitation" class="w-4 h-4 mt-0.5 rounded text-teal-600 focus:ring-teal-500">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Sanitasi Ompreng & Kontainer</span>
                                <span class="text-[11px] text-slate-500">Meja pemorsian, wadah ompreng & boks armada steril</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" checked disabled class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Uji Organoleptik Sampel Dapur</span>
                                <span class="text-[11px] text-slate-500">Aroma sedap segar, rasa normal seimbang, matang merata</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Baris 4: Putusan & Tindakan Korektif -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Keputusan Akhir Suhu QC</label>
                        <select name="qc_verdict" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs font-bold bg-white focus:border-teal-500 focus:outline-none">
                            <option value="passed">PASSED — Suhu Aman, Layak Kirim ke 7 Titik</option>
                            <option value="conditional_pass">CONDITIONAL PASS — Perlu Pemanasan Ulang Sebelum Muat</option>
                            <option value="failed_hold">FAILED / HOLD — Tahan Distribusi (Bahaya Suhu Kritis)</option>
                        </select>
                    </div>

                    <div class="md:col-span-8">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Audit Termal & Tindakan Korektif</label>
                        <input type="text" name="corrective_action" placeholder="Cth: Suhu inti mencapai 84.5°C, aroma ayam panggang segar, bebas kontaminasi."
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs font-medium focus:border-teal-500 focus:outline-none bg-white">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-500 hover:to-emerald-500 text-white font-bold text-xs shadow-lg shadow-teal-600/30 transition cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Hasil Audit Suhu & Keamanan Pangan
                    </button>
                </div>
            </form>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- SECTION 2: RIWAYAT AUDIT SUHU & BANK SAMPEL (FULL WIDTH - TIDAK SPLIT!) -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'all' || activeTab === 'history'" x-transition class="surface-card rounded-3xl p-6 sm:p-7 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-[11px] font-extrabold uppercase tracking-widest text-emerald-600">Log Pemeriksaan Termal</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 mt-1">Riwayat Audit Suhu & Bank Sampel Makanan</h3>
                <p class="text-xs text-slate-500">Rekam jejak suhu masak, holding, dan pengarsipan chiller 48 jam di SPPG Sungai Selayur 1.</p>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $inspections->count() }} Data Tercatat</span>
        </div>

        <!-- Filter Bar Suhu (Tombol Kontras Jelas!) -->
        <form method="GET" action="{{ route('quality-control.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end mb-6 bg-slate-50/80 p-4 rounded-2xl border border-slate-200/70">
            <div class="lg:col-span-4">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Kode Sampel / Menu / Tindakan</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode sampel SMP-... atau nama menu..."
                       class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold focus:border-teal-500 focus:outline-none">
            </div>

            <div class="lg:col-span-3">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Keputusan QC (Verdict)</label>
                <select name="verdict" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 focus:border-teal-500 focus:outline-none">
                    <option value="all" {{ $verdict === 'all' ? 'selected' : '' }}>Semua Keputusan</option>
                    <option value="passed" {{ $verdict === 'passed' ? 'selected' : '' }}>PASSED — Lulus Siap Kirim</option>
                    <option value="conditional_pass" {{ $verdict === 'conditional_pass' ? 'selected' : '' }}>CONDITIONAL PASS — Bersyarat</option>
                    <option value="failed_hold" {{ $verdict === 'failed_hold' ? 'selected' : '' }}>FAILED / HOLD — Tahan Distribusi</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 focus:border-teal-500 focus:outline-none">
            </div>

            <div class="lg:col-span-3 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition cursor-pointer flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Terapkan Filter
                </button>
                @if($search !== '' || $verdict !== 'all' || $dateFrom || $dateTo)
                    <a href="{{ route('quality-control.index') }}" class="py-2.5 px-3 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 font-bold text-xs hover:bg-rose-100 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        @if($inspections->isEmpty())
            <div class="text-center py-12 px-4 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50">
                <p class="text-xs text-slate-500">Tidak ada riwayat audit suhu yang sesuai filter.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($inspections as $qc)
                    <div class="p-5 rounded-2xl border border-slate-200/90 bg-white hover:border-teal-400 hover:shadow-md transition">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <!-- Badge Kode Sampel Kontras Tinggi! -->
                                <span class="font-mono font-extrabold text-xs px-3 py-1 rounded-xl bg-teal-50 text-teal-900 border border-teal-300">
                                    {{ $qc->food_sample_code }}
                                </span>
                                <h4 class="font-bold text-sm text-slate-900">{{ $qc->productionRun?->dailyMenu?->menu_name }}</h4>
                                <span class="text-xs text-slate-400">({{ $qc->productionRun?->dailyMenu?->menu_code }})</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase {{ $qc->qc_verdict === 'passed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                    {{ strtoupper(str_replace('_', ' ', $qc->qc_verdict)) }}
                                </span>
                            </div>
                        </div>

                        <!-- 3 PILAR INDIKATOR SUHU TERMAL -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 my-4">
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Suhu Masak Inti</span>
                                    <span class="font-mono font-extrabold text-base text-rose-600">{{ $qc->cooking_core_temp_c }}°C</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $qc->cooking_core_temp_c >= 75 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                    {{ $qc->cooking_core_temp_c >= 75 ? 'Lolos (≥75°C)' : 'Bahaya (<75°C)' }}
                                </span>
                            </div>

                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Suhu Holding Panas</span>
                                    <span class="font-mono font-extrabold text-base text-amber-600">{{ $qc->holding_temp_c }}°C</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $qc->holding_temp_c >= 60 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                    {{ $qc->holding_temp_c >= 60 ? 'Aman (≥60°C)' : 'Dingin (<60°C)' }}
                                </span>
                            </div>

                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Suhu Pra-Distribusi</span>
                                    <span class="font-mono font-extrabold text-base text-teal-600">{{ $qc->pre_dispatch_temp_c }}°C</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $qc->pre_dispatch_temp_c >= 60 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                    {{ $qc->pre_dispatch_temp_c >= 60 ? 'Siap Muat (≥60°C)' : 'Kurang Hangat' }}
                                </span>
                            </div>
                        </div>

                        <!-- Footer Info & Tindak Lanjut -->
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs text-slate-600">
                            <div class="flex flex-wrap items-center gap-3">
                                <span>Inspektor: <strong class="text-slate-900">{{ $qc->inspector?->name }}</strong></span>
                                <span class="text-slate-300">•</span>
                                <span>Waktu Audit: <strong class="text-slate-900">{{ $qc->inspected_at->translatedFormat('d M Y, H:i') }} WIB</strong></span>
                                <span class="text-slate-300">•</span>
                                @if($qc->food_sample_retained)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 font-bold text-[10px]">
                                        ✓ Bank Sampel Chiller (2x24 Jam)
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                @if($qc->corrective_action)
                                    <span class="text-[11px] text-slate-600 italic max-w-md truncate">"{{ $qc->corrective_action }}"</span>
                                @endif

                                @if($qc->qc_verdict !== 'passed' && in_array(auth()->user()->role->value, ['admin', 'kepala_dapur']))
                                    <button @click="openResolve({{ Js::from($qc) }})" class="px-3 py-1 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs transition cursor-pointer">
                                        Tindak Lanjut Ka. Dapur
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- SECTION 3: GRAFIK TELEMETRI TERMAL & PUTUSAN (FULL WIDTH - TIDAK TERPOTONG!) -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'all' || activeTab === 'charts'" x-transition class="surface-card rounded-3xl p-6 sm:p-7 mb-8">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-200">Telemetri Visual</span>
                <h3 class="font-display font-extrabold text-xl text-slate-900 mt-1">Grafik Pemantauan Suhu & Evaluasi Kelayakan Pangan</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                <h4 class="text-xs font-bold text-slate-800 mb-3">Tren Suhu Inti Masak, Holding & Pra-Distribusi per Batch (°C)</h4>
                <div class="h-64">
                    <canvas id="qcTempChart"></canvas>
                </div>
            </div>

            <div class="lg:col-span-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <h4 class="text-xs font-bold text-slate-800 mb-2">Proporsi Putusan Kelayakan Pangan</h4>
                <div class="h-56 flex items-center justify-center">
                    <canvas id="qcVerdictChart"></canvas>
                </div>
                <p class="text-[11px] text-slate-400 text-center mt-2">Berdasarkan hasil uji suhu & keamanan pangan</p>
            </div>
        </div>
    </div>

    <!-- MODAL TINDAK LANJUT KEPALA DAPUR -->
    <template x-teleport="body">
        <div x-show="openResolveModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openResolveModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 my-auto">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-amber-600">Otoritas Kepala Dapur</span>
                        <h3 class="font-display font-extrabold text-base text-slate-900">Tindak Lanjut Temuan Suhu QC</h3>
                    </div>
                    <button @click="openResolveModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">✕</button>
                </div>

                <form method="POST" :action="'{{ url('/quality-control') }}/' + (selectedQc ? selectedQc.id : '') + '/resolve'" class="space-y-4">
                    @csrf
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                        <p class="text-slate-500">Kode Sampel: <strong class="text-slate-900" x-text="selectedQc ? selectedQc.food_sample_code : ''"></strong></p>
                        <p class="text-slate-500">Suhu Masak Inti: <strong class="text-rose-600" x-text="(selectedQc ? selectedQc.cooking_core_temp_c : '') + '°C'"></strong></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tindakan Korektif</label>
                        <textarea name="corrective_action" rows="3" required placeholder="Cth: Dilakukan pemanasan ulang combi steamer selama 10 menit..."
                                  class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:border-amber-500 focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Penyelesaian</label>
                        <select name="corrective_action_status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold">
                            <option value="resolved">Selesai / Teratasi (Resolved)</option>
                            <option value="open">Masih Dalam Penanganan (Open)</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openResolveModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold shadow-md cursor-pointer">Simpan Tindakan</button>
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
    // 1. Grafik Termal Suhu Masak, Holding & Pre-Dispatch
    const chartLabels = @json($chartLabels);
    const coreTemps = @json($chartCoreTemps);
    const holdTemps = @json($chartHoldTemps);
    const dispatchTemps = @json($chartDispatchTemps);

    const ctxTemp = document.getElementById('qcTempChart');
    if (ctxTemp) {
        new Chart(ctxTemp, {
            type: 'line',
            data: {
                labels: chartLabels.length ? chartLabels : ['Hari Ini'],
                datasets: [
                    {
                        label: 'Suhu Masak Inti (Min 75°C)',
                        data: coreTemps.length ? coreTemps : [84.5],
                        borderColor: '#e11d48',
                        backgroundColor: 'rgba(225, 29, 72, 0.1)',
                        borderWidth: 2.5,
                        fill: false,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: '#e11d48'
                    },
                    {
                        label: 'Suhu Holding (Min 60°C)',
                        data: holdTemps.length ? holdTemps : [67.8],
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3,
                        pointRadius: 3
                    },
                    {
                        label: 'Suhu Pra-Distribusi (Min 60°C)',
                        data: dispatchTemps.length ? dispatchTemps : [63.5],
                        borderColor: '#0d9488',
                        backgroundColor: 'rgba(13, 148, 136, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 10, weight: 'bold' }, boxWidth: 12 }
                    }
                },
                scales: {
                    y: {
                        min: 40,
                        max: 100,
                        ticks: { stepSize: 10, font: { size: 10 } },
                        grid: { color: 'rgba(181, 224, 234, 0.08)' }
                    },
                    x: {
                        ticks: { font: { size: 10 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Grafik Donut Putusan Kelayakan Suhu
    const verdictCounts = @json($verdictCounts);
    const ctxVerdict = document.getElementById('qcVerdictChart');
    if (ctxVerdict) {
        new Chart(ctxVerdict, {
            type: 'doughnut',
            data: {
                labels: ['Lolos (Passed)', 'Bersyarat', 'Tahan (Hold)'],
                datasets: [{
                    data: [verdictCounts.passed || 1, verdictCounts.conditional_pass || 0, verdictCounts.failed_hold || 0],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 10, weight: 'bold' }, boxWidth: 10 }
                    }
                },
                cutout: '68%'
            }
        });
    }
});
</script>
@endpush
