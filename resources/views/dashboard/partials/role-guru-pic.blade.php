<!-- RINGKASAN TUGAS GURU PIC / SEKOLAH & POSYANDU -->
@php
    $latestSchoolDelivery = $mySchoolDeliveries->first();
    [$schoolDeliveryStatusLabel, $schoolDeliveryStatusTone] = match($latestSchoolDelivery?->status) {
        'preparing' => ['Persiapan muat', 'warning'],
        'in_transit' => ['Dalam perjalanan', 'info'],
        'arrived' => ['Tiba di lokasi', 'success'],
        'confirmed_clean' => ['Dikonfirmasi', 'success'],
        'confirmed_with_issue' => ['Dikonfirmasi dengan catatan', 'warning'],
        default => ['Menunggu pengiriman', 'neutral'],
    };
@endphp
<div class="surface-card rounded-3xl p-6 sm:p-7 border-l-4 border-l-purple-500 shadow-soft">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-5 border-b border-slate-100 mb-6">
        <div class="flex items-start gap-4">
            <div class="role-summary-icon flex shrink-0 items-center justify-center" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6M9 9h.01M15 9h.01M9 12h.01M15 12h.01"/></svg>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-purple-50 text-purple-800 border border-purple-200">
                        Ringkasan Guru PIC / Posyandu
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Titik Sasaran Penerima Manfaat MBG</span>
                </div>
                <h3 class="font-display font-extrabold text-xl text-slate-900 mt-1">
                    {{ $userInstitution?->name ?? 'Titik Penerima Sekolah / Posyandu' }}
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Tanggung jawab pokok: mengonfirmasi porsi makanan yang tiba secara fisik, mengisi penilaian uji organoleptik digital (rasa & aroma), dan menyampaikan tiket keluhan jika terjadi kendala.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Guru PIC -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('distribution.index', ['tab' => 'deliveries'] + ($latestSchoolDelivery ? ['delivery_id' => $latestSchoolDelivery->id] : [])) }}" class="px-4 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-600 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Konfirmasi Porsi & Uji Rasa
            </a>
            <a href="{{ route('distribution.index', ['tab' => 'complaints', 'action' => 'create-complaint']) }}" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs shadow-sm flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Buat Tiket Keluhan
            </a>
            <a href="{{ route('reports.index', ['type' => 'distribusi_organoleptik']) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                Rekapitulasi Sekolah
            </a>
        </div>
    </div>

    <!-- 4 KARTU BENTO KHUSUS GURU PIC -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-purple-50/60 border border-purple-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-700">Kuota Penerima Sekolah</span>
            <p class="font-display font-extrabold text-xl text-purple-800 mt-1">{{ number_format($userInstitution?->default_quota ?? 0, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">Porsi</span></p>
            <p class="text-xs text-slate-600 mt-1">Siswa / Balita terdaftar aktif</p>
        </div>

        <div class="p-4 rounded-2xl bg-sky-50/60 border border-sky-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-sky-700">Status Armada Kirim</span>
            <div class="mt-2">
                @include('partials.ui.status-badge', ['tone' => $schoolDeliveryStatusTone, 'label' => $schoolDeliveryStatusLabel])
            </div>
            <p class="text-xs text-slate-600 mt-2">Plat: {{ $latestSchoolDelivery?->vehicle_plate_number ?? 'Armada SPPG' }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Uji Organoleptik</span>
            <p class="font-display font-extrabold text-xl text-emerald-800 mt-1">
                {{ $pendingOrganolepticCount > 0 ? 'Perlu Diisi' : 'Tuntas / Aman' }}
            </p>
            <p class="text-xs text-slate-600 mt-1">Uji kelayakan rasa, aroma & suhu tiba</p>
        </div>

        <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-200">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-700">Tiket Keluhan Sekolah</span>
            <p class="font-display font-extrabold text-xl text-rose-600 mt-1">{{ $mySchoolComplaints->count() }} <span class="text-xs font-normal text-slate-500">Kasus</span></p>
            <p class="text-xs text-slate-600 mt-1">{{ $mySchoolComplaints->where('status', 'resolved')->count() }} telah ditindaklanjuti Aslap</p>
        </div>
    </div>

    <!-- PENGIRIMAN TERBARU MENUJU SEKOLAH INI -->
    @if($mySchoolDeliveries->count() > 0)
    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
            Riwayat Surat Jalan Menuju {{ $userInstitution?->name }}:
        </h4>
        <div class="space-y-2">
            @foreach($mySchoolDeliveries as $sd)
            @php
                $schoolHistoryStatusLabel = match($sd->status) {
                    'preparing' => 'Persiapan muat',
                    'in_transit' => 'Dalam perjalanan',
                    'arrived' => 'Tiba di lokasi',
                    'confirmed_clean' => 'Dikonfirmasi',
                    'confirmed_with_issue' => 'Dikonfirmasi dengan catatan',
                    default => 'Menunggu pengiriman',
                };
            @endphp
            <div class="role-list-item flex items-center justify-between p-3 rounded-xl bg-white border border-slate-200 text-xs">
                <div>
                    <span class="font-mono font-bold text-purple-600">{{ $sd->waybill_number }}</span>
                    <span class="text-slate-700 font-bold ml-2">{{ $sd->dispatched_portions }} Porsi</span>
                    <span class="text-slate-600 block sm:inline sm:ml-2">Status: {{ $schoolHistoryStatusLabel }}</span>
                </div>
                <a href="{{ route('distribution.index', ['tab' => 'deliveries', 'delivery_id' => $sd->id]) }}" class="px-3 py-1 rounded-lg bg-purple-700 text-white font-bold text-[11px] hover:bg-purple-600 transition shrink-0">
                    Konfirmasi Penerimaan &rarr;
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
