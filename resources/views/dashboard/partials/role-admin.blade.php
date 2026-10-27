<!-- WORKSPACE KHUSUS ADMINISTRATOR SISTEM (Budi Santoso, S.Kom) -->
<div class="gsap-stagger-card surface-card rounded-3xl p-6 sm:p-7 border-l-4 border-l-indigo-600 shadow-soft">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-5 border-b border-slate-100 dark:border-slate-800 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 border border-indigo-500/25 flex items-center justify-center text-2xl shrink-0 font-bold">
                🛡️
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-indigo-50 text-indigo-800 border border-indigo-200 dark:bg-indigo-500/20 dark:text-indigo-300">
                        Pusat Kendali Eksekutif & Admin
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Full Access • Pengawasan Seluruh Siklus Operasional SPPG</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 dark:text-white mt-1">
                    Pusat Komando & Otoritas Sistem — Budi Santoso, S.Kom
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Otoritas penuh mengelola 7 akun jabatan personil (RBAC), 7 titik mitra sekolah/posyandu (1.414 kuota), standar SOP BGN, dan log audit sistem.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Administrator -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('users-rbac.index') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Kelola Mitra & RBAC
            </a>
            <a href="{{ route('reports.index', ['type' => 'kinerja_role']) }}" class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Laporan Kinerja 7 Role
            </a>
            <a href="{{ route('settings.appearance') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition">
                Tema & Tampilan
            </a>
        </div>
    </div>

    <!-- 4 KARTU BENTO KHUSUS ADMIN -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Total Kuota Sasaran</span>
            <p class="font-display font-extrabold text-xl text-indigo-800 dark:text-indigo-300 mt-1">1.414 <span class="text-xs font-normal text-slate-500">Porsi</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">5 Sekolah (1 Pesantren, 4 SD) &bull; 2 Posyandu</p>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Personel & Relawan Aktif</span>
            <p class="font-display font-extrabold text-xl text-emerald-800 dark:text-emerald-300 mt-1">7 Role / 35 Relawan</p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">RBAC terisolasi sesuai SOP BGN</p>
        </div>

        <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 dark:text-amber-400">Valuasi Gudang & PAGU</span>
            <p class="font-display font-extrabold text-xl text-amber-800 dark:text-amber-300 mt-1">Rp {{ number_format($totalInventoryValue, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">PAGU: Rp 15.000/porsi &bull; {{ $ingredients->count() }} komoditas</p>
        </div>

        <div class="p-4 rounded-2xl bg-teal-50/60 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-teal-700 dark:text-teal-400">Status 7 Titik Distribusi</span>
            <p class="font-display font-extrabold text-xl text-teal-800 dark:text-teal-300 mt-1">{{ $confirmedCount }}/{{ $totalDeliveries }} <span class="text-xs font-normal text-slate-500">Tuntas</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Tingkat verifikasi: <strong>{{ $deliveryRate }}%</strong></p>
        </div>
    </div>
</div>

