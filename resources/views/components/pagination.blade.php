@props([
    'paginator',
    'label' => 'data',
])

@if($paginator && $paginator->hasPages())
    <div class="flex flex-wrap items-center justify-between gap-md pt-md border-t border-border-soft">
        <p class="font-body text-micro text-ink-muted tabular-nums">
            Menampilkan {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} dari {{ number_format($paginator->total(), 0, ',', '.') }} {{ $label }}
        </p>

        <nav aria-label="Navigasi halaman" class="flex items-center gap-xs">
            @if($paginator->onFirstPage())
                <span class="px-md py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink-muted opacity-50">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-md py-xs rounded-full bg-surface border border-border-soft font-body text-micro text-ink hover:bg-surface-subtle">Sebelumnya</a>
            @endif

            <span class="px-md py-xs rounded-full bg-obsidian text-on-obsidian font-mono text-micro tabular-nums">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-md py-xs rounded-full bg-surface border border-border-soft font-body text-micro text-ink hover:bg-surface-subtle">Berikutnya</a>
            @else
                <span class="px-md py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink-muted opacity-50">Berikutnya</span>
            @endif
        </nav>
    </div>
@endif
