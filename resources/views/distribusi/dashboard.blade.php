<x-layouts.tim-distribusi title="Dashboard Tim Distribusi">
    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-md">
        <a href="{{ route('distribusi.pengiriman.index') }}" class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm hover:border-obsidian transition-colors">
            <span class="font-body text-label-sm text-ink-muted">Tugas pengiriman aktif</span>
            <div class="font-display text-page-title text-ink tabular-nums">{{ $data['active_count'] }}</div>
            <p class="font-body text-micro text-ink-muted">Berstatus Disiapkan atau Berangkat</p>
        </a>

        <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm">
            <span class="font-body text-label-sm text-ink-muted">Porsi sudah diserahkan</span>
            <div class="font-display text-page-title text-ink tabular-nums">
                {{ number_format($data['portions_handed_over'], 0, ',', '.') }}
                <span class="text-body-md font-body text-ink-muted">/ {{ number_format($data['total_target'], 0, ',', '.') }}</span>
            </div>
            <p class="font-body text-micro text-ink-muted">Total porsi pada rute Tiba/Diterima/Bermasalah</p>
        </article>

        <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm">
            <span class="font-body text-label-sm text-ink-muted">Titik selesai serah terima</span>
            <div class="font-display text-page-title text-ink tabular-nums">{{ $data['handed_over_count'] }}/{{ $data['total_routes'] }}</div>
            <p class="font-body text-micro text-ink-muted">Rute sudah tiba di lokasi</p>
        </article>

        <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm">
            <span class="font-body text-label-sm text-ink-muted">Koordinator Lapangan</span>
            <div class="font-display text-section-title text-ink">{{ $data['aslap_name'] }}</div>
            <p class="font-body text-micro text-ink-muted">Asisten Lapangan · Jadwal disusun 06:30 WIB</p>
        </article>
    </section>

    <section class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
        <div class="lg:col-span-2 bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
            <div class="flex items-center justify-between gap-sm">
                <h2 class="font-display text-section-title text-ink">Tugas Pengiriman Hari Ini</h2>
                <a href="{{ route('distribusi.riwayat.index') }}" class="font-body text-label-sm text-ink underline">Lihat Riwayat Pengiriman</a>
            </div>

            <div class="flex flex-col gap-sm">
                @foreach($data['routes_today'] as $route)
                    <article class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col sm:flex-row sm:items-center justify-between gap-sm">
                        <div>
                            <span class="font-mono text-micro text-ink-muted">{{ $route->waybill_number }} · {{ $route->planned_departure_time ?? $route->departed_at?->timezone('Asia/Jakarta')->format('H:i') }} – {{ $route->scheduled_arrival_time }}</span>
                            <h3 class="font-display text-card-title text-ink">{{ $route->location?->name }} ({{ number_format($route->dispatched_portions, 0, ',', '.') }} porsi)</h3>
                            <p class="font-body text-micro text-ink-muted">{{ $route->courier_name }} · {{ $route->vehicle_plate }}</p>
                        </div>
                        <div class="flex items-center gap-sm">
                            <span class="px-sm py-0.5 rounded-full text-micro bg-surface border border-border-soft text-ink">
                                {{ $route->status->label() }}
                            </span>
                            @if(in_array($route->status, [\App\Enums\DeliveryStatus::DISIAPKAN, \App\Enums\DeliveryStatus::BERANGKAT], true))
                                <a href="{{ route('distribusi.pengiriman.index') }}#route-{{ $route->id }}" class="min-h-[44px] px-md inline-flex items-center rounded-full bg-obsidian text-on-obsidian font-body text-label-sm">
                                    {{ $route->status === \App\Enums\DeliveryStatus::DISIAPKAN ? 'Update Status' : 'Catat Serah Terima' }}
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <aside class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md self-start">
            <h2 class="font-display text-section-title text-ink">SOP Verifikasi Serah Terima</h2>
            <ul class="list-disc pl-lg font-body text-body-md text-ink-muted space-y-2">
                <li>Hitung jumlah ompreng bersama Guru PIC atau Kader Posyandu di lokasi.</li>
                <li>Catat nama lengkap penerima di lapangan saat status diubah menjadi Tiba.</li>
                <li>Unggah bukti foto/PDF surat jalan (maksimal 2 MB) bila tersedia.</li>
                <li>Status <strong>Diterima</strong> hanya ditetapkan setelah Guru PIC mengirim konfirmasi.</li>
            </ul>
        </aside>
    </section>
</x-layouts.tim-distribusi>
