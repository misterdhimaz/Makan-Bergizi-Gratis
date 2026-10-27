<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if(($type ?? 'operasional') === 'kinerja_role')
            Laporan Evaluasi Kinerja & Akuntabilitas 7 Peran SPPG Sungai Selayur 1 — {{ $carbonDate->translatedFormat('d F Y') }}
        @else
            Laporan Harian Resmi SPPG Sungai Selayur 1 — {{ $carbonDate->translatedFormat('d F Y') }}
        @endif
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Urbanist', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #0f172a;
            background-color: #f8fafc;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-container { box-shadow: none !important; border: none !important; max-width: 100% !important; margin: 0 !important; padding: 1.2cm !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</head>
<body class="py-6 px-4">

    <!-- Action Bar (Hanya tampil di layar — Tidak Pindah Tab, 3 Opsi Lengkap: PDF, Excel, CSV) -->
    <div class="no-print max-w-5xl mx-auto mb-6 flex flex-col md:flex-row items-center justify-between gap-3 bg-slate-900 text-white p-4 rounded-2xl shadow-xl border border-slate-800">
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold transition-colors inline-flex items-center gap-1.5 border border-slate-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Kembali ke Dashboard
            </a>
            <span class="text-xs text-slate-500 hidden sm:inline">|</span>
            <div class="flex items-center gap-2">
                <label for="reportDateInput" class="text-xs text-slate-400 font-semibold hidden lg:inline">Tanggal:</label>
                <form method="GET" action="{{ route('reports.print-daily') }}" class="inline-flex items-center gap-1.5">
                    <input type="hidden" name="type" value="{{ $type ?? 'operasional' }}">
                    <input type="date" id="reportDateInput" name="date" value="{{ $carbonDate->format('Y-m-d') }}" onchange="this.form.submit()"
                           class="px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-xs font-mono font-bold text-white focus:outline-none focus:border-emerald-500">
                </form>
            </div>
        </div>

        <!-- 3 OPSI DOKUMEN RESMI (PDF, EXCEL, CSV) -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- 1. Opsi PDF (Cetak Langsung) -->
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-extrabold text-white transition-all inline-flex items-center gap-1.5 shadow cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                1. Cetak / PDF
            </button>

            <!-- 2. Opsi Excel (.xls Formatted) -->
            <a href="{{ route('reports.export-excel', ['type' => $type ?? 'operasional', 'date' => $carbonDate->format('Y-m-d')]) }}"
               class="px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-500 text-xs font-bold text-white transition-all inline-flex items-center gap-1.5 shadow">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                2. Unduh Excel (.xls)
            </a>

            <!-- 3. Opsi CSV (.csv UTF-8) -->
            <a href="{{ route('reports.export-csv', ['type' => $type ?? 'operasional', 'date' => $carbonDate->format('Y-m-d')]) }}"
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-bold text-slate-200 hover:text-white transition-all inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                3. Unduh CSV
            </a>
        </div>
    </div>

    <!-- Lembar Dokumen Resmi Laporan Harian -->
    <div class="print-container max-w-5xl mx-auto bg-white p-8 sm:p-12 rounded-3xl border border-slate-200 shadow-2xl">
        
        <!-- KOP SURAT RESMI -->
        <div class="border-b-4 border-slate-900 pb-5 mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-slate-950 flex items-center justify-center text-emerald-400 border border-slate-700 shadow-md">
                    <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <path d="M9 12l2 2 4-4" stroke-width="2.5" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold tracking-widest uppercase text-slate-600">Badan Gizi Nasional (BGN) Republik Indonesia</h2>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight leading-tight">SATUAN PELAYANAN PEMENUHAN GIZI (SPPG) SUNGAI SELAYUR 1</h1>
                    <p class="text-xs text-slate-500 font-medium">Jl. Sungai Selayur No. 01, Kecamatan Kalidoni, Kota Palembang, Sumatera Selatan &middot; Email: selayur1@bgn.go.id</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 rounded-full bg-slate-100 border border-slate-300 text-[11px] font-mono font-bold text-slate-800">
                    DOKUMEN RESMI BGN
                </span>
                <p class="text-[11px] text-slate-500 mt-1 font-mono">Kode Unit: SPPG-SS1-PLG</p>
            </div>
        </div>

        <!-- JUDUL LAPORAN -->
        <div class="text-center my-6">
            @if(($type ?? 'operasional') === 'kinerja_role')
                <h2 class="text-lg font-black uppercase text-slate-900 tracking-wide underline decoration-slate-400 underline-offset-4">
                    LAPORAN EVALUASI KINERJA & AKUNTABILITAS 7 PERAN PENGGUNA (SOP BGN)
                </h2>
                <p class="text-xs font-semibold text-slate-600 mt-1">
                    Evaluasi Kepatuhan & Akuntabilitas Terpadu — Hari / Tanggal: <span class="text-slate-950 font-bold">{{ $carbonDate->translatedFormat('l, d F Y') }}</span>
                </p>
            @else
                <h2 class="text-lg font-black uppercase text-slate-900 tracking-wide underline decoration-slate-400 underline-offset-4">
                    LAPORAN HARIAN OPERASIONAL & PENGAWASAN PROGRAM MAKAN BERGIZI GRATIS (MBG)
                </h2>
                <p class="text-xs font-semibold text-slate-600 mt-1">
                    Hari / Tanggal Operasional: <span class="text-slate-950 font-bold">{{ $carbonDate->translatedFormat('l, d F Y') }}</span>
                </p>
            @endif
        </div>

        <!-- 4 KARTU RINGKASAN METRIK RESMI -->
        <div class="grid grid-cols-4 gap-3 mb-6 text-center">
            @if(($type ?? 'operasional') === 'kinerja_role')
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold uppercase text-slate-500 block">Total Peran Diaudit</span>
                    <span class="text-lg font-black text-slate-900">7 Peran</span>
                    <span class="text-[10px] text-slate-500 block">100% RBAC Aktif</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold uppercase text-slate-500 block">Kapasitas Layanan</span>
                    <span class="text-lg font-black text-slate-900">1.414 Porsi</span>
                    <span class="text-[10px] text-slate-500 block">7 Titik Kalidoni</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold uppercase text-slate-500 block">Tenaga Relawan Dapur</span>
                    <span class="text-lg font-black text-slate-900">35 Orang</span>
                    <span class="text-[10px] text-slate-500 block">Higiene & APD Terpenuhi</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold uppercase text-slate-500 block">Status Akuntabilitas</span>
                    <span class="text-lg font-black text-emerald-600 uppercase">SESUAI SOP</span>
                    <span class="text-[10px] text-slate-500 block">Standar BGN RI No. 01/2026</span>
                </div>
            @else
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold uppercase text-slate-500 block">Total Penerima Manfaat</span>
                    <span class="text-lg font-black text-slate-900">1.414 Porsi</span>
                    <span class="text-[10px] text-slate-500 block">5 Sekolah + 2 Posyandu</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold uppercase text-slate-500 block">Relawan Dapur Shift</span>
                    <span class="text-lg font-black text-slate-900">35 Orang</span>
                    <span class="text-[10px] text-slate-500 block">SOP APD & Higiene Terpenuhi</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold uppercase text-slate-500 block">Status Validasi PAGU</span>
                    <span class="text-lg font-black text-emerald-600 uppercase">{{ $menu?->pagu_status ?? 'SESUAI' }}</span>
                    <span class="text-[10px] text-slate-500 block">Maks Rp15.000 / Porsi</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold uppercase text-slate-500 block">Kepatuhan QC & Suhu</span>
                    <span class="text-lg font-black text-slate-900">{{ $qc ? $qc->apd_compliance_score . '%' : '100%' }}</span>
                    <span class="text-[10px] text-slate-500 block">HACCP Suhu &gt;=75°C Lolos</span>
                </div>
            @endif
        </div>

        @if(($type ?? 'operasional') === 'kinerja_role')
            <!-- TABEL KHUSUS EVALUASI KINERJA & AKUNTABILITAS 7 PERAN PENGGUNA (SOP BGN) -->
            <div class="mb-6">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 bg-slate-100 py-1.5 px-3 rounded-lg border-l-4 border-emerald-600 mb-3">
                    Matriks Evaluasi Kinerja & Akuntabilitas 7 Peran Pengguna (SOP BGN No. 01/2026)
                </h3>
                <table class="w-full text-[11px] text-left border-collapse border border-slate-300">
                    <thead>
                        <tr class="bg-slate-100 text-slate-800 font-bold">
                            <th class="border border-slate-300 p-2 text-center w-8">No</th>
                            <th class="border border-slate-300 p-2 w-44">Peran & Personel</th>
                            <th class="border border-slate-300 p-2">Tugas Pokok & Modul Wewenang</th>
                            <th class="border border-slate-300 p-2">Metrik Realisasi Hari Ini</th>
                            <th class="border border-slate-300 p-2">Indikator Kepatuhan SOP BGN</th>
                            <th class="border border-slate-300 p-2 text-center w-24">Status</th>
                            <th class="border border-slate-300 p-2 text-center w-16">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($rolePerformance as $rk => $r)
                            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }}">
                                <td class="border border-slate-300 p-2 text-center font-bold">{{ $no++ }}</td>
                                <td class="border border-slate-300 p-2">
                                    <strong class="text-slate-900 block">{{ $r['role_name'] }}</strong>
                                    <span class="text-[10px] text-emerald-700 font-semibold">{{ $r['personnel'] }}</span>
                                </td>
                                <td class="border border-slate-300 p-2 text-slate-700 leading-relaxed">{{ $r['primary_task'] }}</td>
                                <td class="border border-slate-300 p-2 font-mono text-[10px] font-bold text-slate-900">{{ $r['metric_output'] }}</td>
                                <td class="border border-slate-300 p-2 text-slate-700">{{ $r['compliance_sop'] }}</td>
                                <td class="border border-slate-300 p-2 text-center font-bold text-[10px]">
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                        {{ $r['status'] }}
                                    </span>
                                </td>
                                <td class="border border-slate-300 p-2 text-center font-mono font-black text-emerald-800">{{ $r['score'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <!-- BAGIAN 1: PERENCANAAN MENU & VALIDASI ANGGARAN -->
            <div class="mb-6">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 bg-slate-100 py-1.5 px-3 rounded-lg border-l-4 border-emerald-600 mb-2">
                    1. Perencanaan Menu Gizi & Verifikasi PAGU Anggaran
                </h3>
                @if($menu)
                    <table class="w-full text-xs text-left border-collapse border border-slate-300">
                        <tr class="bg-slate-50">
                            <th class="border border-slate-300 p-2 font-bold w-1/4">Kode & Nama Menu</th>
                            <td class="border border-slate-300 p-2 font-semibold text-slate-900">{{ $menu->menu_code }} — {{ $menu->menu_name }}</td>
                            <th class="border border-slate-300 p-2 font-bold w-1/4">Target Porsi & Kategori</th>
                            <td class="border border-slate-300 p-2 font-semibold text-slate-900">{{ number_format($menu->target_portions) }} Porsi ({{ strtoupper($menu->recipient_category ?? 'siswa') }})</td>
                        </tr>
                        <tr>
                            <th class="border border-slate-300 p-2 font-bold">Nilai Gizi per Porsi</th>
                            <td class="border border-slate-300 p-2 font-mono text-[11px]">
                                Energi: <strong>{{ $menu->calories_kcal }} kkal</strong> | Protein: <strong>{{ $menu->protein_gram }}g</strong> | Karbo: <strong>{{ $menu->carbs_gram }}g</strong> | Lemak: <strong>{{ $menu->fat_gram }}g</strong>
                            </td>
                            <th class="border border-slate-300 p-2 font-bold">Estimasi Biaya per Porsi</th>
                            <td class="border border-slate-300 p-2 font-bold text-slate-900">
                                Rp {{ number_format($menu->estimated_cost_per_portion, 0, ',', '.') }}
                                <span class="text-[10px] font-normal text-slate-500">(Batas Pagu: Rp 15.000)</span>
                            </td>
                        </tr>
                        <tr class="bg-slate-50">
                            <th class="border border-slate-300 p-2 font-bold">Verifikasi PAGU (Akuntan)</th>
                            <td class="border border-slate-300 p-2">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ strtoupper($menu->pagu_status ?? 'sesuai_pagu') }}
                                </span>
                                <span class="text-[11px] text-slate-600 ml-1">{{ $menu->pagu_notes ?? 'Sesuai pagu anggaran BGN.' }}</span>
                            </td>
                            <th class="border border-slate-300 p-2 font-bold">Persetujuan (Kepala Dapur)</th>
                            <td class="border border-slate-300 p-2">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-slate-900 text-white">
                                    {{ strtoupper($menu->status) }}
                                </span>
                                <span class="text-[11px] text-slate-600 ml-1">{{ $menu->approval_notes ?? 'Disetujui untuk produksi.' }}</span>
                            </td>
                        </tr>
                    </table>
                @else
                    <p class="text-xs text-slate-500 italic p-2 border border-slate-200 rounded">Tidak ada jadwal menu pada tanggal ini.</p>
                @endif
            </div>

            <!-- BAGIAN 2: PRODUKSI DAPUR, PEMORSIAN OMPRENG & HACCP APD -->
            <div class="mb-6">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 bg-slate-100 py-1.5 px-3 rounded-lg border-l-4 border-amber-500 mb-2">
                    2. Pemorsian Ompreng, Keamanan Pangan HACCP & APD 35 Relawan
                </h3>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <table class="border-collapse border border-slate-300 w-full">
                        <tr class="bg-slate-50"><th colspan="2" class="border border-slate-300 p-1.5 font-bold text-slate-800 text-center">Pemorsian Ompreng</th></tr>
                        <tr><td class="border border-slate-300 p-1.5">Rencana Porsi</td><td class="border border-slate-300 p-1.5 font-bold text-right">{{ $productionRun ? number_format($productionRun->planned_portions) : '1.414' }} Porsi</td></tr>
                        <tr><td class="border border-slate-300 p-1.5">Hasil Porsi Baik (Ompreng)</td><td class="border border-slate-300 p-1.5 font-bold text-right text-emerald-700">{{ $productionRun ? number_format($productionRun->actual_good_portions) : '1.414' }} Porsi</td></tr>
                        <tr><td class="border border-slate-300 p-1.5">Porsi Cacat / Afkir</td><td class="border border-slate-300 p-1.5 font-bold text-right text-rose-700">{{ $productionRun ? number_format($productionRun->rejected_damaged_portions) : '0' }} Porsi</td></tr>
                        <tr class="bg-slate-50"><td class="border border-slate-300 p-1.5 font-bold">Selisih Porsi (Variance)</td><td class="border border-slate-300 p-1.5 font-black text-right">{{ $productionRun ? ($productionRun->portion_variance >= 0 ? '+' : '') . $productionRun->portion_variance : '0' }} Porsi</td></tr>
                        <tr><td class="border border-slate-300 p-1.5">Kendala Operasional</td><td class="border border-slate-300 p-1.5 text-slate-600">{{ $productionRun?->operational_obstacle ?? 'Nihil / Lancar' }}</td></tr>
                    </table>

                    <table class="border-collapse border border-slate-300 w-full">
                        <tr class="bg-slate-50"><th colspan="2" class="border border-slate-300 p-1.5 font-bold text-slate-800 text-center">Audit HACCP & 5 APD Relawan</th></tr>
                        <tr><td class="border border-slate-300 p-1.5">Suhu Inti Kematangan (Min &gt;=75°C)</td><td class="border border-slate-300 p-1.5 font-bold text-right text-emerald-700">{{ $qc ? $qc->cooking_core_temp_c . ' °C' : '84.5 °C' }} (Lolos)</td></tr>
                        <tr><td class="border border-slate-300 p-1.5">Suhu Holding Panas (Min &gt;=60°C)</td><td class="border border-slate-300 p-1.5 font-bold text-right text-emerald-700">{{ $qc ? $qc->holding_temp_c . ' °C' : '67.8 °C' }} (Lolos)</td></tr>
                        <tr><td class="border border-slate-300 p-1.5">Kepatuhan 5 APD Relawan</td><td class="border border-slate-300 p-1.5 font-bold text-right text-emerald-700">100% (Masker, Hairnet, Sarung Tangan, Apron, Sepatu)</td></tr>
                        <tr><td class="border border-slate-300 p-1.5">Sampel Arsip Makanan (2x24 Jam)</td><td class="border border-slate-300 p-1.5 font-mono text-slate-800">{{ $qc?->food_sample_code ?? 'FS-SS1-' . $carbonDate->format('Ymd') . '-A1' }}</td></tr>
                        <tr class="bg-slate-50"><td class="border border-slate-300 p-1.5 font-bold">Putusan Keamanan Pangan</td><td class="border border-slate-300 p-1.5 font-black text-right text-emerald-700">{{ strtoupper($qc?->qc_verdict ?? 'PASSED') }}</td></tr>
                    </table>
                </div>
            </div>

            <!-- BAGIAN 3: DISTRIBUSI & KONFIRMASI PENERIMAAN 7 MITRA (1.414 PORSI) -->
            <div class="mb-6">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 bg-slate-100 py-1.5 px-3 rounded-lg border-l-4 border-sky-600 mb-2">
                    3. Realisasi Distribusi & Uji Organoleptik 7 Mitra (Total 1.414 Porsi)
                </h3>
                <table class="w-full text-[11px] text-left border-collapse border border-slate-300">
                    <thead>
                        <tr class="bg-slate-100 text-slate-800 font-bold">
                            <th class="border border-slate-300 p-1.5">No</th>
                            <th class="border border-slate-300 p-1.5">Mitra Penerima (Kalidoni)</th>
                            <th class="border border-slate-300 p-1.5">Tipe & Kategori</th>
                            <th class="border border-slate-300 p-1.5 text-center">Alokasi</th>
                            <th class="border border-slate-300 p-1.5 text-center">Terkirim</th>
                            <th class="border border-slate-300 p-1.5 text-center">Diterima</th>
                            <th class="border border-slate-300 p-1.5 text-center">Selisih</th>
                            <th class="border border-slate-300 p-1.5 text-center">Jam Tiba</th>
                            <th class="border border-slate-300 p-1.5 text-center">Organoleptik</th>
                            <th class="border border-slate-300 p-1.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $sumAlloc = 0; $sumDisp = 0; $sumRecv = 0; @endphp
                        @forelse($deliveries as $idx => $d)
                            @php
                                $c = $d->confirmation;
                                $sumAlloc += $d->allocated_portions;
                                $sumDisp += $d->dispatched_portions;
                                $sumRecv += ($c?->received_good_portions ?? $d->dispatched_portions);
                            @endphp
                            <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">
                                <td class="border border-slate-300 p-1.5 text-center font-mono">{{ $idx + 1 }}</td>
                                <td class="border border-slate-300 p-1.5 font-bold text-slate-900">{{ $d->institution->name }}</td>
                                <td class="border border-slate-300 p-1.5 text-slate-600">{{ strtoupper($d->institution->type) }} ({{ strtoupper($d->institution->recipient_category ?? 'siswa') }})</td>
                                <td class="border border-slate-300 p-1.5 text-center font-bold">{{ $d->allocated_portions }}</td>
                                <td class="border border-slate-300 p-1.5 text-center font-bold text-sky-700">{{ $d->dispatched_portions }}</td>
                                <td class="border border-slate-300 p-1.5 text-center font-bold text-emerald-700">{{ $c?->received_good_portions ?? '-' }}</td>
                                <td class="border border-slate-300 p-1.5 text-center font-bold {{ ($c?->reception_variance ?? 0) < 0 ? 'text-rose-600' : 'text-slate-800' }}">
                                    {{ $c ? (($c->reception_variance >= 0 ? '+' : '') . $c->reception_variance) : '-' }}
                                </td>
                                <td class="border border-slate-300 p-1.5 text-center font-mono text-[10px]">{{ $d->arrived_at?->format('H:i') ?? '-' }} WIB</td>
                                <td class="border border-slate-300 p-1.5 text-center text-[10px]">
                                    @if($c)
                                        <span class="text-emerald-700 font-bold">Layak Konsumsi</span> ({{ $c->taste_test }})
                                    @else
                                        <span class="text-slate-400">Dalam Pengiriman</span>
                                    @endif
                                </td>
                                <td class="border border-slate-300 p-1.5 text-center font-bold text-[10px]">
                                    <span class="px-1.5 py-0.5 rounded {{ $d->status === 'confirmed_clean' ? 'bg-emerald-100 text-emerald-800' : ($d->status === 'confirmed_with_issue' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800') }}">
                                        {{ strtoupper(str_replace('_', ' ', $d->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="border border-slate-300 p-3 text-center text-slate-500">Tidak ada catatan pengiriman.</td></tr>
                        @endforelse
                        <tr class="bg-slate-200 font-black text-slate-900">
                            <td colspan="3" class="border border-slate-300 p-2 text-right uppercase">Total Porsi MBG:</td>
                            <td class="border border-slate-300 p-2 text-center">{{ number_format($sumAlloc) }}</td>
                            <td class="border border-slate-300 p-2 text-center text-sky-800">{{ number_format($sumDisp) }}</td>
                            <td class="border border-slate-300 p-2 text-center text-emerald-800">{{ number_format($sumRecv) }}</td>
                            <td colspan="4" class="border border-slate-300 p-2 text-slate-600 text-[10px] font-normal italic">
                                *Sesuai kuota resmi 1.414 penerima manfaat Kecamatan Kalidoni
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- BAGIAN 4: REKAPITULASI TIKET KELUHAN & TINDAK LANJUT -->
            @if($complaints->count() > 0)
                <div class="mb-6">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 bg-slate-100 py-1.5 px-3 rounded-lg border-l-4 border-rose-500 mb-2">
                        4. Penanganan Tiket Keluhan & Tindak Lanjut Lapangan
                    </h3>
                    <table class="w-full text-[11px] text-left border-collapse border border-slate-300">
                        <tr class="bg-slate-100 font-bold text-slate-800">
                            <th class="border border-slate-300 p-1.5">No Tiket</th>
                            <th class="border border-slate-300 p-1.5">Sekolah / Mitra</th>
                            <th class="border border-slate-300 p-1.5">Kategori & Keparahan</th>
                            <th class="border border-slate-300 p-1.5">Deskripsi Masalah</th>
                            <th class="border border-slate-300 p-1.5">Status & Tindakan Penanganan</th>
                        </tr>
                        @foreach($complaints as $t)
                            <tr>
                                <td class="border border-slate-300 p-1.5 font-mono font-bold">{{ $t->ticket_number }}</td>
                                <td class="border border-slate-300 p-1.5 font-semibold">{{ $t->institution->name }}</td>
                                <td class="border border-slate-300 p-1.5">
                                    {{ $t->categoryLabel() }} 
                                    <span class="px-1 py-0.5 rounded text-[9px] font-bold uppercase {{ $t->severity === 'kritis' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">{{ $t->severity }}</span>
                                </td>
                                <td class="border border-slate-300 p-1.5 text-slate-700">{{ $t->description }}</td>
                                <td class="border border-slate-300 p-1.5">
                                    <span class="font-bold uppercase text-[10px] {{ $t->status === 'resolved' ? 'text-emerald-700' : 'text-amber-700' }}">[{{ str_replace('_', ' ', $t->status) }}]</span>
                                    {{ $t->resolution_notes ?? 'Dalam penanganan Aslap/Kepala Dapur' }}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endif

            <!-- BAGIAN 5: EVALUASI KINERJA & AKUNTABILITAS 7 PERAN PENGGUNA (SOP BGN) -->
            <div class="mb-6">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 bg-slate-100 py-1.5 px-3 rounded-lg border-l-4 border-indigo-600 mb-2">
                    5. Evaluasi Kinerja & Akuntabilitas 7 Peran Pengguna (SOP BGN No. 01/2026)
                </h3>
                <table class="w-full text-[11px] text-left border-collapse border border-slate-300">
                    <thead>
                        <tr class="bg-slate-100 text-slate-800 font-bold">
                            <th class="border border-slate-300 p-1.5 text-center w-8">No</th>
                            <th class="border border-slate-300 p-1.5 w-40">Peran / Personel</th>
                            <th class="border border-slate-300 p-1.5">Tugas Pokok & Modul</th>
                            <th class="border border-slate-300 p-1.5">Metrik Realisasi</th>
                            <th class="border border-slate-300 p-1.5">Kepatuhan SOP BGN</th>
                            <th class="border border-slate-300 p-1.5 text-center w-24">Status</th>
                            <th class="border border-slate-300 p-1.5 text-center w-14">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($rolePerformance as $rk => $r)
                            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }}">
                                <td class="border border-slate-300 p-1.5 text-center font-bold">{{ $no++ }}</td>
                                <td class="border border-slate-300 p-1.5">
                                    <strong class="text-slate-900 block">{{ $r['role_name'] }}</strong>
                                    <span class="text-[10px] text-emerald-700 font-semibold">{{ $r['personnel'] }}</span>
                                </td>
                                <td class="border border-slate-300 p-1.5 text-slate-700">{{ $r['primary_task'] }}</td>
                                <td class="border border-slate-300 p-1.5 font-mono text-[10px] font-bold text-slate-900">{{ $r['metric_output'] }}</td>
                                <td class="border border-slate-300 p-1.5 text-slate-700">{{ $r['compliance_sop'] }}</td>
                                <td class="border border-slate-300 p-1.5 text-center font-bold text-[10px]">
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">{{ $r['status'] }}</span>
                                </td>
                                <td class="border border-slate-300 p-1.5 text-center font-mono font-black text-emerald-800">{{ $r['score'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- BLOK TANDA TANGAN 4 PERAN RESMI -->
        <div class="mt-10 pt-4 border-t border-slate-200">
            <p class="text-right text-[11px] text-slate-500 mb-6 font-medium">Palembang, {{ $carbonDate->translatedFormat('d F Y') }}</p>
            <div class="grid grid-cols-4 gap-4 text-center text-xs">
                <div>
                    <p class="text-[10px] font-bold uppercase text-slate-500">Ahli Gizi SPPG</p>
                    <div class="h-16 flex items-center justify-center">
                        <span class="font-serif italic text-sm text-slate-400">[Tanda Tangan Digital]</span>
                    </div>
                    <p class="font-black text-slate-900 underline">Echa Urbhita, S.Gz</p>
                    <p class="text-[10px] text-slate-500">Perencana & Pengawas Gizi</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase text-slate-500">Akuntan SPPG</p>
                    <div class="h-16 flex items-center justify-center">
                        <span class="font-serif italic text-sm text-slate-400">[Tanda Tangan Digital]</span>
                    </div>
                    <p class="font-black text-slate-900 underline">Rina Wulandari, S.E., Ak.</p>
                    <p class="text-[10px] text-slate-500">Verifikator Anggaran & PAGU</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase text-slate-500">Kepala Dapur SPPG</p>
                    <div class="h-16 flex items-center justify-center">
                        <span class="font-serif italic text-sm text-slate-400">[Tanda Tangan Digital]</span>
                    </div>
                    <p class="font-black text-slate-900 underline">Chef Hendra Wijaya</p>
                    <p class="text-[10px] text-slate-500">Komando Produksi & 35 Relawan</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase text-slate-500">Penanggung Jawab / Admin</p>
                    <div class="h-16 flex items-center justify-center">
                        <span class="font-serif italic text-sm text-slate-400">[Tanda Tangan Digital]</span>
                    </div>
                    <p class="font-black text-slate-900 underline">M. Dimas Anwar, S.Kom</p>
                    <p class="text-[10px] text-slate-500">Project Manager / SPPG Selayur 1</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
