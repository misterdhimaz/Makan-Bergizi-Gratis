<!-- RINGKASAN TUGAS AHLI GIZI -->
<div class="surface-card rounded-3xl p-6 sm:p-7 border-l-4 border-l-emerald-500 shadow-soft">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-5 border-b border-slate-100 mb-6">
        <div class="flex items-start gap-4">
            <div class="role-summary-icon flex shrink-0 items-center justify-center" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-8"/><path d="M12 14c-4.5 0-7-2.5-7-7 4.5 0 7 2.5 7 7Z"/><path d="M12 11c0-4 2.5-6 7-6 0 4.5-2 7-7 7"/></svg>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-emerald-50 text-emerald-800 border border-emerald-200">
                        Ringkasan Ahli Gizi
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Standar AKG & Audit Termal HACCP BGN</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 mt-1">
                    Kendali Mutu Gizi — {{ auth()->user()->name }}
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Tanggung jawab utama: menyusun menu bergizi seimbang, memeriksa suhu pangan, menyimpan sampel makanan, dan memantau kepatuhan APD.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Ahli Gizi -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('menu-planning.index') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Susun Menu Gizi
            </a>
            <a href="{{ route('quality-control.index') }}" class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Audit Suhu QC
            </a>
            <a href="{{ route('apd-inspection.index') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Ceklis 5 APD
            </a>
            <a href="{{ route('reports.index', ['type' => 'qc_apd']) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                Laporan Kinerja Gizi
            </a>
        </div>
    </div>

    <!-- 4 KARTU BENTO KHUSUS AHLI GIZI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Nutrisi Menu Hari Ini</span>
            <p class="font-display font-extrabold text-xl text-slate-900 mt-1">{{ $todayMenu?->calories_kcal ?? 0 }} <span class="text-xs font-normal text-slate-500">kkal</span></p>
            <p class="text-xs text-slate-600 mt-1">Protein: <strong>{{ $todayMenu?->protein_gram ?? 0 }}g</strong> &bull; Karbo: {{ $todayMenu?->carbs_gram ?? 0 }}g</p>
        </div>

        <div class="p-4 rounded-2xl bg-teal-50/60 border border-teal-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-teal-700">Audit Suhu Masak Inti</span>
            <p class="font-display font-extrabold text-xl text-rose-600 mt-1">{{ $latestQc?->cooking_core_temp_c ? $latestQc->cooking_core_temp_c . '°C' : '-' }}</p>
            <p class="text-xs text-slate-600 mt-1">Standar BGN: <strong class="text-emerald-700">&ge; 75°C</strong> (Holding: {{ $latestQc?->holding_temp_c ? $latestQc->holding_temp_c . '°C' : '-' }})</p>
        </div>

        <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700">Kepatuhan 5 APD Relawan</span>
            <p class="font-display font-extrabold text-xl text-indigo-700 mt-1">{{ $todayApd?->compliance_percentage ?? ($latestQc?->apd_compliance_score ?? 0) }}%</p>
            <p class="text-xs text-slate-600 mt-1">{{ $volunteersCount }} Relawan Dapur &bull; SOP Higienitas BGN</p>
        </div>

        <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700">Bank Sampel Chiller 48 Jam</span>
            <p class="font-display font-extrabold text-xl text-amber-700 mt-1">{{ \App\Models\QcInspection::where('food_sample_retained', true)->count() }} <span class="text-xs font-normal text-slate-500">Sampel</span></p>
            <p class="text-xs text-slate-600 mt-1">Pengarsipan wajib 2x24 jam sebelum kirim</p>
        </div>
    </div>

    <!-- ANTREAN TUGAS AKTIF AHLI GIZI -->
    @if($uninspectedRuns->count() > 0 || $myDraftMenus->count() > 0)
    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            Antrean Tugas Butuh Tindakan Anda:
        </h4>
        <div class="space-y-2">
            @foreach($uninspectedRuns as $run)
            <div class="role-list-item flex items-center justify-between p-3 rounded-xl bg-white border border-slate-200 text-xs">
                <div>
                    <span class="font-bold text-slate-900">Batch Produksi {{ $run->dailyMenu?->menu_code }}</span>
                    <span class="text-slate-500">&bull; {{ $run->dailyMenu?->menu_name }} ({{ number_format($run->planned_portions, 0, ',', '.') }} porsi) belum diaudit suhunya</span>
                </div>
                <a href="{{ route('quality-control.index') }}" class="px-3 py-1 rounded-lg bg-teal-600 text-white font-bold text-[11px] hover:bg-teal-500 transition">
                    Audit Suhu Sekarang &rarr;
                </a>
            </div>
            @endforeach
            @foreach($myDraftMenus as $dm)
            <div class="role-list-item flex items-center justify-between p-3 rounded-xl bg-white border border-slate-200 text-xs">
                <div>
                    <span class="font-bold text-slate-900">{{ $dm->menu_code }} — {{ $dm->menu_name }}</span>
                    <span class="text-amber-600 font-semibold">[{{ $dm->status_label }}]</span>
                </div>
                <a href="{{ route('menu-planning.index') }}" class="px-3 py-1 rounded-lg bg-emerald-600 text-white font-bold text-[11px] hover:bg-emerald-500 transition">
                    Lengkapi & Ajukan &rarr;
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
