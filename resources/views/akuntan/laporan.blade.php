<x-layouts.akuntan
    title="Laporan Pemeriksaan PAGU"
    subtitle="Ringkasan status pemeriksaan anggaran menu (hanya lihat)."
    breadcrumb="Akuntan · Laporan Pemeriksaan PAGU (Hanya lihat)"
>
    {{-- Filter Bar --}}
    <form
        method="GET"
        action="{{ route('akuntan.laporan.index') }}"
        class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col md:flex-row md:items-end justify-between gap-md"
    >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-md flex-1">
            <label class="flex flex-col gap-xs">
                <span class="font-body text-label-sm text-ink">Periode</span>
                <select
                    name="periode"
                    onchange="this.form.submit()"
                    class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink"
                >
                    <option value="minggu_ini" @selected($periode === 'minggu_ini')>Minggu ini (23 – 29 Nov 2026)</option>
                    <option value="bulan_ini" @selected($periode === 'bulan_ini')>Bulan ini (Nov 2026)</option>
                    <option value="semua" @selected($periode === 'semua')>Semua periode</option>
                </select>
            </label>

            <label class="flex flex-col gap-xs">
                <span class="font-body text-label-sm text-ink">Status</span>
                <select
                    name="status"
                    onchange="this.form.submit()"
                    class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink"
                >
                    <option value="semua" @selected($statusFilter === 'semua')>Semua status</option>
                    <option value="menunggu_pemeriksaan_anggaran" @selected($statusFilter === 'menunggu_pemeriksaan_anggaran')>Menunggu Pemeriksaan Anggaran</option>
                    <option value="lolos_pagu" @selected($statusFilter === 'lolos_pagu')>Lolos PAGU</option>
                    <option value="perlu_revisi_anggaran" @selected($statusFilter === 'perlu_revisi_anggaran')>Perlu Revisi Anggaran</option>
                    <option value="disetujui" @selected($statusFilter === 'disetujui')>Disetujui (terkunci)</option>
                    <option value="minta_revisi" @selected($statusFilter === 'minta_revisi')>Minta Revisi</option>
                </select>
            </label>
        </div>

        <span class="px-md py-sm rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink-muted self-start md:self-end">
            Mode Hanya Lihat
        </span>
    </form>

    {{-- 4 Summary Cards --}}
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-md">
        <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-xs">
            <span class="font-body text-micro text-ink-muted">Menu diajukan</span>
            <span class="font-display text-kpi text-ink tabular-nums">{{ $diajukanCount }}</span>
        </article>

        <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-xs">
            <span class="font-body text-micro text-ink-muted">Lolos PAGU</span>
            <span class="font-display text-kpi text-on-success-soft tabular-nums">{{ $lolosCount }}</span>
        </article>

        <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-xs">
            <span class="font-body text-micro text-ink-muted">Perlu Revisi Anggaran</span>
            <span class="font-display text-kpi text-on-danger-soft tabular-nums">{{ $revisiCount }}</span>
        </article>

        <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-xs">
            <span class="font-body text-micro text-ink-muted">Menunggu</span>
            <span class="font-display text-kpi text-on-alert-soft tabular-nums">{{ $menungguCount }}</span>
        </article>
    </section>

    {{-- Desktop Table --}}
    <section class="hidden md:block bg-surface rounded-xl p-lg border border-border-soft overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-border-soft font-body text-micro text-ink-muted">
                    <th class="py-sm pr-md">Tanggal</th>
                    <th class="py-sm pr-md">Nama Menu</th>
                    <th class="py-sm pr-md">Kategori &amp; Porsi</th>
                    <th class="py-sm pr-md">Hasil PAGU</th>
                    <th class="py-sm pr-md">Pemeriksa &amp; Waktu</th>
                    <th class="py-sm pr-md">Catatan</th>
                    <th class="py-sm">Persetujuan Kepala Dapur</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-soft font-body text-body-md">
                @forelse ($menus as $menu)
                    @php
                        $status = $menu->menuStatus();
                        $latestCheck = $menu->paguChecks->first();
                    @endphp
                    <tr>
                        <td class="py-md pr-md font-mono text-micro text-ink whitespace-nowrap">
                            {{ $menu->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M Y') }}
                        </td>
                        <td class="py-md pr-md text-ink">{{ $menu->nama_menu }}</td>
                        <td class="py-md pr-md text-ink-muted whitespace-nowrap">
                            {{ $menu->categorySummaryLabel() }} · {{ number_format($menu->target_portions, 0, ',', '.') }}
                        </td>
                        <td class="py-md pr-md whitespace-nowrap">
                            @if ($latestCheck)
                                <span @class([
                                    'px-sm py-xs rounded-full font-body text-micro',
                                    'bg-success-soft text-on-success-soft' => $latestCheck->hasil === \App\Enums\PaguCheckResult::LOLOS,
                                    'bg-danger-soft text-on-danger-soft' => $latestCheck->hasil === \App\Enums\PaguCheckResult::PERLU_REVISI,
                                ])>
                                    {{ $latestCheck->hasil->label() }}
                                </span>
                            @else
                                <span class="px-sm py-xs rounded-full bg-alert-soft text-on-alert-soft font-body text-micro">
                                    Menunggu
                                </span>
                            @endif
                        </td>
                        <td class="py-md pr-md font-mono text-micro text-ink-muted whitespace-nowrap">
                            @if ($latestCheck)
                                {{ $latestCheck->accountant?->name ?? 'Rizky Pratama' }} · {{ $latestCheck->checked_at?->timezone('Asia/Jakarta')->translatedFormat('j M H:i') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="py-md pr-md text-ink-muted">
                            {{ $latestCheck?->catatan ?? '-' }}
                        </td>
                        <td class="py-md whitespace-nowrap">
                            <span class="px-sm py-xs rounded-full font-body text-micro {{ $status->badgeClasses() }}">
                                {{ $status->displayLabel() }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-xl text-center text-ink-muted">
                            Tidak ada data menu pada periode dan status yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    {{-- Mobile Stacked Cards --}}
    <section class="md:hidden flex flex-col gap-sm">
        @forelse ($menus as $menu)
            @php
                $status = $menu->menuStatus();
                $latestCheck = $menu->paguChecks->first();
            @endphp
            <article class="bg-surface rounded-xl p-md border border-border-soft flex flex-col gap-xs">
                <div class="flex items-center justify-between gap-xs">
                    <span class="font-mono text-micro text-ink">
                        {{ $menu->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M') }}
                        · {{ $menu->categorySummaryLabel() }} · {{ number_format($menu->target_portions, 0, ',', '.') }}
                    </span>
                    <span class="px-sm py-xs rounded-full font-body text-micro whitespace-nowrap {{ $status->badgeClasses() }}">
                        {{ $status->displayLabel() }}
                    </span>
                </div>
                <span class="font-body text-card-title text-ink">{{ $menu->nama_menu }}</span>
                @if ($latestCheck)
                    <span class="font-mono text-micro text-ink-muted">
                        {{ $latestCheck->checked_at?->timezone('Asia/Jakarta')->translatedFormat('j M H:i') }} · {{ $latestCheck->catatan }}
                    </span>
                @else
                    <span class="font-body text-micro text-ink-muted">Belum diperiksa Akuntan</span>
                @endif
            </article>
        @empty
            <div class="bg-surface rounded-xl p-lg border border-border-soft text-center font-body text-body-md text-ink-muted">
                Tidak ada data menu pada periode ini.
            </div>
        @endforelse
    </section>
</x-layouts.akuntan>
