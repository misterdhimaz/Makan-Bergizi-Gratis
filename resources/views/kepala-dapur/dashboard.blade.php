<x-layouts.kepala-dapur title="Dashboard Kepala Dapur" :badges="$badges">
    @php
        $metrics = $data['metrics'];
        $persetujuanTotal = $metrics['menu_menunggu_kepala_dapur'] + $metrics['koreksi_menunggu'];
        $apdCheck = $data['latest_apd_check'];
        $apdPct = $apdCheck && $apdCheck->total_volunteers > 0
            ? (int) round(($apdCheck->compliant_volunteers / $apdCheck->total_volunteers) * 100)
            : 0;
        $siapSaji = $data['siap_saji_temp'];
        $siapSajiVal = $siapSaji['actual'] ?? $siapSaji['aktual'] ?? null;
        $siapSajiOk = $siapSaji['passed'] ?? $siapSaji['sesuai'] ?? true;
    @endphp

    {{-- 4 Kartu KPI Utama --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-md">
        <a href="{{ route('kepala-dapur.persetujuan.index') }}" class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm hover:border-obsidian transition-colors">
            <div class="flex items-center justify-between gap-sm">
                <span class="font-body text-label-sm text-ink-muted">Persetujuan &amp; koreksi</span>
                <span class="px-sm py-0.5 rounded-full bg-alert-soft text-on-alert-soft font-body text-micro">Perlu keputusan</span>
            </div>
            <div class="font-display text-page-title text-ink tabular-nums">{{ number_format($persetujuanTotal, 0, ',', '.') }}</div>
            <p class="font-body text-micro text-ink-muted tabular-nums">
                {{ $metrics['menu_menunggu_kepala_dapur'] }} menu · {{ $metrics['koreksi_menunggu'] }} koreksi menunggu
            </p>
        </a>

        <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm">
            <div class="flex items-center justify-between gap-sm">
                <span class="font-body text-label-sm text-ink-muted">Realisasi produksi hari ini</span>
                <span class="px-sm py-0.5 rounded-full bg-success-soft text-on-success-soft font-mono text-micro tabular-nums">{{ $data['ompreng_pct'] }}%</span>
            </div>
            <div class="font-display text-page-title text-ink tabular-nums">
                {{ number_format($data['ompreng_terisi'], 0, ',', '.') }}
                <span class="text-body-md text-ink-muted font-body">/ {{ number_format($data['target_porsi'], 0, ',', '.') }} porsi</span>
            </div>
            <p class="font-body text-micro text-ink-muted">{{ $data['selisih_bahan_text'] }}</p>
        </article>

        <a href="{{ route('kepala-dapur.tindak-lanjut.index', ['tab' => 'temuan']) }}" class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm hover:border-obsidian transition-colors">
            <div class="flex items-center justify-between gap-sm">
                <span class="font-body text-label-sm text-ink-muted">Kepatuhan APD (35 relawan)</span>
                <span class="px-sm py-0.5 rounded-full bg-alert-soft text-on-alert-soft font-mono text-micro tabular-nums">
                    {{ $metrics['temuan_keamanan_terbuka'] }} temuan terbuka
                </span>
            </div>
            <div class="font-display text-page-title text-ink tabular-nums">
                {{ $apdPct }}%
                <span class="text-body-md text-ink-muted font-body">({{ $apdCheck?->compliant_volunteers ?? 0 }}/{{ $apdCheck?->total_volunteers ?? 0 }})</span>
            </div>
            <p class="font-body text-micro text-ink-muted">
                Shift {{ $apdCheck ? ucfirst($apdCheck->shift) : '-' }} · {{ $metrics['temuan_keamanan_terbuka'] }} temuan menunggu tindak lanjut
            </p>
        </a>

        <a href="{{ route('kepala-dapur.tindak-lanjut.index', ['tab' => 'keluhan']) }}" class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm hover:border-obsidian transition-colors">
            <div class="flex items-center justify-between gap-sm">
                <span class="font-body text-label-sm text-ink-muted">Keluhan distribusi aktif</span>
                @if($metrics['keluhan_serius'] > 0)
                    <span class="px-sm py-0.5 rounded-full bg-danger-soft text-on-danger-soft font-body text-micro">Prioritas</span>
                @endif
            </div>
            <div class="font-display text-page-title text-ink tabular-nums">
                {{ $metrics['keluhan_aktif'] }}
                <span class="text-body-md text-ink-muted font-body">· {{ $data['tiba_count'] }}/{{ $data['total_routes'] }} tiba</span>
            </div>
            <p class="font-body text-micro text-ink-muted tabular-nums">
                {{ $metrics['ringkasan_titik'] }} ({{ number_format($metrics['diterima'], 0, ',', '.') }} / {{ number_format($metrics['dikirim'], 0, ',', '.') }} porsi)
            </p>
        </a>
    </section>

    {{-- Baris 2: Menunggu Keputusan & Koreksi Data Terkunci --}}
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
        <div class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
            <div class="flex items-center justify-between gap-sm">
                <h2 class="font-display text-section-title text-ink">Menunggu keputusan Kepala Dapur</h2>
                <a href="{{ route('kepala-dapur.persetujuan.index', ['tab' => 'menunggu']) }}" class="font-body text-label-sm text-ink underline">Buka</a>
            </div>
            @if($data['menus_pending']->isEmpty())
                <div class="p-md rounded-lg bg-surface-subtle border border-border-soft font-body text-body-md text-ink-muted">
                    Tidak ada menu yang menunggu persetujuan.
                </div>
            @else
                <div class="flex flex-col gap-sm">
                    @foreach($data['menus_pending'] as $menu)
                        <div class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col gap-xs">
                            <div class="flex items-center justify-between gap-sm">
                                <span class="font-display text-card-title text-ink">{{ $menu->operational_date?->translatedFormat('j M Y') }}</span>
                                <span class="px-sm py-0.5 rounded-full bg-success-soft text-on-success-soft font-body text-micro">Lolos PAGU</span>
                            </div>
                            <p class="font-body text-body-md text-ink">{{ $menu->name }}</p>
                            <span class="font-body text-micro text-ink-muted">{{ number_format($menu->target_portions, 0, ',', '.') }} porsi · {{ $menu->last_note }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
            <div class="flex items-center justify-between gap-sm">
                <h2 class="font-display text-section-title text-ink">Koreksi Data Terkunci</h2>
                <a href="{{ route('kepala-dapur.persetujuan.index', ['tab' => 'koreksi']) }}" class="font-body text-label-sm text-ink underline">Tinjau</a>
            </div>
            @if($data['revisions_pending']->isEmpty())
                <div class="p-md rounded-lg bg-surface-subtle border border-border-soft font-body text-body-md text-ink-muted">
                    Tidak ada permintaan koreksi menunggu.
                </div>
            @else
                <div class="flex flex-col gap-sm">
                    @foreach($data['revisions_pending'] as $rev)
                        <div class="p-md rounded-lg bg-alert-soft/40 border border-alert-soft flex flex-col gap-xs">
                            <div class="flex items-center justify-between gap-sm">
                                <span class="font-display text-card-title text-ink">{{ $rev->field ?? 'Data Terkunci' }}</span>
                                <span class="px-sm py-0.5 rounded-full bg-alert-soft text-on-alert-soft font-body text-micro">Menunggu</span>
                            </div>
                            <p class="font-body text-body-md text-ink">{{ $rev->reason }}</p>
                            <span class="font-body text-micro text-ink-muted">Pemohon: {{ $rev->requester?->name ?? '-' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Baris 3: Perlu Tindak Lanjut & Operasional Hari Ini --}}
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
        <div class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
            <div class="flex items-center justify-between gap-sm">
                <h2 class="font-display text-section-title text-ink">Perlu tindak lanjut</h2>
                <a href="{{ route('kepala-dapur.tindak-lanjut.index') }}" class="font-body text-label-sm text-ink underline">Kelola</a>
            </div>
            <div class="flex flex-col gap-sm">
                @foreach($data['open_findings'] as $finding)
                    <div class="p-md rounded-lg bg-danger-soft/40 border border-danger-soft flex flex-col gap-xs">
                        <div class="flex items-center justify-between gap-sm">
                            <span class="font-body text-label-sm font-semibold text-ink">Temuan {{ $finding->type }}</span>
                            <span class="px-sm py-0.5 rounded-full bg-danger-soft text-on-danger-soft font-body text-micro">Terbuka</span>
                        </div>
                        <p class="font-body text-body-md text-ink">{{ $finding->description }}</p>
                    </div>
                @endforeach
                @foreach($data['active_serious_complaints'] as $complaint)
                    <div class="p-md rounded-lg bg-alert-soft/40 border border-alert-soft flex flex-col gap-xs">
                        <div class="flex items-center justify-between gap-sm">
                            <span class="font-mono text-label-sm text-ink">{{ $complaint->ticket_number }} · {{ $complaint->location?->name }}</span>
                            <span class="px-sm py-0.5 rounded-full bg-danger-soft text-on-danger-soft font-body text-micro">Serius</span>
                        </div>
                        <p class="font-body text-body-md text-ink">{{ $complaint->description }}</p>
                    </div>
                @endforeach
                @foreach($data['open_obstacles'] as $obs)
                    <div class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col gap-xs">
                        <div class="flex items-center justify-between gap-sm">
                            <span class="font-body text-label-sm font-semibold text-ink">Kendala Produksi</span>
                            <span class="px-sm py-0.5 rounded-full bg-alert-soft text-on-alert-soft font-body text-micro">Terbuka</span>
                        </div>
                        <p class="font-body text-body-md text-ink">{{ $obs->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-md">
            <div class="flex flex-col gap-md">
                <div class="flex items-center justify-between gap-sm">
                    <h2 class="font-display text-section-title text-ink">Operasional hari ini</h2>
                    <a href="{{ route('kepala-dapur.laporan.index') }}" class="min-h-[44px] px-md inline-flex items-center gap-xs rounded-full bg-obsidian text-on-obsidian font-body text-label-sm">
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">download</span>
                        <span>Ekspor laporan</span>
                    </a>
                </div>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-sm">
                    <div class="p-md rounded-lg bg-surface-subtle border border-border-soft">
                        <dt class="font-body text-micro text-ink-muted">Suhu makanan siap saji</dt>
                        <dd class="font-display text-card-title text-ink mt-xs tabular-nums">
                            {{ $siapSajiVal !== null ? number_format((float) $siapSajiVal, 1, ',', '.').' °C' : '-' }}
                            <span class="ml-xs px-sm py-0.5 rounded-full text-micro {{ $siapSajiOk ? 'bg-success-soft text-on-success-soft' : 'bg-danger-soft text-on-danger-soft' }}">
                                {{ $siapSajiOk ? 'Sesuai' : 'Tidak sesuai' }}
                            </span>
                        </dd>
                    </div>
                    <div class="p-md rounded-lg bg-surface-subtle border border-border-soft">
                        <dt class="font-body text-micro text-ink-muted">Kebersihan dapur</dt>
                        <dd class="font-display text-card-title text-ink mt-xs">
                            {{ $data['kebersihan_items_count'] > 0 ? $data['kebersihan_items_count'].' item SOP' : 'Belum diisi' }}
                        </dd>
                    </div>
                    <div class="p-md rounded-lg bg-surface-subtle border border-border-soft">
                        <dt class="font-body text-micro text-ink-muted">Pengiriman</dt>
                        <dd class="font-display text-card-title text-ink mt-xs tabular-nums">
                            {{ $metrics['titik_terkonfirmasi'] }}/{{ $metrics['total_titik'] }} terkonfirmasi diterima
                        </dd>
                    </div>
                    <div class="p-md rounded-lg bg-surface-subtle border border-border-soft">
                        <dt class="font-body text-micro text-ink-muted">Selisih bahan</dt>
                        <dd class="font-display text-card-title text-ink mt-xs">{{ $data['selisih_bahan_text'] }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    {{-- Baris 4: Tabel Status Menu 26 Nov – 1 Des (Desktop & Mobile Setara) --}}
    <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
        <h2 class="font-display text-section-title text-ink">Status menu 26 Nov – 1 Des</h2>

        {{-- Desktop Table --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border-soft font-body text-micro text-ink-muted uppercase">
                        <th class="py-sm pr-md">Tanggal</th>
                        <th class="py-sm pr-md">Menu</th>
                        <th class="py-sm pr-md">Porsi</th>
                        <th class="py-sm pr-md">Status</th>
                        <th class="py-sm">Catatan terakhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft font-body text-body-md">
                    @foreach($data['menu_range'] as $menu)
                        @php $status = $menu->menuStatus(); @endphp
                        <tr>
                            <td class="py-md pr-md font-mono text-label-sm whitespace-nowrap">{{ $menu->operational_date?->translatedFormat('j M') }}</td>
                            <td class="py-md pr-md text-ink">{{ $menu->name }}</td>
                            <td class="py-md pr-md font-mono tabular-nums">{{ number_format($menu->target_portions, 0, ',', '.') }}</td>
                            <td class="py-md pr-md">
                                <span class="px-sm py-0.5 rounded-full text-micro {{ $status->badgeClasses() }}">{{ $status->displayLabel() }}</span>
                            </td>
                            <td class="py-md text-ink-muted">{{ $menu->last_note ?? $menu->approval_notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards (memuat seluruh field termasuk Catatan terakhir & Acuan operasional) --}}
        <div class="lg:hidden flex flex-col gap-sm">
            @foreach($data['menu_range'] as $menu)
                @php $status = $menu->menuStatus(); @endphp
                <article class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col gap-xs">
                    <div class="flex items-center justify-between gap-sm">
                        <span class="font-mono text-label-sm text-ink">{{ $menu->operational_date?->translatedFormat('j M Y') }} · {{ number_format($menu->target_portions, 0, ',', '.') }} porsi</span>
                        <span class="px-sm py-0.5 rounded-full text-micro {{ $status->badgeClasses() }}">{{ $status->displayLabel() }}</span>
                    </div>
                    <p class="font-body text-body-md text-ink font-medium">{{ $menu->name }}</p>
                    <p class="font-body text-micro text-ink-muted">Catatan terakhir: {{ $menu->last_note ?? $menu->approval_notes ?? '-' }}</p>
                    <p class="font-body text-micro text-ink-muted">Acuan operasional: {{ number_format($menu->target_portions, 0, ',', '.') }} porsi target</p>
                </article>
            @endforeach
        </div>
    </section>
</x-layouts.kepala-dapur>
