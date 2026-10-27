<!-- WORKSPACE KHUSUS KEPALA DAPUR (Chef Hendra Wijaya) -->
<div class="gsap-stagger-card surface-card rounded-3xl p-6 sm:p-7 border-l-4 border-l-amber-500 shadow-soft">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-5 border-b border-slate-100 dark:border-slate-800 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/15 text-amber-600 dark:text-amber-300 border border-amber-500/25 flex items-center justify-center text-2xl shrink-0 font-bold">
                👨‍🍳
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-500/20 dark:text-amber-300">
                        Workspace Kepala Dapur
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Otoritas Approval & Komando 35 Relawan Masak</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 dark:text-white mt-1">
                    Ruang Komando Produksi & Approval — Chef Hendra Wijaya
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Tanggung jawab pokok: persetujuan final siklus menu yang lolos PAGU, memimpin 35 relawan dalam memasak 1.414 porsi, mengontrol porsi layak saji vs reject, serta menegakkan disiplin APD dapur.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Kepala Dapur -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('menu-planning.index') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Approval Menu
            </a>
            <a href="{{ route('production-inventory.index') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buka Sesi Masak
            </a>
            <a href="{{ route('apd-inspection.index') }}" class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Verifikasi APD
            </a>
            <a href="{{ route('reports.index', ['type' => 'stok_selisih']) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition">
                Laporan Dapur
            </a>
        </div>
    </div>

    <!-- 4 KARTU BENTO KHUSUS KEPALA DAPUR -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 dark:text-amber-400">Butuh Approval Anda</span>
            <p class="font-display font-extrabold text-xl text-amber-800 dark:text-amber-300 mt-1">{{ $pendingApprovalMenus->count() }} <span class="text-xs font-normal text-slate-500">Menu Draf</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Sudah lolos verifikasi PAGU Akuntan</p>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Realisasi Porsi Masak</span>
            <p class="font-display font-extrabold text-xl text-emerald-800 dark:text-emerald-300 mt-1">{{ number_format($actualPortions) }} <span class="text-xs font-normal text-slate-500">/ {{ number_format($plannedPortions) }}</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Target Alokasi 1.414 Porsi Siap Saji</p>
        </div>

        <div class="p-4 rounded-2xl bg-rose-50/60 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-700 dark:text-rose-400">Selisih & Porsi Rusak</span>
            <p class="font-display font-extrabold text-xl text-rose-600 mt-1">{{ $variance >= 0 ? '+' : '' }}{{ $variance }} <span class="text-xs font-normal text-slate-500">Porsi</span></p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Porsi Cacat/Reject: <strong>{{ $rejectedPortions }} Porsi</strong></p>
        </div>

        <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-800/40">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Kepatuhan APD Dapur</span>
            <p class="font-display font-extrabold text-xl text-indigo-700 dark:text-indigo-300 mt-1">{{ $todayApd?->compliance_percentage ?? ($latestQc?->apd_compliance_score ?? 0) }}%</p>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">35 Relawan &bull; Verifikasi Ka. Dapur</p>
        </div>
    </div>

    <!-- ANTREAN APPROVAL MENU KEPALA DAPUR -->
    @if($pendingApprovalMenus->count() > 0)
    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
            Menu Menunggu Keputusan Persetujuan Kepala Dapur:
        </h4>
        <div class="space-y-2">
            @foreach($pendingApprovalMenus as $pam)
            <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                <div>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $pam->menu_code }} — {{ $pam->menu_name }}</span>
                    <span class="text-slate-500 dark:text-slate-400">&bull; {{ number_format($pam->target_portions) }} Porsi &bull; Biaya: Rp {{ number_format($pam->estimated_cost_per_portion, 0, ',', '.') }}</span>
                </div>
                <a href="{{ route('menu-planning.index') }}" class="px-3 py-1 rounded-lg bg-emerald-600 text-white font-bold text-[11px] hover:bg-emerald-500 transition">
                    Beri Keputusan &rarr;
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

