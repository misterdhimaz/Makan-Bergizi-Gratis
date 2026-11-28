@php
    $fmt = static fn (int|float|null $n): string => number_format((int) $n, 0, ',', '.');
@endphp

<x-layouts.ahli-gizi
    title="Laporan (Hanya lihat)"
    subtitle="Ringkasan rekap menu dan pemorsian harian SPPG Sungai Selayur 1"
    breadcrumb="Ahli Gizi · Hanya lihat"
>
    <x-slot:actions>
        <span class="inline-flex items-center gap-xs px-md py-xs rounded-full bg-surface border border-border-soft font-body text-micro text-ink-muted">
            <span class="material-symbols-outlined text-label-sm" aria-hidden="true">visibility</span>
            Hanya lihat
        </span>
    </x-slot:actions>

    <section class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
        <h2 class="font-display text-section-title text-ink">Rekap Menu &amp; Target Porsi</h2>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border-soft">
                        <th class="py-sm pr-md font-body text-label-sm text-ink-muted">Tanggal</th>
                        <th class="py-sm px-md font-body text-label-sm text-ink-muted">Menu</th>
                        <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Target Porsi</th>
                        <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Ompreng Terisi</th>
                        <th class="py-sm pl-md font-body text-label-sm text-ink-muted">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    @foreach($menus as $m)
                        <tr>
                            <td class="py-md pr-md font-mono text-label-sm text-ink">{{ $m->operational_date?->translatedFormat('d M Y') }}</td>
                            <td class="py-md px-md font-body text-card-title text-ink">{{ $m->menu_name }}</td>
                            <td class="py-md px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $fmt($m->target_portions) }}</td>
                            <td class="py-md px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $m->productionRun ? $fmt($m->productionRun->actual_good_portions) : '—' }}</td>
                            <td class="py-md pl-md font-body text-micro text-ink-muted">{{ $m->status_label }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="md:hidden flex flex-col gap-sm">
            @foreach($menus as $m)
                <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-xs">
                    <span class="font-mono text-micro text-ink-muted">{{ $m->operational_date?->translatedFormat('d M Y') }} · {{ $fmt($m->target_portions) }} porsi</span>
                    <span class="font-body text-card-title text-ink">{{ $m->menu_name }}</span>
                    <span class="font-body text-micro text-ink-muted">{{ $m->status_label }}</span>
                </article>
            @endforeach
        </div>
    </section>
</x-layouts.ahli-gizi>
