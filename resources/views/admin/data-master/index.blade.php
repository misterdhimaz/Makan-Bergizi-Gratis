<x-layouts.admin
    title="Ringkasan Data Master"
    subtitle="Status kelengkapan referensi operasional SPPG Sungai Selayur 1"
    breadcrumb="Administrator Sistem · Data Master"
>
    <section aria-label="Status kelengkapan" class="grid grid-cols-1 sm:grid-cols-3 gap-md">
        <x-stat-card
            label="Kelengkapan Data Master"
            :value="$completeness['siap_count'].' dari '.$completeness['total']"
            :badge="$completeness['siap_count'] === $completeness['total'] ? 'Siap penuh' : 'Perlu dilengkapi'"
            :badge-variant="$completeness['siap_count'] === $completeness['total'] ? 'success' : 'warning'"
            highlight
        />
        <x-stat-card
            label="Komponen Siap"
            :value="(string) $completeness['siap_count']"
            icon="check_circle"
        />
        <x-stat-card
            label="Belum Lengkap"
            :value="(string) ($completeness['total'] - $completeness['siap_count'])"
            icon="pending_actions"
        />
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-md">
        @foreach($completeness['items'] as $item)
            <article class="bg-surface rounded-xl border border-border-soft p-lg flex flex-col justify-between gap-md">
                <div class="flex flex-col gap-xs">
                    <div class="flex items-center justify-between gap-sm">
                        <h2 class="font-display text-card-title text-ink">{{ $item['label'] }}</h2>
                        <x-badge-status
                            :variant="$item['complete'] ? 'success' : 'warning'"
                            :label="$item['complete'] ? 'Siap' : 'Belum Lengkap'"
                        />
                    </div>
                    <p class="font-body text-body-md text-ink-muted">{{ $item['detail'] }}</p>
                </div>

                <div>
                    <a
                        href="{{ $routeMap[$item['key']] ?? '#' }}"
                        class="inline-flex items-center gap-xs px-md py-xs rounded-full bg-obsidian text-on-obsidian font-body text-micro font-semibold hover:opacity-90"
                    >
                        Kelola {{ $item['label'] }}
                        <span class="material-symbols-outlined text-body-md" aria-hidden="true">arrow_forward</span>
                    </a>
                </div>
            </article>
        @endforeach
    </section>
</x-layouts.admin>
