<!-- WORKSPACE KHUSUS TIM DISTRIBUSI (Rudi Hartono - Kurir/Driver Armada) -->
<div class="gsap-stagger-card surface-card rounded-3xl p-6 sm:p-7 border-l-4 border-l-orange-500 shadow-soft">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-5 border-b border-slate-100 dark:border-slate-800 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-orange-500/15 text-orange-600 dark:text-orange-300 border border-orange-500/25 flex items-center justify-center text-2xl shrink-0 font-bold">
                🛵
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-orange-50 text-orange-800 border border-orange-200 dark:bg-orange-500/20 dark:text-orange-300">
                        Workspace Tim Distribusi
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Armada Pengiriman & Berita Acara Serah Terima</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 dark:text-white mt-1">
                    Ruang Tugas Armada & Kurir — Rudi Hartono
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Tanggung jawab pokok: mengantarkan paket makanan bergizi tepat waktu ke 5 sekolah dan 2 posyandu, memperbarui status perjalanan armada, serta mengunggah bukti fisik serah terima surat jalan.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Tim Distribusi -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('distribution.index') }}" class="px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Perbarui Status Surat Jalan
            </a>
            <a href="{{ route('reports.index', ['type' => 'distribusi_organoleptik']) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition">
                Laporan Distribusi
            </a>
        </div>
    </div>

    <!-- 4 KARTU BENTO KHUSUS TIM DISTRIBUSI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-orange-50/60 dark:bg-orange-950/20 border border-orange-200 dark:border-orange-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-orange-700 dark:text-orange-400">Surat Jalan Hari Ini</span>
            <p class="font-display font-extrabold text-xl text-orange-800 dark:text-orange-300 mt-1">{{ $deliveries->count() }} <span class="text-xs font-normal text-slate-500">Tujuan</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Armada terdaftar ke 7 titik sasaran</p>
        </div>

        <div class="p-4 rounded-2xl bg-sky-50/60 dark:bg-sky-950/20 border border-sky-200 dark:border-sky-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-sky-700 dark:text-sky-400">Dalam Perjalanan (In Transit)</span>
            <p class="font-display font-extrabold text-xl text-sky-800 dark:text-sky-300 mt-1">{{ $inTransitCount }} <span class="text-xs font-normal text-slate-500">Armada</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Menuju lokasi penerima manfaat</p>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Tiba / Diterima Sekolah</span>
            <p class="font-display font-extrabold text-xl text-emerald-800 dark:text-emerald-300 mt-1">{{ $confirmedCount }} <span class="text-xs font-normal text-slate-500">Sekolah</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Porsi fisik berhasil diserahterimakan</p>
        </div>

        <div class="p-4 rounded-2xl bg-teal-50/60 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-teal-700 dark:text-teal-400">Bukti Foto Serah Terima</span>
            <p class="font-display font-extrabold text-xl text-teal-800 dark:text-teal-300 mt-1">{{ $deliveries->whereNotNull('dispatch_photo_path')->count() }} <span class="text-xs font-normal text-slate-500">Berkas</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Foto fisik surat jalan terunggah di sistem</p>
        </div>
    </div>

    <!-- DAFTAR SURAT JALAN AKTIF HARI INI -->
    @if($deliveries->count() > 0)
    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
            Daftar Armada & Rute Pengiriman Hari Ini:
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($deliveries->take(4) as $deliv)
            <div class="p-3.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs flex items-center justify-between">
                <div>
                    <span class="font-mono font-bold text-orange-600 dark:text-orange-400">{{ $deliv->waybill_number }}</span>
                    <p class="font-bold text-slate-900 dark:text-white mt-0.5">{{ $deliv->institution?->name }}</p>
                    <span class="text-slate-500 dark:text-slate-400">{{ $deliv->dispatched_portions }} Porsi • Plat: {{ $deliv->vehicle_plate_number }}</span>
                </div>
                <div class="text-right">
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-slate-100 text-slate-700 border border-slate-200 block mb-1.5">
                        {{ str_replace('_', ' ', $deliv->status) }}
                    </span>
                    <a href="{{ route('distribution.index') }}" class="text-[11px] font-bold text-orange-600 hover:underline">Update &rarr;</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

