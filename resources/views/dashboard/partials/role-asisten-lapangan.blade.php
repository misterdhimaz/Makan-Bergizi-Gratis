<!-- RINGKASAN TUGAS ASISTEN LAPANGAN -->
@php $roleCriticalComplaintsCount = $unresolvedTickets->where('severity', 'kritis')->count(); @endphp
<div class="surface-card rounded-3xl p-6 sm:p-7 border-l-4 border-l-teal-500 shadow-soft">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-5 border-b border-slate-100 mb-6">
        <div class="flex items-start gap-4">
            <div class="role-summary-icon flex shrink-0 items-center justify-center" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"/><circle cx="7.5" cy="18" r="1.5"/><circle cx="17.5" cy="18" r="1.5"/></svg>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-teal-50 text-teal-800 border border-teal-200">
                        Ringkasan Asisten Lapangan
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Logistik gudang, distribusi, dan tindak lanjut keluhan</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 mt-1">
                    Tugas Lapangan — {{ auth()->user()->name }}
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Penerimaan bahan baku, penjadwalan distribusi, pemantauan konfirmasi penerimaan, dan tindak lanjut keluhan sekolah.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Asisten Lapangan -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('distribution.index', ['tab' => 'deliveries', 'action' => 'create-waybill']) }}" class="px-4 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-600 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Terbitkan Surat Jalan
            </a>
            <a href="{{ route('production-inventory.index') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Penerimaan Bahan
            </a>
            <a href="{{ route('distribution.index', ['tab' => 'complaints']) }}" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Tindak Lanjut Keluhan
            </a>
            <a href="{{ route('reports.index', ['type' => 'distribusi_organoleptik']) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                Laporan Distribusi
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-teal-50/60 border border-teal-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-teal-700">Jadwal Pengiriman {{ $allInstitutions->count() }} Titik</span>
            <p class="font-display font-extrabold text-xl text-teal-800 mt-1">{{ $deliveries->count() }} <span class="text-xs font-normal text-slate-500">Surat Jalan</span></p>
            <p class="text-xs text-slate-600 mt-1">Total Porsi Alokasi: <strong>{{ number_format($grandTotalQuota, 0, ',', '.') }} Porsi</strong></p>
        </div>

        <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-700">Tiket Keluhan Sekolah</span>
            <p class="font-display font-extrabold text-xl text-rose-600 mt-1">{{ $unresolvedTickets->count() }} <span class="text-xs font-normal text-slate-500">Kasus Aktif</span></p>
            <p class="text-xs text-slate-600 mt-1">Kritis: <strong class="text-rose-700">{{ $roleCriticalComplaintsCount }}</strong> &bull; Perlu tindak lanjut segera</p>
        </div>

        <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700">Stok Kritis Gudang</span>
            <p class="font-display font-extrabold text-xl text-amber-800 mt-1">{{ $lowStockIngredients->count() }} <span class="text-xs font-normal text-slate-500">Komoditas</span></p>
            <p class="text-xs text-slate-600 mt-1">Mendekati batas stok minimum</p>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Konfirmasi Terima Sekolah</span>
            <p class="font-display font-extrabold text-xl text-emerald-800 mt-1">{{ $deliveryRate }}%</p>
            <p class="text-xs text-slate-600 mt-1">{{ $confirmedCount }} dari {{ $totalDeliveries }} titik terverifikasi</p>
        </div>
    </div>

    @if($unresolvedTickets->count() > 0)
    <div class="p-4 rounded-2xl bg-rose-50/50 border border-rose-200">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-rose-800 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            Keluhan sekolah yang perlu ditindaklanjuti
        </h4>
        <div class="space-y-2">
            @foreach($unresolvedTickets as $ticket)
            <div class="role-list-item flex items-center justify-between p-3 rounded-xl bg-white border border-rose-200 text-xs">
                <div class="min-w-0">
                    <span class="font-bold text-slate-900">{{ $ticket->ticket_number }} • {{ $ticket->institution?->name }}</span>
                    <p class="mt-1 text-slate-600">{{ $ticket->description }}</p>
                    @php
                        [$severityClass, $severityIcon, $severityLabel] = match($ticket->severity) {
                            'kritis' => [
                                'bg-rose-100 text-rose-800 border-rose-200',
                                'M12 9v4m0 4h.01m-8.5 2h17L12 3 3.5 19z',
                                'Kritis',
                            ],
                            'sedang' => [
                                'bg-amber-100 text-amber-800 border-amber-200',
                                'M12 8v4m0 4h.01m7.938 4H4.062c-1.54 0-2.502-1.667-1.732-3L10.268 4c.77-1.333 2.694-1.333 3.464 0l7.938 13c.77 1.333-.192 3-1.732 3z',
                                'Sedang',
                            ],
                            default => [
                                'bg-sky-100 text-sky-800 border-sky-200',
                                'M12 16v-4m0-4h.01m9.99 4a10 10 0 11-20 0 10 10 0 0120 0z',
                                'Rendah',
                            ],
                        };
                    @endphp
                    <span class="mt-2 inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-black uppercase {{ $severityClass }}">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="{{ $severityIcon }}"/>
                        </svg>
                        {{ $severityLabel }}
                    </span>
                </div>
                <a href="{{ route('distribution.index', ['tab' => 'complaints']) }}" class="px-3 py-1 rounded-lg bg-rose-600 text-white font-bold text-[11px] hover:bg-rose-500 transition shrink-0">
                    Tindak Lanjuti &rarr;
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
