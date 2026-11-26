@props(['title', 'meta' => null, 'href' => null, 'cta' => 'Lihat detail'])

<article {{ $attributes->class(['ui-task-row flex min-w-0 flex-col gap-3 border-b border-slate-200 py-4 text-sm last:border-b-0 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div class="min-w-0">
        <h3 class="text-sm font-semibold text-brand-navy">{{ $title }}</h3>
        @if ($meta !== null)
            <p class="mt-1 text-xs text-slate-600">{{ $meta }}</p>
        @endif
        {{ $slot }}
    </div>
    @if ($href !== null)
        <x-ui.button variant="secondary" :href="$href">{{ $cta }}</x-ui.button>
    @endif
</article>
