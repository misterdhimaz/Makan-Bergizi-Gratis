<!-- WORKSPACE KHUSUS ASISTEN LAPANGAN (Ahmad Fauzi) -->
<div class="gsap-stagger-card surface-card rounded-3xl p-6 sm:p-7 border-l-4 border-l-teal-500 shadow-soft">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-5 border-b border-slate-100 dark:border-slate-800 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-teal-500/15 text-teal-600 dark:text-teal-300 border border-teal-500/25 flex items-center justify-center text-2xl shrink-0 font-bold">
                🚚
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-teal-50 text-teal-800 border border-teal-200 dark:bg-teal-500/20 dark:text-teal-300">
                        Workspace Asisten Lapangan (Aslap)
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Logistik Gudang, Rute 7 Titik & Resolusi Keluhan</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 dark:text-white mt-1">
                    Ruang Logistik & Koordinasi Lapangan — Ahmad Fauzi
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Tanggung jawab pokok: penerimaan fisik bahan baku segar dari suplier, penjadwalan armada distribusi ke 5 sekolah & 2 posyandu, memantau konfirmasi penerimaan, serta menuntaskan tiket aduan sekolah.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Asisten Lapangan -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('distribution.index') }}" class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Terbitkan Surat Jalan
            </a>
            <a href="{{ route('production-inventory.index') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Penerimaan Bahan
            </a>
            <a href="{{ route('distribution.index') }}" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Tindak Lanjut Keluhan
            </a>
            <a href="{{ route('reports.index', ['type' => 'distribusi_organoleptik']) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition">
                Laporan Distribusi
            </a>
        </div>
    </div>

    <!-- 4 KARTU BENTO KHUSUS ASISTEN LAPANGAN -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-teal-50/60 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-teal-700 dark:text-teal-400">Jadwal Pengiriman 7 Titik</span>
            <p class="font-display font-extrabold text-xl text-teal-800 dark:text-teal-300 mt-1">{{ $deliveries->count() }} <span class="text-xs font-normal text-slate-500">Surat Jalan</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Total Porsi Alokasi: <strong>1.414 Porsi</strong></p>
        </div>

        <div class="p-4 rounded-2xl bg-rose-50/60 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-700 dark:text-rose-400">Tiket Keluhan Sekolah</span>
            <p class="font-display font-extrabold text-xl text-rose-600 mt-1">{{ $unresolvedTickets->count() }} <span class="text-xs font-normal text-slate-500">Kasus Aktif</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Kritis: <strong class="text-rose-700 dark:text-rose-400">{{ $criticalComplaintsCount }}</strong> &bull; Butuh resolusi cepat</p>
        </div>

        <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 dark:text-amber-400">Stok Kritis Gudang</span>
            <p class="font-display font-extrabold text-xl text-amber-800 dark:text-amber-300 mt-1">{{ $lowStockIngredients->count() }} <span class="text-xs font-normal text-slate-500">Komoditas</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Mendekati safety stock batas minimal</p>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Konfirmasi Terima Sekolah</span>
            <p class="font-display font-extrabold text-xl text-emerald-800 dark:text-emerald-300 mt-1">{{ $deliveryRate }}%</p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">{{ $confirmedCount }} dari {{ $totalDeliveries }} titik terverifikasi</p>
        </div>
    </div>

    <!-- ANTREAN TIKET KELUHAN MEMERLUKAN RESOLUSI -->
    @if($unresolvedTickets->count() > 0)
    <div class="p-4 rounded-2xl bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800/40">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-rose-800 dark:text-rose-300 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
            Aduan / Tiket Kendala dari Sekolah Membutuhkan Tindak Lanjut Aslap:
        </h4>
        <div class="space-y-2">
            @foreach($unresolvedTickets as $ticket)
            <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-800 border border-rose-200 dark:border-rose-900 text-xs">
                <div>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $ticket->ticket_number }} • {{ $ticket->institution?->name }}</span>
                    <span class="text-rose-600 font-semibold uppercase text-[10px] block sm:inline sm:ml-2">[{{ $ticket->severity }}] {{ $ticket->description }}</span>
                </div>
                <a href="{{ route('distribution.index') }}" class="px-3 py-1 rounded-lg bg-rose-600 text-white font-bold text-[11px] hover:bg-rose-500 transition shrink-0">
                    Tindak Lanjuti &rarr;
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

