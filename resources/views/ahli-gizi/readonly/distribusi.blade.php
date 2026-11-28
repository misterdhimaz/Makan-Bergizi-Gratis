@php
    $fmt = static fn (int|float|null $n): string => number_format((int) $n, 0, ',', '.');
@endphp

<x-layouts.ahli-gizi
    title="Distribusi (Hanya lihat)"
    subtitle="Pemantauan ringkas pengiriman harian SPPG Sungai Selayur 1"
    breadcrumb="Ahli Gizi · Hanya lihat"
>
    <x-slot:actions>
        <span class="inline-flex items-center gap-xs px-md py-xs rounded-full bg-surface border border-border-soft font-body text-micro text-ink-muted">
            <span class="material-symbols-outlined text-label-sm" aria-hidden="true">visibility</span>
            Hanya lihat
        </span>
    </x-slot:actions>

    <section class="grid grid-cols-2 lg:grid-cols-4 gap-md">
        <x-stat-card label="Dikirim" :value="$fmt($stats['dikirim'])" icon="local_shipping" />
        <x-stat-card label="Diterima" :value="$fmt($stats['diterima'])" badge="Terkonfirmasi" badge-variant="success" highlight />
        <x-stat-card label="Belum dikonfirmasi" :value="$fmt($stats['belum_dikonfirmasi'])" icon="hourglass_top" />
        <x-stat-card label="Selisih" :value="(string) $stats['selisih']" icon="difference" />
    </section>

    <section class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
        <h2 class="font-display text-section-title text-ink">Rute Pengiriman Hari Ini</h2>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border-soft">
                        <th class="py-sm pr-md font-body text-label-sm text-ink-muted">No. Surat Jalan</th>
                        <th class="py-sm px-md font-body text-label-sm text-ink-muted">Lokasi Penerima</th>
                        <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Dikirim</th>
                        <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Diterima</th>
                        <th class="py-sm pl-md font-body text-label-sm text-ink-muted">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    @foreach($routes as $route)
                        <tr>
                            <td class="py-md pr-md font-mono text-micro text-ink-muted">{{ $route->waybill_number }}</td>
                            <td class="py-md px-md font-body text-card-title text-ink">{{ $route->location?->name }}</td>
                            <td class="py-md px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $fmt($route->dispatched_portions) }}</td>
                            <td class="py-md px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $route->confirmed_portions === null ? '—' : $fmt($route->confirmed_portions) }}</td>
                            <td class="py-md pl-md"><x-badge-status :label="$route->status->label()" :variant="$route->status->badgeVariant()" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="md:hidden flex flex-col gap-sm">
            @foreach($routes as $route)
                <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-xs">
                    <div class="flex items-center justify-between gap-sm">
                        <span class="font-body text-card-title text-ink">{{ $route->location?->name }}</span>
                        <x-badge-status :label="$route->status->label()" :variant="$route->status->badgeVariant()" />
                    </div>
                    <span class="font-mono text-micro text-ink-muted">{{ $route->waybill_number }} · {{ $fmt($route->dispatched_portions) }} porsi</span>
                </article>
            @endforeach
        </div>
    </section>
</x-layouts.ahli-gizi>
