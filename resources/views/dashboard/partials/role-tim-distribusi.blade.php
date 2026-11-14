<!-- RINGKASAN TUGAS TIM DISTRIBUSI -->
<div class="surface-card rounded-3xl p-6 sm:p-7 border-l-4 border-l-orange-500 shadow-soft">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-5 border-b border-slate-100 mb-6">
        <div class="flex items-start gap-4">
            <div class="role-summary-icon flex shrink-0 items-center justify-center" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"/><circle cx="7.5" cy="18" r="1.5"/><circle cx="17.5" cy="18" r="1.5"/></svg>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-orange-50 text-orange-800 border border-orange-200">
                        Ringkasan Tim Distribusi
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Armada Pengiriman & Berita Acara Serah Terima</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 mt-1">
                    Tugas Pengiriman — {{ auth()->user()->name }}
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Tanggung jawab pokok: mengantarkan paket makanan bergizi tepat waktu ke 5 sekolah dan 2 posyandu, memperbarui status perjalanan armada, serta mengunggah bukti fisik serah terima surat jalan.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Tim Distribusi -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('distribution.index', ['tab' => 'deliveries']) }}" class="px-4 py-2.5 rounded-xl bg-orange-700 hover:bg-orange-600 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Perbarui Status Surat Jalan
            </a>
            <a href="{{ route('reports.index', ['type' => 'distribusi_organoleptik']) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                Laporan Distribusi
            </a>
        </div>
    </div>

    <!-- 4 KARTU BENTO KHUSUS TIM DISTRIBUSI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-orange-50/60 border border-orange-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-orange-700">Surat Jalan Hari Ini</span>
            <p class="font-display font-extrabold text-xl text-orange-800 mt-1">{{ $deliveries->count() }} <span class="text-xs font-normal text-slate-500">Tujuan</span></p>
            <p class="text-xs text-slate-600 mt-1">Armada terdaftar ke 7 titik sasaran</p>
        </div>

        <div class="p-4 rounded-2xl bg-sky-50/60 border border-sky-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-sky-700">Dalam perjalanan</span>
            <p class="font-display font-extrabold text-xl text-sky-800 mt-1">{{ $inTransitCount }} <span class="text-xs font-normal text-slate-500">Armada</span></p>
            <p class="text-xs text-slate-600 mt-1">Menuju lokasi penerima manfaat</p>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Tiba / Diterima Sekolah</span>
            <p class="font-display font-extrabold text-xl text-emerald-800 mt-1">{{ $confirmedCount }} <span class="text-xs font-normal text-slate-500">Sekolah</span></p>
            <p class="text-xs text-slate-600 mt-1">Porsi fisik berhasil diserahterimakan</p>
        </div>

        <div class="p-4 rounded-2xl bg-teal-50/60 border border-teal-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-teal-700">Bukti Foto Serah Terima</span>
            <p class="font-display font-extrabold text-xl text-teal-800 mt-1">{{ $deliveries->whereNotNull('dispatch_photo_path')->count() }} <span class="text-xs font-normal text-slate-500">Berkas</span></p>
            <p class="text-xs text-slate-600 mt-1">Foto fisik surat jalan terunggah di sistem</p>
        </div>
    </div>

    <!-- DAFTAR SURAT JALAN AKTIF HARI INI -->
    @if($deliveries->count() > 0)
    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
            Daftar Armada & Rute Pengiriman Hari Ini:
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($deliveries->take(4) as $deliv)
            @php
                [$deliveryStatusLabel, $deliveryStatusTone] = match($deliv->status) {
                    'preparing' => ['Persiapan muat', 'warning'],
                    'in_transit' => ['Dalam perjalanan', 'info'],
                    'arrived' => ['Tiba di lokasi', 'success'],
                    'confirmed_clean' => ['Dikonfirmasi', 'success'],
                    'confirmed_with_issue' => ['Dikonfirmasi dengan catatan', 'warning'],
                    default => ['Status tidak diketahui', 'neutral'],
                };
            @endphp
            <div class="p-3.5 rounded-xl bg-white border border-slate-200 text-xs flex items-center justify-between">
                <div>
                    <span class="font-mono font-bold text-orange-600">{{ $deliv->waybill_number }}</span>
                    <p class="font-bold text-slate-900 mt-0.5">{{ $deliv->institution?->name }}</p>
                    <span class="text-slate-500">{{ $deliv->dispatched_portions }} Porsi • Plat: {{ $deliv->vehicle_plate_number }}</span>
                </div>
                <div class="text-right">
                    @include('partials.ui.status-badge', ['tone' => $deliveryStatusTone, 'label' => $deliveryStatusLabel])
                    <a href="{{ route('distribution.index', ['tab' => 'deliveries', 'delivery_id' => $deliv->id]) }}" class="mt-1 inline-block text-[11px] font-bold text-orange-800 hover:underline">Perbarui &rarr;</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
