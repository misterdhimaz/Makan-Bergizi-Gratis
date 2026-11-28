<x-layouts.ahli-gizi
    title="Data Master (Hanya lihat)"
    subtitle="Referensi lokasi, kategori, standar porsi, daftar bahan, pemasok, dan SOP BGN"
    breadcrumb="Ahli Gizi · Hanya lihat"
>
    <x-slot:actions>
        <span class="inline-flex items-center gap-xs px-md py-xs rounded-full bg-surface border border-border-soft font-body text-micro text-ink-muted">
            <span class="material-symbols-outlined text-label-sm" aria-hidden="true">visibility</span>
            Hanya lihat
        </span>
    </x-slot:actions>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
        <section class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
            <h2 class="font-display text-section-title text-ink">Lokasi Penerima ({{ $locations->count() }})</h2>
            <ul class="divide-y divide-border-soft">
                @foreach($locations as $loc)
                    <li class="py-sm flex items-center justify-between gap-sm">
                        <span class="font-body text-body-md text-ink">{{ $loc->name }}</span>
                        <span class="font-mono text-micro text-ink-muted tabular-nums">{{ number_format($loc->target_portions, 0, ',', '.') }} porsi</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
            <h2 class="font-display text-section-title text-ink">Daftar Bahan Aktif ({{ $ingredients->count() }})</h2>
            <ul class="divide-y divide-border-soft">
                @foreach($ingredients as $ing)
                    <li class="py-sm flex items-center justify-between gap-sm">
                        <span class="font-body text-body-md text-ink">{{ $ing->name }}</span>
                        <span class="font-body text-micro text-ink-muted">{{ $ing->category }} · {{ $ing->unit }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
            <h2 class="font-display text-section-title text-ink">Standar Porsi ({{ $portions->count() }})</h2>
            <ul class="divide-y divide-border-soft">
                @foreach($portions as $std)
                    <li class="py-sm flex items-center justify-between gap-sm">
                        <span class="font-body text-body-md text-ink">{{ $std->recipientCategory?->name }} · {{ $std->meal_component }}</span>
                        <span class="font-mono text-micro text-ink-muted">{{ (int) $std->quantity }} {{ $std->unit }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
            <h2 class="font-display text-section-title text-ink">Checklist SOP BGN ({{ $checklists->count() }})</h2>
            <ul class="divide-y divide-border-soft">
                @foreach($checklists as $sop)
                    <li class="py-sm flex items-center justify-between gap-sm">
                        <span class="font-body text-body-md text-ink">{{ $sop->title }}</span>
                        <span class="font-mono text-micro text-ink-muted">{{ $sop->threshold_value }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-layouts.ahli-gizi>
