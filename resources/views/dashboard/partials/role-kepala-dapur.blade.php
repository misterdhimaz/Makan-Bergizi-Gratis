<!-- RINGKASAN TUGAS KEPALA DAPUR -->
<div class="surface-card rounded-3xl p-6 sm:p-7 border-l-4 border-l-amber-500 shadow-soft">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-5 border-b border-slate-100 mb-6">
        <div class="flex items-start gap-4">
            <div class="role-summary-icon flex shrink-0 items-center justify-center" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11h16l-1 9H5l-1-9Z"/><path d="M7 11a5 5 0 0 1 10 0M9 4V2m6 2V2"/></svg>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-amber-50 text-amber-800 border border-amber-200">
                        Ringkasan Kepala Dapur
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Persetujuan menu dan koordinasi produksi</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 mt-1">
                    Produksi & Persetujuan Menu — {{ auth()->user()->name }}
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Tanggung jawab pokok: persetujuan final siklus menu yang lolos PAGU, memimpin {{ $volunteersCount }} relawan, mengontrol porsi layak saji vs reject, serta menegakkan disiplin APD dapur.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Kepala Dapur -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('menu-planning.index') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Persetujuan Menu
            </a>
            <a href="{{ route('production-inventory.index') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buka Sesi Masak
            </a>
            <a href="{{ route('apd-inspection.index') }}" class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Verifikasi APD
            </a>
            <a href="{{ route('reports.index', ['type' => 'stok_selisih']) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                Laporan Dapur
            </a>
        </div>
    </div>

    <!-- 4 KARTU BENTO KHUSUS KEPALA DAPUR -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700">Butuh Approval Anda</span>
            <p class="font-display font-extrabold text-xl text-amber-800 mt-1">{{ $pendingApprovalMenus->count() }} <span class="text-xs font-normal text-slate-500">Menu Draf</span></p>
            <p class="text-xs text-slate-600 mt-1">Sudah lolos verifikasi PAGU Akuntan</p>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Realisasi Porsi Masak</span>
            <p class="font-display font-extrabold text-xl text-emerald-800 mt-1">{{ number_format($actualPortions, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">/ {{ number_format($plannedPortions, 0, ',', '.') }}</span></p>
            <p class="text-xs text-slate-600 mt-1">Total Kuota Mitra Aktif: {{ number_format($grandTotalQuota, 0, ',', '.') }} porsi/hari</p>
        </div>

        <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-700">Selisih & Porsi Rusak</span>
            <p class="font-display font-extrabold text-xl text-rose-600 mt-1">{{ $variance >= 0 ? '+' : '' }}{{ $variance }} <span class="text-xs font-normal text-slate-500">Porsi</span></p>
            <p class="text-xs text-slate-600 mt-1">Porsi cacat: <strong>{{ $todayProduction?->rejected_damaged_portions ?? 0 }} porsi</strong></p>
        </div>

        <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700">Kepatuhan APD Dapur</span>
            <p class="font-display font-extrabold text-xl text-indigo-700 mt-1">{{ $todayApd?->compliance_percentage ?? ($latestQc?->apd_compliance_score ?? 0) }}%</p>
            <p class="text-xs text-slate-600 mt-1">{{ $volunteersCount }} Relawan &bull; Verifikasi Ka. Dapur</p>
        </div>
    </div>

    <!-- ANTREAN APPROVAL MENU KEPALA DAPUR -->
    @if($pendingApprovalMenus->count() > 0)
    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            Menu Menunggu Keputusan Persetujuan Kepala Dapur:
        </h4>
        <div class="space-y-2">
            @foreach($pendingApprovalMenus as $pam)
            <div class="role-list-item flex items-center justify-between p-3 rounded-xl bg-white border border-slate-200 text-xs">
                <div>
                    <span class="font-bold text-slate-900">{{ $pam->menu_code }} — {{ $pam->menu_name }}</span>
                    <span class="text-slate-500">&bull; {{ number_format($pam->target_portions, 0, ',', '.') }} Porsi &bull; Biaya: Rp {{ number_format($pam->estimated_cost_per_portion, 0, ',', '.') }}</span>
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
