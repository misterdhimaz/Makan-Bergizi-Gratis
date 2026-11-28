@php
    $now = now('Asia/Jakarta');
    $hour = (int) $now->format('G');
    $salam = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
    $nameParts = preg_split('/\s+/', trim((string) (auth()->user()?->name ?? 'Rina Marlina'))) ?: [];
    $firstName = collect($nameParts)->first(
        static fn (string $part): bool => mb_strlen($part) > 1 && ! str_ends_with($part, '.')
    ) ?? ($nameParts[0] ?? 'Rina');

    $schoolName = $location?->name ?? 'Pesantren Al-Falah Palembang';
    $schoolShort = str_replace(' Palembang', '', $schoolName);
    $fmt = static fn (int|float|null $n): string => number_format((int) $n, 0, ',', '.');
    $scheduledTime = $todayRoute?->scheduled_arrival_time ?: '08:00';
    $arrivedLabel = $todayRoute?->arrived_at ? $todayRoute->arrived_at->copy()->timezone('Asia/Jakarta')->format('H:i') : '08:12';
    $preparedLabel = $todayRoute?->prepared_at ? $todayRoute->prepared_at->copy()->timezone('Asia/Jakarta')->format('H:i').' WIB' : '06:15 WIB';
    $departedLabel = $todayRoute?->departed_at ? $todayRoute->departed_at->copy()->timezone('Asia/Jakarta')->format('H:i').' WIB' : '07:35 WIB';
    $confirmedLabel = $todayRoute?->confirmed_at ? $todayRoute->confirmed_at->copy()->timezone('Asia/Jakarta')->format('H:i').' WIB' : 'Belum dikonfirmasi';
    $courierName = $todayRoute?->courier_name ?? $todayRoute?->courier?->name ?? 'Dedi Pratama';
    $vehiclePlate = $todayRoute?->vehicle_plate ?? 'B 9142 KDA';
    $organolepticStatus = $todayRoute?->organoleptic_status ?? 'Layak';
    $latestTicket = $complaints->first();
    $hasPriorityTicket = $complaints->contains(fn ($c) => $c->severity === 'kritis' && in_array($c->status, ['open', 'in_progress'], true));

    $components = is_array($todayMenu?->components_json) ? $todayMenu->components_json : [];
    $menuCards = [
        ['label' => 'Makanan Pokok', 'icon' => 'rice_bowl', 'value' => $components['pokok'] ?? 'Nasi Putih'],
        ['label' => 'Lauk Hewani', 'icon' => 'restaurant', 'value' => $components['hewani'] ?? 'Ayam Goreng Lengkuas'],
        ['label' => 'Lauk Nabati', 'icon' => 'lunch_dining', 'value' => $components['nabati'] ?? 'Tahu Goreng'],
        ['label' => 'Sayur', 'icon' => 'eco', 'value' => $components['sayur'] ?? 'Tumis Buncis & Wortel'],
        ['label' => 'Pelengkap', 'icon' => 'local_drink', 'value' => $components['minuman'] ?? 'Susu UHT 200 ml'],
        ['label' => 'Wadah', 'icon' => 'takeout_dining', 'value' => 'Ompreng Stainless'],
    ];
@endphp

<x-layouts.guru-pic
    :title="$salam.', '.$firstName"
    mobile-title="REKAGIZI MBG"
    :subtitle="'Pemantauan penerimaan MBG '.$schoolShort.' · '.$now->translatedFormat('l, d M Y')"
    :location="$location"
