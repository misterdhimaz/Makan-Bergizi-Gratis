@php
    $hour = (int) now('Asia/Jakarta')->format('G');
    $salam = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
    $nameParts = preg_split('/\s+/', trim((string) (auth()->user()?->name ?? 'Nadia Putri'))) ?: [];
    $firstName = collect($nameParts)->first(
        static fn (string $part): bool => mb_strlen($part) > 1 && ! str_ends_with($part, '.')
    ) ?? ($nameParts[0] ?? 'Nadia');
    $fmt = static fn (int|float|null $n): string => number_format((int) $n, 0, ',', '.');
    $varianceSummary = $varianceItems->pluck('short')->implode(' · ');
@endphp

<x-layouts.ahli-gizi
    :title="$salam.', '.$firstName"
    :subtitle="'Ringkasan operasional menu, pemorsian, dan keamanan pangan · '.now('Asia/Jakarta')->translatedFormat('l, d M Y')"
    breadcrumb="Ahli Gizi · SPPG Sungai Selayur 1"
>
    <x-slot:actions>
        <a href="{{ route('ahli-gizi.menu.index', ['aksi' => 'buat']) }}"
           class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90">
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">add</span>
            Buat Draf Menu
        </a>
    </x-slot:actions>

    <div
        x-data="{
            revisiOpen: false,
            revisiAction: '{{ $todayMenu ? route('ahli-gizi.menu.request-revision', $todayMenu) : '#' }}',
            revisiLabel: '{{ $todayMenu ? ($todayMenu->operational_date?->translatedFormat('d M Y').' · Status: Disetujui (terkunci)') : '' }}',
            openRevisi() {
                this.revisiOpen = true;
            }
        }"
        class="flex flex-col gap-lg"
    >
        {{-- ROW 1: 6 Kartu KPI --}}
        <section aria-label="Ringkasan metrik Ahli Gizi" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-md items-stretch">
            {{-- 1. Menu hari ini --}}
            <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm">
                <div class="flex items-center justify-between gap-sm">
                    <span class="font-body text-label-sm text-ink-muted">Menu hari ini</span>
                    <span class="inline-flex items-center gap-xs px-sm py-xs rounded-full bg-success-soft text-on-success-soft font-body text-micro whitespace-nowrap">
                        <span class="material-symbols-outlined text-micro" aria-hidden="true">lock</span>
                        {{ $todayMenu?->isLocked() ? 'Disetujui' : ($todayMenu?->display_status_label ?? 'Belum ada') }}
                    </span>
                </div>
                <div class="flex flex-col gap-xs">
                    <span class="font-display text-section-title text-ink">{{ $todayMenu?->operational_date?->translatedFormat('d M Y') ?? now('Asia/Jakarta')->translatedFormat('d M Y') }}</span>
                    <p class="font-body text-micro text-ink-muted truncate">{{ $todayMenu?->menu_name ?? 'Belum ada menu' }}</p>
                </div>
                <div class="pt-xs flex items-center justify-between gap-sm border-t border-border-soft">
                    <span class="font-body text-micro text-ink-muted">{{ $todayMenu?->isLocked() ? 'Data terkunci' : 'Draf aktif' }}</span>
                    @if($todayMenu && $todayMenu->isLocked())
                        <button type="button" @click="openRevisi()" class="px-md py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink hover:bg-surface-muted whitespace-nowrap cursor-pointer">
                            Ajukan revisi menu
                        </button>
                    @endif
                </div>
            </article>

            {{-- 2. Target porsi --}}
            <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm">
                <div class="flex items-center justify-between gap-sm">
                    <span class="font-body text-label-sm text-ink-muted">Target porsi</span>
                    <span class="material-symbols-outlined text-card-title text-ink-muted" aria-hidden="true">groups</span>
                </div>
                <div class="flex items-baseline justify-between gap-sm">
                    <span class="font-display text-kpi text-ink tabular-nums">{{ $fmt($targetPortions) }}</span>
                    <span class="font-mono text-micro text-ink-muted tabular-nums">4 kategori</span>
                </div>
                <span class="font-body text-micro text-ink-muted">Siswa, Ibu hamil, Ibu menyusui, Balita</span>
            </article>

            {{-- 3. Ompreng terisi --}}
            <article class="bg-lime-accent rounded-xl p-lg flex flex-col justify-between gap-sm">
                <div class="flex items-center justify-between gap-sm">
                    <span class="font-body text-label-sm text-on-lime">Ompreng terisi</span>
                    <span class="px-sm py-xs rounded-full bg-success-soft text-on-success-soft font-mono text-micro tabular-nums">{{ $omprengPct }}%</span>
                </div>
                <div class="flex items-baseline justify-between gap-sm">
                    <span class="font-display text-kpi text-on-lime tabular-nums">{{ $fmt($omprengFilled) }}</span>
                    <span class="font-mono text-micro text-on-lime tabular-nums">dari {{ $fmt($targetPortions) }}</span>
                </div>
                <span class="font-body text-micro text-on-lime">{{ $omprengFilled >= $targetPortions ? 'Pemorsian hari ini lengkap' : 'Pemorsian sedang berjalan' }}</span>
            </article>

            {{-- 4. Selisih bahan --}}
            <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm">
                <div class="flex items-center justify-between gap-sm">
                    <span class="font-body text-label-sm text-ink-muted">Selisih bahan</span>
                    <span class="px-sm py-xs rounded-full {{ $varianceItems->isNotEmpty() ? 'bg-alert-soft text-on-alert-soft' : 'bg-success-soft text-on-success-soft' }} font-body text-micro">
                        {{ $varianceItems->isNotEmpty() ? 'Perlu dicermati' : 'Sesuai rencana' }}
                    </span>
                </div>
                <div class="flex items-baseline justify-between gap-sm">
                    <span class="font-display text-kpi text-ink tabular-nums">{{ $varianceItems->count() }} bahan</span>
                    <span class="font-mono text-micro text-on-alert-soft tabular-nums truncate">{{ $varianceSummary ?: '0 selisih' }}</span>
                </div>
                <span class="font-body text-micro text-ink-muted">Selisih rencana vs digunakan</span>
            </article>

            {{-- 5. Kepatuhan APD --}}
            <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm">
                <div class="flex items-center justify-between gap-sm">
                    <span class="font-body text-label-sm text-ink-muted">Kepatuhan APD</span>
                    <span class="px-sm py-xs rounded-full bg-success-soft text-on-success-soft font-mono text-micro tabular-nums">
                        {{ $morningCheck ? $morningCheck->compliancePercent() : '—' }}
                    </span>
                </div>
                <div class="flex items-baseline justify-between gap-sm">
                    <span class="font-display text-kpi text-ink tabular-nums">
                        {{ $morningCheck ? $morningCheck->compliant_volunteers.' / '.$morningCheck->total_volunteers : '— / 35' }}
                    </span>
                    <span class="font-mono text-micro text-success tabular-nums">Shift Pagi</span>
                </div>
                <span class="font-body text-micro text-ink-muted">
                    {{ $morningCheck ? $morningCheck->compliant_volunteers.' dari '.$morningCheck->total_volunteers.' relawan patuh' : 'Belum diperiksa' }}
                </span>
            </article>

            {{-- 6. Temuan terbuka --}}
            <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm">
                <div class="flex items-center justify-between gap-sm">
                    <span class="font-body text-label-sm text-ink-muted">Temuan terbuka</span>
                    <span class="px-sm py-xs rounded-full {{ $openFindings->isNotEmpty() ? 'bg-danger-soft text-on-danger-soft' : 'bg-success-soft text-on-success-soft' }} font-body text-micro">
                        {{ $openFindings->isNotEmpty() ? 'Terbuka' : 'Aman' }}
                    </span>
                </div>
                <div class="flex items-baseline justify-between gap-sm">
                    <span class="font-display text-kpi text-ink tabular-nums">{{ $openFindings->count() }}</span>
                    <span class="font-body text-micro text-on-danger-soft">{{ $openFindings->first()?->type ?? 'Tidak ada' }}</span>
                </div>
                <span class="font-body text-micro text-ink-muted">Menunggu tindak lanjut Kepala Dapur</span>
            </article>
        </section>

        {{-- ROW 2: Perlu tindakan saya + Keluhan aktif (Hanya lihat) --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-lg items-start">
            <div class="lg:col-span-2 bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
                <div class="flex items-center justify-between gap-sm">
                    <div class="flex items-center gap-sm">
                        <span class="material-symbols-outlined text-section-title text-ink" aria-hidden="true">pending_actions</span>
                        <h2 class="font-display text-section-title text-ink">Perlu tindakan saya</h2>
                    </div>
                    <span class="px-sm py-xs rounded-full bg-surface-subtle border border-border-soft font-mono text-micro text-ink tabular-nums">{{ $actionMenus->count() }} menu</span>
                </div>

                <div class="flex flex-col gap-sm">
                    @forelse($actionMenus as $m)
                        @php
                            $badgeClass = match ($m->status) {
                                'minta_revisi' => 'bg-alert-soft text-on-alert-soft',
                                'perlu_revisi_anggaran' => 'bg-danger-soft text-on-danger-soft',
                                default => 'bg-surface-muted text-ink',
                            };
                            $badgeLabel = match ($m->status) {
                                'minta_revisi' => 'Minta Revisi',
                                'perlu_revisi_anggaran' => 'Perlu Revisi Anggaran',
                                default => 'Draf',
                            };
                        @endphp
                        <div class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-wrap items-center justify-between gap-md">
                            <div class="flex flex-col gap-xs min-w-0 flex-1">
                                <div class="flex items-center gap-sm flex-wrap">
                                    <span class="font-body text-card-title text-ink">Menu {{ $m->operational_date?->translatedFormat('d M Y') }}</span>
                                    <span class="px-sm py-xs rounded-full {{ $badgeClass }} font-body text-micro whitespace-nowrap">{{ $badgeLabel }}</span>
                                </div>
                                <p class="font-body text-body-md text-ink-muted">{{ $m->approval_notes }}</p>
                            </div>
                            <a href="{{ route('ahli-gizi.menu.index', ['ubah' => $m->id]) }}" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 whitespace-nowrap">
                                {{ $m->status === 'draf' ? 'Lanjutkan Draf' : 'Ubah Draf' }}
                            </a>
                        </div>
                    @empty
                        <x-empty-state icon="task_alt" title="Semua rencana menu selesai" description="Tidak ada draf atau permintaan revisi yang menunggu tindakan." />
                    @endforelse
                </div>
            </div>

            <aside id="keluhan-aktif" class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
                <div class="flex items-center justify-between gap-sm">
                    <div class="flex items-center gap-sm">
                        <span class="material-symbols-outlined text-section-title text-ink" aria-hidden="true">chat_bubble_outline</span>
                        <h2 class="font-display text-section-title text-ink">Keluhan aktif: {{ $activeComplaints->count() }}</h2>
                    </div>
                    <span class="inline-flex items-center gap-xs px-sm py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink-muted whitespace-nowrap">
                        <span class="material-symbols-outlined text-micro" aria-hidden="true">visibility</span>
                        Hanya lihat
                    </span>
                </div>

                @forelse($activeComplaints as $complaint)
                    <div class="p-md rounded-lg bg-danger-soft/40 border border-danger-soft flex flex-col gap-xs">
                        <div class="flex items-center justify-between gap-sm">
                            <span class="font-body text-card-title text-ink">{{ $complaint->location?->name ?? $complaint->institution?->name }}</span>
                            <span class="px-sm py-xs rounded-full bg-danger-soft text-on-danger-soft font-body text-micro">Bermasalah</span>
                        </div>
                        <p class="font-body text-body-md text-ink-muted">{{ $complaint->description }}</p>
                        <span class="font-body text-micro text-ink-muted">{{ $complaint->reported_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y · H:i') }} WIB</span>
                    </div>
                @empty
                    <x-empty-state icon="check_circle" title="Tidak ada keluhan aktif" description="Seluruh pengiriman berjalan normal." />
                @endforelse

                <p class="font-body text-micro text-ink-muted">Penanganan keluhan dikelola oleh Kepala Dapur dan Asisten Lapangan.</p>
            </aside>
        </section>

        {{-- ROW 3: Ringkasan distribusi hari ini (Hanya lihat) --}}
        <section id="ringkasan-distribusi" aria-label="Ringkasan distribusi hari ini" class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
            <div class="flex flex-wrap items-center justify-between gap-md">
                <div class="flex items-center gap-sm flex-wrap">
                    <span class="material-symbols-outlined text-section-title text-ink" aria-hidden="true">local_shipping</span>
                    <h2 class="font-display text-section-title text-ink">Ringkasan distribusi hari ini</h2>
                    <span class="inline-flex items-center gap-xs px-sm py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink-muted whitespace-nowrap">
                        <span class="material-symbols-outlined text-micro" aria-hidden="true">visibility</span>
                        Hanya lihat
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-sm">
                    <span class="px-md py-xs rounded-full bg-surface-subtle border border-border-soft font-mono text-micro text-ink tabular-nums">Dikirim {{ $fmt($deliveryStats['dikirim']) }}</span>
                    <span class="px-md py-xs rounded-full bg-success-soft text-on-success-soft font-mono text-micro tabular-nums">Diterima {{ $fmt($deliveryStats['diterima']) }}</span>
                    <span class="px-md py-xs rounded-full bg-surface-subtle border border-border-soft font-mono text-micro text-ink-muted tabular-nums">Belum dikonfirmasi {{ $fmt($deliveryStats['belum_dikonfirmasi']) }}</span>
                    <span class="px-md py-xs rounded-full bg-alert-soft text-on-alert-soft font-mono text-micro tabular-nums">Selisih {{ $deliveryStats['selisih'] }}</span>
                </div>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-soft">
                            <th class="py-sm pr-md font-body text-label-sm text-ink-muted">Lokasi penerima</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Target porsi</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted">Status pengiriman</th>
                            <th class="py-sm pl-md font-body text-label-sm text-ink-muted">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        @foreach($routes as $route)
                            @php
                                $statusVal = $route->status->value;
                                $ket = match ($statusVal) {
                                    'Diterima' => $route->confirmed_portions === $route->dispatched_portions
                                        ? 'Diterima lengkap '.$route->confirmed_portions.' porsi'
                                        : 'Terima '.$route->confirmed_portions.' (selisih '.($route->confirmed_portions - $route->dispatched_portions).' porsi)',
                                    'Tiba' => 'Menunggu konfirmasi kader/PIC',
                                    'Berangkat' => 'Dalam perjalanan ke lokasi',
                                    'Bermasalah' => '4 ompreng tumpah saat tiba',
                                    default => 'Sedang dimuat ke kendaraan antar',
                                };
                            @endphp
                            <tr>
                                <td class="py-md pr-md font-body text-card-title text-ink">{{ $route->location?->name }}</td>
                                <td class="py-md px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $fmt($route->dispatched_portions) }}</td>
                                <td class="py-md px-md">
                                    <x-badge-status :label="$route->status->label()" :variant="$route->status->badgeVariant()" />
                                </td>
                                <td class="py-md pl-md font-body text-body-md text-ink-muted">{{ $ket }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Kartu Mobile Distribusi --}}
            <div class="md:hidden flex flex-col gap-sm">
                @foreach($routes as $route)
                    <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-xs">
                        <div class="flex items-center justify-between gap-sm">
                            <span class="font-body text-card-title text-ink">{{ $route->location?->name }}</span>
                            <x-badge-status :label="$route->status->label()" :variant="$route->status->badgeVariant()" />
                        </div>
                        <span class="font-mono text-micro text-ink-muted">{{ $fmt($route->dispatched_portions) }} porsi · {{ $route->waybill_number }}</span>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Modal (Desktop) / Bottom Sheet (Mobile) Ajukan Revisi Menu --}}
        <x-slide-panel open-var="revisiOpen" title="Ajukan revisi menu" subtitle="Menu Disetujui (terkunci) · Persetujuan Kepala Dapur">
            <form method="POST" :action="revisiAction" class="flex flex-col gap-md">
                @csrf
                <div class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col gap-xs">
                    <span class="font-body text-micro text-ink-muted">Menu yang diajukan revisi</span>
                    <p class="font-body text-body-md text-ink" x-text="revisiLabel"></p>
                </div>

                <label class="flex flex-col gap-xs">
                    <span class="font-body text-label-sm text-ink">Usulan perubahan (opsional)</span>
                    <input type="text" name="proposed_change" placeholder="Contoh: Sayur: Buncis → Buncis dan wortel" class="w-full rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                </label>

                <label class="flex flex-col gap-xs">
                    <span class="font-body text-label-sm text-ink">Alasan revisi <span class="text-on-danger-soft">*</span></span>
                    <textarea name="reason" rows="3" required minlength="5" placeholder="Tuliskan alasan perubahan menu yang sudah disetujui..." class="w-full rounded-md border border-border-soft bg-surface px-md py-sm font-body text-body-md text-ink"></textarea>
                </label>

                <div class="p-sm rounded-lg bg-alert-soft/50 border border-alert-soft flex items-center gap-sm">
                    <span class="material-symbols-outlined text-card-title text-on-alert-soft shrink-0" aria-hidden="true">schedule</span>
                    <span class="font-body text-micro text-on-alert-soft">Menunggu persetujuan Kepala Dapur (Hendra Wijaya) setelah diajukan.</span>
                </div>

                <div class="flex items-center justify-end gap-sm pt-md border-t border-border-soft">
                    <button type="button" @click="revisiOpen = false" class="px-lg py-sm rounded-full bg-surface-subtle border border-border-soft font-body text-label-sm text-ink cursor-pointer">Batal</button>
                    <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">Kirim Pengajuan</button>
                </div>
            </form>
        </x-slide-panel>
    </div>
</x-layouts.ahli-gizi>
