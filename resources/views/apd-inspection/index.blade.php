@extends('layouts.app')

@section('title', 'Pemeriksaan Kepatuhan APD Relawan Dapur')
@section('page_title', 'Pemeriksaan Kepatuhan APD Relawan Dapur')
@section('page_subtitle', 'Formulir ceklis harian Ahli Gizi untuk pemantauan 5 APD & sanitasi 35 relawan dapur per shift')

@section('content')
<div x-data="{
    activeTab: 'all', // 'all', 'form', 'history', 'charts'
    openVerifyModal: false,
    selectedInspection: null,
    totalVolunteers: {{ $defaultVolunteers }},
    volunteersChecked: {{ $defaultVolunteers }},
    apronCount: {{ $defaultVolunteers }},
    hairnetCount: {{ $defaultVolunteers }},
    maskCount: {{ $defaultVolunteers }},
    glovesCount: {{ $defaultVolunteers }},
    shoesCount: {{ $defaultVolunteers }},
    cleanNails: true,
    noJewelry: true,
    handwashReady: true,

    get complianceScore() {
        if (this.volunteersChecked <= 0) return 100;
        const total = (Number(this.apronCount) + Number(this.hairnetCount) + Number(this.maskCount) + Number(this.glovesCount) + Number(this.shoesCount));
        const max = 5 * this.volunteersChecked;
        const base = (total / max) * 100;
        let penalty = 0;
        if (!this.cleanNails) penalty += 3;
        if (!this.noJewelry) penalty += 3;
        if (!this.handwashReady) penalty += 4;
        return Math.max(0, Math.min(100, Math.round((base - penalty) * 10) / 10));
    },

    get scoreStatus() {
        const s = this.complianceScore;
        if (s >= 90) return { label: 'Patuh Penuh SOP (BGN)', color: 'text-emerald-700 bg-emerald-50 border-emerald-200', dot: 'bg-emerald-500' };
        if (s >= 75) return { label: 'Peringatan / Perlu Pengarahan', color: 'text-amber-700 bg-amber-50 border-amber-200', dot: 'bg-amber-500' };
        return { label: 'Pelanggaran Kritis SOP', color: 'text-rose-700 bg-rose-50 border-rose-200', dot: 'bg-rose-500' };
    },

    setAllFull() {
        this.apronCount = this.volunteersChecked;
        this.hairnetCount = this.volunteersChecked;
        this.maskCount = this.volunteersChecked;
        this.glovesCount = this.volunteersChecked;
        this.shoesCount = this.volunteersChecked;
        this.cleanNails = true;
        this.noJewelry = true;
        this.handwashReady = true;
    },

    openVerify(item) {
        this.selectedInspection = item;
        this.openVerifyModal = true;
    }
}">

    <!-- Hero Banner Kepatuhan APD Dapur -->
    <div class="gsap-reveal relative rounded-3xl overflow-hidden bg-gradient-to-r from-[#06204D] via-[#092B66] to-[#041432] text-white p-6 lg:p-8 mb-6 shadow-xl border border-emerald-500/20">
        <div class="ambient-orb w-72 h-72 bg-emerald-500/20 -top-20 -right-10"></div>
        <div class="ambient-orb w-56 h-56 bg-amber-400/15 bottom-0 left-1/3" style="animation-delay: -5s;"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-[11px] font-bold uppercase tracking-widest mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    SOP BGN • Standar Higienitas 35 Relawan Dapur SPPG Sungai Selayur 1
                </div>
                <h2 class="font-display font-extrabold text-2xl lg:text-3xl tracking-tight text-white">
                    Pemeriksaan Kepatuhan 5 APD & Higienitas Dapur
                </h2>
                <p class="text-xs sm:text-sm text-emerald-100/75 mt-1.5 max-w-2xl leading-relaxed">
                    Formulir ceklis harian resmi bagi <strong class="text-white">Ahli Gizi</strong> untuk memeriksa kelengkapan celemek, penutup kepala, masker, sarung tangan steril, dan sepatu tertutup pada <strong class="text-white">35 Relawan Dapur</strong> sebelum proses pemorsian ompreng 1.414 porsi.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('quality-control.index') }}"
                   class="px-4 py-3 rounded-2xl bg-teal-500/20 hover:bg-teal-500/30 border border-teal-400/40 text-teal-200 font-bold text-xs flex items-center gap-2 transition shadow-sm">
                    <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Buka Cek Suhu (HACCP) &rarr;
                </a>
            </div>
        </div>

        <!-- 4 KPI Strip -->
        <div class="relative z-10 grid grid-cols-2 lg:grid-cols-4 gap-3.5 mt-6 pt-6 border-t border-white/10">
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-300/80">Rata-rata Kepatuhan APD</p>
                <p class="font-display font-extrabold text-2xl text-white mt-1">{{ $avgCompliance }}% <span class="text-xs font-normal text-slate-300">Target ≥ 90%</span></p>
            </div>
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-300/80">Kapasitas Relawan Dapur</p>
                <p class="font-display font-extrabold text-2xl text-white mt-1">{{ $defaultVolunteers }} <span class="text-xs font-normal text-slate-300">Relawan / Shift</span></p>
            </div>
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-teal-300/80">Item Wajib APD BGN</p>
                <p class="font-display font-extrabold text-2xl text-emerald-300 mt-1">5 APD <span class="text-xs font-normal text-slate-300">+ 3 Higienitas</span></p>
            </div>
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-rose-300/80">Pelanggaran Kritis</p>
                <p class="font-display font-extrabold text-2xl text-white mt-1">{{ $criticalCount }} <span class="text-xs font-normal text-slate-300">Tercatat</span></p>
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
                    :class="activeTab === 'form' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                + Form Ceklis APD Hari Ini
            </button>
            <button @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                Riwayat Kepatuhan APD ({{ $recentInspections->count() }})
            </button>
            <button @click="activeTab = 'charts'"
                    :class="activeTab === 'charts' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                Grafik Tren & Regulasi SOP
            </button>
        </div>

        @if($shift !== 'all' || $status !== 'all')
            <a href="{{ route('apd-inspection.index') }}" class="px-3.5 py-2 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold hover:bg-rose-100 transition">
                Reset Filter Aktif
            </a>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- SECTION 1: FORMULIR CEKLIS APD (FULL WIDTH - TIDAK SPLIT!) -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'all' || activeTab === 'form'" x-transition class="surface-card rounded-3xl p-6 sm:p-7 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-[11px] font-extrabold uppercase tracking-widest text-emerald-600">Formulir Ceklis Harian Ahli Gizi</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 mt-1">Ceklis Kepatuhan 5 Kelengkapan APD & Higienitas Personel</h3>
                <p class="text-xs text-slate-500">Periksa kepatuhan 35 relawan dapur per shift. Anda dapat mencentang satu per satu atau gunakan tombol cepat.</p>
            </div>
            <button type="button" @click="setAllFull()" class="px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold transition flex items-center gap-2 cursor-pointer border border-emerald-200 shadow-xs shrink-0">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                Tandai Semua 35 Relawan Patuh (100%)
            </button>
        </div>

        <form method="POST" action="{{ route('apd-inspection.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($activeProduction)
                <input type="hidden" name="production_run_id" value="{{ $activeProduction->id }}">
            @endif

            <!-- Baris 1: Tanggal, Shift & Relawan -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Pemeriksaan</label>
                    <input type="date" name="inspection_date" value="{{ $date }}" required
                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none bg-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Shift Kerja Dapur</label>
                    <select name="shift" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none bg-white">
                        <option value="Pagi (05:00 - 11:00)">Pagi (05:00 - 11:00) — Shift Utama</option>
                        <option value="Siang (11:00 - 17:00)">Siang (11:00 - 17:00) — Shift Pembersihan & Persiapan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jumlah Relawan Hadir</label>
                    <input type="number" name="volunteers_checked" x-model.number="volunteersChecked" min="1" max="100" required
                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs font-extrabold focus:border-emerald-500 focus:outline-none text-emerald-700 bg-white">
                    <input type="hidden" name="total_volunteers" x-model="volunteersChecked">
                </div>
            </div>

            <!-- LIVE SCORE INDICATOR GAUGE (LEBAR PENUH) -->
            <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-[#0A2647] to-slate-800 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-md">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-300">Kalkulasi Skor Kepatuhan Real-Time</span>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="font-display font-extrabold text-3xl text-emerald-400" x-text="complianceScore + '%'"></span>
                        <span class="text-xs px-3 py-1 rounded-full font-bold border"
                              :class="scoreStatus.color"
                              x-text="scoreStatus.label"></span>
                    </div>
                </div>
                <div class="text-left sm:text-right text-xs text-slate-300">
                    <p>Standar Minimal SOP BGN: <strong class="text-white">≥ 90%</strong></p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Basis Data: <span x-text="volunteersChecked"></span> Relawan Dapur</p>
                </div>
            </div>

            <!-- 5 KARTU CEKLIS APD WAJIB (GRID LEGA 2 ATAU 3 KOLOM) -->
            <div>
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-3">Checklist 5 Kelengkapan APD Wajib SOP BGN:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    
                    <!-- 1. Celemek / Apron -->
                    <div class="p-4 rounded-2xl border-2 border-slate-200/80 bg-slate-50/50 hover:bg-white transition flex flex-col justify-between">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-base shrink-0">🥼</span>
                            <div>
                                <h5 class="font-bold text-xs text-slate-900">1. Celemek / Apron Kedap Air</h5>
                                <p class="text-[11px] text-slate-500 leading-relaxed">Bersih, kedap air & kuah, menutupi dada hingga lutut.</p>
                            </div>
                        </div>
                        <div class="pt-3 border-t border-slate-200/70 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-600">Relawan Patuh:</span>
                            <div class="flex items-center gap-1.5">
                                <input type="number" name="apron_compliant_count" x-model.number="apronCount" min="0" :max="volunteersChecked" required
                                       class="w-16 px-2.5 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-center bg-white focus:border-emerald-500">
                                <span class="text-xs text-slate-400 font-mono">/ <span x-text="volunteersChecked"></span></span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Penutup Kepala / Hairnet -->
                    <div class="p-4 rounded-2xl border-2 border-slate-200/80 bg-slate-50/50 hover:bg-white transition flex flex-col justify-between">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-base shrink-0">🧢</span>
                            <div>
                                <h5 class="font-bold text-xs text-slate-900">2. Penutup Kepala / Hairnet Rapi</h5>
                                <p class="text-[11px] text-slate-500 leading-relaxed">Menutup seluruh rambut kepala, tidak ada helai terurai.</p>
                            </div>
                        </div>
                        <div class="pt-3 border-t border-slate-200/70 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-600">Relawan Patuh:</span>
                            <div class="flex items-center gap-1.5">
                                <input type="number" name="hairnet_compliant_count" x-model.number="hairnetCount" min="0" :max="volunteersChecked" required
                                       class="w-16 px-2.5 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-center bg-white focus:border-emerald-500">
                                <span class="text-xs text-slate-400 font-mono">/ <span x-text="volunteersChecked"></span></span>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Masker Medis -->
                    <div class="p-4 rounded-2xl border-2 border-slate-200/80 bg-slate-50/50 hover:bg-white transition flex flex-col justify-between">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="w-9 h-9 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-base shrink-0">😷</span>
                            <div>
                                <h5 class="font-bold text-xs text-slate-900">3. Masker Medis / Sanitasi Mulut</h5>
                                <p class="text-[11px] text-slate-500 leading-relaxed">Menutup rapat hidung dan mulut, diganti bila lembap.</p>
                            </div>
                        </div>
                        <div class="pt-3 border-t border-slate-200/70 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-600">Relawan Patuh:</span>
                            <div class="flex items-center gap-1.5">
                                <input type="number" name="mask_compliant_count" x-model.number="maskCount" min="0" :max="volunteersChecked" required
                                       class="w-16 px-2.5 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-center bg-white focus:border-emerald-500">
                                <span class="text-xs text-slate-400 font-mono">/ <span x-text="volunteersChecked"></span></span>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Sarung Tangan Steril -->
                    <div class="p-4 rounded-2xl border-2 border-slate-200/80 bg-slate-50/50 hover:bg-white transition flex flex-col justify-between">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-base shrink-0">🧤</span>
                            <div>
                                <h5 class="font-bold text-xs text-slate-900">4. Sarung Tangan Steril (Food-Grade)</h5>
                                <p class="text-[11px] text-slate-500 leading-relaxed">Wajib saat menyentuh makanan matang & ompreng.</p>
                            </div>
                        </div>
                        <div class="pt-3 border-t border-slate-200/70 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-600">Relawan Patuh:</span>
                            <div class="flex items-center gap-1.5">
                                <input type="number" name="gloves_compliant_count" x-model.number="glovesCount" min="0" :max="volunteersChecked" required
                                       class="w-16 px-2.5 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-center bg-white focus:border-emerald-500">
                                <span class="text-xs text-slate-400 font-mono">/ <span x-text="volunteersChecked"></span></span>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Sepatu Dapur Tertutup -->
                    <div class="p-4 rounded-2xl border-2 border-slate-200/80 bg-slate-50/50 hover:bg-white transition flex flex-col justify-between">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-base shrink-0">👟</span>
                            <div>
                                <h5 class="font-bold text-xs text-slate-900">5. Sepatu Dapur Tertutup (Anti-Slip)</h5>
                                <p class="text-[11px] text-slate-500 leading-relaxed">Sol karet anti-slip tertutup, bukan sandal jepit.</p>
                            </div>
                        </div>
                        <div class="pt-3 border-t border-slate-200/70 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-600">Relawan Patuh:</span>
                            <div class="flex items-center gap-1.5">
                                <input type="number" name="safety_shoes_compliant_count" x-model.number="shoesCount" min="0" :max="volunteersChecked" required
                                       class="w-16 px-2.5 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-center bg-white focus:border-emerald-500">
                                <span class="text-xs text-slate-400 font-mono">/ <span x-text="volunteersChecked"></span></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 3 CHECKLIST HIGIENITAS TAMBAHAN -->
            <div class="p-5 rounded-2xl border border-slate-200 bg-white">
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-3">Checklist Higienitas & Sanitasi Personel:</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 cursor-pointer">
                        <input type="checkbox" name="clean_nails_compliant" value="1" x-model="cleanNails"
                               class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Kuku Bersih & Bebas Kutek</span>
                            <span class="text-[11px] text-slate-500">Kuku pendek terawat (-3% jika gagal)</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 cursor-pointer">
                        <input type="checkbox" name="no_jewelry_compliant" value="1" x-model="noJewelry"
                               class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Bebas Perhiasan / Cincin</span>
                            <span class="text-[11px] text-slate-500">Tanpa gelang & jam tangan (-3% jika gagal)</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 cursor-pointer">
                        <input type="checkbox" name="handwash_facility_ready" value="1" x-model="handwashReady"
                               class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Wastafel & Sabun Mengalir</span>
                            <span class="text-[11px] text-slate-500">Hand sanitizer aktif di dapur (-4% jika gagal)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- CATATAN & FOTO BUKTI -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Temuan Ahli Gizi</label>
                    <textarea name="notes" rows="3" placeholder="Cth: Seluruh 35 relawan tertib memakai APD lengkap. Area pemorsian higienis..."
                              class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs font-medium focus:border-emerald-500 focus:outline-none bg-white"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Unggah Foto Dokumentasi Kepatuhan (Opsional)</label>
                    <input type="file" name="photo" accept="image/*"
                           class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs text-slate-600 focus:border-emerald-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 bg-white">
                    <p class="text-[11px] text-slate-400 mt-1">Dokumentasi kepatuhan 5 APD 35 relawan dapur (JPG, PNG, WEBP)</p>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-7 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Ceklis Kepatuhan APD Dapur
                </button>
            </div>
        </form>
    </div>

    <!-- ========================================== -->
    <!-- SECTION 2: RIWAYAT CEKLIS APD (FULL WIDTH - TIDAK SPLIT!) -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'all' || activeTab === 'history'" x-transition class="surface-card rounded-3xl p-6 sm:p-7 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                    <span class="text-[11px] font-extrabold uppercase tracking-widest text-teal-600">Log Pemeriksaan Higienitas</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 mt-1">Riwayat Pemeriksaan Kepatuhan APD Dapur</h3>
                <p class="text-xs text-slate-500">Rekam jejak pemeriksaan harian 5 APD oleh Ahli Gizi dan verifikasi Kepala Dapur.</p>
            </div>

            <!-- Filter Tanggal & Shift -->
            <form method="GET" action="{{ route('apd-inspection.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="date" name="date" value="{{ $date }}"
                       class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 bg-white">
                <select name="shift" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 bg-white">
                    <option value="all" {{ $shift === 'all' ? 'selected' : '' }}>Semua Shift</option>
                    <option value="Pagi (05:00 - 11:00)" {{ $shift === 'Pagi (05:00 - 11:00)' ? 'selected' : '' }}>Pagi</option>
                    <option value="Siang (11:00 - 17:00)" {{ $shift === 'Siang (11:00 - 17:00)' ? 'selected' : '' }}>Siang</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-teal-600 text-white text-xs font-bold hover:bg-teal-700 transition">
                    Filter
                </button>
            </form>
        </div>

        @if($recentInspections->isEmpty())
            <div class="text-center py-12 px-4 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50">
                <p class="text-xs text-slate-500">Belum ada catatan pemeriksaan APD.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                            <th class="py-3.5 px-4">Tanggal & Shift</th>
                            <th class="py-3.5 px-4">Ahli Gizi (Inspektor)</th>
                            <th class="py-3.5 px-4">Rincian 5 APD (Patuh/Total)</th>
                            <th class="py-3.5 px-4">Kebersihan Personel</th>
                            <th class="py-3.5 px-4">Tingkat Kepatuhan</th>
                            <th class="py-3.5 px-4">Catatan & Tindak Lanjut</th>
                            <th class="py-3.5 px-4 text-right">Verifikasi Ka. Dapur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($recentInspections as $insp)
                            @php
                                $badge = $insp->statusBadge();
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <p class="font-bold text-slate-900">{{ $insp->inspection_date->translatedFormat('d M Y') }}</p>
                                    <span class="text-[11px] text-slate-500">{{ $insp->shift }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-800">{{ $insp->inspector->name }}</p>
                                    <span class="text-[10px] text-slate-500 uppercase">{{ $insp->inspector->role->label() }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap gap-1 text-[11px]">
                                        <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-800 font-bold border border-blue-200" title="Celemek">🥼 {{ $insp->apron_compliant_count }}/{{ $insp->volunteers_checked }}</span>
                                        <span class="px-2 py-0.5 rounded-lg bg-purple-50 text-purple-800 font-bold border border-purple-200" title="Hairnet">🧢 {{ $insp->hairnet_compliant_count }}/{{ $insp->volunteers_checked }}</span>
                                        <span class="px-2 py-0.5 rounded-lg bg-teal-50 text-teal-800 font-bold border border-teal-200" title="Masker">😷 {{ $insp->mask_compliant_count }}/{{ $insp->volunteers_checked }}</span>
                                        <span class="px-2 py-0.5 rounded-lg bg-amber-50 text-amber-800 font-bold border border-amber-200" title="Sarung Tangan">🧤 {{ $insp->gloves_compliant_count }}/{{ $insp->volunteers_checked }}</span>
                                        <span class="px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-800 font-bold border border-indigo-200" title="Sepatu">👟 {{ $insp->safety_shoes_compliant_count }}/{{ $insp->volunteers_checked }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <span class="{{ $insp->clean_nails_compliant ? 'text-emerald-700 font-bold' : 'text-rose-500 font-bold' }}" title="Kuku Bersih">
                                            {{ $insp->clean_nails_compliant ? '✓ Kuku' : '✗ Kuku' }}
                                        </span>
                                        <span class="text-slate-300">•</span>
                                        <span class="{{ $insp->no_jewelry_compliant ? 'text-emerald-700 font-bold' : 'text-rose-500 font-bold' }}" title="Bebas Perhiasan">
                                            {{ $insp->no_jewelry_compliant ? '✓ Aksesoris' : '✗ Aksesoris' }}
                                        </span>
                                        <span class="text-slate-300">•</span>
                                        <span class="{{ $insp->handwash_facility_ready ? 'text-emerald-700 font-bold' : 'text-rose-500 font-bold' }}" title="Wastafel Siap">
                                            {{ $insp->handwash_facility_ready ? '✓ Sabun' : '✗ Sabun' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-display font-extrabold text-sm text-slate-900 block">{{ $insp->compliance_percentage }}%</span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badge['class'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs">
                                    <p class="text-slate-700 truncate text-xs">{{ $insp->notes ?? 'Tidak ada catatan khusus.' }}</p>
                                    @if($insp->corrective_action)
                                        <p class="text-[11px] text-amber-700 font-semibold mt-0.5">Tindak Lanjut: {{ $insp->corrective_action }}</p>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($insp->verified_by_head_chef_id)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ✓ Terverifikasi
                                        </span>
                                    @else
                                        @if(in_array(auth()->user()->role->value, ['admin', 'kepala_dapur']))
                                            <button @click="openVerify({{ Js::from($insp) }})"
                                                    class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-950 text-[11px] font-bold transition cursor-pointer">
                                                Verifikasi Ka. Dapur
                                            </button>
                                        @else
                                            <span class="text-[11px] text-slate-400 font-medium">Menunggu Verifikasi</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- SECTION 3: GRAFIK TREN KEPATUHAN & REGULASI SOP (FULL WIDTH - TIDAK SPLIT!) -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'all' || activeTab === 'charts'" x-transition class="surface-card rounded-3xl p-6 sm:p-7 mb-8">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-200">Tren Telemetri APD</span>
                <h3 class="font-display font-extrabold text-xl text-slate-900 mt-1">Grafik Tren Kepatuhan APD & Protokol Higienitas BGN</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                <h4 class="text-xs font-bold text-slate-800 mb-3">Grafik Kepatuhan APD 14 Hari Terakhir (%)</h4>
                <div class="h-64">
                    <canvas id="apdTrendChart"></canvas>
                </div>
            </div>

            <div class="lg:col-span-4 p-5 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200/80 flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-700 bg-white/80 px-2.5 py-1 rounded-full border border-emerald-200 inline-block mb-3">
                        Protokol SOP BGN
                    </span>
                    <h4 class="font-bold text-slate-900 text-sm mb-2">Standar 35 Relawan Dapur</h4>
                    <ul class="text-xs text-slate-600 space-y-2 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Pemeriksaan Harian:</strong> Dilakukan oleh Ahli Gizi sebelum pemorsian ompreng dimulai.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>5 APD Wajib:</strong> Celemek, hairnet, masker, sarung tangan steril, dan sepatu tertutup.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Target Kelulusan:</strong> Minimal 90% untuk dapat beroperasi tanpa catatan khusus.</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-4 pt-3 border-t border-emerald-200 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-semibold">Audit Suhu Makanan?</span>
                    <a href="{{ route('quality-control.index') }}" class="text-xs font-bold text-emerald-800 hover:underline">
                        Buka Cek Suhu &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL VERIFIKASI TINDAK LANJUT KEPALA DAPUR -->
    <template x-teleport="body">
        <div x-show="openVerifyModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openVerifyModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 my-auto">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-amber-600">Otoritas Kepala Dapur</span>
                        <h3 class="font-display font-extrabold text-base text-slate-900">Verifikasi Hasil Pemeriksaan APD</h3>
                    </div>
                    <button @click="openVerifyModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">✕</button>
                </div>

                <form method="POST" :action="'{{ url('/apd-inspection') }}/' + (selectedInspection ? selectedInspection.id : '') + '/verify'" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                        <p class="text-slate-500">Tanggal: <strong class="text-slate-800" x-text="selectedInspection ? selectedInspection.inspection_date : ''"></strong></p>
                        <p class="text-slate-500">Shift: <strong class="text-slate-800" x-text="selectedInspection ? selectedInspection.shift : ''"></strong></p>
                        <p class="text-slate-500">Skor Kepatuhan: <strong class="text-emerald-700 font-bold" x-text="(selectedInspection ? selectedInspection.compliance_percentage : '') + '%'"></strong></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tindakan Korektif / Catatan Pembinaan Relawan</label>
                        <textarea name="corrective_action" rows="3" required
                                  placeholder="Cth: Telah dilakukan pembinaan langsung kepada relawan di pos pemorsian untuk selalu memakai masker dengan rapat..."
                                  class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:border-amber-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openVerifyModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold shadow-md cursor-pointer">Verifikasi & Simpan</button>
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
    const chartLabels = @json($chartLabels);
    const chartScores = @json($chartScores);

    const ctx = document.getElementById('apdTrendChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels.length ? chartLabels : ['Hari Ini'],
                datasets: [
                    {
                        label: 'Kepatuhan APD (%)',
                        data: chartScores.length ? chartScores : [100],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: '#059669'
                    },
                    {
                        label: 'Standar Minimal SOP (90%)',
                        data: (chartScores.length ? chartScores : [100]).map(() => 90),
                        borderColor: '#f59e0b',
                        borderWidth: 1.5,
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: { font: { size: 10, weight: 'bold' }, boxWidth: 12 }
                    }
                },
                scales: {
                    y: {
                        min: 50,
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
});
</script>
@endpush