>
    <x-slot:actions>
        <a href="{{ route('guru-pic.confirmation.index') }}"
           class="min-h-[44px] inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 transition-opacity whitespace-nowrap">
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">fact_check</span>
            <span>Konfirmasi Penerimaan</span>
        </a>
    </x-slot:actions>

    {{-- ================= DESKTOP VIEW (1:1 Stitch Desktop) ================= --}}
    <div class="hidden lg:flex lg:flex-col lg:gap-lg">
        {{-- 4 KPI Cards Row --}}
        <section aria-label="Ringkasan Penerimaan Hari Ini" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-md items-stretch">
            {{-- Lime Focal Card --}}
            <article class="bg-lime-accent rounded-xl p-lg flex flex-col justify-between gap-sm h-full">
                <div class="flex items-center justify-between gap-sm">
                    <span class="font-body text-label-sm text-on-lime">Target porsi hari ini</span>
                    <span class="material-symbols-outlined text-card-title text-on-lime" aria-hidden="true">lunch_dining</span>
                </div>
                <div class="flex items-baseline justify-between gap-sm">
                    <span class="font-display text-kpi text-on-lime tabular-nums">{{ $fmt($targetPortions) }}</span>
                    <span class="font-mono text-micro text-on-lime tabular-nums">porsi</span>
                </div>
                <span class="font-body text-micro text-on-lime">Siswa SMP &amp; SMA · SPPG Sungai Selayur 1</span>
            </article>

            <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm h-full">
                <div class="flex items-center justify-between gap-sm">
                    <span class="font-body text-label-sm text-ink-muted">Pengiriman</span>
                    @if($delayMinutes > 0)
                        <span class="px-sm py-xs rounded-full bg-alert-soft text-on-alert-soft font-mono text-micro whitespace-nowrap tabular-nums">Telat {{ $delayMinutes }} mnt</span>
                    @else
                        <span class="px-sm py-xs rounded-full bg-success-soft text-on-success-soft font-mono text-micro whitespace-nowrap">Tepat waktu</span>
                    @endif
                </div>
                <div class="flex items-baseline justify-between gap-sm">
                    <span class="font-display text-kpi text-ink tabular-nums">{{ $arrivedLabel }}</span>
                    <span class="font-mono text-micro text-ink-muted tabular-nums">Jadwal {{ $scheduledTime }} WIB</span>
                </div>
                <span class="font-body text-micro text-ink-muted">Kurir {{ $courierName }} · Armada {{ $vehiclePlate }}</span>
            </article>

            <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm h-full">
                <div class="flex items-center justify-between gap-sm">
                    <span class="font-body text-label-sm text-ink-muted">Porsi diterima</span>
                    @if($variance < 0)
                        <span class="px-sm py-xs rounded-full bg-alert-soft text-on-alert-soft font-mono text-micro whitespace-nowrap tabular-nums">{{ $variance }} porsi</span>
                    @else
                        <span class="px-sm py-xs rounded-full bg-success-soft text-on-success-soft font-mono text-micro whitespace-nowrap">Sesuai</span>
                    @endif
                </div>
                <div class="flex items-baseline justify-between gap-sm">
                    <span class="font-display text-kpi text-ink tabular-nums">{{ $fmt($confirmedPortions) }}</span>
                    <span class="font-mono text-micro text-ink-muted tabular-nums">dari {{ $fmt($targetPortions) }}</span>
                </div>
                <span class="font-body text-micro text-ink-muted truncate">{{ $todayRoute?->notes ?: 'Sesuai pengiriman' }}</span>
            </article>

            <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm h-full">
                <div class="flex items-center justify-between gap-sm">
                    <span class="font-body text-label-sm text-ink-muted">Tiket keluhan</span>
                    @if($hasPriorityTicket)
                        <span class="px-sm py-xs rounded-full bg-danger-soft text-on-danger-soft font-body text-micro whitespace-nowrap">Prioritas</span>
                    @elseif($activeComplaintsCount > 0)
                        <span class="px-sm py-xs rounded-full bg-alert-soft text-on-alert-soft font-body text-micro whitespace-nowrap">Aktif</span>
                    @else
                        <span class="px-sm py-xs rounded-full bg-success-soft text-on-success-soft font-body text-micro whitespace-nowrap">Aman</span>
                    @endif
                </div>
                <div class="flex items-baseline justify-between gap-sm">
                    <span class="font-display text-kpi text-ink tabular-nums">{{ $activeComplaintsCount }} Aktif</span>
                    <span class="font-mono text-micro text-ink-muted tabular-nums">
                        {{ $latestTicket?->reported_at ? $latestTicket->reported_at->copy()->timezone('Asia/Jakarta')->format('H:i').' WIB' : '—' }}
                    </span>
                </div>
                <span class="font-mono text-micro text-ink-muted tabular-nums truncate">
                    @if($latestTicket)
                        {{ $latestTicket->ticket_number }} · {{ $latestTicket->status === 'resolved' ? 'Selesai' : 'Dalam tindak lanjut' }}
                    @else
                        Belum ada tiket keluhan aktif
                    @endif
                </span>
            </article>
        </section>

        {{-- Middle Grid: Menu Hari Ini + Status Pengiriman & Aksi Cepat --}}
        <section class="grid grid-cols-1 xl:grid-cols-3 gap-lg items-stretch">
            <article class="xl:col-span-2 bg-surface rounded-xl p-xl border border-border-soft flex flex-col justify-between gap-lg">
                <div class="flex flex-wrap items-center justify-between gap-sm">
                    <div class="flex flex-col gap-xs">
                        <h2 class="font-display text-section-title text-ink">Menu Hari Ini</h2>
                        <span class="font-body text-micro text-ink-muted">{{ $now->translatedFormat('l, d M Y') }} · Disetujui Kepala Dapur · SPPG Sungai Selayur 1</span>
                    </div>
                    <span class="inline-flex items-center gap-xs px-md py-xs rounded-full {{ $organolepticStatus === 'Layak' ? 'bg-success-soft text-on-success-soft' : 'bg-alert-soft text-on-alert-soft' }} font-body text-label-sm whitespace-nowrap">
                        <span class="material-symbols-outlined text-label-sm" aria-hidden="true">verified</span>
                        Uji Organoleptik: {{ $organolepticStatus }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-md">
                    @foreach($menuCards as $item)
                        <div class="p-md rounded-md bg-surface-subtle border border-border-soft flex items-center gap-sm">
                            <div class="w-8 h-8 rounded-sm bg-surface border border-border-soft flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-card-title text-ink" aria-hidden="true">{{ $item['icon'] }}</span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="font-body text-micro text-ink-muted">{{ $item['label'] }}</span>
                                <span class="font-body text-card-title text-ink truncate">{{ $item['value'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Progress Pengiriman --}}
                <div class="pt-md border-t border-border-soft flex flex-col gap-sm">
                    <span class="font-body text-label-sm text-ink">Jejak Pengiriman Hari Ini</span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-sm">
                        <div class="p-sm rounded-md bg-success-soft/60 flex items-center gap-sm">
                            <span class="material-symbols-outlined text-card-title text-on-success-soft shrink-0" aria-hidden="true">check_circle</span>
                            <div class="flex flex-col min-w-0">
                                <span class="font-body text-micro text-on-success-soft">1. Disiapkan</span>
                                <span class="font-mono text-label-sm text-on-success-soft tabular-nums">{{ $preparedLabel }}</span>
                            </div>
                        </div>
                        <div class="p-sm rounded-md bg-success-soft/60 flex items-center gap-sm">
                            <span class="material-symbols-outlined text-card-title text-on-success-soft shrink-0" aria-hidden="true">local_shipping</span>
                            <div class="flex flex-col min-w-0">
                                <span class="font-body text-micro text-on-success-soft">2. Berangkat</span>
                                <span class="font-mono text-label-sm text-on-success-soft tabular-nums">{{ $departedLabel }}</span>
                            </div>
                        </div>
                        <div class="p-sm rounded-md {{ $delayMinutes > 0 ? 'bg-alert-soft text-on-alert-soft' : 'bg-success-soft/60 text-on-success-soft' }} flex items-center gap-sm">
                            <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">schedule</span>
                            <div class="flex flex-col min-w-0">
                                <span class="font-body text-micro">3. Tiba</span>
                                <span class="font-mono text-label-sm tabular-nums">{{ $arrivedLabel }} WIB</span>
                            </div>
                        </div>
                        <div class="p-sm rounded-md {{ $todayRoute?->confirmed_at ? 'bg-success-soft/60 text-on-success-soft' : 'bg-surface-subtle text-ink-muted' }} flex items-center gap-sm">
                            <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">verified</span>
                            <div class="flex flex-col min-w-0">
                                <span class="font-body text-micro">4. Dikonfirmasi</span>
                                <span class="font-mono text-label-sm tabular-nums truncate">{{ $confirmedLabel }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            {{-- Ringkasan Keluhan & Aksi Cepat --}}
            <aside class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col justify-between gap-md">
                <div class="flex flex-col gap-md">
                    <div class="flex items-center justify-between gap-sm">
                        <h2 class="font-display text-section-title text-ink">Tindak Lanjut Keluhan</h2>
                        <a href="{{ route('guru-pic.complaints.index') }}" class="font-body text-label-sm text-ink underline">Lihat semua</a>
                    </div>

                    @forelse($complaints->take(2) as $ticket)
                        @php $isClosed = $ticket->status === 'resolved'; @endphp
                        <div class="p-md rounded-md {{ $isClosed ? 'bg-surface-subtle border border-border-soft' : 'bg-alert-soft/50 border border-alert-soft' }} flex flex-col gap-xs">
                            <div class="flex items-center justify-between gap-sm">
                                <span class="font-mono text-micro {{ $isClosed ? 'text-ink-muted' : 'text-on-alert-soft' }} tabular-nums">{{ $ticket->ticket_number }}</span>
                                <span class="px-sm py-xs rounded-full {{ $isClosed ? 'bg-success-soft text-on-success-soft' : 'bg-alert-soft text-on-alert-soft' }} font-body text-micro">
                                    {{ $isClosed ? 'Selesai' : 'Dalam Tindak Lanjut' }}
                                </span>
                            </div>
                            <p class="font-body text-body-md text-ink text-pretty">{{ $ticket->description }}</p>
                            <span class="font-body text-micro text-ink-muted">
                                {{ $ticket->resolution_notes ?: 'Menunggu tindak lanjut SPPG' }}
                            </span>
                        </div>
                    @empty
                        <p class="font-body text-body-md text-ink-muted">Belum ada tiket keluhan untuk sekolah ini.</p>
                    @endforelse
                </div>

                <a href="{{ route('guru-pic.complaints.index', ['aksi' => 'buat']) }}"
                   class="w-full min-h-[44px] inline-flex items-center justify-center gap-sm px-lg py-sm rounded-full bg-surface-subtle border border-border-soft font-body text-label-sm text-ink hover:bg-surface-muted">
                    <span class="material-symbols-outlined text-card-title" aria-hidden="true">add_a_photo</span>
                    Buat Tiket Keluhan Baru
                </a>
            </aside>
        </section>

        {{-- Riwayat Penerimaan Sekolah --}}
        <section class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
            <div class="flex flex-wrap items-center justify-between gap-sm">
                <h2 class="font-display text-section-title text-ink">Riwayat Penerimaan {{ $schoolShort }}</h2>
                <span class="font-body text-micro text-ink-muted">Menampilkan {{ $historyRoutes->count() }} hari terakhir</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-soft">
                            <th class="py-sm pr-md font-body text-label-sm text-ink-muted">Tanggal</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Dikirim</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Diterima</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Selisih</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted">Jam Tiba</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted">Organoleptik</th>
                            <th class="py-sm pl-md font-body text-label-sm text-ink-muted">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        @foreach($historyRoutes as $row)
                            @php
                                $rowConfirmed = $row->confirmed_portions ?? $row->dispatched_portions;
                                $rowDiff = (int) $rowConfirmed - (int) $row->dispatched_portions;
                                $rowDelay = $row->delayMinutes();
                                $rowArrived = $row->arrived_at ? $row->arrived_at->copy()->timezone('Asia/Jakarta')->format('H:i').' WIB' : '—';
                                $isBermasalah = $row->status?->value === 'Bermasalah';
                            @endphp
                            <tr class="{{ $isBermasalah ? 'bg-alert-soft/30' : '' }}">
                                <td class="py-md pr-md font-mono text-label-sm text-ink whitespace-nowrap">{{ $row->operational_date?->translatedFormat('d M Y') }}</td>
                                <td class="py-md px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $fmt($row->dispatched_portions) }}</td>
                                <td class="py-md px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $fmt($rowConfirmed) }}</td>
                                <td class="py-md px-md text-right">
                                    @if($rowDiff < 0)
                                        <span class="px-sm py-xs rounded-full bg-alert-soft text-on-alert-soft font-mono text-micro tabular-nums">{{ $rowDiff }}</span>
                                    @else
                                        <span class="font-mono text-micro text-ink-muted tabular-nums">0</span>
                                    @endif
                                </td>
                                <td class="py-md px-md font-mono text-label-sm {{ $rowDelay > 0 ? 'text-on-alert-soft' : 'text-ink' }} whitespace-nowrap">
                                    {{ $rowArrived }}{{ $rowDelay > 0 ? ' (Terlambat)' : '' }}
                                </td>
                                <td class="py-md px-md">
                                    <span class="px-sm py-xs rounded-full bg-success-soft text-on-success-soft font-body text-micro">{{ $row->organoleptic_status ?: 'Layak' }}</span>
                                </td>
                                <td class="py-md pl-md">
                                    @if($isBermasalah)
                                        <span class="px-sm py-xs rounded-full bg-alert-soft text-on-alert-soft font-body text-micro">Ada Keluhan</span>
                                    @else
                                        <span class="px-sm py-xs rounded-full bg-success-soft text-on-success-soft font-body text-micro">Sesuai</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- ================= MOBILE VIEW (1:1 Stitch Mobile) ================= --}}
    <div class="lg:hidden flex flex-col gap-lg">
        {{-- Greeting --}}
        <section class="flex flex-col gap-xs">
            <h1 class="font-display text-page-title text-ink">{{ $salam }}, {{ $firstName }}</h1>
            <p class="font-body text-body-md text-ink-muted">{{ $now->translatedFormat('l, d M Y') }} · {{ $fmt($targetPortions) }} porsi terjadwal</p>
        </section>

        {{-- Lime Focal Card + 2 Compact Cards --}}
        <section class="grid grid-cols-1 gap-md">
            <article class="bg-lime-accent rounded-xl p-lg flex flex-col gap-sm">
                <div class="flex items-center justify-between gap-sm">
                    <span class="font-body text-card-title text-on-lime">Penerimaan Hari Ini</span>
                    <div class="flex items-center gap-xs shrink-0">
                        @if($variance < 0)
                            <span class="px-sm py-xs rounded-full bg-alert-soft text-on-alert-soft font-mono text-micro tabular-nums whitespace-nowrap">{{ $variance }} porsi</span>
                        @endif
                        @if($delayMinutes > 0)
                            <span class="px-sm py-xs rounded-full bg-alert-soft text-on-alert-soft font-mono text-micro tabular-nums whitespace-nowrap">Telat {{ $delayMinutes }} mnt</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-baseline gap-sm">
                    <span class="font-display text-kpi text-on-lime tabular-nums">{{ $fmt($confirmedPortions) }} / {{ $fmt($targetPortions) }} porsi</span>
                </div>
                <span class="font-body text-micro text-on-lime">Tiba {{ $arrivedLabel }} WIB · {{ $todayRoute?->notes ?: 'Sesuai pengiriman' }}</span>
                <a href="{{ route('guru-pic.confirmation.index') }}" class="min-h-[44px] w-full inline-flex items-center justify-center gap-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm">
                    <span class="material-symbols-outlined text-card-title" aria-hidden="true">fact_check</span>
                    Konfirmasi &amp; Uji Organoleptik
                </a>
            </article>

            <div class="grid grid-cols-2 gap-md">
                <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-xs">
                    <div class="flex items-center justify-between gap-xs">
                        <span class="font-body text-micro text-ink-muted">Uji Organoleptik</span>
                        <span class="material-symbols-outlined text-card-title text-success" aria-hidden="true">verified</span>
                    </div>
                    <span class="font-display text-section-title text-ink">{{ $organolepticStatus }}</span>
                    <span class="font-body text-micro text-on-success-soft">Diisi {{ $confirmedLabel }}</span>
                </article>
                <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-xs">
                    <div class="flex items-center justify-between gap-xs">
                        <span class="font-body text-micro text-ink-muted">Tiket Keluhan</span>
                        <span class="material-symbols-outlined text-card-title text-on-alert-soft" aria-hidden="true">report_problem</span>
                    </div>
                    <span class="font-display text-section-title text-ink tabular-nums">{{ $activeComplaintsCount }} Aktif</span>
                    <span class="font-mono text-micro text-on-alert-soft tabular-nums truncate">{{ $latestTicket?->ticket_number ?? 'Aman' }}</span>
                </article>
            </div>
        </section>

        {{-- Menu Hari Ini --}}
        <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
            <div class="flex items-center justify-between gap-sm">
                <h2 class="font-display text-section-title text-ink">Menu Hari Ini</h2>
                <span class="px-sm py-xs rounded-full bg-surface-subtle border border-border-soft font-mono text-micro text-ink-muted">{{ $now->translatedFormat('d M Y') }}</span>
            </div>
            <div class="flex flex-wrap gap-xs">
                @foreach(array_slice($menuCards, 0, 5) as $item)
                    <span class="px-sm py-xs rounded-md bg-surface-subtle border border-border-soft font-body text-label-sm text-ink">{{ $item['value'] }}</span>
                @endforeach
            </div>
            <div class="grid grid-cols-2 gap-sm pt-sm border-t border-border-soft">
                <div class="p-sm rounded-md bg-success-soft/60 flex flex-col gap-xs">
                    <span class="font-body text-micro text-on-success-soft">Berangkat SPPG</span>
                    <span class="font-mono text-label-sm text-on-success-soft tabular-nums">{{ $departedLabel }}</span>
                </div>
                <div class="p-sm rounded-md {{ $delayMinutes > 0 ? 'bg-alert-soft text-on-alert-soft' : 'bg-success-soft/60 text-on-success-soft' }} flex flex-col gap-xs">
                    <span class="font-body text-micro">{{ $delayMinutes > 0 ? "Tiba (Telat {$delayMinutes} mnt)" : 'Tiba Tepat Waktu' }}</span>
                    <span class="font-mono text-label-sm tabular-nums">{{ $arrivedLabel }} WIB</span>
                </div>
            </div>
        </section>

        {{-- Keluhan Terakhir --}}
        <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
            <div class="flex items-center justify-between gap-sm">
                <h2 class="font-display text-section-title text-ink">Keluhan Terakhir</h2>
                <a href="{{ route('guru-pic.complaints.index') }}" class="font-body text-label-sm text-ink underline">Semua</a>
            </div>
            @if($latestTicket)
                <div class="p-md rounded-md {{ $latestTicket->status === 'resolved' ? 'bg-surface-subtle border border-border-soft' : 'bg-alert-soft/50 border border-alert-soft' }} flex flex-col gap-xs">
                    <div class="flex items-center justify-between gap-sm">
                        <span class="font-mono text-micro {{ $latestTicket->status === 'resolved' ? 'text-ink-muted' : 'text-on-alert-soft' }}">{{ $latestTicket->ticket_number }}</span>
                        <span class="px-sm py-xs rounded-full {{ $latestTicket->status === 'resolved' ? 'bg-success-soft text-on-success-soft' : 'bg-alert-soft text-on-alert-soft' }} font-body text-micro">
                            {{ $latestTicket->status === 'resolved' ? 'Selesai' : 'Tindak Lanjut' }}
                        </span>
                    </div>
                    <p class="font-body text-body-md text-ink text-pretty">{{ $latestTicket->description }}</p>
                    <span class="font-body text-micro text-ink-muted">{{ $latestTicket->reported_at?->copy()->timezone('Asia/Jakarta')->translatedFormat('d M Y · H:i') }} WIB</span>
                </div>
            @endif
            <a href="{{ route('guru-pic.complaints.index', ['aksi' => 'buat']) }}" class="min-h-[44px] w-full inline-flex items-center justify-center gap-sm rounded-full bg-surface-subtle border border-border-soft font-body text-label-sm text-ink">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">add_a_photo</span>
                Buat Tiket Keluhan
            </a>
        </section>
    </div>
</x-layouts.guru-pic>
