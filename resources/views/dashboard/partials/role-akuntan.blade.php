<!-- WORKSPACE KHUSUS AKUNTAN SPPG (Rina Wulandari, S.E., Ak.) -->
<div class="gsap-stagger-card surface-card rounded-3xl p-6 sm:p-7 border-l-4 border-l-sky-500 shadow-soft">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-5 border-b border-slate-100 dark:border-slate-800 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-sky-500/15 text-sky-600 dark:text-sky-300 border border-sky-500/25 flex items-center justify-center text-2xl shrink-0 font-bold">
                💰
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-sky-50 text-sky-800 border border-sky-200 dark:bg-sky-500/20 dark:text-sky-300">
                        Workspace Akuntan SPPG
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Pengawasan Anggaran Biaya & Valuasi Persediaan</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 dark:text-white mt-1">
                    Ruang Kerja Validasi PAGU & Finansial — Rina Wulandari, S.E., Ak.
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Tanggung jawab pokok: verifikasi batas pagu anggaran menu (maksimal Rp 15.000/porsi), valuasi persediaan bahan baku gudang, audit nota pembelian suplier, dan rekapitulasi efisiensi biaya.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Akuntan -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('menu-planning.index') }}" class="px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Validasi PAGU Menu
            </a>
            <a href="{{ route('production-inventory.index') }}" class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Audit Stok Gudang
            </a>
            <a href="{{ route('reports.index', ['type' => 'stok_selisih']) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition">
                Laporan Valuasi Bahan
            </a>
        </div>
    </div>

    <!-- 4 KARTU BENTO KHUSUS AKUNTAN -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-sky-50/60 dark:bg-sky-950/20 border border-sky-200 dark:border-sky-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-sky-700 dark:text-sky-400">Menunggu Review PAGU</span>
            <p class="font-display font-extrabold text-xl text-sky-800 dark:text-sky-300 mt-1">{{ $pendingPaguMenus->count() }} <span class="text-xs font-normal text-slate-500">Jadwal Menu</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Batas PAGU BGN: <strong>Rp {{ number_format($paguLimit, 0, ',', '.') }}</strong>/porsi</p>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Biaya Menu Aktif</span>
            <p class="font-display font-extrabold text-xl text-emerald-800 dark:text-emerald-300 mt-1">Rp {{ number_format($todayMenu?->estimated_cost_per_portion ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Status: <strong class="text-emerald-700 dark:text-emerald-400">{{ strtoupper($todayMenu?->pagu_status ?? 'Sesuai PAGU') }}</strong></p>
        </div>

        <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 dark:text-amber-400">Total Valuasi Persediaan</span>
            <p class="font-display font-extrabold text-xl text-amber-800 dark:text-amber-300 mt-1">Rp {{ number_format($totalInventoryValue, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">{{ $ingredients->count() }} komoditas aktif di gudang</p>
        </div>

        <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Menu Lolos PAGU</span>
            <p class="font-display font-extrabold text-xl text-indigo-800 dark:text-indigo-300 mt-1">{{ $paguCompliantCount }} <span class="text-xs font-normal text-slate-500">Menu</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">100% tervalidasi sebelum ke Kepala Dapur</p>
        </div>
    </div>

    <!-- ANTREAN REVIEW PAGU -->
    @if($pendingPaguMenus->count() > 0)
    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-sky-500 animate-ping"></span>
            Menu Siap Dilakukan Validasi Biaya PAGU:
        </h4>
        <div class="space-y-2">
            @foreach($pendingPaguMenus as $pm)
            <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                <div>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $pm->menu_code }} — {{ $pm->menu_name }}</span>
                    <span class="text-slate-500 dark:text-slate-400">&bull; Estimasi: Rp {{ number_format($pm->estimated_cost_per_portion, 0, ',', '.') }} (Batas: Rp {{ number_format($paguLimit, 0, ',', '.') }})</span>
                </div>
                <a href="{{ route('menu-planning.index') }}" class="px-3 py-1 rounded-lg bg-sky-600 text-white font-bold text-[11px] hover:bg-sky-500 transition">
                    Verifikasi PAGU &rarr;
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

