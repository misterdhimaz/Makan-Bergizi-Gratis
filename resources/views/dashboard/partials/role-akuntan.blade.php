<!-- RINGKASAN TUGAS AKUNTAN -->
<div class="surface-card rounded-3xl p-6 sm:p-7 border-l-4 border-l-sky-500 shadow-soft">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-5 border-b border-slate-100 mb-6">
        <div class="flex items-start gap-4">
            <div class="role-summary-icon flex shrink-0 items-center justify-center" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5"/><path d="M4 19h17"/><path d="m7 15 4-4 3 2 5-6"/><path d="M16 7h3v3"/></svg>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-sky-50 text-sky-800 border border-sky-200">
                        Ringkasan Akuntan
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Pemeriksaan batas biaya menu dan laporan operasional</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 mt-1">
                    Validasi PAGU Menu — {{ auth()->user()->name }}
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Tanggung jawab utama: memeriksa kesesuaian biaya menu dengan batas PAGU dan melihat laporan terkait anggaran.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Akuntan -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('menu-planning.index') }}" class="px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Validasi PAGU Menu
            </a>
            <a href="{{ route('sop-config.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                Atur Batas PAGU
            </a>
            <a href="{{ route('reports.index', ['type' => 'operasional']) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                Lihat Laporan
            </a>
        </div>
    </div>

    <!-- 4 KARTU BENTO KHUSUS AKUNTAN -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-sky-50/60 border border-sky-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-sky-700">Menunggu Review PAGU</span>
            <p class="font-display font-extrabold text-xl text-sky-800 mt-1">{{ $pendingPaguMenus->count() }} <span class="text-xs font-normal text-slate-500">Jadwal Menu</span></p>
            <p class="text-xs text-slate-600 mt-1">Batas PAGU BGN: <strong>Rp {{ number_format($paguLimit, 0, ',', '.') }}</strong>/porsi</p>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Biaya Menu Aktif</span>
            <p class="font-display font-extrabold text-xl text-emerald-800 mt-1">Rp {{ number_format($todayMenu?->estimated_cost_per_portion ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-600 mt-1">Status:
                <strong class="text-emerald-700">
                    {{ match($todayMenu?->pagu_status) {
                        'sesuai_pagu' => 'Sesuai PAGU',
                        'melebihi_pagu' => 'Melebihi PAGU',
                        'menunggu_review', 'pending' => 'Menunggu pemeriksaan',
                        default => 'Belum ditetapkan',
                    } }}
                </strong>
            </p>
        </div>

        <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700">Batas PAGU per Porsi</span>
            <p class="font-display font-extrabold text-xl text-amber-800 mt-1">Rp {{ number_format($paguLimit, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-600 mt-1">Batas biaya menu yang perlu diverifikasi</p>
        </div>

        <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700">Menu Sesuai PAGU</span>
            <p class="font-display font-extrabold text-xl text-indigo-800 mt-1">{{ $paguCompliantCount }} <span class="text-xs font-normal text-slate-500">Menu</span></p>
            <p class="text-xs text-slate-600 mt-1">Terverifikasi sesuai batas biaya</p>
        </div>
    </div>

    <!-- ANTREAN REVIEW PAGU -->
    @if($pendingPaguMenus->count() > 0)
    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
            Menu Siap Dilakukan Validasi Biaya PAGU:
        </h4>
        <div class="space-y-2">
            @foreach($pendingPaguMenus as $pm)
            <div class="role-list-item flex items-center justify-between p-3 rounded-xl bg-white border border-slate-200 text-xs">
                <div>
                    <span class="font-bold text-slate-900">{{ $pm->menu_code }} — {{ $pm->menu_name }}</span>
                    <span class="text-slate-500">&bull; Estimasi: Rp {{ number_format($pm->estimated_cost_per_portion, 0, ',', '.') }} (Batas: Rp {{ number_format($paguLimit, 0, ',', '.') }})</span>
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
