@extends('layouts.app')

@section('title', 'Laporan Resmi & Cetak MBG')
@section('page_title', 'Laporan & Rekapitulasi Operasional MBG')
@section('page_subtitle', 'Dokumen resmi Standar BGN (Badan Gizi Nasional) — Pratinjau langsung, Cetak PDF, Unduh Excel & CSV')

@section('content')
<div class="reports-page space-y-6">
    <style>
        .reports-page input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        .reports-page select,
        .reports-page textarea {
            min-height: 44px;
            font-size: 16px;
        }

        .reports-page button,
        .reports-page a[href] {
            min-height: 44px;
        }

        .reports-page a[href] {
            display: inline-flex;
            align-items: center;
        }
    </style>

    <!-- HERO BANNER PUSAT LAPORAN RESMI BGN -->
    <div class="relative rounded-3xl overflow-hidden bg-[#061F47] text-white p-6 lg:p-8 shadow-sm">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-[11px] font-bold uppercase tracking-widest mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Dokumen Operasional Resmi Badan Gizi Nasional (BGN)
                </div>
                <h2 class="font-display font-extrabold text-2xl lg:text-3xl tracking-tight text-white">
                    Pusat Laporan & Cetak Rekapitulasi MBG
                </h2>
                <p class="text-xs sm:text-sm text-teal-100/75 mt-1.5 max-w-2xl leading-relaxed">
                    Sistem pelaporan terpadu SPPG Sungai Selayur 1. Seluruh rekapitulasi perencanaan gizi, log masak dapur, audit suhu HACCP, kepatuhan APD relawan, serta berita acara serah terima 7 titik sasaran terdokumentasi akurat dan siap cetak/ekspor.
                </p>
            </div>

            <!-- 3 Opsi Aksi Cepat Dokumen -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button
                    type="button"
                    onclick="printReportIframe()"
                    class="px-4 py-3 rounded-2xl bg-[#C8A96A] hover:brightness-95 text-[#061F47] font-extrabold text-sm flex items-center gap-2 transition cursor-pointer"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak / Simpan PDF
                </button>

                <a
                    href="{{ route('reports.export-excel', ['type' => $type, 'date' => $carbonDate->format('Y-m-d')]) }}"
                    class="px-4 py-3 rounded-2xl bg-white text-[#061F47] font-bold text-sm flex items-center gap-2 shadow-sm transition hover:bg-slate-100"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Unduh Excel (.xls)
                </a>

                <a
                    href="{{ route('reports.export-csv', ['type' => $type, 'date' => $carbonDate->format('Y-m-d')]) }}"
                    class="px-4 py-3 rounded-2xl border border-white/40 text-white font-bold text-sm flex items-center gap-2 transition hover:bg-white/10"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh CSV
                </a>
            </div>
        </div>

        <!-- KPI SUMMARY STRIP UNTUK TANGGAL YANG DIPILIH -->
        <div class="relative z-10 grid grid-cols-2 lg:grid-cols-5 gap-3.5 mt-6 pt-6 border-t border-white/10">
            <div class="bg-white/10 border border-white/20 rounded-2xl p-3.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-300/80">Menu Terjadwal</p>
                <p class="font-display font-extrabold text-base text-white mt-0.5 truncate">{{ $menu?->menu_code ?? 'MNU-SS1' }}</p>
                <span class="text-[10px] text-slate-300 truncate block">{{ $menu?->menu_name ?? 'Paket Gizi Standar BGN' }}</span>
            </div>

            <div class="bg-white/10 border border-white/20 rounded-2xl p-3.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-teal-300/80">Realisasi Porsi Masak</p>
                <p class="font-display font-extrabold text-base text-white mt-0.5">{{ number_format($productionRun?->portions_good ?? 0) }} Porsi</p>
                <span class="inline-flex items-center gap-1 text-[10px] text-emerald-200"><span aria-hidden="true">✓</span>Target: {{ number_format($productionRun?->planned_portions ?? ($menu?->target_portions ?? 0)) }}</span>
            </div>

            <div class="bg-white/10 border border-white/20 rounded-2xl p-3.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-300/80">Suhu Masak Inti QC</p>
                <p class="font-display font-extrabold text-base text-amber-300 mt-0.5">{{ $qc?->cooking_core_temp_c ? $qc->cooking_core_temp_c . '°C' : '-' }}</p>
                <span class="text-[10px] text-slate-200">Standar HACCP: ≥ 75°C</span>
            </div>

            <div class="bg-white/10 border border-white/20 rounded-2xl p-3.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-300/80">Distribusi 7 Titik</p>
                <p class="font-display font-extrabold text-base text-white mt-0.5">{{ $deliveries->where('status', 'confirmed_clean')->count() }}/{{ $deliveries->count() }} Tuntas</p>
                <span class="inline-flex items-center gap-1 text-[10px] text-slate-200"><span aria-hidden="true">✓</span>{{ number_format($deliveries->sum('dispatched_portions')) }} Porsi Terkirim</span>
            </div>

            <div class="bg-white/10 border border-white/20 rounded-2xl p-3.5 col-span-2 lg:col-span-1">
                <p class="text-[10px] font-bold uppercase tracking-wider text-rose-300/80">Aduan / Tiket Keluhan</p>
                <p class="font-display font-extrabold text-base text-white mt-0.5">{{ $complaints->count() }} Kasus</p>
                <span class="inline-flex items-center gap-1 text-[10px] text-slate-200"><span aria-hidden="true">✓</span>{{ $complaints->where('status', 'resolved')->count() }} Terselesaikan</span>
            </div>
        </div>
    </div>

    <!-- PANEL FILTER & PENGATURAN JENIS LAPORAN (BERSIH, TANPA POP-UP!) -->
    <div class="surface-card rounded-3xl p-6 border border-slate-200/80 shadow-soft">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <!-- Pilihan Jenis Laporan (6 Jenis Lengkap Sesuai SOP BGN) -->
            <div class="md:col-span-5">
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Jenis laporan</label>
                <select name="type" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-bold bg-white text-slate-800 focus:border-emerald-500 focus:outline-none">
                    <option value="operasional" {{ $type === 'operasional' ? 'selected' : '' }}>1. Laporan Harian Terpadu (All-in-One Standar BGN)</option>
                    <option value="stok_selisih" {{ $type === 'stok_selisih' ? 'selected' : '' }}>2. Rekapitulasi Realisasi & Selisih Porsi Dapur</option>
                    <option value="qc_apd" {{ $type === 'qc_apd' ? 'selected' : '' }}>3. Audit Suhu Termal (HACCP) & Kepatuhan APD Relawan</option>
                    <option value="distribusi_organoleptik" {{ $type === 'distribusi_organoleptik' ? 'selected' : '' }}>4. Distribusi Porsi & Uji Organoleptik 7 Titik Sasaran</option>
                    <option value="tiket_keluhan" {{ $type === 'tiket_keluhan' ? 'selected' : '' }}>5. Rekapitulasi Aduan & Penanganan Tiket Keluhan</option>
                    <option value="kinerja_role" {{ $type === 'kinerja_role' ? 'selected' : '' }}>6. Laporan Evaluasi Kinerja & Akuntabilitas 7 Peran (SOP BGN)</option>
                </select>
            </div>

            <!-- Pemilih Tanggal Operasional -->
            <div class="md:col-span-3">
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Tanggal operasional</label>
                <input
                    type="date"
                    name="date"
                    value="{{ $carbonDate->format('Y-m-d') }}"
                    onchange="this.form.submit()"
                    class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-bold bg-white text-slate-800 focus:border-emerald-500 focus:outline-none"
                >
            </div>

            <!-- Tombol Aksi Terapkan Filter -->
            <div class="md:col-span-4 flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 py-2.5 px-4 rounded-2xl bg-[#061F47] hover:bg-[#0B2B5B] text-white font-bold text-sm shadow-sm flex items-center justify-center gap-1.5 transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Tampilkan Dokumen
                </button>

                <a
                    href="{{ route('reports.index') }}"
                    class="inline-flex items-center py-2.5 px-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition"
                    title="Kembalikan ke hari ini"
                >
                    Hari Ini
                </a>
            </div>
        </form>
    </div>

    @if($type === 'kinerja_role')
    <!-- ==================== TAMPILAN INTERAKTIF EVALUASI KINERJA 7 PERAN ==================== -->
    <div class="surface-card rounded-3xl p-6 border border-slate-200/80 shadow-soft space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800" aria-hidden="true">✓</span>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600">Matriks Akuntabilitas 7 Peran</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 mt-0.5">
                    Evaluasi Kinerja 7 Peran Pengguna SPPG Sungai Selayur 1
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                    Audit realisasi tugas pokok, kepatuhan SOP Badan Gizi Nasional (BGN), dan status akuntabilitas hari {{ $carbonDate->translatedFormat('l, d F Y') }}.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                    Standar SOP: BGN No. 01/2026
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @foreach($rolePerformance as $rk => $r)
                <div class="p-5 rounded-2xl border border-slate-200/80 bg-white hover:border-emerald-400 hover:shadow-md transition">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 font-extrabold text-sm flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($rk, 0, 2)) }}
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-black text-sm text-slate-900">{{ $r['role_name'] }}</h4>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 font-mono">{{ $rk }}</span>
                                </div>
                                <p class="text-xs font-bold text-emerald-700 mt-0.5">{{ $r['personnel'] }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <span aria-hidden="true">✓</span>
                                {{ $r['status'] }}
                            </span>
                            <span class="px-2.5 py-1 rounded-xl bg-slate-900 text-white font-mono font-black text-xs">
                                Skor: {{ $r['score'] }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 mt-3 pt-1 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider block">Tugas Pokok & Modul Wewenang</span>
                            <span class="font-medium text-slate-800 mt-0.5 block leading-relaxed">{{ $r['primary_task'] }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider block">Metrik Realisasi Hari Ini</span>
                            <span class="font-bold text-slate-900 mt-0.5 block font-mono text-[11px] leading-relaxed">{{ $r['metric_output'] }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-200/60">
                            <span class="text-[10px] font-bold uppercase text-emerald-700 tracking-wider block">Indikator Kepatuhan SOP BGN</span>
                            <span class="font-bold text-emerald-900 mt-0.5 block leading-relaxed">{{ $r['compliance_sop'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- AREA PRATINJAU DOKUMEN CETAK INTERAKTIF DALAM SISTEM (TETAP DI SINI, TANPA PINDAH TAB) -->
    <div class="surface-card rounded-3xl p-6 border border-slate-200/80 shadow-soft">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 mb-4 border-b border-slate-100">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600">Dokumen Siap Cetak (Pratinjau Resmi BGN)</span>
                <h3 class="font-display font-extrabold text-lg text-slate-900 mt-0.5">
                    @if($type === 'kinerja_role')
                        Laporan Evaluasi Kinerja & Akuntabilitas 7 Peran — {{ $carbonDate->translatedFormat('d F Y') }}
                    @else
                        Lembar Kerja Operasional Harian — Tanggal {{ $carbonDate->translatedFormat('d F Y') }}
                    @endif
                </h3>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs text-slate-500 hidden md:inline">Pratinjau Akurat Format Kertas A4:</span>
                <button
                    type="button"
                    onclick="printReportIframe()"
                    class="px-4 py-2 rounded-xl bg-[#C8A96A] hover:brightness-95 text-[#061F47] font-extrabold text-sm shadow-sm flex items-center gap-1.5 transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Dokumen
                </button>
            </div>
        </div>

        <!-- Frame Dokumen Cetak Harian Resmi -->
        <div class="relative w-full rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 p-2 sm:p-4">
            <iframe
                id="reportDocumentIframe"
                src="{{ route('reports.print-daily', ['date' => $carbonDate->format('Y-m-d'), 'type' => $type]) }}"
                class="w-full h-[850px] bg-white rounded-xl shadow border border-slate-200/80"
                title="Pratinjau Dokumen Laporan Resmi SPPG"
            ></iframe>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function printReportIframe() {
        const frame = document.getElementById('reportDocumentIframe');
        if (frame && frame.contentWindow) {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        } else {
            window.print();
        }
    }
</script>
@endpush
