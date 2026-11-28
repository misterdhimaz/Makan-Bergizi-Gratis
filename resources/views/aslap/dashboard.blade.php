<x-layouts.aslap title="Dashboard Asisten Lapangan" :complaintBadge="$complaintBadge">
    @php $metrics = $data['metrics']; @endphp

    {{-- 4 Kartu KPI --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-md">
        <a href="{{ route('aslap.keluhan.index') }}" class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm hover:border-obsidian transition-colors">
            <div class="flex items-center justify-between gap-sm">
                <span class="font-body text-label-sm text-ink-muted">Keluhan perlu tindak lanjut</span>
                @if($metrics['keluhan_serius'] > 0)
                    <span class="px-sm py-0.5 rounded-full bg-danger-soft text-on-danger-soft font-body text-micro">Prioritas</span>
                @endif
            </div>
            <div class="font-display text-page-title text-ink tabular-nums">{{ $metrics['keluhan_aktif'] }}</div>
            <p class="font-body text-micro text-ink-muted">{{ $metrics['keluhan_serius'] }} prioritas serius aktif</p>
        </a>

        <a href="{{ route('aslap.pengadaan.index') }}" class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm hover:border-obsidian transition-colors">
            <div class="flex items-center justify-between gap-sm">
                <span class="font-body text-label-sm text-ink-muted">Penerimaan bahan baku</span>
                <span class="px-sm py-0.5 rounded-full bg-alert-soft text-on-alert-soft font-body text-micro">Menu {{ $data['next_menu']?->operational_date?->translatedFormat('j M') }}</span>
            </div>
            <div class="font-display text-page-title text-ink tabular-nums">{{ $data['proc_received_count'] }} / {{ $data['proc_total_count'] }}</div>
            <p class="font-body text-micro text-ink-muted">Menunggu 20:00 malam ini · Nadia Putri</p>
        </a>

        <a href="{{ route('aslap.distribusi.index') }}" class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm hover:border-obsidian transition-colors">
            <div class="flex items-center justify-between gap-sm">
                <span class="font-body text-label-sm text-ink-muted">Rute distribusi terjadwal</span>
                <span class="px-sm py-0.5 rounded-full bg-surface-subtle border border-border-soft font-mono text-micro tabular-nums">{{ $data['editable_routes']->count() }} dapat diubah</span>
            </div>
            <div class="font-display text-page-title text-ink tabular-nums">{{ $data['routes_today']->count() }} rute</div>
            <p class="font-body text-micro text-ink-muted tabular-nums">{{ number_format($data['total_scheduled_portions'], 0, ',', '.') }} porsi total hari ini</p>
        </a>

        <a href="{{ route('aslap.keluhan.index') }}" class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm hover:border-obsidian transition-colors">
            <div class="flex items-center justify-between gap-sm">
                <span class="font-body text-label-sm text-ink-muted">Konfirmasi penerimaan</span>
                <span class="px-sm py-0.5 rounded-full bg-alert-soft text-on-alert-soft font-mono text-micro tabular-nums">Selisih {{ $metrics['selisih_terkonfirmasi'] }}</span>
            </div>
            <div class="font-display text-page-title text-ink tabular-nums">
                {{ $metrics['titik_terkonfirmasi'] }} / {{ $metrics['total_titik'] }}
                <span class="text-body-md font-body text-ink-muted">({{ number_format($metrics['diterima'], 0, ',', '.') }} porsi)</span>
            </div>
            <p class="font-body text-micro text-ink-muted tabular-nums">
                Dikirim {{ number_format($metrics['dikirim'], 0, ',', '.') }} · Belum konfirmasi {{ number_format($metrics['belum_dikonfirmasi'], 0, ',', '.') }} ({{ $data['variance_locations_count'] }} lokasi selisih)
            </p>
        </a>
    </section>

    {{-- Tindakan Prioritas Hari Ini --}}
    <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
        <div class="flex items-center justify-between gap-sm">
            <h2 class="font-display text-section-title text-ink">Tindakan Prioritas Hari Ini</h2>
            <a href="{{ route('aslap.keluhan.index') }}" class="font-body text-label-sm text-ink underline">Buka Keluhan &amp; Laporan</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
            <div class="p-md rounded-lg bg-danger-soft/40 border border-danger-soft flex flex-col gap-xs">
                <span class="font-body text-label-sm font-semibold text-ink">Keluhan Serius Terbuka</span>
                @forelse($data['active_complaints'] as $c)
                    <p class="font-body text-body-md text-ink">{{ $c->ticket_number }} · {{ $c->location?->name }}</p>
                    <span class="font-body text-micro text-ink-muted">{{ $c->description }}</span>
                @empty
                    <p class="font-body text-body-md text-ink-muted">Tidak ada keluhan aktif.</p>
                @endforelse
            </div>
            <div class="p-md rounded-lg bg-alert-soft/40 border border-alert-soft flex flex-col gap-xs">
                <span class="font-body text-label-sm font-semibold text-ink">Penerimaan Bahan Belum Dicatat</span>
                <p class="font-body text-body-md text-ink tabular-nums">{{ $data['proc_total_count'] - $data['proc_received_count'] }} dari {{ $data['proc_total_count'] }} bahan menunggu kedatangan</p>
                <a href="{{ route('aslap.pengadaan.index') }}" class="font-body text-micro text-ink underline">Catat penerimaan →</a>
            </div>
            <div class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col gap-xs">
                <span class="font-body text-label-sm font-semibold text-ink">Jadwal Masih Dapat Diubah</span>
                <p class="font-body text-body-md text-ink tabular-nums">{{ $data['editable_routes']->count() }} rute berstatus Disiapkan</p>
                <a href="{{ route('aslap.distribusi.index') }}" class="font-body text-micro text-ink underline">Atur jadwal &amp; armada →</a>
            </div>
        </div>
    </section>

    {{-- Monitoring Penerimaan Hari Ini (7 Lokasi) --}}
    <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
        <h2 class="font-display text-section-title text-ink">Monitoring Penerimaan Hari Ini (7 Lokasi)</h2>

        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border-soft font-body text-micro text-ink-muted uppercase">
                        <th class="py-sm pr-md">Tujuan</th>
                        <th class="py-sm pr-md">Pengantar &amp; Armada</th>
                        <th class="py-sm pr-md">Porsi</th>
                        <th class="py-sm">Status Terima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft font-body text-body-md">
                    @foreach($data['routes_today'] as $route)
                        @php
                            $diff = $route->confirmed_portions !== null ? ($route->confirmed_portions - $route->dispatched_portions) : null;
                        @endphp
                        <tr>
                            <td class="py-md pr-md font-medium text-ink">{{ $route->location?->name }}</td>
                            <td class="py-md pr-md text-ink-muted">{{ $route->courier_name }} · {{ $route->vehicle_plate }}</td>
                            <td class="py-md pr-md font-mono tabular-nums">{{ number_format($route->dispatched_portions, 0, ',', '.') }}</td>
                            <td class="py-md">
                                @if($route->confirmed_portions === null)
                                    <span class="px-sm py-0.5 rounded-full bg-alert-soft text-on-alert-soft text-micro">Menunggu konfirmasi</span>
                                @elseif($diff === 0)
                                    <span class="px-sm py-0.5 rounded-full bg-success-soft text-on-success-soft text-micro">Sesuai ({{ $route->confirmed_at?->timezone('Asia/Jakarta')->format('H:i') }})</span>
                                @else
                                    <span class="px-sm py-0.5 rounded-full bg-danger-soft text-on-danger-soft text-micro">Kurang {{ abs($diff) }} ({{ $route->confirmed_at?->timezone('Asia/Jakarta')->format('H:i') }})</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="lg:hidden flex flex-col gap-sm">
            @foreach($data['routes_today'] as $route)
                @php $diff = $route->confirmed_portions !== null ? ($route->confirmed_portions - $route->dispatched_portions) : null; @endphp
                <article class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col gap-xs">
                    <div class="flex items-center justify-between gap-sm">
                        <span class="font-display text-card-title text-ink">{{ $route->location?->name }}</span>
                        <span class="font-mono text-label-sm tabular-nums">{{ number_format($route->dispatched_portions, 0, ',', '.') }} porsi</span>
                    </div>
                    <p class="font-body text-micro text-ink-muted">{{ $route->courier_name }} · {{ $route->vehicle_plate }}</p>
                    <div>
                        @if($route->confirmed_portions === null)
                            <span class="px-sm py-0.5 rounded-full bg-alert-soft text-on-alert-soft text-micro">Menunggu konfirmasi</span>
                        @elseif($diff === 0)
                            <span class="px-sm py-0.5 rounded-full bg-success-soft text-on-success-soft text-micro">Sesuai ({{ $route->confirmed_at?->timezone('Asia/Jakarta')->format('H:i') }})</span>
                        @else
                            <span class="px-sm py-0.5 rounded-full bg-danger-soft text-on-danger-soft text-micro">Kurang {{ abs($diff) }} ({{ $route->confirmed_at?->timezone('Asia/Jakarta')->format('H:i') }})</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</x-layouts.aslap>
