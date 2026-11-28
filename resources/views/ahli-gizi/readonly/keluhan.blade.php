<x-layouts.ahli-gizi
    title="Keluhan (Hanya lihat)"
    subtitle="Pantauan keluhan dari titik penerima manfaat · Penanganan oleh Kepala Dapur & Asisten Lapangan"
    breadcrumb="Ahli Gizi · Hanya lihat"
>
    <x-slot:actions>
        <span class="inline-flex items-center gap-xs px-md py-xs rounded-full bg-surface border border-border-soft font-body text-micro text-ink-muted">
            <span class="material-symbols-outlined text-label-sm" aria-hidden="true">visibility</span>
            Hanya lihat
        </span>
    </x-slot:actions>

    <section class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
        <h2 class="font-display text-section-title text-ink">Daftar Tiket Keluhan</h2>

        <div class="flex flex-col gap-sm">
            @forelse($complaints as $complaint)
                <article class="p-md rounded-lg border border-border-soft bg-surface-subtle flex flex-col gap-xs">
                    <div class="flex flex-wrap items-center justify-between gap-sm">
                        <div class="flex items-center gap-sm">
                            <span class="font-mono text-micro text-ink-muted">{{ $complaint->ticket_number }}</span>
                            <span class="font-body text-card-title text-ink">{{ $complaint->location?->name ?? $complaint->institution?->name }}</span>
                        </div>
                        <x-badge-status :label="$complaint->status === 'resolved' ? 'Selesai' : 'Terbuka'" :variant="$complaint->status === 'resolved' ? 'success' : 'danger'" />
                    </div>
                    <p class="font-body text-body-md text-ink">{{ $complaint->description }}</p>
                    <span class="font-body text-micro text-ink-muted">{{ $complaint->reported_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y · H:i') }} WIB</span>
                </article>
            @empty
                <x-empty-state icon="check_circle" title="Belum ada keluhan" description="Seluruh pengiriman berlangsung tanpa keluhan." />
            @endforelse
        </div>
    </section>
</x-layouts.ahli-gizi>
