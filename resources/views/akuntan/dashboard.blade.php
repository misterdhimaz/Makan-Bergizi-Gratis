@php
    $firstName = explode(' ', trim((string) (auth()->user()?->name ?? 'Rizky')))[0] ?: 'Rizky';
    $totalTargetPorsi = collect($pagu_rows)->sum('portions');
@endphp

<x-layouts.akuntan
    title="Dashboard Akuntan"
    :subtitle="'Selamat pagi, ' . $firstName . ' · Pemeriksaan kesesuaian anggaran menu SPPG Sungai Selayur 1'"
    breadcrumb="Akuntan · SPPG Sungai Selayur 1"
>
    {{-- Mobile Greeting --}}
    <section class="lg:hidden flex flex-col gap-xs">
        <h1 class="font-display text-page-title text-ink">Selamat pagi, {{ $firstName }}</h1>
        <p class="font-body text-body-md text-ink-muted">Pemeriksaan kesesuaian anggaran menu SPPG Sungai Selayur 1</p>
    </section>

    {{-- 4 KPI Cards --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-md">
        <article class="bg-lime-accent rounded-xl p-lg flex flex-col justify-between gap-sm">
            <div class="flex items-center justify-between gap-sm">
                <span class="font-body text-label-sm text-on-lime">Menunggu pemeriksaan</span>
                <span class="px-sm py-xs rounded-full bg-surface text-ink font-body text-micro">Prioritas</span>
            </div>
            <div class="flex items-baseline justify-between gap-sm">
                <span class="font-display text-kpi text-on-lime tabular-nums">{{ $menunggu }}</span>
                <span class="font-body text-micro text-on-lime">
                    @if ($oldest_waiting)
                        Menu {{ $oldest_waiting->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M') }}
                    @else
                        Tidak ada antrean
                    @endif
                </span>
            </div>
        </article>

        <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm">
            <span class="font-body text-label-sm text-ink-muted">Lolos PAGU</span>
            <div class="flex items-baseline justify-between gap-sm">
                <span class="font-display text-kpi text-ink tabular-nums">{{ $lolos }}</span>
                <span class="font-body text-micro text-on-success-soft">Siap disetujui</span>
            </div>
        </article>

        <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm">
            <span class="font-body text-label-sm text-ink-muted">Perlu Revisi Anggaran</span>
            <div class="flex items-baseline justify-between gap-sm">
                <span class="font-display text-kpi text-ink tabular-nums">{{ $perlu_revisi }}</span>
                <span class="font-body text-micro text-on-danger-soft">Kembali ke Ahli Gizi</span>
            </div>
        </article>

        <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col justify-between gap-sm">
            <span class="font-body text-label-sm text-ink-muted">Parameter PAGU aktif</span>
            <div class="flex items-baseline justify-between gap-sm">
                <span class="font-display text-kpi text-ink tabular-nums">{{ $parameter_count }}</span>
                <span class="font-body text-micro text-ink-muted">
                    {{ $parameter_updated_at ? 'Update ' . $parameter_updated_at->timezone('Asia/Jakarta')->translatedFormat('j M Y') : 'Aktif' }}
                </span>
            </div>
        </article>
    </section>

    {{-- Bento Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-lg items-start">
        <div class="xl:col-span-8 flex flex-col gap-lg">
            {{-- Perlu Diperiksa Sekarang --}}
            <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                <div class="flex items-center justify-between gap-sm">
                    <h2 class="font-display text-section-title text-ink">Perlu diperiksa sekarang</h2>
                    <a href="{{ route('akuntan.validasi.index', ['tab' => 'menunggu']) }}" class="font-body text-label-sm text-ink underline hover:no-underline">
                        Buka Validasi
                    </a>
                </div>

                @if ($oldest_waiting)
                    <article class="rounded-lg p-lg bg-surface-subtle border border-border-soft flex flex-col sm:flex-row sm:items-center justify-between gap-md">
                        <div class="flex flex-col gap-xs min-w-0">
                            <div class="flex flex-wrap items-center gap-sm">
                                <span class="font-mono text-micro text-ink">
                                    {{ $oldest_waiting->operational_date?->timezone('Asia/Jakarta')->translatedFormat('l, j M Y') }}
                                </span>
                                <span class="px-sm py-xs rounded-full bg-alert-soft text-on-alert-soft font-body text-micro">
                                    Menunggu Pemeriksaan Anggaran
                                </span>
                            </div>
                            <h3 class="font-display text-section-title text-ink">{{ $oldest_waiting->nama_menu }}</h3>
                            <p class="font-body text-micro text-ink-muted">
                                {{ $oldest_waiting->categorySummaryLabel() }} · Total {{ number_format($oldest_waiting->target_portions, 0, ',', '.') }} porsi
                            </p>
                            <p class="font-body text-micro text-ink-muted">
                                Diajukan oleh {{ $oldest_waiting->submitter?->name ?? 'Nadia Putri' }}
                                @if ($oldest_waiting->submitted_at)
                                    · {{ $oldest_waiting->submitted_at->timezone('Asia/Jakarta')->translatedFormat('j M H:i') }} WIB
                                @endif
                            </p>
                        </div>
                        <a
                            href="{{ route('akuntan.validasi.index', ['tab' => 'menunggu', 'menu' => $oldest_waiting->id]) }}"
                            class="min-h-[44px] px-lg inline-flex items-center justify-center rounded-full bg-obsidian text-on-obsidian font-body text-label-sm whitespace-nowrap shrink-0"
                        >
                            Periksa Menu
                        </a>
                    </article>
                @else
                    <div class="p-lg rounded-lg bg-surface-subtle border border-border-soft text-center font-body text-body-md text-ink-muted">
                        Semua menu yang diajukan sudah diperiksa.
                    </div>
                @endif
            </section>

            {{-- Ringkasan Parameter PAGU --}}
            <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                <div class="flex items-center justify-between gap-sm">
                    <div>
                        <h2 class="font-display text-section-title text-ink">Ringkasan Parameter PAGU</h2>
                        <p class="font-body text-micro text-ink-muted">Total target porsi {{ number_format($totalTargetPorsi, 0, ',', '.') }} porsi per hari</p>
                    </div>
                    <a href="{{ route('akuntan.parameter-pagu.index') }}" class="font-body text-label-sm text-ink underline hover:no-underline">
                        Kelola Parameter
                    </a>
                </div>

                {{-- Desktop Table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border-soft font-body text-micro text-ink-muted">
                                <th class="py-sm pr-md">Kategori</th>
                                <th class="py-sm pr-md text-right">Target Porsi</th>
                                <th class="py-sm pr-md text-right">Batas PAGU / Porsi</th>
                                <th class="py-sm">Berlaku Sejak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-soft font-body text-body-md">
                            @foreach ($pagu_rows as $row)
                                <tr>
                                    <td class="py-md pr-md text-ink font-medium">{{ $row['category'] }}</td>
                                    <td class="py-md pr-md text-right font-mono tabular-nums text-ink">{{ number_format($row['portions'], 0, ',', '.') }}</td>
                                    <td class="py-md pr-md text-right font-mono tabular-nums text-ink">
                                        Rp {{ number_format($row['nominal'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-md text-ink-muted">
                                        {{ $row['effective_date'] ? \Carbon\Carbon::parse($row['effective_date'])->translatedFormat('j M Y') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="md:hidden flex flex-col gap-sm">
                    @foreach ($pagu_rows as $row)
                        <div class="p-md rounded-lg bg-surface-subtle border border-border-soft flex items-center justify-between gap-sm">
                            <div class="flex flex-col">
                                <span class="font-body text-card-title text-ink">{{ $row['category'] }}</span>
                                <span class="font-body text-micro text-ink-muted">
                                    {{ number_format($row['portions'], 0, ',', '.') }} porsi
                                </span>
                            </div>
                            <span class="font-mono text-card-title text-ink tabular-nums">
                                Rp {{ number_format($row['nominal'], 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Status Menu Minggu Ini --}}
            <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                <div class="flex items-center justify-between gap-sm">
                    <h2 class="font-display text-section-title text-ink">Status Menu Minggu Ini</h2>
                    <a href="{{ route('akuntan.laporan.index') }}" class="font-body text-label-sm text-ink underline hover:no-underline">
                        Lihat Laporan
                    </a>
                </div>

                {{-- Desktop Table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border-soft font-body text-micro text-ink-muted">
                                <th class="py-sm pr-md">Tanggal</th>
                                <th class="py-sm pr-md">Menu</th>
                                <th class="py-sm pr-md">Kategori &amp; Porsi</th>
                                <th class="py-sm pr-md">Status</th>
                                <th class="py-sm text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-soft font-body text-body-md">
                            @foreach ($menus as $menu)
                                @php $status = $menu->menuStatus(); @endphp
                                <tr>
                                    <td class="py-md pr-md font-mono text-micro text-ink whitespace-nowrap">
                                        {{ $menu->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M') }}
                                    </td>
                                    <td class="py-md pr-md text-ink">{{ $menu->nama_menu }}</td>
                                    <td class="py-md pr-md text-ink-muted whitespace-nowrap">
                                        {{ $menu->categorySummaryLabel() }} · {{ number_format($menu->target_portions, 0, ',', '.') }}
                                    </td>
                                    <td class="py-md pr-md whitespace-nowrap">
                                        <span class="px-sm py-xs rounded-full font-body text-micro {{ $status->badgeClasses() }}">
                                            {{ $status->displayLabel() }}
                                        </span>
                                    </td>
                                    <td class="py-md text-right whitespace-nowrap">
                                        <a
                                            href="{{ route('akuntan.validasi.index', ['tab' => $status === \App\Enums\MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN ? 'menunggu' : 'semua', 'menu' => $menu->id]) }}"
                                            class="font-body text-label-sm text-ink underline hover:no-underline"
                                        >
                                            {{ $status === \App\Enums\MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN ? 'Periksa' : 'Detail' }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="md:hidden flex flex-col gap-sm">
                    @foreach ($menus as $menu)
                        @php $status = $menu->menuStatus(); @endphp
                        <a
                            href="{{ route('akuntan.validasi.index', ['tab' => $status === \App\Enums\MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN ? 'menunggu' : 'semua', 'menu' => $menu->id]) }}"
                            class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col gap-xs"
                        >
                            <div class="flex items-center justify-between gap-sm">
                                <span class="font-mono text-micro text-ink">
                                    {{ $menu->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M Y') }}
                                </span>
                                <span class="px-sm py-xs rounded-full font-body text-micro {{ $status->badgeClasses() }}">
                                    {{ $status->displayLabel() }}
                                </span>
                            </div>
                            <span class="font-body text-card-title text-ink">{{ $menu->nama_menu }}</span>
                            <span class="font-body text-micro text-ink-muted">
                                {{ $menu->categorySummaryLabel() }} · {{ number_format($menu->target_portions, 0, ',', '.') }} porsi
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- Right Column: Aktivitas Saya --}}
        <aside class="xl:col-span-4 bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
            <h2 class="font-display text-section-title text-ink">Aktivitas Saya</h2>
            <div class="flex flex-col gap-sm">
                @forelse ($activities as $activity)
                    <div class="p-sm rounded-md bg-surface-subtle border border-border-soft flex flex-col gap-xs">
                        <span class="font-mono text-micro text-ink-muted">
                            {{ $activity->occurred_at?->timezone('Asia/Jakarta')->translatedFormat('j M H:i') }}
                        </span>
                        <span class="font-body text-body-md text-ink">{{ $activity->action }}</span>
                    </div>
                @empty
                    <p class="font-body text-body-md text-ink-muted">Belum ada aktivitas pemeriksaan PAGU.</p>
                @endforelse
            </div>
        </aside>
    </div>
</x-layouts.akuntan>
